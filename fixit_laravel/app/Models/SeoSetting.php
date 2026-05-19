<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, HasTranslations, HandlesLegacyTranslations;

    public $translatable = [
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
    ];

    protected $fillable = [
        'page_name',
        'page_slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'robots',
        'schema_markup',
        'is_active',
    ];

    protected $casts = [
        'schema_markup' => 'array',
    ];

    public function sluggable(): array
    {
        return [
            'page_slug' => [
                'source' => 'page_name',
            ],
        ];
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');

        // Filter media based on the language in custom_properties
        if (isset($attributes['media']) && is_array($attributes['media'])) {
            $attributes['media'] = array_filter($attributes['media'], function ($media) use ($locale) {
                return isset($media['custom_properties']['language'])
                    && $media['custom_properties']['language'] === $locale;
            });

            // Re-index the array to avoid gaps in indices after filtering
            $attributes['media'] = array_values($attributes['media']);
        }

        return  $this->handleModelTranslations($this, $attributes, $this->translatable);
    }
}
