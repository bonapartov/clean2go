<?php

namespace Modules\ProviderOnboarding\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use Modules\ProviderOnboarding\Services\NpdVerificationService;

class CheckPendingNpdOnLogin
{
    public function __construct(private NpdVerificationService $npdService) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        $verification = ProviderVerification::where('user_id', $user->id)
            ->whereIn('npd_status', ['pending', 'active'])
            ->whereIn('taxpayer_type', ['self_employed', 'ip_on_npd'])
            ->first();

        if (!$verification || !$verification->inn) {
            return;
        }

        // Повторная проверка только если pending или прошло > 24ч с последней проверки
        $needsCheck = $verification->npd_status === 'pending'
            || ($verification->npd_verified_at && $verification->npd_verified_at->diffInHours(now()) >= 24);

        if (!$needsCheck) {
            return;
        }

        $result = $this->npdService->check($verification->inn);

        if ($result['status'] === 'pending') {
            return; // nalog.ru недоступен — не меняем статус
        }

        $verification->npd_status = $result['status'];
        $verification->npd_verified_at = now();
        $verification->npd_raw_response = $result['raw'];

        if ($result['status'] === 'inactive' && !$verification->payments_frozen) {
            $verification->payments_frozen = true;
            $verification->payments_frozen_reason = 'npd_lost';
            $verification->payments_frozen_at = now();

            OnboardingLog::record($user->id, 'npd_check', 'npd_lost', [
                'previous_status' => 'active',
            ]);
        } elseif ($result['status'] === 'active') {
            OnboardingLog::record($user->id, 'npd_check', 'npd_restored', []);
        }

        $verification->save();
    }
}
