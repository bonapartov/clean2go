<?php

namespace Modules\ProviderOnboarding\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

class IntegrationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Общие
            ['key' => 'onboarding_mock_mode', 'type' => 'toggle', 'group' => 'general', 'label' => 'Режим Mock (Sprint 1)', 'value' => '1'],
            ['key' => 'onboarding_enabled', 'type' => 'toggle', 'group' => 'general', 'label' => 'Онбординг включён', 'value' => '1'],

            // ФНС + НПД / DaData (один ключ покрывает оба: ЕГРЮЛ/ЕГРИП/ИНН физлица + статус НПД)
            ['key' => 'dadata_api_key', 'type' => 'password', 'group' => 'fns', 'label' => 'DaData API Key', 'value' => null],
            ['key' => 'dadata_secret_key', 'type' => 'password', 'group' => 'fns', 'label' => 'DaData Secret Key', 'value' => null],

            // Паспорт
            ['key' => 'passport_provider', 'type' => 'select', 'group' => 'passport', 'label' => 'Провайдер верификации паспорта', 'value' => 'manual'],
            ['key' => 'passport_api_key', 'type' => 'password', 'group' => 'passport', 'label' => 'API Key (Суфтех / Контур)', 'value' => null],
            ['key' => 'passport_endpoint', 'type' => 'text', 'group' => 'passport', 'label' => 'Endpoint URL', 'value' => null],

            // Telegram уведомления администратору
            ['key' => 'telegram_bot_token', 'type' => 'password', 'group' => 'passport', 'label' => 'Telegram Bot Token', 'value' => null],
            ['key' => 'telegram_admin_chat_id', 'type' => 'text', 'group' => 'passport', 'label' => 'Telegram Admin Chat ID', 'value' => null],

            // ФССП
            ['key' => 'fssp_provider', 'type' => 'select', 'group' => 'fssp', 'label' => 'Провайдер ФССП', 'value' => 'manual'],
            ['key' => 'fssp_api_key', 'type' => 'password', 'group' => 'fssp', 'label' => 'API Key (Контур / SmartDeal)', 'value' => null],
            ['key' => 'fssp_debt_threshold', 'type' => 'number', 'group' => 'fssp', 'label' => 'Порог долга для блокировки (руб.)', 'value' => '10000'],

            // Договор
            ['key' => 'contract_company_name', 'type' => 'text', 'group' => 'contract', 'label' => 'Наименование компании в договоре', 'value' => null],
            ['key' => 'contract_company_inn', 'type' => 'text', 'group' => 'contract', 'label' => 'ИНН компании', 'value' => null],
            ['key' => 'contract_director', 'type' => 'text', 'group' => 'contract', 'label' => 'ФИО директора', 'value' => null],

            // Версии шаблонов договоров (инкрементировать после деплоя нового шаблона)
            ['key' => 'contract_version_self_employed', 'type' => 'number', 'group' => 'contract', 'label' => 'Версия договора: Самозанятый', 'value' => '1'],
            ['key' => 'contract_version_ip', 'type' => 'number', 'group' => 'contract', 'label' => 'Версия договора: ИП', 'value' => '1'],
            ['key' => 'contract_version_ooo', 'type' => 'number', 'group' => 'contract', 'label' => 'Версия договора: ООО', 'value' => '1'],
            ['key' => 'contract_version_gph', 'type' => 'number', 'group' => 'contract', 'label' => 'Версия договора: ГПХ', 'value' => '1'],
        ];

        foreach ($settings as $setting) {
            IntegrationSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
