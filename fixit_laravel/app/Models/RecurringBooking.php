<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'consumer_id',
        'provider_id',
        'service_id',
        'address_id',
        'frequency',
        'start_date',
        'end_date',
        'total_occurrences',
        'occurrences_completed',
        'next_booking_date',
        'subscription_id',
        'payment_method',
        'payment_status',
        'amount',
        'currency',
        'is_active',
        'status',
        'booking_data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_booking_date' => 'date',
        'amount' => 'float',
        'is_active' => 'boolean',
        'occurrences_completed' => 'integer',
        'total_occurrences' => 'integer',
        'booking_data' => 'array',
    ];

    /**
     * Get the initial booking that created this recurring booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Get the consumer (customer)
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    /**
     * Get the provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get the service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Get the address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get all bookings created from this recurring booking
     */
    public function generatedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'recurring_booking_id');
    }

    /**
     * Scope to get active recurring bookings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Calculate next booking date based on frequency
     */
    public function calculateNextBookingDate($fromDate = null): ?Carbon
    {
        $fromDate = $fromDate ?? Carbon::now();
        
        switch ($this->frequency) {
            case 'weekly':
                return $fromDate->copy()->addWeek();
            case 'monthly':
                return $fromDate->copy()->addMonth();
            case 'yearly':
                return $fromDate->copy()->addYear();
            default:
                return null;
        }
    }

    /**
     * Check if recurring booking should continue
     */
    public function shouldContinue(): bool
    {
        if (!$this->is_active || $this->status !== 'active') {
            return false;
        }

        // Check if we've reached the maximum occurrences
        if ($this->total_occurrences && $this->occurrences_completed >= $this->total_occurrences) {
            return false;
        }

        // Check if we've passed the end date
        if ($this->end_date && Carbon::now()->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Increment occurrences and update next booking date
     */
    public function incrementOccurrence(): void
    {
        $this->increment('occurrences_completed');
        
        if ($this->shouldContinue()) {
            // Calculate next booking date from current next_booking_date (not today)
            $baseDate = $this->next_booking_date ? Carbon::parse($this->next_booking_date) : Carbon::now();
            $this->next_booking_date = $this->calculateNextBookingDate($baseDate);
        } else {
            $this->status = 'completed';
            $this->is_active = false;
            $this->next_booking_date = null;
        }
        
        $this->save();
    }

    /**
     * Pause the recurring booking
     */
    public function pause(): void
    {
        $this->status = 'paused';
        $this->is_active = false;
        $this->save();
    }

    /**
     * Resume the recurring booking
     */
    public function resume(): void
    {
        if ($this->shouldContinue()) {
            $this->status = 'active';
            $this->is_active = true;
            $this->next_booking_date = $this->calculateNextBookingDate();
            $this->save();
        }
    }

    /**
     * Cancel the recurring booking
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->is_active = false;
        $this->save();
    }
}
