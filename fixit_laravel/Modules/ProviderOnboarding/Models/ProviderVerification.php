<?php

namespace Modules\ProviderOnboarding\Models;

use Database\Factories\ProviderVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ProviderVerification extends Model
{
    use HasFactory;

    protected $table = 'provider_verifications';

    protected static function newFactory(): ProviderVerificationFactory
    {
        return ProviderVerificationFactory::new();
    }

    protected $fillable = [
        'user_id', 'inn', 'taxpayer_type', 'legal_name', 'director_name',
        'ogrn', 'registration_date', 'inn_status', 'inn_verified_at', 'inn_raw_response',
        'npd_status', 'npd_verified_at', 'npd_raw_response',
        'okveds', 'okved_warning',
        'passport_series', 'passport_number', 'passport_issued_by',
        'passport_issued_date', 'passport_dept_code',
        'passport_photo_path', 'passport_selfie_path',
        'passport_status', 'passport_verified_at', 'passport_raw_response',
        'fssp_status', 'fssp_debt_amount', 'fssp_checked_at', 'fssp_raw_response',
        'contract_type', 'contract_version', 'contract_path', 'contract_signed_at',
        'contract_sign_method', 'contract_sms_code', 'contract_sms_sent_at', 'contract_ip_address',
        'payments_frozen', 'payments_frozen_reason', 'payments_frozen_at',
        'power_of_attorney_path', 'signatory_name',
        'onboarding_status', 'manual_review_reason', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'inn_raw_response' => 'array',
        'npd_raw_response' => 'array',
        'okveds' => 'array',
        'fssp_raw_response' => 'array',
        'passport_raw_response' => 'array',
        'okved_warning' => 'boolean',
        'registration_date' => 'date',
        'passport_issued_date' => 'date',
        'inn_verified_at' => 'datetime',
        'npd_verified_at' => 'datetime',
        'passport_verified_at' => 'datetime',
        'fssp_checked_at' => 'datetime',
        'contract_signed_at' => 'datetime',
        'contract_sms_sent_at' => 'datetime',
        'payments_frozen' => 'boolean',
        'payments_frozen_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
