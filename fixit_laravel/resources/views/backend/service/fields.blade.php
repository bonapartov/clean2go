@use('app\Helpers\Helpers')
@use('App\Enums\ServiceTypeEnum')
@php
    if (isset($service->destination_location['country_id']) || old('country_id')) {
        $states = \App\Models\State::where(
            'country_id',
            old('country_id', @$service->destination_location['country_id']),
        )->get();
    } else {
        $states = [];
    }
@endphp
<ul class="nav nav-tabs tab-coupon" id="servicetab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') != null ? '' : 'show active' }}" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true" data-original-title="" title="" data-tab="0">
            <i data-feather="settings"></i>{{ __('static.general') }}</a>
    </li>

    <li class="nav-item">
        <a class="nav-link" id="faq-tab" data-bs-toggle="tab" href="#faq" role="tab" aria-controls="faq" aria-selected="true" data-original-title="" title="" data-tab="2">
            <i data-feather="help-circle"></i> {{ __('FAQ\'s') }}</a>
    </li>
</ul>

<div class="tab-content" id="servicetabContent">

        <div class="form-group row">
            <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
            <div class="col-md-10">
                <ul class="language-list">
                    @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                        @if(isset($service))
                            <li>
                                <a href="{{ route('backend.service.edit', ['service' => $service->id, 'locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                                    <i data-feather="arrow-up-right"></i>
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('backend.service.create', ['locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                                    <i data-feather="arrow-up-right"></i>
                                </a>
                            </li>
                        @endif
                    @empty
                        <li>
                            <a href="{{ route('backend.service.edit', ['service' => $service->id, 'locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English
                                <i data-feather="arrow-up-right"></i>
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

    <div class="tab-pane fade {{ session('active_tab') != null ? '' : 'show active' }}" id="general" role="tabpanel" aria-labelledby="general-tab">
        <input type="hidden" name="locale" value="{{ request('locale') }}">
        <div class="form-group row">
            <label class="col-md-2" for="title">{{ __('static.title') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
            <div class="col-md-10 input-copy-box">
                <input class='form-control' type="text" id="title" name="title" value="{{ isset($service->title) ? $service->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}" placeholder="{{ __('static.service.enter_title') }} ({{ request('locale', app()->getLocale()) }})">
                <button class="btn ai-generate-btn" id="generateTitle" data-url="{{ route('backend.custom-ai-model.generate-title') }}" data-locale="{{ request('locale', app()->getLocale()) }}">generate title</button>
                @error('title')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <!-- Copy Icon -->
                <span class="input-copy-icon" data-tooltip="Copy">
                    <i data-feather="copy"></i>
                </span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="zone_id">{{ __('static.service.zone') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <select class="select-2 form-control" id="zone_id" name="zone_id[]" multiple data-placeholder="{{ __('static.service.select_zone') }}">
                    @foreach ($zones as $key => $option)
                        <option value="{{ $key }}"
                            @if (!empty($selected_zones) && in_array($key, $selected_zones)) selected
                            @elseif (is_array(old('zone_id')) && in_array($key, old('zone_id'))) selected
                            @endif>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                @error('zone_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <span class="text-gray mt-1 d-block">
                   {{ __('static.zone.to_add_new_zone') }}
                    <a href="{{ route('backend.zone.create') }}" class="text-primary">
                        <b>{{ __('static.zone.here') }}</b>
                    </a>
                </span>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="category_id">{{ __('static.service.category') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <select id="category_id" class="select-2 form-control categories disable-all"
                    data-placeholder="{{ __('static.service.select_categories') }}" name="category_id[]" multiple>
                    <option value=""></option>
                </select>
                @error('category_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="type">{{ __('static.service.type') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <select class="select-2 form-control" id="type" name="type"
                    data-placeholder="{{ __('static.service.select_type') }}">
                    <option class="select-placeholder" value=""></option>
                    @foreach ([ServiceTypeEnum::FIXED => __('static.fixed'), ServiceTypeEnum::PROVIDER_SITE => __('static.provider_site'), ServiceTypeEnum::REMOTELY => __('static.remotely'), ServiceTypeEnum::SCHEDULED => __('static.scheduled')] as $key => $option)
                        <option class="option" value="{{ $key }}"
                            @if (old('type', isset($service) ? $service->type : '') == $key) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        @if (!auth()->user()->hasRole('provider') && auth()->user()->can('backend.provider.index'))
            <div class="form-group row">
                <label class="col-md-2" for="user_id">{{ __('static.service.provider') }}</label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control user-dropdown" id="user_id" name="user_id" data-placeholder="{{ __('static.service.select_provider') }}">
                        <option class="select-placeholder" value=""></option>
                        @foreach ($providers as $key => $option)
                            <option value="{{ $option->id }}" sub-title="{{ $option->email }}"
                                image="{{ $option->getFirstMedia('image')?->getUrl() }}"
                                data-type="{{ $option->type }}"
                                @if (old('user_id', isset($service) ? $service->user_id : '') == $option->id) selected @endif>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        @endif
        @hasrole('provider')
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}" id="user_id">
        @endhasrole

        <div class="form-group row d-none" id="address_id_wrapper">
            <label class="col-md-2" for="address_id">{{ __('Address') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <select class="select-2 form-control" id="address_id" name="address_id" data-placeholder="Select address">
                    <option class="select-placeholder" value=""></option>
                </select>
                @error('address_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="required_servicemen">{{ __('static.service.required_servicemen') }}<span>*</span></label>
            <div class="col-md-10">
                <input class='form-control' type="number" id="required_servicemen" name="required_servicemen" value="{{ old('required_servicemen', $service->required_servicemen ?? '') }}" placeholder="{{ __('static.service.enter_required_servicemen') }}">
                @error('required_servicemen')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="price">{{ __('static.service.price') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
                    <div class="w-100">
                        <input class='form-control' type="number" id="price" name="price" min="1"
                            value="{{ isset($service->price) ? $service->price : old('price') }}"
                            placeholder="{{ __('static.coupon.price') }}">
                        @error('price')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="discount">{{ __('static.service.discount') }}</label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <div class="w-100 percent">
                        <input class='form-control' id="discount" type="number" name="discount" min="0" value="{{ $service->discount ?? old('discount') }}" placeholder="{{ __('static.service.enter_discount') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
                    </div>
                    <span class="input-group-text">%</span>
                    @error('discount')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="service_rate">{{ __('static.service.service_rate') }}</label>
            <div class="col-md-10">
                <input class='form-control' type="number" id="service_rate" name="service_rate" value="{{ isset($service->service_rate) ? $service->service_rate : old('service_rate') }}" placeholder="{{ __('static.service.service_rate') }}" readonly>
                @error('service_rate')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="per_serviceman_commission">{{ __('static.service.per_serviceman_commission') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <div class="w-100 percent">
                        <input class='form-control' id="per_serviceman_commission" type="number" name="per_serviceman_commission" min="0" value="{{ isset($service->per_serviceman_commission) ? $service->per_serviceman_commission : old('per_serviceman_commission') }}" placeholder="{{ __('static.service.enter_per_serviceman_commission') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
                    </div>
                    <span class="input-group-text">%</span>
                    @error('per_serviceman_commission')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="is_advance_payment_enabled">{{ __('static.service.is_advance_payment_enabled') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($service))
                            <input class="form-control" type="hidden" name="is_advance_payment_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="is_advance_payment_enabled" id="is_advance_payment_enabled" value="1" {{ old('is_advance_payment_enabled', $service->is_advance_payment_enabled ?? false) ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="is_advance_payment_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="is_advance_payment_enabled" id="is_advance_payment_enabled" value="1" {{ old('is_advance_payment_enabled', false) ? 'checked' : '' }}>
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row" id="advance_payment_percentage_container" style="{{ old('is_advance_payment_enabled', isset($service) && $service->is_advance_payment_enabled ? true : false) ? '' : 'display: none;' }}">
            <label class="col-md-2" for="advance_payment_percentage">{{ __('static.service.advance_payment_percentage') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <div class="w-100 percent">
                        <input class='form-control' id="advance_payment_percentage" type="number" name="advance_payment_percentage" min="0" max="100" step="0.01" value="{{ isset($service->advance_payment_percentage) ? $service->advance_payment_percentage : old('advance_payment_percentage') }}" placeholder="{{ __('static.service.enter_advance_payment_percentage') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
                    </div>
                    <span class="input-group-text">%</span>
                    @error('advance_payment_percentage')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="tax_id">{{ __('static.service.taxes') }}</label>
            <div class="col-md-10 error-div disable-select">
                <select class="select-2 form-control tax_id" id="tax_id[]" name="tax_id[]" data-placeholder="{{ __('static.service.select_tax') }}" multiple>
                    <option class="select-placeholder" value=""></option>

                </select>
                @error('tax_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="duration">{{ __('static.service.duration') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="number" min="1" name="duration" id="duration" value="{{ isset($service->duration) ? $service->duration : old('duration') }}" placeholder="{{ __('static.service.enter_duration') }}">
                @error('duration')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="duration_unit">{{ __('static.service.duration_unit') }}<span>
                    *</span></label>
            <div class="col-md-10 error-div">
                <select class="select-2 form-control" id="duration_unit" name="duration_unit" data-placeholder="{{ __('static.service.select_duration_unit') }}">
                    <option class="select-placeholder" value=""></option>
                    @foreach (['hours' => 'Hours', 'minutes' => 'Minutes'] as $key => $option)
                        <option class="option" value="{{ $key }}"
                            @if (old('duration_unit', $service->duration_unit ?? '') === $key) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
                @error('duration_unit')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="video">{{ __('static.video') }}
                ({{ request('locale', app()->getLocale()) }})</label>
            <div class="col-md-10 input-copy-box">
                <input class='form-control' type="text" id="video" name="video" value="{{ isset($service->video) ? $service->getTranslation('video', request('locale', app()->getLocale())) : old('video') }}" placeholder="{{ __('static.service.enter_video') }} ({{ request('locale', app()->getLocale()) }})">
                @error('video')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <!-- Copy Icon -->
                <span class="input-copy-icon" data-tooltip="Copy">
                    <i data-feather="copy"></i>
                </span>
            </div>
        </div>
        {{-- Единое поле изображения --}}
        <div class="form-group row">
            <label for="main_image" class="col-md-2">{{ __('static.categories.image') }}
                ({{ request('locale', app()->getLocale()) }})</label>
            <div class="col-md-10">
                <input class="form-control" type="file" id="main_image" name="main_image" accept="image/*">
                <small class="text-muted d-block mt-1">{{ __('static.service.main_image_hint') }}</small>
                <div id="main_image_preview" style="display:none; margin-top:10px;">
                    <img id="main_image_preview_img" style="max-height:150px; border-radius:6px;">
                </div>
            </div>
        </div>

        @if (isset($service))
            @php
                $locale = request('locale');
                $thumbItems = $service->getMedia('thumbnail')->filter(fn($m) => $m->getCustomProperty('language') === $locale);
            @endphp
            @if ($thumbItems->count() > 0)
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-2 text-muted small pt-2">{{ __('static.categories.thumbnail') }}</div>
                        <div class="col-md-10">
                            <div class="image-list">
                                @foreach ($thumbItems as $media)
                                    <div class="image-list-detail">
                                        <div class="position-relative">
                                            <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Thumbnail" class="image-list-item">
                                            <div class="close-icon"><i data-feather="x"></i></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Раздельная загрузка --}}
        <div class="form-group row">
            <div class="col-md-10 offset-md-2">
                <a href="#individual-images" data-bs-toggle="collapse" class="text-primary small">
                    <i data-feather="settings" style="width:14px;height:14px;"></i>
                    {{ __('static.service.individual_images') }}
                </a>
            </div>
        </div>
        <div id="individual-images" class="collapse">
            <div class="form-group row">
                <label for="thumbnail" class="col-md-2">{{ __('static.categories.thumbnail') }}
                    ({{ request('locale', app()->getLocale()) }})</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" id="thumbnail" name="thumbnail">
                    @error('thumbnail')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="image" class="col-md-2">{{ __('static.categories.image') }}
                    ({{ request('locale', app()->getLocale()) }})</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" id="image[]" name="image[]" multiple>
                    @error('image')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
            @if (isset($service))
                @php
                    $mediaItems = $service->getMedia('image')->filter(fn($m) => $m->getCustomProperty('language') === $locale);
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
                                                <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Image" class="image-list-item">
                                                <div class="close-icon"><i data-feather="x"></i></div>
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
                <label for="web_thumbnail" class="col-md-2">{{ __('static.categories.web_thumbnail') }}
                    ({{ request('locale', app()->getLocale()) }})</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" id="web_thumbnail" name="web_thumbnail">
                    @error('web_thumbnail')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
            @if (isset($service))
                @php
                    $mediaItems = $service->getMedia('web_thumbnail')->filter(fn($m) => $m->getCustomProperty('language') === $locale);
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
                                                <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Web Thumbnail" class="image-list-item">
                                                <div class="close-icon"><i data-feather="x"></i></div>
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
                <label for="web_images" class="col-md-2">{{ __('static.categories.web_images') }}
                    ({{ request('locale', app()->getLocale()) }})</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" id="web_images" name="web_images[]" multiple>
                    @error('web_images')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
            @if (isset($service))
                @php
                    $mediaItems = $service->getMedia('web_images')->filter(fn($m) => $m->getCustomProperty('language') === $locale);
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
                                                <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Web Image" class="image-list-item">
                                                <div class="close-icon"><i data-feather="x"></i></div>
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
            <label for="image" class="col-md-2">{{ __('static.page.content') }}
                ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
            <div class="col-md-10">
                <div class="input-copy-box">
                    <textarea class="summary-ckeditor" id="content" name="content" cols="65" rows="5">{{ isset($service->content) ? $service->getTranslation('content', request('locale', app()->getLocale())) : old('content') }}</textarea>
                    <button class="btn ai-generate-content-btn" id="generateContent" data-url="{{ route('backend.custom-ai-model.generate-content') }}" data-content_type="service" data-locale="{{ request('locale', app()->getLocale()) }}">generate content</button>
                </div>
                @error('content')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="role">{{ __('static.service.is_random_related_services') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($service))
                            <input class="form-control" type="hidden" name="is_random_related_services" value="0">
                            <input class="form-check-input" id="is_related" type="checkbox" name="is_random_related_services" id="" value="1" {{ $service->is_random_related_services ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="is_random_related_services" value="0">
                            <input class="form-check-input" id="is_related" type="checkbox" name="is_random_related_services" id="" value="1">
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row services" @if (isset($service) && $service->is_random_related_services) style="display:none" @endif>
            <label class="col-md-2" for="service_id">{{ __('static.service.related_services') }}</label>
            <div class="col-md-10 error-div select-dropdown">
                <select id="related_services" class="select-2 form-control user-dropdown" search="true" name="service_id[]" data-placeholder="{{ __('static.service.select_related_services') }}" multiple>
                    <option value=""></option>
                </select>
                @error('service_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2" for="is_featured">{{ __('static.service.is_featured') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($service))
                            <input class="form-control" type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                {{ $service->is_featured ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="status">{{ __('static.status') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($service))
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" {{ $service->status ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
        </div>
    </div>

    <div class="tab-pane fade" id="faq" role="tabpanel" aria-labelledby="faq-tab">
        <div class="d-flex justify-content-end position-relative faq-generate-box">
            <button type="button"
                    class="btn ai-generate-faq-btn faq-generate"
                    data-url="{{ route('backend.custom-ai-model.generate-faq') }}"
                    data-locale="{{ request('locale', app()->getLocale()) }}">
                {{ __('static.service.generate_faqs') ?? 'Generate FAQs' }}
            </button>
        </div>
        <div class="faq-container mb-2">
            @if (isset($service) && !$service->faqs->isEmpty())
                @foreach ($service->faqs as $index => $faq)
                    <div class="faqs-structure mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-11 col-sm-10 col-12">
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="faqs[{{ $index }}][question]">{{ __('static.service.question') }}
                                        ({{ request('locale', app()->getLocale()) }})
                                    </label>
                                    <div class="col-md-10 input-copy-box">
                                        <input class="form-control" type="text" name="faqs[{{ $index }}][question]" id="faqs[{{ $index }}][question]" placeholder="{{ __('static.service.enter_question') }} ({{ request('locale', app()->getLocale()) }})" value="{{ $faq->getTranslation('question', request('locale', app()->getLocale())) }}">
                                        <!-- Copy Icon -->
                                        <span class="input-copy-icon" data-tooltip="Copy">
                                            <i data-feather="copy"></i>
                                        </span>
                                    </div>
                                </div>
                                <input type="hidden" name="faqs[{{ $index }}][id]" value="{{ $faq['id'] }}">
                                <div class="form-group row">
                                    <label class="col-md-2" for="faqs[{{ $index }}][answer]">{{ __('static.service.answer') }}({{ request('locale', app()->getLocale()) }})</label>
                                    <div class="col-md-10 input-copy-box">
                                        <textarea class="form-control" name="faqs[{{ $index }}][answer]" id="faqs[{{ $index }}][answer]" placeholder="{{ __('static.service.enter_answer') }} ({{ request('locale', app()->getLocale()) }})" cols="30" rows="5">{{ $faq->getTranslation('answer', request('locale', app()->getLocale())) }}</textarea>
                                        <!-- Copy Icon -->
                                        <span class="input-copy-icon" data-tooltip="Copy">
                                            <i data-feather="copy"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 col-sm-2 col-12">
                                <div class="remove-faq mt-4">
                                    <i data-feather="trash-2" class="text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="faqs-structure faq-page mb-4">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <div class="form-group row">
                                <label class="col-md-2" for="faqs[0][question]">{{ __('static.service.question') }}</label>
                                <div class="col-md-10">
                                    <input class="form-control faq-input" type="text" name="faqs[0][question]" id="faqs[0][question]" value="{{ old('faqs[0][question]') }}" placeholder="{{ __('static.service.enter_question') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2" for="faqs[0][answer]">{{ __('static.service.answer') }}</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" name="faqs[0][answer]" id="faqs[0][answer]" placeholder="{{ __('static.service.enter_answer') }}" cols="30" rows="5">{{ old('faqs[0][answer]') }}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <div class="remove-faq mt-4">
                                        <i data-feather="trash-2" class="text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-11 col-10">
            <div class="form-group row mt-4">
                <label class="col-md-2"></label>
                <div class="col-md-10">
                    <button type="button" id="add-faq" class="btn btn-secondary add-faq">{{ __('static.service.add_faq') }}</button>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
            <button class="btn btn-primary submitBtn spinner-btn" type="submit">{{ __('static.submit') }}</button>
        </div>
    </div>
</div>


@push('js')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('app.yandex_map_api_key') }}&lang=ru_RU" type="text/javascript"></script>
    <script src="{{ asset('admin/js/custom-ai/ai-content-generation.js') }}"></script>
    <script>
        (function($) {

            "use strict";
            $(document).ready(function() {

                // ========== ADD THIS CODE TO FIX SELECT2 SEARCH INPUT FIELD NOT CLICKABLE ==========

                // Fix for Select2 search input not clickable in modals
                $(document).on('select2:open', function(e) {
                    const dropdown = $('.select2-container--open');
                    dropdown.css('z-index', 9999);
                });

                // Initialize all Select2 dropdowns with proper configuration
                $('.select-2').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : document.body
                    });
                });

                // Reinitialize when modals are shown to fix z-index issues
                $('.modal').on('shown.bs.modal', function() {
                    $(this).find('.select-2').select2({
                        dropdownParent: $(this)
                    });
                });

                // ========== END OF SELECT2 FIX CODE ==========

                // Подставляем комиссию из выбранной категории (только при создании)
                var commissionUserEdited = {{ isset($service) ? 'true' : 'false' }};
                $('#category_id').on('select2:select select2:unselect', function() {
                    if (commissionUserEdited) return;
                    var ids = $(this).val();
                    if (!ids || !ids.length) return;
                    var firstId = ids[0];
                    $.getJSON('/backend/category/' + firstId + '/commission', function(data) {
                        if (data.commission > 0 && !commissionUserEdited) {
                            $('#per_serviceman_commission').val(data.commission);
                        }
                    });
                });
                $('#per_serviceman_commission').on('input', function() {
                    commissionUserEdited = true;
                });

                const providerDropdown = document.getElementById('user_id');
                const servicemenInput = document.getElementById('required_servicemen');
                const commissionInput = document.getElementById('per_serviceman_commission');

                function updateServicemenField() {
                    const selectedOption = providerDropdown.options[providerDropdown.selectedIndex];
                    const providerType = selectedOption?.getAttribute('data-type');

                    if (providerType === 'freelancer') {
                        servicemenInput.value = 1;
                        commissionInput.value = 0;
                        servicemenInput.setAttribute('readonly', true);
                        commissionInput.setAttribute('readonly', true);
                    } else {
                        servicemenInput.removeAttribute('readonly',false);
                        commissionInput.removeAttribute('readonly',false);
                    }
                }

                setTimeout(updateServicemenField, 300);

                $('.user-dropdown').on('select2:select', function (e) {
                    updateServicemenField();
                });

                const typeSelect = $('#type');
                const addressWrapper = $('#address_id_wrapper');
                const addressSelect = $('#address_id');
                const addressTab = $('#address_tab');
                const editAddressTab = $('#edit_address_tab');

                function toggleTabs() {
                    const isCreateRoute = window.location.pathname === '/backend/service/create';
                    const isProviderSite = typeSelect.val() === "{{ ServiceTypeEnum::PROVIDER_SITE }}";
                    if (isProviderSite) {
                        addressWrapper.removeClass('d-none');
                        const providerId = $('#user_id').val() || $('select#user_id').val();
                        loadProviderAddresses(providerId);
                    } else {
                        addressWrapper.addClass('d-none');
                        addressSelect.val('').trigger('change');
                    }
                    if (isProviderSite && !isCreateRoute) {
                        addressTab.addClass('d-none');
                        editAddressTab.removeClass('d-none');
                    } else {

                        editAddressTab.addClass('d-none');
                        addressTab.removeClass('d-none');
                    }
                }
                toggleTabs();
                typeSelect.on('change', toggleTabs);

                $('.user-dropdown').on('change', function(){
                    if (typeSelect.val() === "{{ ServiceTypeEnum::PROVIDER_SITE }}"){
                        const providerId = $(this).val();
                        loadProviderAddresses(providerId);
                    }
                });

                function loadProviderAddresses(providerId){
                    if(!providerId){
                        addressSelect.empty();
                        return;
                    }
                    const url = "{{ route('backend.get-provider-addresses', ['provider_id' => 'PROVIDER_ID']) }}".replace('PROVIDER_ID', providerId);
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(addresses){
                            addressSelect.empty();
                            addressSelect.append('<option class="select-placeholder" value=""></option>');
                            $.each(addresses, function(_, address){
                                const text = address.address ?? 'Address #' + address.id;
                                const option = new Option(text, address.id, false, false);
                                addressSelect.append(option);
                            });
                            @isset($service)
                                addressSelect.val("{{ $service->address_id ?? '' }}").trigger('change');
                            @endisset
                        }
                    });
                }

                function isTabAddress() {
                    const isCreateRoute = window.location.pathname === '/backend/service/create';
                    const providerSiteType = "{{ ServiceTypeEnum::PROVIDER_SITE }}";

                    if (isCreateRoute || typeSelect.val() !== providerSiteType) {
                        return true;
                    }
                    return false;
                }

                function isTabDestinationAddress() {
                    const isCreateRoute = window.location.pathname !== '/backend/service/create';
                    const providerSiteType = "{{ ServiceTypeEnum::PROVIDER_SITE }}";
                    if (isCreateRoute && typeSelect.val() === providerSiteType) {

                        return true;
                    }
                    return false;
                }

                function initializeGoogleAutocomplete() {
                    ymaps.ready(function() {
                        try {
                            $(".autocomplete-google").each(function() {
                                const input = this;
                                const suggestView = new ymaps.SuggestView(input, {
                                    provider: 'yandex#map',
                                    results: 5
                                });
                                suggestView.events.add('select', function(e) {
                                    const item = e.get('item');
                                    if (item && item.value) getAddressDetails(item.value);
                                });
                            });
                        } catch (error) {
                            console.error("Yandex Maps Suggest error:", error);
                        }
                    });
                }

                function populateStates(countryId, state) {
                    $(".select-state").html('');
                    $.ajax({
                        url: "{{ url('/states') }}",
                        type: "POST",
                        data: {
                            country_id: countryId,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result) {
                            $.each(result.states, function(key, value) {

                                console.log(key, value, "TEAXUDI")
                                $('.select-state').append(
                                    `<option value="${value.id}" ${value.id === state ? 'selected' : ''}>${value.name}</option>`
                                );
                            });
                            console.log(result,defaultStateId)
                            var defaultStateId = $(".select-state").data("default-state-id");
                            if (defaultStateId !== '') {
                                $('.select-state').val(defaultStateId);
                            }
                        }
                    });
                }

                function getAddressDetails(placeId) {
                    $.ajax({
                        url: "/backend/google-address",
                        type: 'GET',
                        dataType: "json",
                        data: {
                            placeId: placeId,
                        },
                        success: function(data) {
                            console.log("address data", data.location)
                            $('#latitude').val(data.location.lat);
                            $('#longitude').val(data.location.lng);
                            $('#lat').val(data.location.lat);
                            $('#lng').val(data.location.lng);

                            $('#city').val(data.locality);
                            $('#postal_code').val(data.postal_code);
                            $('#postal_code').val(data.postal_code);
                            var street = '';
                            if (data.streetNumber) {
                                street += data.streetNumber + ", ";
                            }

                            if (data.streetName) {
                                street += data.streetName + ", ";
                            }
                            $('#street_address_1').val(street);
                            $('#area').val(data.area);
                            var countryId = data.country_id;
                            if (countryId) {
                                $('#country_id').val(countryId).trigger('change');
                            }

                            var stateId = data.state_id;
                            if (stateId) {
                                console.log("called");
                                $('.select-state').attr('data-default-state-id', stateId);
                                $('.select-state').val(stateId).trigger('change');
                                populateStates(countryId, stateId);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log("AJAX error in getAddressDetails:", textStatus,
                                errorThrown);
                        }
                    });
                }

                initializeGoogleAutocomplete();


                $("#serviceForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "category_id[]": "required",
                        "type": "required",
                        "user_id": {
                            required: false
                        },
                        "required_servicemen": "required",
                        "price": "required",
                        "per_serviceman_commission": "required",
                        "advance_payment_percentage": {
                            required: function() {
                                const isScheduled = $('#type').val() === "{{ ServiceTypeEnum::SCHEDULED }}";
                                return !isScheduled && $('#is_advance_payment_enabled').prop('checked');
                            }
                        },
                        "duration": "required",
                        "duration_unit": "required",
                        "image[]": {
                            required: false,
                        },
                        "thumbnail": {
                            required: false,
                        },
                        "service_id[]": {
                            required: false
                        },
                        "country_id": {
                            required: isTabAddress
                        },
                        "state_id": {
                            required: isTabAddress
                        },
                        "postal_code": {
                            required: isTabAddress
                        },
                        "city": {
                            required: isTabAddress
                        },
                        "address": {
                            required: isTabAddress
                        },
                        "destination[country_id]": {
                            required: isTabDestinationAddress
                        },
                        "destination[state_id]": {
                            required: isTabDestinationAddress
                        },
                        "destination[postal_code]": {
                            required: isTabDestinationAddress
                        },
                        "destination[city]": {
                            required: isTabDestinationAddress
                        },
                        "destination[area]": {
                            required: isTabDestinationAddress
                        },
                        "destination[address]": {
                            required: isTabDestinationAddress
                        },
                    },
                    messages: {
                        "image[]": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });
                $('#add-faq').unbind().click(function() {
                    var allInputsFilled = true;

                    $('.faqs-structure').find('.form-group.row').each(function() {
                        var question = $(this).find('input[name^="faqs"]').val()?.trim();
                        var answer = $(this).find('input[name^="faqs"]').val()?.trim();

                        if (question === '' || answer === '') {
                            allInputsFilled = false;
                            $(this).find('input[name^="faqs"]').addClass('is-invalid');
                            $(this).find('input[name^="faqs"]').removeClass('is-valid');
                        } else {
                            $(this).find('input[name^="faqs"]').removeClass('is-invalid');
                        }

                    });


                    if (!allInputsFilled) {
                        return;
                    }

                    var inputGroup = $('.faqs-structure').last().clone();
                    var newIndex = $('.faqs-structure').length;

                    inputGroup.find('input[name^="faqs"]').each(function() {
                        var oldName = $(this).attr('name');
                        var newName = oldName.replace(/\[\d+\]/, '[' + newIndex + ']');
                        $(this).attr('name', newName).val('');
                    });

                    inputGroup.find('textarea[name^="faqs"]').each(function() {
                        var oldName = $(this).attr('name');
                        var newName = oldName.replace(/\[\d+\]/, '[' + newIndex + ']');
                        $(this).attr('name', newName).val('');
                    });

                    $(".faq-container").append(inputGroup);

                });

                $(document).on('click', '.remove-faq', function() {
                    $(this).closest('.faqs-structure').remove();
                });

                $('#price, #discount').on('input', updateServiceRate);
                updateServiceRate();

                $('select[name="type"]').on('change', function(e) {
                    var selectedType = $(this).val();
                });

                $(document).on('change', '#is_related', function(e) {
                    let status = $(this).prop('checked') == true ? 1 : 0;
                    if (status == true) {
                        $('.services').hide();
                    } else {
                        $('.services').show();
                    }
                });

                // Toggle advance payment percentage field
                $(document).on('change', '#is_advance_payment_enabled', function(e) {
                    let isEnabled = $(this).prop('checked') == true;
                    if (isEnabled) {
                        $('#advance_payment_percentage_container').show();
                        $('#advance_payment_percentage').attr('required', true);
                    } else {
                        $('#advance_payment_percentage_container').hide();
                        $('#advance_payment_percentage').removeAttr('required').val('');
                    }
                });

                // Hide advance payment fields when service type is scheduled
                function toggleAdvancePaymentFields() {
                    const selectedType = $('#type').val();
                    const isScheduled = selectedType === "{{ ServiceTypeEnum::SCHEDULED }}";
                    const advancePaymentRow = $('#is_advance_payment_enabled').closest('.form-group.row');
                    const advancePaymentPercentageRow = $('#advance_payment_percentage_container').closest('.form-group.row');

                    if (isScheduled) {
                        advancePaymentRow.hide();
                        advancePaymentPercentageRow.hide();
                        $('#is_advance_payment_enabled').prop('checked', false);
                        $('#advance_payment_percentage').removeAttr('required').val('');
                    } else {
                        advancePaymentRow.show();
                        // Show percentage field only if advance payment is enabled
                        if ($('#is_advance_payment_enabled').prop('checked')) {
                            advancePaymentPercentageRow.show();
                        }
                    }
                }

                // Call on type change
                $('#type').on('change', function() {
                    toggleAdvancePaymentFields();
                });

                // Call on page load
                toggleAdvancePaymentFields();

                var initialProviderID = $('input[name="user_id"]').val() || $('select[name="user_id"]').val();

                if (initialProviderID) {
                    loadServices(initialProviderID);
                }
                @isset($service)
                    var selectedServices = {!! json_encode($service->related_services->where('id', '!=', $service->id)->pluck('id')->toArray() ?? []) !!};
                    loadServices(initialProviderID, selectedServices);
                @endisset

                $('select[name="user_id"]').on('change', function() {
                    var providerID = $(this).val();
                    loadServices(providerID);
                });

                function loadServices(providerID, selectedServices) {

                    let url = "{{ route('backend.get-provider-services', '') }}";
                    if (providerID) {
                        $.ajax({
                            url: url,
                            type: "GET",
                            data: {
                                provider_id: providerID,
                            },
                            success: function(data) {
                                $('#related_services').empty();
                                $.each(data, function(id, optionData) {
                                    var option = new Option(optionData.title, optionData
                                        .id);
                                    if (optionData.media.length > 0) {
                                        var imageUrl = optionData.media[0].original_url
                                        $(option).attr("image", imageUrl);
                                    }

                                    if (selectedServices && selectedServices.includes(
                                            String(optionData.id))) {
                                        $(option).prop("selected", true);
                                    }

                                    $('#related_services').append(option);
                                });

                                $('#related_services').val(selectedServices).trigger('change');
                            },
                        });
                    }
                }

                setTimeout(function () {
                    @isset($service)
                        var selectedCategories = {!! json_encode($service->categories->pluck('id')->toArray()) !!};
                        var selectedTaxes = {!! json_encode($service->taxes->pluck('id')->map(fn($id) => (int) $id)->toArray()) !!};
                        var initialZoneIDs = $('select[name="zone_id[]"]').val();
                        if (initialZoneIDs && initialZoneIDs.length > 0) {
                            loadCategories(initialZoneIDs, selectedCategories);
                            loadTaxes(initialZoneIDs[0], selectedTaxes);
                        } else {
                            loadCategories(['selectAll'], selectedCategories);
                        }
                    @else
                        loadCategories(['selectAll'], []);
                    @endisset
                }, 500);

            var zoneUpdatingFromCategory = false;

            $('select[name="zone_id[]"]').on('change', function() {
                if (zoneUpdatingFromCategory) return;
                var zoneId = $(this).val();
                if (!zoneId || !zoneId.length) return;
                loadCategories(zoneId);
                loadTaxes(zoneId[0]);
            });

            $('#category_id').on('select2:select select2:unselect', function() {
                var categoryIds = $(this).val();
                if (!categoryIds || !categoryIds.length) return;
                $.getJSON('{{ route('backend.get-category-zones') }}', { category_ids: categoryIds }, function(zones) {
                    if (!zones.length) return;
                    var zoneIds = zones.map(function(z) { return String(z.id); });
                    zoneUpdatingFromCategory = true;
                    $('select[name="zone_id[]"]').val(zoneIds).trigger('change.select2');
                    zoneUpdatingFromCategory = false;
                });
            });

            function loadCategories(zoneId, selectedCategories = []) {
                let url = "{{ route('backend.get-zone-categories') }}";

                let zoneData = Array.isArray(zoneId) ? zoneId : [zoneId];

                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        zone_id: zoneData,
                    },
                    success: function(data) {
                        $('#category_id').empty();

                        $.each(data, function(id, optionData) {
                            var option = new Option(optionData, id);
                            if (selectedCategories && selectedCategories.includes(id)) {
                                $(option).prop("selected", true);
                            }
                            $('#category_id').append(option);
                        });

                        $('#category_id').val(selectedCategories).trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                    }
                });
            }

                function loadTaxes(zoneID, selectedTaxes = []) {
                    let url = "{{ route('backend.get-zone-taxes') }}";

                    $.ajax({
                        url: url,
                        type: "GET",
                        data: {
                            zone_id: zoneID,
                        },
                        success: function(data) {
                            let $taxSelect = $('.tax_id');
                            $taxSelect.empty();

                            $.each(data, function(index, tax) {
                                let isSelected = selectedTaxes.length > 0
                                    ? selectedTaxes.includes(tax.id)
                                    : true; // default to select all if not specified

                                let option = new Option(tax.name, tax.id, isSelected, isSelected);
                                $taxSelect.append(option);
                            });

                            // Always set the values and trigger change
                            const autoSelected = selectedTaxes.length > 0
                                ? selectedTaxes
                                : data.map(t => t.id);

                            $taxSelect.val(autoSelected).trigger('change');

                            // Disable the dropdown to make it read-only
                            $taxSelect.prop('disabled', true);

                            console.log("Taxes loaded and dropdown disabled.");
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading taxes:", error);
                        }
                    });
                }
            });
        })(jQuery);

        function updateServiceRate() {
            var price = parseFloat($('#price').val()) || 0;
            var discount = parseFloat($('#discount').val()) || 0;
            var serviceRate = price - (price * (discount / 100));
            $('#service_rate').val(serviceRate.toFixed(2));
        }

        function isServiceImage() {
            @if (isset($service->media) && !$service->media->isEmpty())
                return false;
            @else
                return true;
            @endif
        }

        function isProvider() {
            @if (auth()->user()->hasrole('provider'))
                return false;
            @else
                return true;
            @endif
        }

        function isServiceRelated() {
            return $('#is_related').prop('checked') ? false : true;
        }

        $('.disable-all').on('change', function() {
                const $currentSelect = $(this);
                const selectedValues = $currentSelect.val();
                const allOption = "selectAll";

                if (selectedValues && selectedValues.includes(allOption)) {

                    $currentSelect.val([allOption]);
                    $currentSelect.find('option').not(`[value="${allOption}"]`).prop('disabled', true);
                } else {

                    $currentSelect.find('option').prop('disabled', false);
                }
            });

            // Превью главного изображения
            $('#main_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#main_image_preview_img').attr('src', e.target.result);
                        $('#main_image_preview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#main_image_preview').hide();
                }
            });
    </script>
@endpush
