<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App-to-app call over our LiveKit server (own code, not Fixit).
 * A ringing call nobody answered within RING_SECONDS counts as missed.
 */
class Call extends Model
{
    public const RING_SECONDS = 45;

    protected $fillable = [
        'booking_id', 'caller_id', 'callee_id', 'room', 'type', 'status', 'answered_at', 'ended_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function callee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'callee_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isParticipant(int $userId): bool
    {
        return $userId === (int) $this->caller_id || $userId === (int) $this->callee_id;
    }

    /** Turns an unanswered ringing call into missed once it has rung out. */
    public function expireIfUnanswered(): self
    {
        if ($this->status === 'ringing' && $this->created_at->lt(now()->subSeconds(self::RING_SECONDS))) {
            $this->update(['status' => 'missed', 'ended_at' => now()]);
        }

        return $this;
    }
}
