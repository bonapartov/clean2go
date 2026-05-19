@extends('frontend.auth.master')
@section('content')
    <div class="log-in-section">
        <div class="row login-content g-0">
            <div class="login-main">
                <div class="login-card">
                    <div class="login-title">
                        <h2>{{ __('frontend::auth.verify_otp') }}</h2>
                        <p>{{ __('frontend::auth.enter_verification_code') }}
                            <span>+{{ $code }} {{ $phone }}</span>
                        </p>
                    </div>
                    <div class="login-detail phone-detail">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('frontend.login.otp.submit') }}" method="post" id="otpForm">
                            @csrf
                            <input type="hidden" name="code" value="{{ $code }}">
                            <input type="hidden" name="phone" value="{{ $phone }}">

                            <div class="form-group">
                                <label for="otp">{{ __('frontend::auth.enter_otp') }}</label>
                                <div class="otp-field">
                                    <input type="number" id="otp1" name="otp1" class="otp__digit">
                                    <input type="number" id="otp2" name="otp2" class="otp__digit">
                                    <input type="number" id="otp3" name="otp3" class="otp__digit">
                                    <input type="number" id="otp4" name="otp4" class="otp__digit">
                                    <input type="number" id="otp5" name="otp5" class="otp__digit">
                                    <input type="number" id="otp6" name="otp6" class="otp__digit">
                                </div>
                            </div>
                            @error('otp')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <input type="hidden" name="otp" id="otp_hidden">
                            <button type="submit" class="otp-btn btn btn-solid">{{ __('frontend::auth.verify_proceed') }}</button>
                        </form>
                        <a href="{{route('frontend.login')}}" class="mt-2 d-block text-center">{{ __('Login Back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script src="{{ asset('frontend/js/otp-handler.js') }}"></script>
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#otpForm").validate({
                ignore: [],
                rules: {
                    "otp": {
                        required: true,
                    },
                }
            });

            $('#otpForm').on('submit', function(e) {
                var otpValue = '';
                $('.otp__digit').each(function() {
                    otpValue += $(this).val();
                });
                $('#otp_hidden').val(otpValue);
            });
        });
    })(jQuery);
</script>
@endpush
