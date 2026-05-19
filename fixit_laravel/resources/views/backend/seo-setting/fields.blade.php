@push('style')
<link href="{{ asset('admin/css/vendors/tagify.css') }}" rel="stylesheet">
@endpush
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
    <div class="col-md-10">
        <ul class="language-list">
            @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                @if(isset($SeoSetting))
                    <li>
                        <a href="{{ route('backend.seo-setting.edit', ['seo_setting' => $SeoSetting->id, 'locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                            <i data-feather="arrow-up-right"></i>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('backend.seo-setting.index', ['locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                            <i data-feather="arrow-up-right"></i>
                        </a>
                    </li>
                @endif
            @empty
                <li>
                    <a href="{{ route('backend.seo-setting.edit', ['seo_setting' => $SeoSetting->id ?? null, 'locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English
                        <i data-feather="arrow-up-right"></i>
                    </a>
                </li>
            @endforelse
        </ul>
    </div>
</div>

<input type="hidden" name="locale" value="{{ request('locale', app()->getLocale()) }}">

<div class="form-group row">
    <label class="col-md-2" for="page_name">{{ __('static.seo_setting.page_name') }}<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" name="page_name" placeholder="{{ __('static.seo_setting.page_name_placeholder') }}" value="{{ isset($SeoSetting->page_name) ? $SeoSetting->page_name : old('page_name') }}">
        @error('page_name')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <!-- Copy Icon -->
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
        <small class="text-muted">{{ __('static.seo_setting.page_name_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="meta_title">{{ __('static.seo_setting.meta_title') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" name="meta_title" placeholder="{{ str_replace(':locale', request('locale', app()->getLocale()), __('static.seo_setting.meta_title_placeholder')) }}" value="{{ isset($SeoSetting) ? $SeoSetting->getTranslation('meta_title', request('locale', app()->getLocale())) : old('meta_title') }}">
        @error('meta_title')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
        <small class="text-muted">{{ __('static.seo_setting.meta_title_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="og_title">{{ __('static.seo_setting.og_title') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" name="og_title" placeholder="{{ str_replace(':locale', request('locale', app()->getLocale()), __('static.seo_setting.og_title_placeholder')) }}" value="{{ isset($SeoSetting) ? $SeoSetting->getTranslation('og_title', request('locale', app()->getLocale())) : old('og_title') }}">
        @error('og_title')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
        <small class="text-muted">{{ __('static.seo_setting.og_title_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="meta_keywords">{{ __('static.seo_setting.meta_keywords') }}<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" name='meta_keywords' value="{{ old('meta_keywords', $SeoSetting->meta_keywords ?? '') }}" placeholder="{{ __('static.seo_setting.meta_keywords_placeholder') }}" autofocus>
        @error('meta_keywords')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
        <small class="text-muted">{{ __('static.seo_setting.meta_keywords_help') }}</small>
    </div>
</div>


<div class="form-group row">
    <label for "meta_description" class="col-md-2">{{ __('static.seo_setting.meta_description') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10">
        <textarea class="form-control ui-widget" placeholder="{{ __('static.seo_setting.meta_description_placeholder') }}" rows="4" id="meta_description" name="meta_description" cols="50" maxlength="150">{{ isset($SeoSetting) ? $SeoSetting->getTranslation('meta_description', request('locale', app()->getLocale())) : old('meta_description') }}</textarea>
        <small class="text-muted d-block mt-1">
            <span id="meta_description_counter">0</span>/150 {{ __('static.seo_setting.characters') }} ({{ __('static.seo_setting.min_characters') }}: 10, {{ __('static.seo_setting.max_characters') }}: 150)
        </small>
        @error('meta_description')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <small class="text-muted">{{ __('static.seo_setting.meta_description_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label for="og_description" class="col-md-2">{{ __('static.seo_setting.og_description') }}({{ request('locale', app()->getLocale()) }})<span>*</span></label>
    <div class="col-md-10">
        <textarea class="form-control ui-widget" placeholder="{{ __('static.seo_setting.og_description_placeholder') }}" rows="4" id="og_description" name="og_description" cols="50" maxlength="150">{{ isset($SeoSetting) ? $SeoSetting->getTranslation('og_description', request('locale', app()->getLocale())) : old('og_description') }}</textarea>
        <small class="text-muted d-block mt-1">
            <span id="og_description_counter">0</span>/150 {{ __('static.seo_setting.characters') }} ({{ __('static.seo_setting.min_characters') }}: 10, {{ __('static.seo_setting.max_characters') }}: 150)
        </small>
        @error('og_description')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <small class="text-muted">{{ __('static.seo_setting.og_description_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label for="meta_image" class="col-md-2">{{ __('static.seo_setting.meta_image') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="file" id="meta_image" name="meta_image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
        @error('meta_image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@if (isset($SeoSetting))
    @php
        $locale = request('locale');
        $mediaItems = $SeoSetting->getMedia('meta_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
    @endphp
    @if ($mediaItems->count() > 0)
        <div class="form-group">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-10">
                    <div class="image-list">
                        @foreach ($mediaItems as $media)
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Service App Thumbnail" class="image-list-item">
                                    <div class="close-icon">
                                        <i data-feather="x"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif


<div class="form-group row">
    <label for="og_image" class="col-md-2">{{ __('static.seo_setting.og_image') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="file" id="og_image" name="og_image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
        @error('og_image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@if (isset($SeoSetting))
    @php
        $locale = request('locale');
        $mediaItems = $SeoSetting->getMedia('og_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
    @endphp
    @if ($mediaItems->count() > 0)
        <div class="form-group">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-10">
                    <div class="image-list">
                        @foreach ($mediaItems as $media)
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Service App Thumbnail" class="image-list-item">
                                    <div class="close-icon">
                                        <i data-feather="x"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

<div class="form-group row">
    <label class="col-md-2" for="robots">{{ __('static.seo_setting.robots') }}</label>
    <div class="col-md-10">
        <select name="robots" id="robots" class="form-control">
            @php
                $robotsValue = old('robots', $SeoSetting->robots ?? 'index,follow');
            @endphp
            <option value="index,follow" {{ $robotsValue === 'index,follow' ? 'selected' : '' }}>{{ __('static.seo_setting.robots_index_follow') }}</option>
            <option value="noindex,follow" {{ $robotsValue === 'noindex,follow' ? 'selected' : '' }}>{{ __('static.seo_setting.robots_noindex_follow') }}</option>
            <option value="index,nofollow" {{ $robotsValue === 'index,nofollow' ? 'selected' : '' }}>{{ __('static.seo_setting.robots_index_nofollow') }}</option>
            <option value="noindex,nofollow" {{ $robotsValue === 'noindex,nofollow' ? 'selected' : '' }}>{{ __('static.seo_setting.robots_noindex_nofollow') }}</option>
        </select>
        @error('robots')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <small class="text-muted">{{ __('static.seo_setting.robots_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="canonical_url">{{ __('static.seo_setting.canonical_url') }}</label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" name="canonical_url" id="canonical_url" placeholder="{{ __('static.seo_setting.canonical_url_placeholder') }}" value="{{ old('canonical_url', $SeoSetting->canonical_url ?? '') }}">
        @error('canonical_url')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
        <small class="text-muted">{{ __('static.seo_setting.canonical_url_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="use_twitter_custom">
        {{ __('static.seo_setting.twitter_custom') }}
    </label>
    <div class="col-md-10">
        <div class="d-flex align-items-center gap-2">
            <label class="switch mb-0">
                <input type="hidden" name="use_twitter_custom" value="0">
                <input class="form-check-input" type="checkbox" id="use_twitter_custom" name="use_twitter_custom" value="1" {{ old('use_twitter_custom', $SeoSetting->twitter_title ?? null ? 'checked' : '') }}>
                <span class="switch-state"></span>
            </label>
            <label class="form-check-label mb-0" for="use_twitter_custom">
                {{ __('static.seo_setting.twitter_custom_label') }}
            </label>
        </div>
        <small class="text-muted d-block mt-1">{{ __('static.seo_setting.twitter_custom_help') }}</small>
    </div>
</div>

<div id="twitter-fields" style="display: none;">
    <div class="form-group row">
        <label class="col-md-2" for="twitter_title">{{ __('static.seo_setting.twitter_title') }} ({{ request('locale', app()->getLocale()) }})</label>
        <div class="col-md-10 input-copy-box">
            <input class="form-control" type="text" name="twitter_title" id="twitter_title" placeholder="{{ str_replace(':locale', request('locale', app()->getLocale()), __('static.seo_setting.twitter_title_placeholder')) }}" value="{{ isset($SeoSetting) ? $SeoSetting->getTranslation('twitter_title', request('locale', app()->getLocale())) : old('twitter_title') }}">
            <span class="input-copy-icon" data-tooltip="Copy">
                <i data-feather="copy"></i>
            </span>
        </div>
        <div class="col-md-10 offset-md-2">
            <small class="text-muted">{{ __('static.seo_setting.twitter_title_help') }}</small>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-2" for="twitter_description">{{ __('static.seo_setting.twitter_description') }} ({{ request('locale', app()->getLocale()) }})</label>
        <div class="col-md-10">
            <textarea class="form-control ui-widget" placeholder="{{ __('static.seo_setting.twitter_description_placeholder') }}" rows="3" id="twitter_description" name="twitter_description" cols="50" maxlength="150">{{ isset($SeoSetting) ? $SeoSetting->getTranslation('twitter_description', request('locale', app()->getLocale())) : old('twitter_description') }}</textarea>
            <small class="text-muted d-block mt-1">
                <span id="twitter_description_counter">0</span>/150 {{ __('static.seo_setting.characters') }} ({{ __('static.seo_setting.min_characters') }}: 10, {{ __('static.seo_setting.max_characters') }}: 150)
            </small>
        </div>
        <div class="col-md-10 offset-md-2">
            <small class="text-muted">{{ __('static.seo_setting.twitter_description_help') }}</small>
        </div>
    </div>

    <div class="form-group row">
        <label for="twitter_image" class="col-md-2">{{ __('static.seo_setting.twitter_image') }}({{ request('locale', app()->getLocale()) }})<span> *</span></label>
        <div class="col-md-10">
            <input class="form-control" type="file" id="twitter_image" name="twitter_image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
            @error('twitter_image')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
    @if (isset($SeoSetting))
        @php
            $locale = request('locale');
            $mediaItems = $SeoSetting->getMedia('twitter_image')->filter(function ($media) use ($locale) {
                return $media->getCustomProperty('language') === $locale;
            });
        @endphp
        @if ($mediaItems->count() > 0)
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <div class="image-list">
                            @foreach ($mediaItems as $media)
                                <div class="image-list-detail">
                                    <div class="position-relative">
                                        <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Twitter Image" class="image-list-item">
                                        <div class="close-icon">
                                            <i data-feather="x"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<div class="form-group row">
    <label class="col-md-2" for="schema_markup">{{ __('static.seo_setting.schema_markup') }}</label>
    <div class="col-md-10">
        <textarea class="form-control ui-widget" placeholder="{{ __('static.seo_setting.schema_markup_placeholder') }}" rows="6" id="schema_markup" name="schema_markup">@php
            $schemaValue = old('schema_markup');
            if (!$schemaValue && isset($SeoSetting->schema_markup)) {
                // If schema_markup is already an array (from cast), encode it
                // If it's a string, check if it's JSON and decode first
                if (is_array($SeoSetting->schema_markup)) {
                    $schemaValue = json_encode($SeoSetting->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } elseif (is_string($SeoSetting->schema_markup)) {
                    // Try to decode if it's a JSON string
                    $decoded = json_decode($SeoSetting->schema_markup, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $schemaValue = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    } else {
                        // If it's not valid JSON, use as is
                        $schemaValue = $SeoSetting->schema_markup;
                    }
                } else {
                    $schemaValue = '';
                }
            }
            echo $schemaValue ?? '';
        @endphp</textarea>
        @error('schema_markup')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <small class="text-muted">{{ __('static.seo_setting.schema_markup_help') }}</small>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="is_active">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($SeoSetting))
                    <input class="form-control" type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $SeoSetting->is_active ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

<div class="text-end">
    <button id="submitBtn" type="submit" class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
</div>

@push('js')
<script src="{{ asset('admin/js/tagify/tagify.js') }}"></script>
@php
    // Calculate media items for validation functions
    $hasMetaImage = false;
    $hasOgImage = false;
    $hasTwitterImage = false;
    
    if (isset($SeoSetting)) {
        $locale = request('locale');
        
        $metaMediaItems = $SeoSetting->getMedia('meta_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
        $hasMetaImage = $metaMediaItems->count() > 0;
        
        $ogMediaItems = $SeoSetting->getMedia('og_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
        $hasOgImage = $ogMediaItems->count() > 0;
        
        $twitterMediaItems = $SeoSetting->getMedia('twitter_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
        $hasTwitterImage = $twitterMediaItems->count() > 0;
    }
@endphp
<script>
    (function() {
        const toggle = document.getElementById('use_twitter_custom');
        const fields = document.getElementById('twitter-fields');
        const syncVisibility = () => {
            if (!toggle) return;
            fields.style.display = toggle.checked ? 'block' : 'none';
        };
        if (toggle) {
            toggle.addEventListener('change', syncVisibility);
            syncVisibility();
        }

        // Character counter for textarea fields
        function updateCounter(textareaId, counterId) {
            const textarea = document.getElementById(textareaId);
            const counter = document.getElementById(counterId);
            if (textarea && counter) {
                const update = () => {
                    const length = textarea.value.length;
                    counter.textContent = length;
                    if (length < 10 || length > 150) {
                        counter.style.color = '#dc3545';
                    } else {
                        counter.style.color = '#28a745';
                    }
                };
                textarea.addEventListener('input', update);
                update(); // Initial count
            }
        }

        updateCounter('meta_description', 'meta_description_counter');
        updateCounter('og_description', 'og_description_counter');
        updateCounter('twitter_description', 'twitter_description_counter');
    })();

    // jQuery validation
    $(document).ready(function() {
        $("#seoSettingForm").validate({
            ignore: [],
            rules: {
                "page_name": "required",
                "mata_title": "required",
                "og_title": "required",
                "meta_description": {
                    required: true,
                    minlength: 10,
                    maxlength: 150
                },
                "og_description": {
                    required: true,
                    minlength: 10,
                    maxlength: 150
                },
                "twitter_title": {
                    required: {
                        depends: function() {
                            return $("#use_twitter_custom").is(":checked");
                        }
                    },
                    maxlength: 255
                },
                "twitter_description": {
                    required: {
                        depends: function() {
                            return $("#use_twitter_custom").is(":checked");
                        }
                    },
                    minlength: {
                        depends: function() {
                            return $("#use_twitter_custom").is(":checked");
                        },
                        param: 10
                    },
                    maxlength: {
                        depends: function() {
                            return $("#use_twitter_custom").is(":checked");
                        },
                        param: 150
                    }
                },
                "meta_image": {
                    required: isMetaImage,
                    accept: "image/jpeg, image/png, image/jpg, image/gif, image/webp"
                },
                "og_image": {
                    required: isOgImage,
                    accept: "image/jpeg, image/png, image/jpg, image/gif, image/webp"
                },
                "twitter_image": {
                    required: function() {
                        if (!$("#use_twitter_custom").is(":checked")) {
                            return false;
                        }
                        return isTwitterImage();
                    },
                    accept: "image/jpeg, image/png, image/jpg, image/gif, image/webp"
                }
            },
            messages: {
                "meta_description": {
                    required: "Meta description is required.",
                    minlength: "Meta description must be at least 10 characters.",
                    maxlength: "Meta description must not exceed 150 characters."
                },
                "og_description": {
                    required: "OG description is required.",
                    minlength: "OG description must be at least 10 characters.",
                    maxlength: "OG description must not exceed 150 characters."
                },
                "twitter_title": {
                    required: "Twitter title is required when override is enabled."
                },
                "twitter_description": {
                    minlength: "Twitter description must be at least 10 characters.",
                    maxlength: "Twitter description must not exceed 150 characters.",
                    required: "Twitter description is required when override is enabled."
                },
                "meta_image": {
                    required: "Meta image is required.",
                    accept: "Only JPEG, PNG, JPG, GIF, and WebP image files are allowed."
                },
                "og_image": {
                    required: "OG image is required.",
                    accept: "Only JPEG, PNG, JPG, GIF, and WebP image files are allowed."
                },
                "twitter_image": {
                    required: "Twitter image is required when override is enabled.",
                    accept: "Only JPEG, PNG, JPG, GIF, and WebP image files are allowed."
                }
            }
        });

        // Update validation when Twitter custom toggle changes
        $("#use_twitter_custom").on('change', function() {
            $("#twitter_description").valid();
            $("#twitter_title").valid();
            $("#twitter_image").valid();
        });

        // Functions to check if images already exist
        function isMetaImage() {
            @if ($hasMetaImage)
                return false;
            @else
                return true;
            @endif
        }

        function isOgImage() {
            @if ($hasOgImage)
                return false;
            @else
                return true;
            @endif
        }

        function isTwitterImage() {
            @if ($hasTwitterImage)
                return false;
            @else
                return true;
            @endif
        }
    });
    document.addEventListener("DOMContentLoaded", function() {
            var input = document.querySelector('input[name=meta_keywords]');
            
            // Convert comma-separated string to Tagify format
            var metaKeywordsData = [];
            if (input.value) {
                // Check if it's already JSON (from Tagify format)
                try {
                    var parsed = JSON.parse(input.value);
                    if (Array.isArray(parsed)) {
                        metaKeywordsData = parsed;
                    }
                } catch(e) {
                    // If not JSON, it's a comma-separated string
                    var keywords = input.value.split(',').map(function(keyword) {
                        return keyword.trim();
                    }).filter(function(keyword) {
                        return keyword.length > 0;
                    });
                    // Convert to Tagify format
                    metaKeywordsData = keywords.map(function(keyword) {
                        return {value: keyword};
                    });
                }
            }

            var tagify = new Tagify(input, {
                whitelist: [],
                enforceWhitelist: false,
                userInput: true
            });

            // Clear the input value and add tags
            input.value = '';
            if (metaKeywordsData.length > 0) {
                tagify.addTags(metaKeywordsData);
            }

            input.form.addEventListener("submit", function() {
                input.value = JSON.stringify(tagify.value);
            });
        });
</script>
@endpush
