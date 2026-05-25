<?php

namespace Modules\ProviderOnboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class OnboardingLog extends Model
{
    public $timestamps = false;

    protected $table = 'onboarding_logs';

    protected $fillable = ['user_id', 'step', 'action', 'data', 'ip_address'];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(int $userId, string $step, string $action, array $data = [], ?string $ip = null): void
    {
        self::create([
            'user_id' => $userId,
            'step' => $step,
            'action' => $action,
            'data' => $data ?: null,
            'ip_address' => $ip,
        ]);
    }
}
