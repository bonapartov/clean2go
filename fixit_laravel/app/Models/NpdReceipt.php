<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Receipt of a self-employed executor for one booking (App\Support\Npd). Own code. */
class NpdReceipt extends Model
{
    protected $fillable = [
        'booking_id', 'executor_id', 'executor_inn', 'service_name', 'amount', 'operation_time',
        'status', 'channel', 'external_id', 'receipt_url', 'attempts', 'last_error',
        'cancel_reason', 'registered_at', 'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'operation_time' => 'datetime',
        'registered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }
}
