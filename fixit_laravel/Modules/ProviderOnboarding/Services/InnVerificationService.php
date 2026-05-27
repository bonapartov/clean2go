<?php

namespace Modules\ProviderOnboarding\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

class InnVerificationService
{
    private const SUGGEST_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party';
    private const FIND_URL    = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';
    private const TIMEOUT     = 10;

    private bool $mockMode;

    public function __construct()
    {
        $this->mockMode = config('provider-onboarding.mock_mode', true);
    }

    /**
     * Проверяет ИНН и возвращает данные налогоплательщика.
     *
     * @return array{taxpayer_type: string, legal_name: string, inn: string, npd_status: string|null, okveds: array|null, message: string, next_step: int|null}
     */
    public function verify(string $inn): array
    {
        if ($this->mockMode) {
            return $this->mockVerify($inn);
        }

        return $this->dadataVerify($inn);
    }

    private function dadataVerify(string $inn): array
    {
        $apiKey = IntegrationSetting::get('dadata_api_key');

        if (!$apiKey) {
            return $this->errorResponse('dadata_not_configured', 'DaData API ключ не настроен. Обратитесь к администратору.');
        }

        $len = strlen($inn);

        if ($len === 12) {
            return $this->verifyIndividual($apiKey, $inn);
        }

        if ($len === 10) {
            return $this->verifyLegal($apiKey, $inn);
        }

        return $this->errorResponse('inn_invalid_length', 'ИНН должен содержать 10 (для организаций) или 12 (для физлиц) цифр.');
    }

    private function verifyIndividual(string $apiKey, string $inn): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Token ' . $apiKey])
                ->post(self::FIND_URL, [
                    'query' => $inn,
                    'type'  => 'INDIVIDUAL',
                ]);

            if ($response->failed()) {
                Log::warning('DaData ЕГРИП lookup failed', ['inn' => $inn, 'status' => $response->status()]);
                return $this->errorResponse('dadata_error', 'Ошибка проверки ИНН. Попробуйте позже.');
            }

            $suggestions = $response->json('suggestions', []);

            if (!empty($suggestions)) {
                $party = $suggestions[0]['data'];
                $okveds = $this->extractOkveds($party);

                return [
                    'taxpayer_type'     => 'individual_entrepreneur',
                    'legal_name'        => $suggestions[0]['value'] ?? '',
                    'inn'               => $inn,
                    'npd_status'        => null, // NpdVerificationService проверит отдельно
                    'okveds'            => $okveds,
                    'ogrn'              => $party['ogrn'] ?? null,
                    'registration_date' => $this->parseDate($party['state']['registration_date'] ?? null),
                    'message'           => 'ИП зарегистрирован в ЕГРИП.',
                    'next_step'         => 3,
                    'raw'               => $suggestions[0],
                ];
            }

            // Не найден в ЕГРИП — считаем самозанятым физлицом
            return [
                'taxpayer_type' => 'self_employed',
                'legal_name'    => '',
                'inn'           => $inn,
                'npd_status'    => null,
                'okveds'        => null,
                'message'       => 'Физлицо. Статус НПД будет проверен.',
                'next_step'     => 3,
                'raw'           => null,
            ];
        } catch (\Exception $e) {
            Log::error('DaData individual verify error', ['inn' => $inn, 'error' => $e->getMessage()]);
            return $this->errorResponse('dadata_error', 'Ошибка проверки ИНН: ' . $e->getMessage());
        }
    }

    private function verifyLegal(string $apiKey, string $inn): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Token ' . $apiKey])
                ->post(self::FIND_URL, [
                    'query' => $inn,
                    'type'  => 'LEGAL',
                ]);

            if ($response->failed()) {
                Log::warning('DaData ЕГРЮЛ lookup failed', ['inn' => $inn, 'status' => $response->status()]);
                return $this->errorResponse('dadata_error', 'Ошибка проверки ИНН. Попробуйте позже.');
            }

            $suggestions = $response->json('suggestions', []);

            if (empty($suggestions)) {
                return $this->errorResponse('inn_not_found', 'Организация с таким ИНН не найдена в ЕГРЮЛ или ликвидирована.');
            }

            $party = $suggestions[0]['data'];

            return [
                'taxpayer_type'     => 'legal_entity',
                'legal_name'        => $suggestions[0]['value'] ?? '',
                'inn'               => $inn,
                'npd_status'        => null,
                'okveds'            => $this->extractOkveds($party),
                'ogrn'              => $party['ogrn'] ?? null,
                'registration_date' => $this->parseDate($party['state']['registration_date'] ?? null),
                'director_name'     => $party['management']['name'] ?? null,
                'message'           => 'Организация найдена в ЕГРЮЛ.',
                'next_step'         => null,
                'raw'               => $suggestions[0],
            ];
        } catch (\Exception $e) {
            Log::error('DaData legal verify error', ['inn' => $inn, 'error' => $e->getMessage()]);
            return $this->errorResponse('dadata_error', 'Ошибка проверки ИНН: ' . $e->getMessage());
        }
    }

    private function extractOkveds(array $party): ?array
    {
        $okveds = [];

        if (!empty($party['okved'])) {
            $okveds[] = [
                'code'    => $party['okved'],
                'name'    => $party['okved_type'] ?? '',
                'is_main' => true,
            ];
        }

        if (!empty($party['okveds'])) {
            foreach ($party['okveds'] as $o) {
                $okveds[] = [
                    'code'    => $o['code'] ?? '',
                    'name'    => $o['name'] ?? '',
                    'is_main' => false,
                ];
            }
        }

        return $okveds ?: null;
    }

    /**
     * Проверяет, есть ли среди ОКВЭД виды деятельности, запрещённые для НПД (ст. 4 ФЗ-422).
     */
    public function hasNpdIncompatibleOkveds(?array $okveds): bool
    {
        if (!$okveds) {
            return false;
        }

        // Префиксы ОКВЭД, несовместимые с НПД: перепродажа, добыча, агенты, фин. услуги, юр. услуги
        $forbidden = ['46', '47', '45.1', '45.2', '45.3', '45.4', '06', '07', '08', '09', '64', '65', '66', '69.1', '69.2'];

        foreach ($okveds as $okved) {
            $code = $okved['code'] ?? '';
            foreach ($forbidden as $prefix) {
                if (str_starts_with($code, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function parseDate(?int $timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }
        return date('Y-m-d', (int) ($timestamp / 1000));
    }

    private function mockVerify(string $inn): array
    {
        $len = strlen($inn);

        if ($len === 12) {
            $lastDigit = (int) substr($inn, -1);

            if ($lastDigit === 0) {
                return $this->errorResponse('inn_not_found', 'ИНН не найден в реестрах ФНС. Проверьте номер и попробуйте снова.');
            }

            if ($lastDigit <= 3) {
                return [
                    'taxpayer_type' => 'individual_entrepreneur',
                    'legal_name'    => 'Тестовый ИП Иванов И.И.',
                    'inn'           => $inn,
                    'npd_status'    => null,
                    'okveds'        => [['code' => '43.21', 'name' => 'Производство электромонтажных работ', 'is_main' => true]],
                    'message'       => 'ИП зарегистрирован в ЕГРИП.',
                    'next_step'     => 3,
                ];
            }

            if ($lastDigit === 4) {
                return [
                    'taxpayer_type' => 'ip_on_npd',
                    'legal_name'    => 'Тестовый ИП Петров П.П.',
                    'inn'           => $inn,
                    'npd_status'    => 'active',
                    'okveds'        => [['code' => '96.09', 'name' => 'Предоставление прочих персональных услуг', 'is_main' => true]],
                    'message'       => 'ИП на НПД. Статус самозанятого: активен.',
                    'next_step'     => 3,
                ];
            }

            return [
                'taxpayer_type' => 'self_employed',
                'legal_name'    => 'Тестовый Самозанятый Сидоров С.С.',
                'inn'           => $inn,
                'npd_status'    => 'active',
                'okveds'        => null,
                'message'       => 'Самозанятый. Статус НПД: активен.',
                'next_step'     => 3,
            ];
        }

        if ($len === 10) {
            return [
                'taxpayer_type' => 'legal_entity',
                'legal_name'    => 'ООО "Тестовая компания"',
                'inn'           => $inn,
                'npd_status'    => null,
                'okveds'        => null,
                'message'       => 'Организация найдена в ЕГРЮЛ.',
                'next_step'     => null,
            ];
        }

        return $this->errorResponse('inn_invalid_length', 'ИНН должен содержать 10 (для организаций) или 12 (для физлиц) цифр.');
    }

    private function errorResponse(string $errorCode, string $message): array
    {
        return [
            'error'     => $errorCode,
            'message'   => $message,
            'next_step' => null,
        ];
    }
}
