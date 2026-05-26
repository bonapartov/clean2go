<?php

namespace Modules\ProviderOnboarding\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Modules\ProviderOnboarding\Models\ProviderVerification;

class ContractService
{
    /**
     * Генерирует PDF договора для пользователя и сохраняет в storage.
     *
     * @return array{path: string, version: int}
     */
    public function generate(ProviderVerification $verification): array
    {
        $contractType = $this->resolveContractType($verification);
        $version = (int) IntegrationSetting::get("contract_version_{$contractType}") ?: 1;

        $variables = $this->buildVariables($verification);
        $html = view("contracts.{$contractType}", $variables)->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait');

        $filename = "contracts/{$verification->user_id}/{$contractType}_v{$version}_" . time() . '.pdf';
        Storage::disk('local')->put($filename, $pdf->output());

        return ['path' => $filename, 'version' => $version];
    }

    /**
     * Возвращает тип шаблона по taxpayer_type/contract_type.
     */
    private function resolveContractType(ProviderVerification $verification): string
    {
        // При потере НПД пользователь уже переведён на gph до вызова generate
        if ($verification->contract_type === 'gph') {
            return 'gph';
        }

        return match ($verification->taxpayer_type) {
            'self_employed'            => 'self_employed',
            'individual_entrepreneur',
            'ip_on_npd'                => 'ip',
            'legal_entity'             => 'ooo',
            default                    => 'self_employed',
        };
    }

    private function buildVariables(ProviderVerification $verification): array
    {
        return [
            'provider_name'    => $verification->legal_name ?? '',
            'inn'              => $verification->inn ?? '',
            'contract_date'    => now()->translatedFormat('d F Y'),
            'company_name'     => IntegrationSetting::get('contract_company_name') ?? '',
            'company_inn'      => IntegrationSetting::get('contract_company_inn') ?? '',
            'company_director' => IntegrationSetting::get('contract_director') ?? '',
            'verification'     => $verification,
        ];
    }
}
