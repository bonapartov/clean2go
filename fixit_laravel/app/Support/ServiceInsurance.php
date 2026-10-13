<?php

namespace App\Support;

use App\Helpers\Helpers;

/**
 * Optional «застраховать услугу» checkbox at checkout (ROADMAP, «План от
 * 13.10.2026 — агентская модель B2C», Sprint 6; insurer integration is
 * Sprint 10).
 *
 * Configured by the admin in Settings → «Общие» → «Страхование услуги»
 * (settings group `insurance`). Off by default: until there is a contract
 * with an insurer the checkbox is not shown and nothing is charged.
 *
 * The client's choice comes as `insurance` (bool) in the checkout/booking
 * request. The amount is added to the order total before the advance
 * payment split, and a snapshot (is_insured, insurance_amount,
 * insurance_insurer) is stored on the parent booking.
 *
 * Covered: only work ordered on the platform. Not covered: work agreed off
 * the platform, an unrecorded substitute executor (see the decisions doc).
 *
 * Own code (not Fixit) — see docs/code-inventory.md.
 */
class ServiceInsurance
{
    public const PRICE_FIXED = 'fixed';

    public const PRICE_PERCENTAGE = 'percentage';

    /** @return array{status:bool, default_on:bool, price_type:string, price:float, insurer_name:string, terms_url:string} */
    public static function settings(): array
    {
        $insurance = Helpers::getSettings()['insurance'] ?? [];

        return [
            'status' => (bool) ($insurance['status'] ?? false),
            // Checkbox pre-ticked for the client (decided 2026-10-13). Legal
            // check pending: ЗоЗПП ст. 16 on pre-ticked paid extras — the
            // admin can switch this off without a release.
            'default_on' => (bool) ($insurance['default_on'] ?? true),
            'price_type' => ($insurance['price_type'] ?? self::PRICE_FIXED) === self::PRICE_PERCENTAGE
                ? self::PRICE_PERCENTAGE : self::PRICE_FIXED,
            'price' => max(0, (float) ($insurance['price'] ?? 0)),
            'insurer_name' => (string) ($insurance['insurer_name'] ?? ''),
            'terms_url' => (string) ($insurance['terms_url'] ?? ''),
        ];
    }

    /** Is the checkbox pre-ticked for a client who has not touched it. */
    public static function isDefaultOn(): bool
    {
        return self::isAvailable() && self::settings()['default_on'];
    }

    /** The admin switched it on and set a price. */
    public static function isAvailable(): bool
    {
        $settings = self::settings();

        return $settings['status'] && $settings['price'] > 0;
    }

    /** Insurance price for an order total (before insurance). */
    public static function amountFor(float $orderTotal): float
    {
        $settings = self::settings();

        if ($settings['price_type'] === self::PRICE_PERCENTAGE) {
            return round($orderTotal * $settings['price'] / 100, 2);
        }

        return round($settings['price'], 2);
    }

    /**
     * What a checkout gets: is it insured, how much, by whom.
     *
     * @return array{is_insured:bool, insurance_amount:float, insurance_insurer:?string}
     */
    public static function forCheckout(bool $requested, float $orderTotal): array
    {
        if (! $requested || ! self::isAvailable()) {
            return ['is_insured' => false, 'insurance_amount' => 0.0, 'insurance_insurer' => null];
        }

        return [
            'is_insured' => true,
            'insurance_amount' => self::amountFor($orderTotal),
            'insurance_insurer' => self::settings()['insurer_name'] ?: null,
        ];
    }
}
