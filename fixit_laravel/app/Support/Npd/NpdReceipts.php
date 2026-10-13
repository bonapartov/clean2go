<?php

namespace App\Support\Npd;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\NpdReceipt;
use App\Models\User;
use App\Support\ExecutorDisclosure;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Throwable;

/**
 * Receipts of self-employed executors («Мой налог») — agency model B2C,
 * ROADMAP Sprint 8. The executor earns the money, so the receipt is in the
 * executor's name; the platform only records it and sends it through a
 * channel.
 *
 * - Booking paid (payment_status → COMPLETED): one receipt per executor on
 *   НПД (self_employed, ip_on_npd) — the sub-bookings if any, else the
 *   booking itself. Amount = the booking's subtotal (service incl. coupon
 *   discount; platform fee and insurance are not the executor's income).
 * - Payment refunded/cancelled: registered receipts are cancelled
 *   («аннулирование»), unsent ones are dropped.
 * - Failed sends stay "failed" and are retried: `php artisan npd:receipts-retry`
 *   (scheduled) or the button in admin.
 *
 * Settings: admin «Верификация РФ → Интеграции РФ → Чеки самозанятых»
 * (integration_settings npd_receipts_enabled / npd_receipts_channel).
 * Own code (not Fixit).
 */
class NpdReceipts
{
    public const NPD_STATUSES = ['self_employed', 'ip_on_npd'];

    public const CHANNELS = [
        'mock' => 'Тест — ничего не отправляется',
        'fns_partner' => 'ФНС, API партнёров — после договора ООО с ФНС',
        'payout_provider' => 'Провайдер выплат (банк / ЮKassa) — после выбора банка',
    ];

    public static function enabled(): bool
    {
        return IntegrationSetting::get('npd_receipts_enabled', '0') === '1';
    }

    public static function channelKey(): string
    {
        $key = (string) IntegrationSetting::get('npd_receipts_channel', 'mock');

        return array_key_exists($key, self::CHANNELS) ? $key : 'mock';
    }

    public static function channel(?string $key = null): NpdReceiptChannel
    {
        return match ($key ?? self::channelKey()) {
            'mock' => new MockChannel,
            'fns_partner' => new UnavailableChannel('Канал «ФНС, API партнёров» ещё не подключён: нужен договор ООО с ФНС и документация API.'),
            default => new UnavailableChannel('Канал «Провайдер выплат» ещё не подключён: ждёт выбора банка (ROADMAP, Sprint 9).'),
        };
    }

    /** Called from Booking::saved when payment_status changes. */
    public static function onPaymentStatusChanged(Booking $booking): void
    {
        if (! self::enabled()) {
            return;
        }

        $status = strtoupper((string) $booking->payment_status);
        if ($status === PaymentStatus::COMPLETED) {
            self::issueFor($booking);
        } elseif (in_array($status, [PaymentStatus::REFUNDED, PaymentStatus::CANCELLED], true)) {
            self::cancelFor($booking, $status === PaymentStatus::REFUNDED ? 'Возврат средств' : 'Отмена оплаты');
        }
    }

    /** @return list<NpdReceipt> */
    public static function issueFor(Booking $booking): array
    {
        $receipts = [];
        foreach (self::billable($booking) as $b) {
            $executor = $b->provider_id ? User::find($b->provider_id) : null;
            if (! $executor) {
                continue;
            }
            $info = $b->executor_snapshot ?: ExecutorDisclosure::forProvider($executor);
            if (! in_array($info['status'] ?? null, self::NPD_STATUSES, true) || empty($info['inn'])) {
                continue;
            }

            $receipt = NpdReceipt::firstOrCreate(
                ['booking_id' => $b->id, 'executor_id' => $executor->id],
                [
                    'executor_inn' => $info['inn'],
                    'service_name' => mb_substr(self::serviceName($b), 0, 255),
                    'amount' => self::amount($b),
                    'operation_time' => now(),
                    'status' => 'pending',
                    'channel' => self::channelKey(),
                ]
            );
            if ($receipt->wasRecentlyCreated) {
                // НПД income limit: warn the executor when it gets close
                self::checkLimit($receipt);
            }
            if (in_array($receipt->status, ['pending', 'failed'], true)) {
                self::send($receipt);
            }
            $receipts[] = $receipt->fresh();
        }

        return $receipts;
    }

    public static function cancelFor(Booking $booking, string $reason): void
    {
        $ids = array_map(fn ($b) => $b->id, self::billable($booking));
        foreach (NpdReceipt::whereIn('booking_id', $ids)->whereNotIn('status', ['cancelled'])->get() as $receipt) {
            if (in_array($receipt->status, ['pending', 'failed'], true)) {
                // Never reached ФНС — nothing to annul.
                $receipt->update(['status' => 'cancelled', 'cancel_reason' => $reason, 'cancelled_at' => now()]);

                continue;
            }
            $receipt->update(['status' => 'cancel_pending', 'cancel_reason' => $reason]);
            self::sendCancel($receipt);
        }
    }

    /** Sends one receipt through its channel; failures are kept for retry. */
    public static function send(NpdReceipt $receipt): NpdReceipt
    {
        try {
            $result = self::channel($receipt->channel)->register($receipt);
            $receipt->update([
                'status' => 'registered',
                'external_id' => $result['external_id'],
                'receipt_url' => $result['receipt_url'] ?? null,
                'registered_at' => now(),
                'attempts' => $receipt->attempts + 1,
                'last_error' => null,
            ]);
            self::notifyClient($receipt);
        } catch (Throwable $e) {
            $receipt->update(['status' => 'failed', 'attempts' => $receipt->attempts + 1, 'last_error' => mb_substr($e->getMessage(), 0, 1000)]);
        }

        return $receipt;
    }

    public static function sendCancel(NpdReceipt $receipt): NpdReceipt
    {
        try {
            self::channel($receipt->channel)->cancel($receipt, (string) $receipt->cancel_reason);
            $receipt->update(['status' => 'cancelled', 'cancelled_at' => now(), 'last_error' => null]);
        } catch (Throwable $e) {
            $receipt->update(['last_error' => mb_substr($e->getMessage(), 0, 1000)]);
        }

        return $receipt;
    }

    /** Retries failed sends and stuck cancellations. Uses the channel selected now. */
    public static function retry(): array
    {
        $done = ['sent' => 0, 'cancelled' => 0, 'still_failing' => 0];
        foreach (NpdReceipt::where('status', 'failed')->get() as $receipt) {
            $receipt->update(['channel' => self::channelKey()]);
            self::send($receipt)->status === 'registered' ? $done['sent']++ : $done['still_failing']++;
        }
        foreach (NpdReceipt::where('status', 'cancel_pending')->get() as $receipt) {
            self::sendCancel($receipt)->status === 'cancelled' ? $done['cancelled']++ : $done['still_failing']++;
        }

        return $done;
    }

    /**
     * Executor's income on НПД through the platform in a calendar year:
     * every receipt that is not annulled (registered, being sent, failed).
     */
    public static function yearIncome(int $executorId, ?int $year = null): float
    {
        $year ??= (int) now()->year;

        return round((float) NpdReceipt::where('executor_id', $executorId)
            ->where('status', '!=', 'cancelled')
            ->whereYear('operation_time', $year)
            ->sum('amount'), 2);
    }

    /** @return array{limit:float, warn_at:float} */
    public static function limits(): array
    {
        $limit = (float) IntegrationSetting::get('npd_limit_amount', '2400000');
        $percent = (float) IntegrationSetting::get('npd_limit_warn_percent', '80');

        return ['limit' => $limit, 'warn_at' => round($limit * $percent / 100, 2)];
    }

    /**
     * After a new receipt: tell the executor when they cross the warning
     * threshold or the limit (422-ФЗ: 2.4 mln ₽ a year; over it they must
     * switch to another tax regime). Income here counts only what went
     * through Clean2Go — other income is unknown to us, the message says so.
     */
    public static function checkLimit(NpdReceipt $receipt): ?string
    {
        ['limit' => $limit, 'warn_at' => $warnAt] = self::limits();
        if ($limit <= 0) {
            return null;
        }
        $after = self::yearIncome($receipt->executor_id, (int) $receipt->operation_time->year);
        $before = $after - $receipt->amount;

        $level = match (true) {
            $before < $limit && $after >= $limit => 'over',
            $before < $warnAt && $after >= $warnAt => 'warn',
            default => null,
        };
        if (! $level) {
            return null;
        }

        $sum = number_format($after, 0, ',', ' ').' ₽ из '.number_format($limit, 0, ',', ' ').' ₽';
        \App\Helpers\Helpers::pushNotification(['message' => [
            'topic' => 'user_'.$receipt->executor_id,
            'notification' => [
                'title' => $level === 'over' ? 'Превышен лимит дохода самозанятого' : 'Доход приближается к лимиту НПД',
                'body' => $level === 'over'
                    ? "Через Clean2Go в этом году: $sum. Для дальнейшей работы нужно сменить налоговый режим (ИП)."
                    : "Через Clean2Go в этом году: $sum. Учитывайте доход и вне платформы.",
            ],
            'data' => ['type' => 'npd_limit', 'level' => $level],
        ]]);

        return $level;
    }

    /**
     * Executors whose income through the platform this year reached the
     * warning threshold — for the admin page.
     *
     * @return list<array{executor_id:int, name:?string, inn:string, income:float, percent:float}>
     */
    public static function nearLimit(): array
    {
        ['limit' => $limit, 'warn_at' => $warnAt] = self::limits();
        if ($limit <= 0) {
            return [];
        }

        return NpdReceipt::query()
            ->selectRaw('executor_id, MAX(executor_inn) as inn, SUM(amount) as income')
            ->where('status', '!=', 'cancelled')
            ->whereYear('operation_time', now()->year)
            ->groupBy('executor_id')
            ->havingRaw('SUM(amount) >= ?', [$warnAt])
            ->orderByDesc('income')
            ->with('executor:id,name')
            ->get()
            ->map(fn ($r) => [
                'executor_id' => (int) $r->executor_id,
                'name' => $r->executor?->name,
                'inn' => (string) $r->inn,
                'income' => round((float) $r->income, 2),
                'percent' => round((float) $r->income / $limit * 100, 1),
            ])->all();
    }

    public const CLIENT_STATUS = [
        'pending' => 'Формируется',
        'failed' => 'Формируется',
        'registered' => 'Выдан',
        'cancel_pending' => 'Аннулируется',
        'cancelled' => 'Аннулирован',
    ];

    /**
     * Receipts the client sees for a booking (its sub-bookings included):
     * by law the self-employed must hand the receipt to the buyer.
     * Test receipts (mock channel) are marked is_test.
     *
     * @return list<array>
     */
    public static function forClient(Booking $booking): array
    {
        $ids = array_map(fn ($b) => $b->id, self::billable($booking));
        if ($booking->parent_id) {
            $ids[] = $booking->id;
        }

        return NpdReceipt::with('executor:id,name')->whereIn('booking_id', array_unique($ids))->orderBy('id')->get()
            ->map(fn (NpdReceipt $r) => [
                'id' => $r->id,
                'executor_name' => $r->executor?->name,
                'executor_inn' => $r->executor_inn,
                'service_name' => $r->service_name,
                'amount' => $r->amount,
                'status' => $r->status,
                'status_label' => self::CLIENT_STATUS[$r->status] ?? $r->status,
                'number' => $r->status === 'registered' || $r->status === 'cancelled' ? $r->external_id : null,
                'url' => $r->receipt_url,
                'registered_at' => $r->registered_at?->toIso8601String(),
                'is_test' => $r->channel === 'mock',
            ])->all();
    }

    /** Push to the client once a real (non-test) receipt is registered. */
    private static function notifyClient(NpdReceipt $receipt): void
    {
        if ($receipt->channel === 'mock') {
            return;
        }
        $booking = $receipt->booking;
        $consumerId = $booking?->parent?->consumer_id ?? $booking?->consumer_id;
        if (! $consumerId) {
            return;
        }

        \App\Helpers\Helpers::pushNotification(['message' => [
            'topic' => 'user_'.$consumerId,
            'notification' => [
                'title' => 'Чек от исполнителя',
                'body' => 'Заказ №'.$booking->booking_number.': '.number_format($receipt->amount, 2, ',', ' ').' ₽',
            ],
            'data' => [
                'type' => 'booking',
                'booking_id' => (string) ($booking->parent_id ?: $booking->id),
                'npd_receipt_id' => (string) $receipt->id,
            ],
        ]]);
    }

    /** @return list<Booking> */
    private static function billable(Booking $booking): array
    {
        $children = $booking->sub_bookings()->get();

        return $children->isNotEmpty() ? $children->all() : [$booking];
    }

    private static function amount(Booking $b): float
    {
        if ($b->subtotal !== null) {
            return round((float) $b->subtotal, 2);
        }

        return round((float) $b->total - (float) $b->platform_fees - (float) $b->insurance_amount, 2);
    }

    private static function serviceName(Booking $b): string
    {
        $title = $b->service?->title;

        return $title ? (string) $title : 'Услуга по заказу №'.$b->booking_number;
    }
}
