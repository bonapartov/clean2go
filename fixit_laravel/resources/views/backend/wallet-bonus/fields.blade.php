@use('app\Helpers\Helpers')
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
    <div class="col-md-10">
        <ul class="language-list">
            @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                @if(isset($walletBonus))
                    <li>
                        <a href="{{ route('backend.walletBonus.edit', ['walletBonu' => $walletBonus->id, 'locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                            <i data-feather="arrow-up-right"></i>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('backend.walletBonus.create', ['locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})
                            <i data-feather="arrow-up-right"></i>
                        </a>
                    </li>
                @endif
            @empty
                <li>
                    <a href="{{ route('backend.walletBonus.edit', ['walletBonu' => $walletBonus->id, 'locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English
                        <i data-feather="arrow-up-right"></i>
                    </a>
                </li>
            @endforelse
        </ul>
    </div>
</div>

<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.name') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class='form-control' type="text" id="name" name="name" value="{{ isset($walletBonus->name) ? $walletBonus->getTranslation('name', request('locale', app()->getLocale())) : old('name') }}" placeholder="{{ __('static.wallet.enter_name') }} ({{ request('locale', app()->getLocale()) }})">
        @error('name')
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
    <label for="address" class="col-md-2">{{ __('static.blog.description') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <textarea class="form-control" rows="4" name="description" id="description" placeholder="{{ __('static.blog.enter_description') }} ({{ request('locale', app()->getLocale()) }})" cols="50">{{ isset($walletBonus->description) ? $walletBonus->getTranslation('description', request('locale', app()->getLocale())) : old('description') }}</textarea>
        @error('description')
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
    <label class="col-md-2" for="type">{{ __('static.coupon.type') }} <span class="required-span">*</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" id="type" name="type" data-placeholder="{{ __('static.coupon.select_type') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (['fixed' => 'Fixed', 'percentage' => 'Percentage'] as $key => $option)
                <option class="option" value="{{ $key }}" @if (old('type', $walletBonus->type ?? '') == $key) selected @endif>{{ $option }}</option>
            @endforeach
        </select>
        @error('type')
            <span class="invalid-feedback d-block">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row fixed">
    <label class="col-md-2" for="amount">{{ __('static.coupon.price') }} <span class="required-span">*</span></label>
    <div class="col-md-10 error-div price">
        <div class="input-group mb-3 flex-nowrap">
            <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
            <div class="w-100">
                <input class="form-control" type="number" id="amount" name="amount" min="1" value="{{ isset($walletBonus->bonus) ? $walletBonus->bonus : old('amount') }}" placeholder="{{ __('static.coupon.enter_price') }}">
                @error('amount')
                    <span class="invalid-feedback d-block">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="form-group row percentage">
    <label class="col-md-2" for="percentage">{{ __('static.coupon.percentage') }} <span class="required-span">*</span></label>
    <div class="col-md-10 price">
        <div class="input-group mb-3 flex-nowrap">
            <div class="w-100">
                <input class="form-control" type="number" id="percentage_amount" name="percentage_amount" min="1" value="{{ isset($walletBonus->bonus) ? $walletBonus->bonus : old('percentage_amount') }}" placeholder="{{ __('static.coupon.enter_percentage') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
                @error('percentage_amount')
                    <span class="invalid-feedback d-block">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <span class="input-group-text">%</span>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="min_top_up_amount">{{ __('static.wallet.min_top_up_amount') }} <span class="required-span">*</span></label>
    <div class="col-md-10">
        <input class="form-control" type="number" min="1" id="min_top_up_amount" name="min_top_up_amount" value="{{ old('min_top_up_amount', $walletBonus->min_top_up_amount ?? '') }}" placeholder="{{ __('static.wallet.enter_min_top_up_amount') }}">
        @error('min_top_up_amount')
            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
        @enderror
        <span class="help-text">{{ __('static.wallet.min_top_up_amount_help') }}</span>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="max_bonus">{{ __('static.wallet.max_bonus') }} <span class="required-span">*</span></label>
    <div class="col-md-10">
        <input class="form-control" type="number" min="0" id="max_bonus" name="max_bonus" value="{{ old('max_bonus', $walletBonus->max_bonus ?? '') }}" placeholder="{{ __('static.wallet.enter_max_bonus') }}">
        @error('max_bonus')
            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
        @enderror
        <span class="help-text">{{ __('static.wallet.max_bonus_help') }}</span>
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.coupon.is_unlimited') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($walletBonus))
                    <input class="form-control" type="hidden" name="is_unlimited" value="0">
                    <input class="form-check-input" id="is_unlimited" type="checkbox" name="is_unlimited"
                        id="" value="1" {{ $walletBonus->is_unlimited ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="is_unlimited" value="0">
                    <input class="form-check-input" id="is_unlimited" type="checkbox" name="is_unlimited"
                        id="" value="1">
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
        <span class="help-text">{{ __('static.wallet.is_unlimited_help') }}</span>
    </div>
</div>

<div class="form-group row" id="usage_limit_per_user">
    <label class="col-md-2" for="usage_limit_per_user">{{ __('static.wallet.usage_per_user') }}</label>
    <div class="col-md-10">
        <input class='form-control' type="number" name="usage_limit_per_user"
            value="{{ isset($walletBonus->usage_limit_per_user) ? $walletBonus->usage_limit_per_user : old('usage_limit_per_user') }}"
            placeholder="{{ __('static.wallet.enter_usage_per_user') }}">
        @error('usage_limit_per_user')
            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
        @enderror
        <span class="help-text">{{ __('static.wallet.usage_per_user_help') }}</span>
    </div>
</div>

<div class="form-group row" id="total_usage_limit">
    <label class="col-md-2" for="total_usage_limit">{{ __('static.wallet.total_usage_of_bonus') }}</label>
    <div class="col-md-10">
        <input class='form-control' type="number" name="total_usage_limit"
            value="{{ isset($walletBonus->total_usage_limit) ? $walletBonus->total_usage_limit : old('total_usage_limit') }}"
            placeholder="{{ __('static.wallet.enter_total_usage_of_bonus') }}">
        @error('total_usage_limit')
            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
        @enderror
        <span class="help-text">{{ __('static.wallet.total_usage_of_bonus_help') }}</span>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="status">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($walletBonus))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" {{ $walletBonus->status ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="is_admin_funded">{{ __('static.wallet.is_admin_funded') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($walletBonus))
                    <input class="form-control" type="hidden" name="is_admin_funded" value="0">
                    <input class="form-check-input" type="checkbox" name="is_admin_funded" id="" value="1" {{ $walletBonus->is_admin_funded ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="is_admin_funded" value="0">
                    <input class="form-check-input" type="checkbox" name="is_admin_funded" id="" value="1">
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
        @php
            $currencySymbol = Helpers::getDefaultCurrencySymbol();
        @endphp
        <span class="help-text">{{ str_replace('{currency}', $currencySymbol, __('static.wallet.is_admin_funded_help')) }}</span>
    </div>
</div>

@push('js')
    <script>
        function toggleInput(type) {
            if (type === 'fixed' || type === '') {
                $('.fixed').show();
                $('.percentage').hide();
                $('.percentage').addClass('d-none');
                $('.percentage').removeClass('d-flex');

            } else {
                $('.fixed').hide();
                $('.percentage').show();
                $('.percentage').removeClass('d-none');
                $('.percentage').addClass('d-flex');
            }
        }

        toggleInput($('#type').val());
        $('#type').on('change', function() {
            var type = $(this).val();
            toggleInput(type);
            if(type === 'fixed') $('#percentage_amount').val('');
            if(type === 'percentage') $('#amount').val('');
        });

          $("#walletBonusForm").validate({
                    ignore: [],
                    rules: {
                        "name": "required",
                        "description" : "required",
                        "type": "required",
                        "max_bonus": "required",
                        "min_top_up_amount" : {
                            required: true,
                            min: 1
                        },
                        usage_limit_per_user: {
                            required: function () {
                                return !$('#is_unlimited').is(':checked');
                            },
                            min: 1
                        },

                        total_usage_limit: {
                            required: function () {
                                return !$('#is_unlimited').is(':checked');
                            },
                            min: 1
                        },
                        "amount": {
                            required: function() {
                                return $('#type').val() === 'fixed';
                            },
                            min: 1
                        },
                        "percentage_amount": {
                            required: function() {
                                return $('#type').val() === 'percentage';
                            },
                            min: 1
                        },
                    },
                });

                $('#walletBonusForm').on('submit', function (e) {
                    let type = $('select[name="type"]').val();

                    if (type === 'fixed') {
                        $('#percentage_amount').val('');
                        
                        if ($('#amount').val() === '') {
                            alert('Please enter fixed amount');
                            e.preventDefault();
                            return false;
                        }
                    }

                    if (type === 'percentage') {
                        $('#amount').val('');
                        
                        if ($('#percentage_amount').val() === '') {
                            alert('Please enter percentage amount');
                            e.preventDefault();
                            return false;
                        }
                    }
                });

                if ($("#is_unlimited").prop("checked")) {
                    $('#usage_limit_per_user').hide();
                    $('#total_usage_limit').hide();
                } else {
                    $('#usage_limit_per_user').show();
                    $('#total_usage_limit').show();
                }

                $(document).on('change', '#is_unlimited', function(e) {
                    if ($(this).is(':checked')) {
                        $('#usage_limit_per_user').hide();
                        $('#total_usage_limit').hide();
                    } else {
                        $('#usage_limit_per_user').show();
                        $('#total_usage_limit').show();

                    }
                });

    </script>
@endpush