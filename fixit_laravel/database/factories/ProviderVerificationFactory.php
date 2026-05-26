<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProviderOnboarding\Models\ProviderVerification;

class ProviderVerificationFactory extends Factory
{
    protected $model = ProviderVerification::class;

    public function definition(): array
    {
        return [
            'user_id'          => null,
            'inn'              => $this->faker->numerify('############'),
            'taxpayer_type'    => 'self_employed',
            'legal_name'       => $this->faker->company(),
            'inn_status'       => 'active',
            'inn_verified_at'  => now(),
            'npd_status'       => 'active',
            'passport_status'  => null,
            'onboarding_status' => 'in_progress',
            'payments_frozen'  => false,
        ];
    }
}
