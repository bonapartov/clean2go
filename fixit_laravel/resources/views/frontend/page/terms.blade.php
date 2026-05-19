@extends('frontend.layout.master')

@section('title', __('frontend::static.terms.terms_conditions'))

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
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.terms.index');
    
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

@section('title', $seoTitle ?? __('frontend::static.terms.terms_conditions'))
@section('meta_description', $seoDescription ?? __('frontend::static.terms.terms_conditions'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.terms.terms_conditions'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.terms.terms_conditions'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.terms.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.terms.terms_conditions'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.terms.terms_conditions'))
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
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.terms.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.terms.terms_conditions')}}</span>
</nav>
@endsection

@section('content')

<!-- Recent Terms & Conditions Section Start -->
<section class="terms-section section-b-space section-bg">
    <div class="container-fluid-lg">
        <div class="terms-content">
            <div class="row">
            @if(count($themeOptions['terms_and_conditions']['banners'] ?? []))
                <div class="col-xxl-8 col-xl-9 col-lg-10 mx-auto">
                    <div class="accordion" id="privacyPolicyExample">
                        
                        @foreach ($themeOptions['terms_and_conditions']['banners'] ?? [] as $key => $banners)
                        <div class="accordion-item">
                            @isset( $banners['title'])
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#privacyPolicyCollapse{{ $key }}" aria-expanded="false"
                                    aria-controls="privacyPolicyCollapse{{ $key }}">
                                    {{ $banners['title'] }}
                                    <i class="iconsax add" icon-name="add"></i>
                                    <i class="iconsax minus" icon-name="minus"></i>
                                </button>
                            </h2>
                            @endisset
                            @isset( $banners['description'])
                            <div id="privacyPolicyCollapse{{ $key }}" class="accordion-collapse collapse"
                                data-bs-parent="#privacyPolicyExample">
                                <div class="accordion-body">
                                    {!! $banners['description'] !!}
                                </div>
                            </div>
                            @endisset
                        </div>
                        
                        @endforeach
                    </div>
                </div>
                @else
                <div class="col-12">
                            <div class="no-data-found bg-white">
                                <svg class="no-data-img">
                                    <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                                </svg>   
                                <p>{{__('frontend::static.terms.data_not_found')}}</p>
                            </div>
                        </div>
                        @endif
            </div>
        </div>
    </div>
</section>
<!-- Recent Terms & Conditions Section End -->

@endsection