<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Setting;
use App\Models\SystemLang;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current_year = date('Y');
        $currency_id = Currency::where('status', true)->first()->id;
        $language_id = SystemLang::where('name', 'English')->first()?->id;
        $baseURL = env('APP_URL');
        $baseName = config('app.name');
        $values = [
            'general' => [
                'light_logo' => '/admin/images/Logo-Light.png',
                'dark_logo' => '/admin/images/logo.png',
                'favicon' => '/admin/images/faviconIcon.png',
                'site_name' => $baseName,
                'default_timezone' => 'Asia/Kolkata',
                'default_currency_id' => $currency_id,
                'platform_fees' => 10,
                'platform_fees_type' => 'fixed',
                'default_language_id' => $language_id,
                'default_sms_gateway' => null,
                'min_booking_amount' => 100,
                'mode' => 'light',
                'firebase_server_key' => '',
                'copyright' => "Copyright {$current_year} © {$baseName} — Bonapartov V.A.",
                'cancellation_restriction_hours' => 1,
                'country_code' => 1,
            ],
            'activation' => [
                'coupon_enable' => '1',
                'wallet_enable' => '1',
                'blogs_enable' => '1',
                'maintenance_mode' => '1',
                'additional_services' => '1',
                'subscription_enable' => '1',
                'social_login_enable' => '1',
                'cash' => '1',
                'free_trial_plan' => '1',
                'service_auto_approve' => '1',
                'provider_auto_approve' => '1',
                'platform_fees_status' => '1',
                'extra_charge_status' => '1',
                'default_credentials' => '1',
                'force_update_in_app' => '0',
                'become_provider' => '1',
            ],
            'provider_commissions' => [
                'status' => '1',
                'min_withdraw_amount' => 500,
                'default_commission_rate' => 10,
                'is_category_based_commission' => '1',
            ],
            'email' => [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587,
                'mail_username' => '',
                'mail_password' => '',
                'mail_encryption' => 'tls',
                'mail_from_address' => '',
                'mail_from_name' => $baseName,
            ],
            'social_login' => [
                'client_id' => '',
                'redirect_url' => "$baseURL/auth/callback/google",
                'client_secret' => '',
            ],
            'google_reCaptcha' => [
                'site_key' => '',
                'secret' => '',
                'status' => '0',
            ],
            'default_creation_limits' => [
                'allowed_max_services' => 5,
                'allowed_max_servicemen' => 10,
                'allowed_max_service_packages' => 3,
                'allowed_max_addresses' => 5,
            ],
            'subscription_plan' => [
                'free_trial_days' => 7,
                'free_trial_enabled' => '1',
            ],
            'agora' => [
                'app_id' => 'qwertyuiopasdfghjklzxcvbnm',
                'certificate' => 'mnbvcxzlkjhgfdsapoiuytrewq',
            ],
            'firebase' => [
                'service_json' => null,
                'yandex_map_api_key' => '',
            ],
            'google' => [
                'client_id' => '',
                'client_secret' => '',
                'redirect_url' => '',
            ],
            'service_request' => [
                'status' => '1',
                'default_tax_id' => '1',
                'per_serviceman_commission' => '5'
            ]
        ];

        Setting::updateOrCreate(['values' => $values]);
    }
}
