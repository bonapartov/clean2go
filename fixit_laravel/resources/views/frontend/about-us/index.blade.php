@extends('frontend.layout.master')

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
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.about.index');
    
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

@section('title', $seoTitle ?? __('frontend::static.about_us.about_us'))
@section('meta_description', $seoDescription ?? __('frontend::static.about_us.about_us'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.about_us.about_us'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.about_us.about_us'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.about.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.about_us.about_us'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.about_us.about_us'))
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
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.about_us.home') }}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.about_us.about_us') }}</span>
</nav>
@endsection

@use('app\Helpers\Helpers')

@section('content')
<!-- Home About us Section Start -->
@if ($themeOptions['about_us']['status'])
<section class="about-us">
    <div class="container-fluid-lg">
        <div class="section-wrap">
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="image-grp">
                        <img src="{{ asset($themeOptions['about_us']['left_bg_image_url']) }}" class="img-fluid" alt="electrician">
                        <img src="{{ asset($themeOptions['about_us']['right_bg_image_url']) }}" class="img-fluid" alt="painter">

                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="title">
                        <h2>{{ $themeOptions['about_us']['title'] }}</h2>
                    </div>
                    <div class="content-detail">
                        <p>
                            {{ $themeOptions['about_us']['description'] }}
                        </p>
                        <ul class="item-lists">
                            <li class="item-list">
                                <div class="icon-box">
                                    <i class="iconsax" icon-name="truck-tick"></i>
                                </div>
                                <div class="detail">
                                    <h4>{{ $themeOptions['about_us']['sub_title1'] }}</h4>
                                    <p>{{ $themeOptions['about_us']['description1'] }}</p>
                                </div>
                            </li>
                            <li class="item-list">
                                <div class="icon-box">
                                    <i class="iconsax" icon-name="crown-2"></i>
                                </div>
                                <div class="detail">
                                    <h4>{{ $themeOptions['about_us']['sub_title2'] }}</h4>
                                    <p>{{ $themeOptions['about_us']['description2'] }}</p>
                                </div>
                            </li>
                            <li class="item-list">
                                <div class="icon-box">
                                    <i class="iconsax" icon-name="bulb-charge"></i>
                                </div>
                                <div class="detail">
                                    <h4>{{ $themeOptions['about_us']['sub_title3'] }}</h4>
                                    <p>{{ $themeOptions['about_us']['description3'] }}</p>
                                </div>
                            </li>
                            <li class="item-list">
                                <div class="icon-box">
                                    <i class="iconsax" icon-name="like-dislike"></i>
                                </div>
                                <div class="detail">
                                    <h4>{{ $themeOptions['about_us']['sub_title4'] }}</h4>
                                    <p>{{ $themeOptions['about_us']['description4'] }}</p>
                                </div>
                            </li>
                            <li class="item-list">
                                <div class="icon-box">
                                    <i class="iconsax" icon-name="group"></i>
                                </div>
                                <div class="detail">
                                    <h4>{{ $themeOptions['about_us']['sub_title5'] }}</h4>
                                    <p>{{ $themeOptions['about_us']['description5'] }}</p>
                                </div>
                            </li>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
<!-- Home About us Section End -->

<!-- Home Our Work Section Start -->
@if ($themeOptions['about_us']['banner_status'])
<section class="our-work section-b-space">
    <div class="container-fluid-sm">
        <div class="our-work-content">
            @isset($themeOptions['about_us']['banners'])
            <div class="row row-cols-xl-4 row-cols-2 counter g-sm-3 g-2">
                @forelse ($themeOptions['about_us']['banners'] as $banner)
                <div class="col">
                    <div class="work-box">
                        <h3>{{ $banner['count'] }}</h3>
                        <p>{{ $banner['title'] }}</p>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <p>{{ __('frontend::static.about_us.banners_not_found') }}</p>
                </div>
                @endforelse
            </div>
            @endisset
        </div>
    </div>
</section>
@endif
<!-- Home Our Work Section End -->

<!-- Home Expert Section Start -->
@if ($themeOptions['about_us']['provider_status'])
<section class="expert-section section-b-space">
    <div class="container-fluid-lg">
        <div class="title dark-title">
            <h2>{{ $themeOptions['about_us']['provider_title'] }}</h2>
            <a class="view-all" href="{{ route('frontend.provider.index') }}" target="_blank">
                {{ __('frontend::static.about_us.view_all') }}
                <i class="iconsax" icon-name="arrow-right"></i>
            </a>
        </div>
        <div class="expert-content">
            <div class="row g-3">
                @php
                $providers = Helpers::getTopProvidersByRatings();
                @endphp
                @forelse ($providers as $provider)
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="card dark-card">
                        <div class="dark-card-img">
                            <img src="{{ Helpers::isFileExistsFromURL($provider?->media?->first()?->getUrl(), true) }}" alt="{{ $provider?->name }}" class="img-fluid profile-pic">
                        </div>
                        <div class="card-body">
                            <div class="card-title">
                                <a href="{{route('frontend.provider.details', $provider->slug)}}">
                                    <h4>{{ $provider?->name }}</h4>
                                </a>
                                <div class="rate">
                                    <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                    <small>{{ $provider?->review_ratings }}</small>
                                </div>
                            </div>
                            <div class="location">
                                <i class="iconsax" icon-name="location"></i>
                                <h5>{{ $provider?->primary_address?->state?->name }} -
                                    {{ $provider?->primary_address?->country?->name }}</h5>
                            </div>
                            <div class="card-detail">
                                <p>{{ $provider?->primary_address?->address }},
                                    {{ $provider?->primary_address?->postal_code }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <p>{{ __('frontend::static.about_us.providers_not_found') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endif
<!-- Home Expert Section End -->

<!-- Home About Us Package Section Start -->
@if ($themeOptions['about_us']['testimonial_status'])
<section class="about-us-section section-b-space">
    <div class="container-fluid-lg">
        <div class="title-1">
            <h2>{{ $themeOptions['about_us']['testimonial_title'] }}</h2>
        </div>
        <div class="about-us-content content-t-space">
            <img src="{{ asset('frontend/images/Dots-1.png') }}" class="image-1" alt="">
            <div class="swiper about-us-slider">
                <div class="swiper-wrapper">
                    @php
                    $testimonials = Helpers::getTestimonials();
                    @endphp
                    @forelse ($testimonials as $testimonial)
                    <div class="swiper-slide">
                        <div class="card">
                            <div class="card-body">
                            <div class="card-title">
                                    <img src="{{ Helpers::isFileExistsFromURL($testimonial?->media?->first()->original_url, true) }}" alt="feature"
                                        class="img-fluid lozad">
                                    <img src="{{ asset('frontend/images/svg/quote.svg') }}" alt="quote"
                                        class="img-fluid quote lozad">
                                    {{-- <img src="{{ asset('frontend/images/svg/quote-active.svg') }}" alt="quote"
                                        class="img-fluid quote-active lozad"> --}}
                                        <svg class="quote-active lozad">
                                            <use xlink:href="{{ asset('frontend/images/svg/quote-active.svg#quote-active') }}"></use>
                                        </svg>
                                    <div>
                                        <h3>{{ $testimonial?->name }}</h3>
                                        <div class="rate">
                                            <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                                class="img-fluid star">
                                            <small>{{ $testimonial?->rating }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-detail">
                                    <p>{{ $testimonial?->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="no-data-found">
                        <p>{{ __('frontend::static.about_us.testimonials_not_found') }}</p>
                    </div>
                    @endforelse
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
@endif
<!-- Home About Us Section End -->
@endsection

@push('js')
<script src="{{ asset('frontend/js/number-count.js') }}"></script>
@endpush
