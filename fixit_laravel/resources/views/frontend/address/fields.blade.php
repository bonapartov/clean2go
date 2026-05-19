@use('app\Helpers\Helpers')
@use('app\Models\State')
@php
    $countries = Helpers::getCountries();
    $countryCodes = Helpers::getCountryCodes();
    $states = [];
    if (isset($address->country_id) || old('country_id')) {
        $states = State::where('country_id', old('country_id', @$address->country_id))?->get();
    }
@endphp
<div class="row g-3">
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="role">{{ __('static.address_category') }}</label>
            <div class="form-group category-list">
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="home" value="Home" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Home' ? 'checked' : '' }}@endisset
                        checked>
                    <label class="form-check-label mb-0 cursor-pointer" for="home">
                        {{ __('static.home') }}
                        <span class="check-box"></span>
                    </label>
                </div>
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="work" value="Work" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Work' ? 'checked' : '' }}@endisset>
                    <label class="form-check-label mb-0 cursor-pointer" for="work">{{ __('static.work') }} <span class="check-box"></span></label>
                </div>
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="other" value="Other" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Other' ? 'checked' : '' }}@endisset>
                    <label class="form-check-label mb-0 cursor-pointer" for="other">{{ __('static.other') }} <span class="check-box"></span></label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="alternative_name">{{ __('static.address.alternative_name') }}</label>
            <div class="w-100">
                <input class='form-control' type="text" name="alternative_name" id="alternative_name"
                    value="{{ $address->alternative_name ?? old('alternative_name') }}"
                    placeholder="{{ __('static.address.enter_alternative_name') }}">
                @error('alternative_name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="alternative_phone">{{ __('static.address.alternative_phone') }}</label>
            <div class="w-100">
                <div class="input-group phone-detail">
                    <select class="select-2 form-control select-country-code" name="code" data-placeholder="">
                        @php
                            $default = old('alternative_code', $address->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                        @endphp
                        <option value="" selected></option>
                        @foreach (Helpers::getCountryCodes() as $key => $option)
                            <option class="option" value="{{ $option->phone_code }}"
                                data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                @if ($option->phone_code == $default) selected @endif
                                data-default="old('alternative_code')">
                                +{{ $option->phone_code }}
                            </option>
                        @endforeach
                    </select>
                    @error('alternative_code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <input class="form-control" type="number" name="alternative_phone" id="alternative_phone"
                        value="{{ $address->alternative_phone ?? old('alternative_phone') }}" min="1"
                        placeholder="{{ __('static.address.enter_alternative_phone') }}">
                    @error('alternative_phone')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="lat" id="lat"
        value="{{ isset($address->lat) ? $address->lat : old('lat') }}">
    <input type="hidden" name="lng" id="lng"
        value="{{ isset($address->lng) ? $address->lng : old('lng') }}">

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="address">{{ __('static.address.address') }}</label>
            <div class="w-100">
                <textarea class="form-control ui-widget autocomplete-yandex" placeholder="{{ __('static.address.enter_address') }}"
                    rows="4" id="address" name="address" cols="50">{{ $address->address ?? old('address') }}</textarea>
                @error('address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="street_address_1">{{ __('static.address.street_address') }}</label>
            <div class="w-100">
                <input class='form-control' type="text" name="street_address" id="street_address_1"
                    value="{{ $address->street_address ?? old('street_address') }}"
                    placeholder="{{ __('static.address.enter_street_address') }}">
                @error('street_address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="country_id">{{ __('static.users.country') }}</label>
            <div class="w-100 error-div select-dropdown">
                <select class="select-2 form-control select-country" id="country_id" name="country_id"
                    data-placeholder="{{ __('static.users.select_country') }}">
                    <option class="select-placeholder" value=""></option>
                    @forelse ($countries as $key => $option)
                        <option class="option" value={{ $key }}
                            @if (old('country_id', $address->country_id ?? '') == $key) selected @endif> {{ $option }}</option>
                    @empty
                        <option value="" disabled></option>
                    @endforelse
                </select>
                @error('country_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="state_id">{{ __('static.users.state') }}</label>
            <div class="w-100 error-div select-dropdown">
                <select class="select-2 form-control select-state"
                    data-default-state-id="{{ old('state_id', $address->state_id ?? '') }}" id="state_id"
                    name="state_id" data-placeholder="{{ __('static.users.select_state') }}">
                    <option class="select-placeholder" value=""></option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}"
                            @if (old('state_id', $address->state_id ?? '') == $state->id) selected @endif>{{ $state->name }}</option>
                    @endforeach
                </select>
                @error('state_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="city">{{ __('static.city') }}</label>
            <div class="w-100">
                <input class='form-control' type="text" name="city" id="city"
                    value="{{ $address->city ?? old('city') }}" placeholder="{{ __('static.users.enter_city') }}">
                @error('city')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="postal_code">{{ __('static.postal_code') }}</label>
            <div class="w-100">
                <input class='form-control' type="text" name="postal_code" id="postal_code"
                    value="{{ isset($address->postal_code) ? $address->postal_code : old('postal_code') }}"
                    placeholder="{{ __('static.users.postal_code') }}">
                @error('postal_code')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    @if(isset($address) && !$address->is_primary)
        <div class="col-12">
            <div class="set-address-box">
                <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                <input class="form-check-input" type="checkbox" name="is_primary" value="1">
            </div>
        </div>
    @elseif (!isset($address))
        <div class="col-12">
            <div class="set-address-box">
                <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                <input class="form-check-input" type="checkbox" name="is_primary" value="1">
            </div>
        </div>
    @endif

</div>


@push('js')
<script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('app.yandex_map_api_key') }}&lang=ru_RU" type="text/javascript"></script>

<script>
    (function($) {
        "use strict";

        $(document).ready(function() {
            
            // ========== THIS CODE TO FIX SELECT2 SEARCH INPUT FIELD NOT CLICKABLE ==========

                // Fix for Select2 search input not clickable in modals
                $(document).on('select2:open', function(e) {
                    const dropdown = $('.select2-container--open');
                    dropdown.css('z-index', 9999);
                });

                $('.select-2').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : document.body
                    });
                });

                $('.modal').on('shown.bs.modal', function() {
                    $(this).find('.select-2').select2({
                        dropdownParent: $(this)
                    });
                });

            // ========== END OF SELECT2 ISSUE ==========

            // When any address modal is opened
            $(document).on('shown.bs.modal', '.address-modal', function () {
                const $modal = $(this);

                // Initialize Yandex Suggest
                ymaps.ready(function() {
                    $modal.find(".autocomplete-yandex").each(function () {
                        const input = this;

                        if (!input._suggestInitialized) {
                            const suggestView = new ymaps.SuggestView(input, {
                                results: 5
                            });

                            suggestView.events.add('select', function (e) {
                                const item = e.get('item');
                                if (item && item.value) {
                                    getAddressDetails(item.value, $modal);
                                }
                            });

                            input._suggestInitialized = true;
                        }
                    });
                });

                // Trigger state population if country is already selected
                $modal.find('.select-country').on('change', function() {
                    const countryId = $(this).val();
                    populateStates(countryId, null, $modal);
                });
            });

            // Get address details using Yandex geocoding
            function getAddressDetails(addressText, $modal) {
                $.ajax({
                    url: "/get-coordinates",
                    type: 'GET',
                    dataType: "json",
                    data: {
                        place_id: addressText,
                    },
                    success: function(data) {
                        if (data.status === 'OK') {
                            const location = data.result.geometry.location;
                            $modal.find('#lng').val(location.lng);
                            $modal.find('#lat').val(location.lat);
                        } else if (data.location) {
                            $modal.find('#lng').val(data.location.lng);
                            $modal.find('#lat').val(data.location.lat);
                            $modal.find('#city').val(data.locality);
                            $modal.find('#postal_code').val(data.postal_code);

                            let street = '';
                            if (data.streetNumber) street += data.streetNumber + ", ";
                            if (data.streetName) street += data.streetName + ", ";
                            $modal.find('#street_address_1').val(street);
                            $modal.find('#area').val(data.area);

                            if (data.country_id) {
                                $modal.find('#country_id').val(data.country_id).trigger('change');
                            }

                            if (data.state_id) {
                                $modal.find('.select-state').val(data.state_id).trigger('change');
                                populateStates(data.country_id, data.state_id, $modal);
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("AJAX error in getAddressDetails:", textStatus, errorThrown);
                    }
                });
            }

            // Populate states for selected country
            function populateStates(countryId, stateId, $modal) {
                $modal.find('.select-state').html('<option value=""></option>');
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
                            $modal.find('.select-state').append(
                                `<option value="${value.id}">${value.name}</option>`
                            );
                        });
                        if (stateId) {
                            $modal.find('.select-state').val(stateId);
                        }
                    }
                });
            }

            // Initial population
            $('.select-country').on('change', function() {
                populateStates($(this).val(), null, $(this).closest('.modal').length ? $(this).closest('.modal') : $('body'));
            });

            // Trigger for non-modal forms
            $('.select-country').each(function() {
                if ($(this).val()) {
                    populateStates($(this).val(), $('.select-state').data('default-state-id'), $(this).closest('.modal').length ? $(this).closest('.modal') : $('body'));
                }
            });

        });
    })(jQuery);
</script>
@endpush