<div class="accordion-item" id="banner-{{ $index }}">
    <h2 class="accordion-header" id="heading-{{ $index }}">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse-{{ $index }}" aria-expanded="true">
            {{ $banner['title'] ?? 'New Banner' }}
        </button>
    </h2>
    <div id="collapse-{{ $index }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $index }}">
        <div class="accordion-body">
            <!-- Banner Title -->
            <div class="form-group row">
                <label  class="col-md-2">{{ __('static.title') }}</label>
                <div class="col-md-10">
                <input type="text" class="form-control" name="value_banners[banners][{{ $index }}][title]"
                    value="{{ $banner['title'] ?? '' }}" placeholder="{{ __('static.common.enter_banner_title') }}">
                </div>
            </div>

            <!-- Description -->
            <div class="form-group row">
                <label for="address" class="col-md-2">{{ __('Description') }}<span> </span></label>
                <div class="col-md-10">
                    <textarea class="form-control" rows="4" name="value_banners[banners][{{ $index }}][description]" id="home_banner[description]"
                        placeholder="{{ __('Enter Description') }}" cols="50">{{ $banner['description'] ?? '' }}</textarea>
                    @error('value_banners[banners][{{ $index }}][description]')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="image" class="col-md-2">{{ __('Image') }}</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" id="banners[{{ $index }}][image_url]"
                        name="value_banners[banners][{{ $index }}][image_url]" value="{{ $banner['image_url'] ?? '' }}">
                    @error('value_banners[banners][{{ $index }}][image_url]')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            @isset($banner['image_url'])
            <div class="form-group row">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <div class="image-list">
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ asset($banner['image_url']) }}"
                                        id="banners[{{ $index }}][image_url]" alt="{{ __('static.settings.favicon') }}"
                                        class="image-list-item">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endisset
            <div class="form-group row">
                <label class="col-md-2">{{ __('static.common.sale_tag') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="value_banners[banners][{{ $index }}][sale_tag]"
                        value="{{ $banner['sale_tag'] ?? '' }}" placeholder="{{ __('static.common.enter_sale_tag') }}">
                </div>
            </div>

            <div class="form-group row">
                <label  class="col-md-2">{{ __('static.email_templates.btn_text') }}</label>
                <div class="col-md-10">
                <input type="text" class="form-control" name="value_banners[banners][{{ $index }}][button_text]"
                    value="{{ $banner['button_text'] ?? '' }}" placeholder="{{ __('static.home_pages.enter_button_text') }}">
                </div>
            </div>

            <!-- Redirect Type Selection -->
            <div class="form-group row">
                <label class="col-md-2">{{ __('static.common.redirect_type') }}</label>
                <div class="col-md-10">
                <select class="form-control redirect-type select-2" id="redirectType-{{ $index }}" name="value_banners[banners][{{ $index }}][redirect_type]">
                    <option selected disabled value="">{{ __('static.common.select_redirect_type') }}</option>
                    <option value="service" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'service' ? 'selected' : '' }}>{{ __('static.additional_service.select_service') }}</option>
                    <option value="service-page" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'service-page' ? 'selected' : '' }}>{{ __('static.common.service_page') }}</option>
                    <option value="package" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'package' ? 'selected' : '' }}>{{ __('static.common.select_service_package') }}</option>
                    <option value="service-package-page" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'service-package-page' ? 'selected' : '' }}>{{ __('static.common.service_package_page') }}</option>
                    <option value="category-page" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'category-page' ? 'selected' : '' }}>{{ __('static.common.category_page') }}</option>
                    <option value="external_url" {{ isset($banner['redirect_type']) && $banner['redirect_type'] == 'external_url' ? 'selected' : '' }}>{{ __('static.common.external_url') }}</option>
                </select>
                </div>
            </div>

            <!-- Dynamic ID Selection (Service, Category, Package) -->
            <div class="form-group dynamic-select row" id="dynamicSelect-{{ $index }}" style="display: none;">
                <label class="col-md-2" id="dynamicLabel-{{ $index }}">{{ __('static.common.select') }}</label>
                <div class="col-md-10">
                <select class="form-control select-2" name="value_banners[banners][{{ $index }}][redirect_id]" id="dynamicSelectInput-{{ $index }}">
                    <!-- Options will be populated dynamically based on the selected redirect type -->
                </select>
                </div>
            </div>

            <!-- External URL -->
            <div class="form-group row" id="externalUrl-{{ $index }}" style="display: none;">
                <label class="col-md-2">{{ __('static.common.external_url') }}</label>
                <div class="col-md-10">
                <input type="url" class="form-control" name="value_banners[banners][{{ $index }}][button_url]"
                    value="{{ $banner['button_url'] ?? '' }}" placeholder="{{ __('static.common.enter_external_url') }}">
                    </div>
            </div>
            <button type="button" class="btn btn-danger remove-banner">{{ __('static.common.remove') }}</button>
        </div>
    </div>
</div>


