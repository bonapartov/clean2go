<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

/**
 * Credentials of payment and SMS gateways (YooKassa, CloudPayments, SMS.ru,
 * SMSC, …), edited by the admin in the existing screens
 * «Платёжные методы» and «SMS-шлюзы».
 *
 * Stored encrypted in integration_settings under "gateway.<field key>",
 * where <field key> is the key from the module's Config/payment.php or
 * Config/sms.php 'fields' (e.g. yookassa_shop_id). Before 2026-10-13 the
 * admin wrote them to .env and modules read env() directly, which returns
 * null once config is cached (CLAUDE.md, «Sensitive Settings»).
 *
 * .env is still read as a fallback while a key has never been saved from
 * the admin, so existing installs keep working: the env name is the field
 * key upper-cased (YOOKASSA_SHOP_ID).
 *
 * Own code (not Fixit) — see docs/code-inventory.md.
 */
class GatewayCredentials
{
    private const PREFIX = 'gateway.';

    public static function get(string $fieldKey, string $default = ''): string
    {
        $stored = IntegrationSetting::get(self::PREFIX.$fieldKey);
        if ($stored !== null) {
            return $stored;
        }

        $fromEnv = env(strtoupper($fieldKey));

        return $fromEnv === null ? $default : (string) $fromEnv;
    }

    public static function set(string $fieldKey, ?string $value, string $group, string $label): void
    {
        // IntegrationSetting::set() only updates an existing row, so make
        // sure there is one. Every gateway credential is stored encrypted.
        IntegrationSetting::firstOrCreate(
            ['key' => self::PREFIX.$fieldKey],
            ['type' => 'password', 'group' => $group, 'label' => mb_substr($label, 0, 255)]
        );

        IntegrationSetting::set(self::PREFIX.$fieldKey, (string) $value, Auth::id());
    }

    /**
     * A module's 'configs' array with real values filled in. The module
     * config files only list the keys (values are blank) so no secret is
     * ever written into a tracked file.
     *
     * @param  array<string, mixed>  $configs
     * @return array<string, string>
     */
    public static function resolve(array $configs): array
    {
        $resolved = [];
        foreach (array_keys($configs) as $fieldKey) {
            $resolved[$fieldKey] = self::get($fieldKey);
        }

        return $resolved;
    }
}
