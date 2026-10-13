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
