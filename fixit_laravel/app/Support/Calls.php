<?php

namespace App\Support;

use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\Call;
use App\Models\User;

/**
 * App-to-app calls over our own LiveKit server (infra/livekit). Replaces
 * Agora, a foreign service (decided 2026-10-13).
 *
 * Settings: admin → Настройки → Сторонние сервисы → «Звонки» (settings
 * group `calls`: status, livekit_url, api_key, api_secret). The key pair
 * must match LIVEKIT_KEYS of the LiveKit server. Only `calls.status`
 * reaches /api/settings; url/key/secret are in SettingController::SECRET_KEYS.
 *
 * Who may call whom: only people sharing a booking — the client and the
 * executor (provider or an assigned serviceman) of that booking, its parent
 * or its sub-bookings. No calls between strangers.
 *
 * Own code (not Fixit).
 */
class Calls
{
    /** @return array{status:bool, livekit_url:string, api_key:string, api_secret:string} */
    public static function settings(): array
    {
        $calls = Helpers::getSettings()['calls'] ?? [];

        return [
            'status' => (bool) ($calls['status'] ?? false),
            'livekit_url' => (string) ($calls['livekit_url'] ?? ''),
            'api_key' => (string) ($calls['api_key'] ?? ''),
            'api_secret' => (string) ($calls['api_secret'] ?? ''),
        ];
    }

    public static function isEnabled(): bool
    {
        $s = self::settings();

        return $s['status'] && $s['livekit_url'] !== '' && $s['api_key'] !== '' && $s['api_secret'] !== '';
    }

    /**
     * Ids this user answers calls for: themselves plus their shadow
     * serviceman (a freelancer's own serviceman account — same person,
     * same phone; see Helpers::createShadowServiceman).
     */
    public static function identitiesOf(User $user): array
    {
        $shadowIds = User::where('provider_id', $user->id)->where('is_shadow', true)->pluck('id')->all();

        return array_values(array_unique(array_map('intval', array_merge([$user->id], $shadowIds))));
    }

    /** User ids taking part in a booking (client, executor, servicemen), parent and sub-bookings included. */
    public static function bookingParticipants(Booking $booking): array
    {
        $family = collect([$booking]);
        if ($booking->parent_id && $booking->parent) {
            $family->push($booking->parent);
        }
        $family = $family->merge($booking->sub_bookings()->get());
        if ($booking->parent) {
            $family = $family->merge($booking->parent->sub_bookings()->get());
        }

        $ids = [];
        foreach ($family as $b) {
            $ids[] = $b->consumer_id;
            $ids[] = $b->provider_id;
            foreach ($b->servicemen()->pluck('users.id') as $servicemanId) {
                $ids[] = $servicemanId;
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /**
     * The booking that lets these two talk, or null.
     * With $bookingId only that booking counts; otherwise the latest shared one.
     */
    public static function sharedBooking(User $a, User $b, ?int $bookingId = null): ?Booking
    {
        if ($a->id === $b->id) {
            return null;
        }

        $candidates = $bookingId
            ? Booking::whereKey($bookingId)->get()
            : Booking::query()
                ->where(fn ($q) => $q->whereIn('consumer_id', [$a->id, $b->id])->orWhereIn('provider_id', [$a->id, $b->id]))
                ->latest('id')->limit(50)->get();

        foreach ($candidates as $booking) {
            $participants = self::bookingParticipants($booking);
            if (in_array((int) $a->id, $participants, true) && in_array((int) $b->id, $participants, true)) {
                return $booking;
            }
        }

        return null;
    }

    /** Access to the call's room for one of its two participants. */
    public static function joinInfo(Call $call, User $user): array
    {
        $s = self::settings();

        return [
            'livekit_url' => $s['livekit_url'],
            'token' => LiveKitToken::forRoom($s['api_key'], $s['api_secret'], $call->room, 'user_'.$user->id, (string) $user->name),
        ];
    }

    /** Incoming-call push to the callee (topic user_<id>, high priority). */
    public static function notifyCallee(Call $call, User $caller): void
    {
        Helpers::pushNotification(['message' => [
            'topic' => 'user_'.$call->callee_id,
            'notification' => [
                'title' => $call->type === 'video' ? 'Входящий видеозвонок' : 'Входящий звонок',
                'body' => (string) $caller->name,
            ],
            'data' => [
                'type' => 'call',
                'call_id' => (string) $call->id,
                'call_type' => $call->type,
                'caller_id' => (string) $caller->id,
                'caller_name' => (string) $caller->name,
                'booking_id' => (string) ($call->booking_id ?? ''),
            ],
            'android' => ['priority' => 'high', 'ttl' => Call::RING_SECONDS.'s'],
        ]]);
    }
}
