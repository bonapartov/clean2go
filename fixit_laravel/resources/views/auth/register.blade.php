@use('App\Helpers\Helpers')
@use('App\Models\Zone')
@use('App\Models\Language')
@use('App\Models\Setting')
@php
$zones = Zone::where('status', true)->pluck('name', 'id');
$countries = Helpers::getCountries();
$languages = Language::get();
$settings = Setting::first()->values;
$locale = app()->getLocale();
    $seoTitle = isset($seoSetting) ? $seoSetting->getTranslation('meta_title', $locale) : null;
    $seoDescription = isset($seoSetting) ? $seoSetting->getTranslation('meta_description', $locale) : null;
    $seoKeywords = isset($seoSetting) ? $seoSetting->meta_keywords : null;
    $seoOgTitle = isset($seoSetting) ? $seoSetting->getTranslation('og_title', $locale) : null;
    $seoOgDescription = isset($seoSetting) ? $seoSetting->getTranslation('og_description', $locale) : null;
    $seoTwitterTitle = isset($seoSetting) && $seoSetting->twitter_title ? $seoSetting->getTranslation('twitter_title', $locale) : null;
    $seoTwitterDescription = isset($seoSetting) && $seoSetting->twitter_description ? $seoSetting->getTranslation('twitter_description', $locale) : null;
    $seoRobots = isset($seoSetting) ? $seoSetting->robots : 'index,follow';
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('become-provider.index');

    // Get images
    $metaImage = null;
    $ogImage = null;
    $twitterImage = null;
    if (isset($seoSetting)) {
        $metaImageMedia = $seoSetting->getMedia('meta_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        })->first();
        $metaImage = $metaImageMedia ? $metaImageMedia->getUrl() : null;
        
        $ogImageMedia = $seoSetting->getMedia('og_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        })->first();
        $ogImage = $ogImageMedia ? $ogImageMedia->getUrl() : null;
        
        $twitterImageMedia = $seoSetting->getMedia('twitter_image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        })->first();
        $twitterImage = $twitterImageMedia ? $twitterImageMedia->getUrl() : null;
    }
@endphp

@extends('auth.master')

@section('title', __('Register'))

@section('title', $seoTitle ?? __('frontend::static.footer.become_provider'))
@section('meta_description', $seoDescription ?? __('frontend::static.footer.become_provider'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.footer.become_provider'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.footer.become_provider'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('become-provider.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.footer.become_provider'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.footer.become_provider'))
@section('twitter_image', $twitterImage ?? $ogImage ?? $metaImage)

{{-- Schema Markup --}}
@if(isset($seoSetting) && $seoSetting->schema_markup)
@push('structured_data')
<script type="application/ld+json">
{!! json_encode($seoSetting->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
@endif


@section('content')
<section class="auth-page" style="background-image: url('{{ env('APP_URL') }}/admin/images/login-bg.png')">
    <div class="container">
         <div class="auth-card">
            <div class="welcome mt-0">
                <h3>{{ __('Become a Provider') }}</h3>
                <p>{{ __('static.sign_in_note') }}</p>
            </div>
            @if ($errors->any())
            <div class="error-note" id="errors">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
                <i data-feather="x" class="close-errors"></i>
            </div>
            @endif
            <div class="main">
                <form class="auth-form" action="{{ route('become-provider.store') }}" id="providerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('auth.become-provider.fields')
                </form>
                <div class="forgot-pass">
                    @if (Route::has('login'))                                
                        <a href="{{ route('login') }}" class="btn ">
                            <i data-feather="arrow-left"></i>
                            {{ __('static.login.back_to_login') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


@push('js')
<script src="https://www.google.com/recaptcha/api.js?render={{ config('app.google_recaptcha_key') }}"></script>

<script type="text/javascript">
    var recaptchaStatus = <?php echo json_encode($settings['google_reCaptcha']['status']); ?>;
    var recaptchaKey = "{{ config('app.google_recaptcha_key') }}";

    $('#providerForm').submit(function(event) {
        event.preventDefault();

        if (!$(this).valid()) {
            return false;
        }
            
        $('.re-captcha').empty();

        if (recaptchaStatus == 1 && recaptchaKey) {
            try {
                grecaptcha.execute(recaptchaKey, { action: 'login' })
                    .then(function(token) {
                        $('#providerForm').prepend(
                            '<input type="hidden" name="g-recaptcha-response" value="' + token + '">'
                        );
                        $('#providerForm').unbind('submit').submit();
                    });
             } catch (e) {
                toastr.error('We could not verify the reCAPTCHA');
                setTimeout(function () {
                    $('#providerForm').off('submit').submit();
                });
            }
        } else {
            $('#providerForm').unbind('submit').submit();
        }
    });
</script>
<script>
$(document).ready(function() {
    $(".close-errors").click(function() {
        $("#errors").remove();
    });

    $("#loginForm").validate({
        ignore: [],
        rules: {
            "email": "required",
            "password": "required",
        }
    });

    $(".default-credentials").click(function() {
        $("#email-input").val("");
        $("#password-input").val("");
        var email = $(this).data("email");
        $("#email-input").val(email);
        $("#password-input").val("123456789");
    });
});
</script>
@endpush