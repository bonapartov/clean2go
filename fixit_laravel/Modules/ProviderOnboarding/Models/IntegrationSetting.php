<?php

namespace Modules\ProviderOnboarding\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $table = 'integration_settings';

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'updated_by'];

    protected $hidden = ['value']; // никогда не сериализуем значение напрямую в JSON

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;
        $value = $setting->type === 'password'
            ? decrypt($setting->value)
            : $setting->value;
        return $value ?? $default;
    }

    public static function set(string $key, string $value, ?int $updatedBy = null): void
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return;

        $setting->value = $setting->type === 'password' ? encrypt($value) : $value;
        $setting->updated_by = $updatedBy;
        $setting->save();
    }

    public static function toggle(string $key): bool
    {
        $setting = self::where('key', $key)->first();
        $current = filter_var($setting?->value, FILTER_VALIDATE_BOOLEAN);
        $newValue = !$current;
        self::set($key, $newValue ? '1' : '0');
        return $newValue;
    }
}
