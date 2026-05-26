<?php

namespace Modules\ProviderOnboarding\Services\Passport;

use Modules\ProviderOnboarding\Models\ProviderVerification;

interface PassportProviderInterface
{
    /**
     * @return array{status: string, message: string, raw_response: array}
     *   status: 'approved' | 'rejected' | 'manual_review' | 'pending'
     */
    public function verify(ProviderVerification $verification): array;
}
