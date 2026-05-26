<?php

namespace Modules\ProviderOnboarding\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingStepCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly int $step,
        public readonly string $status = 'completed',
    ) {}
}
