<?php

namespace App\Models;

use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class WalletBonus extends Model
{
    use HasFactory, SoftDeletes, HandlesLegacyTranslations, HasTranslations;
    
    protected $table = 'wallet_bonuses';

    public $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'description',
        'type',                
        'bonus',               
        'min_top_up_amount',   
        'max_bonus',           
        'status',              
        'is_admin_funded',
        'created_by_id',
        'usage_limit_per_user',
        'total_usage_limit',
        'is_unlimited'
    ];

    protected $casts = [
        'bonus' => 'float',
        'min_top_up_amount' => 'float',
        'max_bonus' => 'float',
        'status' => 'integer',
        'is_admin_funded' => 'integer',
        'usage_limit_per_user' => 'integer',
        'total_usage_limit' => 'integer',
        'is_unlimited' => 'integer',
    ];

    /**
     * Relation with transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_bonus_id');
    }
}
