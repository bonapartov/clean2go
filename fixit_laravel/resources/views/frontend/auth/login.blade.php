@extends('frontend.auth.master')
@section('content')
    @use('App\Models\Setting')
    @php
        $settings = Setting::first()->values;
    @endphp
    <div class="login-title">
        <h2>{{ __('frontend::auth.login_now') }}</h2>
        <p>{{ __('frontend::auth.title') }}</p>
    </div>
    <div class="login-detail mb-0">
        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif
        <form action="{{ route('frontend.login') }}" method="POST" id="loginForm">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="email">{{ __('frontend::auth.email') }}</label>
                <div class="position-relative">
                    <i class="iconsax" icon-name="mail"></i>
                    <input class="form-control form-control-white" id="email" placeholder="{{ __('frontend::auth.enter_email') }}" name="email" type="email">
                </div>
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">{{ __('frontend::auth.password') }}</label>
                <div class="position-relative">
                    <i class="iconsax" icon-name="lock-2"></i>
                    <input class="form-control form-control-white pr-45" id="password" placeholder="{{ __('frontend::auth.enter_your_password') }}" name="password" type="password">
                    <div class="toggle-password">
                        <i class="iconsax eye" icon-name="eye"></i>
                        <i class="iconsax eye-slash" icon-name="eye-slash"></i>
                    </div>
                </div>
                @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="forgot-pass">
                <a href="{{ route('frontend.forgot.index') }}">{{ __('frontend::auth.forgot_password') }} </a>
            </div>

            <button type="submit" class="btn btn-solid submit spinner-btn">{{ __('frontend::auth.login_now') }}
                <span class="spinner-border spinner-border-sm" style="display: none;"></span>
            </button>

            <div class="not-member">
                <span>{{ __('frontend::auth.not_member') }}</span>
                <a href="{{ route('frontend.register.index') }}">{{ __('frontend::auth.signup') }}</a>
            </div>
        </form>
        @isset($settings['activation']['default_credentials'])
            @if($settings['activation']['default_credentials'])
                <div class="demo-credential">
                    <button class="btn btn-outline default-credentials" data-email="user@example.com">{{__('frontend::static.home_page.user')}}</button>
                </div>
            @endif
        @endisset
    </div>
    <div class="other-options">
        <span class="options">{{ __('frontend::auth.or_continue_with') }}</span>
        <ul class="social-media">
            @if ($settings['activation']['social_login_enable'] == 1)
                <li>
                    <a href="{{ route('frontend.redirectToProvider', ['provider' => 'google']) }}" target="_blank"
                        class="social-icon">
                        <img src="{{ asset('frontend/images/social/google.png') }}" alt="google">
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('frontend.login.number') }}" target="_blank" class="social-icon">
                    <img src="{{ asset('frontend/images/social/mobile.png') }}" alt="mobile">
                </a>
            </li>
        </ul>
    </div>
@endsection
@push('js')
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.google_recaptcha_key') }}"></script>

    <script type="text/javascript">
        var recaptchaStatus = <?php echo json_encode($settings['google_reCaptcha']['status']); ?>;
        var recaptchaKey = "{{ config('app.google_recaptcha_key') }}";

        $('#loginForm').submit(function(event) {
            event.preventDefault();

            if (!$(this).valid()) {
                return false;
            }
                
            $('.re-captcha').empty();

            if (recaptchaStatus == 1 && recaptchaKey) {
                try {
                    grecaptcha.execute(recaptchaKey, { action: 'login' })
                        .then(function(token) {
                            $('#loginForm').prepend(
                                '<input type="hidden" name="g-recaptcha-response" value="' + token + '">'
                            );
                            $('#loginForm').unbind('submit').submit();
                        });
                } catch (e) {
                    toastr.error('We could not verify the reCAPTCHA');
                    setTimeout(function () {
                        $('#loginForm').off('submit').submit();
                    });
                }
            } else {
                $('#loginForm').unbind('submit').submit();
            }
        });
    </script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#loginForm").validate({
                    ignore: [],
                    rules: {
                        "email": {
                            required: true,
                            email: true
                        },
                        "password": {
                            required: true
                        },
                    }
                });

                $(".default-credentials").click(function() {
                    $("#email").val("");
                    $("#password").val("");
                    var email = $(this).data("email");
                    $("#email").val(email);
                    $("#password").val("123456789");
                });

            });
        })(jQuery);
    </script>
@endpush
