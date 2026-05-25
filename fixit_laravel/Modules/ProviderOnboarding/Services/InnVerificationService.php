<?php

namespace Modules\ProviderOnboarding\Services;

class InnVerificationService
{
    private bool $mockMode;

    public function __construct()
    {
        $this->mockMode = config('provider-onboarding.mock_mode', true);
    }

    /**
     * Проверяет ИНН и возвращает данные налогоплательщика.
     *
     * @return array{taxpayer_type: string, legal_name: string, inn: string, npd_status: string|null, okveds: array|null, message: string, next_step: int}
     */
    public function verify(string $inn): array
    {
        if ($this->mockMode) {
            return $this->mockVerify($inn);
        }

        // Sprint 2: реальный запрос через DaData PHP SDK
        // $dadata = new \Dadata\DadataClient(IntegrationSetting::get('dadata_api_key'), IntegrationSetting::get('dadata_secret_key'));
        // ...
        throw new \RuntimeException('DaData integration not implemented yet. Set ONBOARDING_MOCK_MODE=true for Sprint 1.');
    }

    private function mockVerify(string $inn): array
    {
        $len = strlen($inn);

        if ($len === 12) {
            // Физлицо/самозанятый
            // Симулируем разные сценарии по последней цифре для тестирования
            $lastDigit = (int) substr($inn, -1);

            if ($lastDigit === 0) {
                return $this->errorResponse('inn_not_found', 'ИНН не найден в реестрах ФНС. Проверьте номер и попробуйте снова.');
            }

            if ($lastDigit <= 3) {
                return [
                    'taxpayer_type' => 'individual_entrepreneur',
                    'legal_name' => 'Тестовый ИП Иванов И.И.',
                    'inn' => $inn,
                    'npd_status' => null,
                    'okveds' => [['code' => '43.21', 'name' => 'Производство электромонтажных работ']],
                    'message' => 'ИП зарегистрирован в ЕГРИП.',
                    'next_step' => 3,
                ];
            }

            if ($lastDigit === 4) {
                // ИП на НПД
                return [
                    'taxpayer_type' => 'ip_on_npd',
                    'legal_name' => 'Тестовый ИП Петров П.П.',
                    'inn' => $inn,
                    'npd_status' => 'active',
                    'okveds' => [['code' => '96.09', 'name' => 'Предоставление прочих персональных услуг']],
                    'message' => 'ИП на НПД. Статус самозанятого: активен.',
                    'next_step' => 3,
                ];
            }

            // По умолчанию — самозанятый
            return [
                'taxpayer_type' => 'self_employed',
                'legal_name' => 'Тестовый Самозанятый Сидоров С.С.',
                'inn' => $inn,
                'npd_status' => 'active',
                'okveds' => null,
                'message' => 'Самозанятый. Статус НПД: активен.',
                'next_step' => 3,
            ];
        }

        if ($len === 10) {
            // ООО
            return [
                'taxpayer_type' => 'legal_entity',
                'legal_name' => 'ООО "Тестовая компания"',
                'inn' => $inn,
                'npd_status' => null,
                'okveds' => null,
                'message' => 'Организация найдена в ЕГРЮЛ.',
                'next_step' => null, // pending_manual после шага 2В
            ];
        }

        return $this->errorResponse('inn_invalid_length', 'ИНН должен содержать 10 (для организаций) или 12 (для физлиц) цифр.');
    }

    private function errorResponse(string $errorCode, string $message): array
    {
        return [
            'error' => $errorCode,
            'message' => $message,
            'next_step' => null,
        ];
    }
}
