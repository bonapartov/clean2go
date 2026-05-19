<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAIModel extends Model
{
    use HasFactory;

    protected $table = 'custom_ai_models';

    /**
     * The values that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'provider',
        'model_name',
        'api_key',
        'base_url',
        'headers',
        'params',
        'payload',
        'is_default',
        'description',
    ];

    protected $casts = [
        'headers' => 'json',
        'params' => 'json',
        'payload' => 'json',
        'is_default' => 'boolean',
    ];
}