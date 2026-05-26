<?php

namespace Modules\ProviderOnboarding\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NpdVerificationService
{
    private const NALOG_URL = 'https://statusnpd.nalog.ru/api/v1/tracker/taxpayer_status';
    private const TIMEOUT = 10;

    /**
     * Проверяет статус НПД через nalog.ru.
     *
     * @return array{status: 'active'|'inactive'|'pending', raw: array|null}
     */
    public function check(string $inn): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::NALOG_URL, [
                    'inn' => $inn,
                    'requestDate' => now()->format('Y-m-d'),
                ]);

            if ($response->failed()) {
                Log::warning('nalog.ru NPD check failed', ['inn' => $inn, 'status' => $response->status()]);
                return ['status' => 'pending', 'raw' => null];
            }

            $data = $response->json();
            $isActive = ($data['status'] ?? '') === 'ACTIVE';

            return [
                'status' => $isActive ? 'active' : 'inactive',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            // nalog.ru недоступен — не блокируем онбординг
            Log::warning('nalog.ru unreachable, NPD check pending', ['inn' => $inn, 'error' => $e->getMessage()]);
            return ['status' => 'pending', 'raw' => null];
        }
    }
}
