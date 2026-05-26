<?php

namespace Modules\ProviderOnboarding\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

class BikLookupService
{
    private const URL     = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/bank';
    private const TIMEOUT = 10;

    /**
     * @return array{found: bool, bank_name: string|null, corr_account: string|null, bik: string|null, error: string|null}
     */
    public function lookup(string $bik): array
    {
        if (config('provider-onboarding.mock_mode', true)) {
            return $this->mockLookup($bik);
        }

        $apiKey    = IntegrationSetting::get('dadata_api_key');
        $secretKey = IntegrationSetting::get('dadata_secret_key');

        if (!$apiKey) {
            return ['found' => false, 'bank_name' => null, 'corr_account' => null, 'bik' => $bik, 'error' => 'dadata_not_configured'];
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Authorization' => "Token {$apiKey}",
                    'X-Secret'      => $secretKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post(self::URL, ['query' => $bik]);

            $suggestions = $response->json('suggestions', []);

            if (empty($suggestions)) {
                return ['found' => false, 'bank_name' => null, 'corr_account' => null, 'bik' => $bik, 'error' => 'not_found'];
            }

            $data = $suggestions[0]['data'] ?? [];

            return [
                'found'        => true,
                'bank_name'    => $suggestions[0]['value'] ?? null,
                'corr_account' => $data['correspondent_account'] ?? null,
                'bik'          => $data['bic'] ?? $bik,
                'error'        => null,
            ];
        } catch (\Throwable $e) {
            Log::error('BikLookupService error', ['bik' => $bik, 'error' => $e->getMessage()]);
            return ['found' => false, 'bank_name' => null, 'corr_account' => null, 'bik' => $bik, 'error' => 'request_failed'];
        }
    }

    private function mockLookup(string $bik): array
    {
        if (!preg_match('/^\d{9}$/', $bik)) {
            return ['found' => false, 'bank_name' => null, 'corr_account' => null, 'bik' => $bik, 'error' => 'invalid_bik'];
        }

        return [
            'found'        => true,
            'bank_name'    => 'ПАО Сбербанк (mock)',
            'corr_account' => '30101810400000000225',
            'bik'          => $bik,
            'error'        => null,
        ];
    }
}
