@extends('frontend.layout.master')

@section('title', __('frontend::static.contact_us.contact_us'))
@php
    $locale = app()->getLocale();
    $seoTitle = isset($seoSetting) ? $seoSetting->getTranslation('meta_title', $locale) : null;
    $seoDescription = isset($seoSetting) ? $seoSetting->getTranslation('meta_description', $locale) : null;
    $seoKeywords = isset($seoSetting) ? $seoSetting->meta_keywords : null;
    $seoOgTitle = isset($seoSetting) ? $seoSetting->getTranslation('og_title', $locale) : null;
    $seoOgDescription = isset($seoSetting) ? $seoSetting->getTranslation('og_description', $locale) : null;
    $seoTwitterTitle = isset($seoSetting) && $seoSetting->twitter_title ? $seoSetting->getTranslation('twitter_title', $locale) : null;
    $seoTwitterDescription = isset($seoSetting) && $seoSetting->twitter_description ? $seoSetting->getTranslation('twitter_description', $locale) : null;
    $seoRobots = isset($seoSetting) ? $seoSetting->robots : 'index,follow';
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.contact.index');
    
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

@section('title', $seoTitle ?? __('frontend::static.contact_us.contact_us'))
@section('meta_description', $seoDescription ?? __('frontend::static.contact_us.contact_us'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.contact_us.contact_us'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.contact_us.contact_us'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.contact.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.contact_us.contact_us'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.contact_us.contact_us'))
@section('twitter_image', $twitterImage ?? $ogImage ?? $metaImage)

{{-- Schema Markup --}}
@if(isset($seoSetting) && $seoSetting->schema_markup)
@push('structured_data')
<script type="application/ld+json">
{!! json_encode($seoSetting->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
@endif

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.contact_us.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.contact_us.contact_us')}}</span>
</nav>
@endsection

@section('content')
<!-- Contact Section Start -->
<section class="contact-section section-b-space section-bg">
    <div class="container-fluid-lg">
        <div class="contact-content">
            <div class="row g-xxl-5 g-lg-4 g-3 justify-content-center">
                <div class="col-xxl-5 col-lg-6">
                    <form action="{{ route('frontend.contact.mail') }}" method="post" id="contactUsForm">
                        @csrf
                        @method('POST')
                        <div class="contact-us-form">
                            <div class="row g-sm-3 g-2">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="firstname">{{__('frontend::static.contact_us.first_name')}}</label>
                                        <input type="text" id="firstname" name="firstname"
                                            class="form-control form-control-gray"
                                            placeholder="{{__('frontend::static.contact_us.enter_your_first_name')}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="lastname">{{__('frontend::static.contact_us.last_name')}}</label>
                                        <input type="text" id="lastname" name="lastname"
                                            class="form-control form-control-gray"
                                            placeholder="{{__('frontend::static.contact_us.enter_your_last_name')}}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="email">{{__('frontend::static.contact_us.email')}}</label>
                                        <input type="email" id="email" name="email"
                                            class="form-control form-control-gray"
                                            placeholder="{{__('frontend::static.contact_us.enter_your_email')}}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="message">{{__('frontend::static.contact_us.message')}}</label>
                                        <textarea class="form-control form-control-gray" name="message" id="" rows="5"
                                            placeholder="{{__('frontend::static.contact_us.write_your_message')}}"></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-solid send-btn mt-3"
                                        id="sendMsg">{{__('frontend::static.contact_us.send_message')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xxl-5 col-lg-6">
                    <div class="title">
                        <h2>{{ $themeOptions['contact_us']['title'] }}</h2>
                    </div>
                    <p class="heading-p">{{ $themeOptions['contact_us']['description'] }}</p>
                    <ul class="contact-info">
                        @if ($themeOptions['contact_us']['email'] ?? false)
                        <li>
                            <i class="iconsax" icon-name="mail"></i>
                            <div class="detail">
                                <h5>{{__('frontend::static.contact_us.email')}}</h5>
                                <p>{{ $themeOptions['contact_us']['email'] }}</p>
                            </div>
                        </li>
                        @endif
                        @if ($themeOptions['contact_us']['contact'] ?? false)
                        <li>
                            <i class="iconsax" icon-name="phone"></i>
                            <div class="detail">
                                <h5>{{__('frontend::static.contact_us.contact')}}</h5>
                                <p> {{ $themeOptions['contact_us']['contact'] }}</p>
                            </div>
                        </li>
                        @endif
                        @if ($themeOptions['contact_us']['location'] ?? false)
                        <li>
                            <i class="iconsax" icon-name="location"></i>
                            <div class="detail">
                                <h5>{{__('frontend::static.contact_us.location')}}</h5>
                                <p>{{ $themeOptions['contact_us']['location'] }}</p>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact Section End -->

@if ($themeOptions['contact_us']['google_map_embed_url'] ?? false)
<!-- Contact Map Section Start -->
<section class="map-section p-0">
    <div class="map">
        <iframe src="{{ $themeOptions['contact_us']['google_map_embed_url'] }}" style="border:0;" allowfullscreen=""
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</section>
<!-- Contact Map Section End -->
@endif
@endsection


@push('js')
<script>
(function($) {
    "use strict";
    $(document).ready(function() {
        let profileFormRules = {
            "firstname": "required",
            "lastname": "required",
            "email": "required",
            "message": "required",
        };

        $("#contactUsForm").validate({
            ignore: [],
            rules: profileFormRules
        });

        $('#sendMsg').on('click', function() {
            if ($("#contactUsForm").valid()) {
                $('#contactUsForm').submit();
            }
        });
    });

})(jQuery);
</script>
@endpush