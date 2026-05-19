<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\SeoSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Prettus\Repository\Eloquent\BaseRepository;

class SeoSettingRepository extends BaseRepository
{
    public function model()
    {
        return SeoSetting::class;
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $data = $request->all();
            // Base validation rules
            $rules = [
                'page_name' => 'required|string|max:255',
                'meta_title' => 'required|string|max:255',
                'og_title' => 'required|string|max:255',
                'meta_keywords' => 'required|string',
                'meta_description' => 'required|string|min:10|max:150',
                'og_description' => 'required|string|min:10|max:150',
                'robots' => 'nullable|string',
                'canonical_url' => 'nullable|url|max:255',
                'schema_markup' => 'nullable|string',
                'use_twitter_custom' => 'nullable|boolean',
                'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'twitter_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ];

            // Twitter fields: required only when custom override is enabled
            if (!empty($data['use_twitter_custom']) && $data['use_twitter_custom'] == 1) {
                $rules['twitter_title'] = 'required|string|max:255';
                $rules['twitter_description'] = 'required|string|min:10|max:150';
            } else {
                // When override is off, apply Meta values to Twitter and clear custom image
                $data['twitter_title'] = $data['meta_title'] ?? null;
                $data['twitter_description'] = $data['meta_description'] ?? null;
                $rules['twitter_title'] = 'nullable|string|max:255';
                $rules['twitter_description'] = 'nullable|string';
            }

            $validator = Validator::make($data, $rules, [
                'meta_description.required' => 'Meta description is required.',
                'meta_description.min' => 'Meta description must be at least 10 characters.',
                'meta_description.max' => 'Meta description must not exceed 150 characters.',
                'og_description.required' => 'OG description is required.',
                'og_description.min' => 'OG description must be at least 10 characters.',
                'og_description.max' => 'OG description must not exceed 150 characters.',
                'twitter_title.required' => 'Twitter title is required when override is enabled.',
                'twitter_description.min' => 'Twitter description must be at least 10 characters.',
                'twitter_description.max' => 'Twitter description must not exceed 150 characters.',
                'twitter_description.required' => 'Twitter description is required when override is enabled.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $SeoSetting = $this->model->findOrFail($id);
            
            // Update translatable fields
            $SeoSetting->setTranslation('meta_title', $locale, $data['meta_title']);
            $SeoSetting->setTranslation('meta_description', $locale, $data['meta_description']);
            $SeoSetting->setTranslation('og_title', $locale, $data['og_title']);
            $SeoSetting->setTranslation('og_description', $locale, $data['og_description']);
            
            if (!empty($data['use_twitter_custom']) && $data['use_twitter_custom'] == 1) {
                $SeoSetting->setTranslation('twitter_title', $locale, $data['twitter_title'] ?? null);
                $SeoSetting->setTranslation('twitter_description', $locale, $data['twitter_description'] ?? null);
            } else {
                // When override is off, use meta values for Twitter
                $SeoSetting->setTranslation('twitter_title', $locale, $data['meta_title'] ?? null);
                $SeoSetting->setTranslation('twitter_description', $locale, $data['meta_description'] ?? null);
            }
            
            // Handle meta_keywords - convert Tagify JSON format to comma-separated string
            if (isset($data['meta_keywords']) && is_string($data['meta_keywords'])) {
                $decoded = json_decode($data['meta_keywords'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Extract values from Tagify format [{"value":"keyword1"},{"value":"keyword2"}]
                    $keywords = array_map(function($item) {
                        return $item['value'] ?? $item;
                    }, $decoded);
                    $data['meta_keywords'] = implode(', ', $keywords);
                }
                // If it's already a comma-separated string, keep it as is
            }

            // Handle schema_markup - decode JSON string if it's a string
            if (isset($data['schema_markup']) && is_string($data['schema_markup'])) {
                $decoded = json_decode($data['schema_markup'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['schema_markup'] = $decoded;
                } else {
                    // If it's not valid JSON, set to null or empty array
                    $data['schema_markup'] = null;
                }
            }
            
            // Remove translatable fields and use_twitter_custom from data before updating non-translatable fields
            $nonTranslatableData = Arr::except($data, [
                'meta_title', 
                'meta_description', 
                'og_title', 
                'og_description', 
                'twitter_title', 
                'twitter_description',
                'use_twitter_custom',
                'meta_image',
                'og_image',
                'twitter_image',
                'locale'
            ]);
            
            $SeoSetting->update($nonTranslatableData);
            $SeoSetting->save();

            // Handle meta_image upload
            if ($request->hasFile('meta_image')) {
                $existingImages = $SeoSetting->getMedia('meta_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $SeoSetting->addMedia($request->file('meta_image'))
                    ->withCustomProperties(['language' => $locale])
                    ->toMediaCollection('meta_image');
            }

            // Handle og_image upload
            if ($request->hasFile('og_image')) {
                $existingImages = $SeoSetting->getMedia('og_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $SeoSetting->addMedia($request->file('og_image'))
                    ->withCustomProperties(['language' => $locale])
                    ->toMediaCollection('og_image');
            }

            // Handle twitter_image upload (only if Twitter custom is enabled)
            if ($request->hasFile('twitter_image') && !empty($data['use_twitter_custom']) && $data['use_twitter_custom'] == 1) {
                $existingImages = $SeoSetting->getMedia('twitter_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $SeoSetting->addMedia($request->file('twitter_image'))
                    ->withCustomProperties(['language' => $locale])
                    ->toMediaCollection('twitter_image');
            } elseif (empty($data['use_twitter_custom']) || $data['use_twitter_custom'] != 1) {
                // If Twitter custom is disabled, delete existing Twitter images for this locale
                $existingImages = $SeoSetting->getMedia('twitter_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
            }

            DB::commit();
            return redirect()->route('backend.seo-setting.index')->with('message', 'SEO Settings Updated Successfully');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus($request)
    {
        try {
            $SeoSetting = $this->model->findOrFail($request->id);
            $SeoSetting->update([
                'is_active' => $request->status,
            ]);
                    
            return response()->json(['message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
