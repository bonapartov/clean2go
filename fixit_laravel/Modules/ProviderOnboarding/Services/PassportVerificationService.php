<?php

namespace Modules\ProviderOnboarding\Services;

use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use Modules\ProviderOnboarding\Services\Passport\ManualPassportProvider;
use Modules\ProviderOnboarding\Services\Passport\PassportProviderInterface;

class PassportVerificationService
{
    private PassportProviderInterface $provider;

    public function __construct()
    {
        $providerKey = IntegrationSetting::get('passport_provider', 'manual');

        $this->provider = match ($providerKey) {
            default => new ManualPassportProvider(),
        };
    }

    public function verify(ProviderVerification $verification): array
    {
        return $this->provider->verify($verification);
    }
}
