<div class="accordion-item" id="banner-{{ $index }}">
    <h2 class="accordion-header" id="heading-{{ $index }}">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse-{{ $index }}" aria-expanded="true">
            {{ $banner['title'] ?? 'New Banner' }}
        </button>
    </h2>
    <div id="collapse-{{ $index }}" class="accordion-collapse collapse show"
        aria-labelledby="heading-{{ $index }}">
        <div class="accordion-body">
            <div class="form-group row">
                <label class="col-md-2">{{ __('Title') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="about_us[banners][{{ $index }}][title]"
                        value="{{ $banner['title'] ?? '' }}" placeholder="{{ __('static.common.enter_banner_title') }}">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2">{{ __('Count') }}</label>
                <div class="col-md-10">
                    <input type="number" class="form-control" name="about_us[banners][{{ $index }}][count]"
                        value="{{ $banner['count'] ?? '' }}" placeholder="{{ __('static.common.enter_total_count') }}">
                </div>
            </div>
            <button type="button" class="btn btn-danger remove-banner">{{ __('static.common.remove') }}</button>
        </div>
    </div>
</div>
