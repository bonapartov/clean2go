<?php

namespace Modules\ProviderOnboarding\Services\Passport;

use Modules\ProviderOnboarding\Models\ProviderVerification;

class ManualPassportProvider implements PassportProviderInterface
{
    public function verify(ProviderVerification $verification): array
    {
        return [
            'status'       => 'manual_review',
            'message'      => 'Паспорт передан на ручную проверку администратору.',
            'raw_response' => ['provider' => 'manual'],
        ];
    }
}
