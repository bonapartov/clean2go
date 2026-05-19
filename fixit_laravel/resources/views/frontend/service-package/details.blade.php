@use('app\Helpers\Helpers')
@use('app\Models\Booking')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\SymbolPositionEnum')

@extends('frontend.layout.master')

@section('title', $package->title)

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
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.service-package.details', ['slug' => $package->slug]);

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

@section('title', $seoTitle ?? __('frontend::static.servicePackages.servicePackages'))
@section('meta_description', $seoDescription ?? __('frontend::static.servicePackages.servicePackages'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.servicePackages.servicePackages'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.servicePackages.servicePackages'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.service-package.details', ['slug' => $package->slug]))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.servicePackages.servicePackages'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.servicePackages.servicePackages'))
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
    <a class="breadcrumb-item" href="{{url('/')}}">{{__('frontend::static.servicePackages.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.service-package.index') }}">{{__('frontend::static.servicePackages.servicePackages')}}</a>
    <span class="breadcrumb-item active">{{ $package->title }}</span>
</nav>
@endsection

@section('content')
<!-- Service List Section Start -->
<section class="service-list-section section-b-space">
    <div class="container-fluid-md">
        <div class="row service-list-content g-4">
            <div class="col-xxl-8 col-xl-7 col-12 order-2 order-xl-1">
                <div class="border br-12 p-20">
                    <div class="ratio_24">
                        <div class="service-img br-12">
                            <img src="{{ asset('frontend/images/banner/1.png') }}" alt="banner" class="bg-img">
                        </div>
                    </div>
                    @php
                        $salePrice = Helpers::getServicePackageSalePrice($package?->id);
                    @endphp
                    <div class="detail-content package-detail-content">
                        <div class="title">
                            <h3>{{ $package->title }}</h3>
                            <small class="amount-value text-success">
                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($salePrice) }}
                                @else
                                    {{ Helpers::covertDefaultExchangeRate($salePrice) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                @endif
                            </small>
                        </div>

                        <div class="b-bottom">
                            
                            <p>
                                {{ $package->description }}
                            </p>
                        </div>
                        <p class="text-dark fw-medium mt-3 mb-2">
                        {{__('frontend::static.servicePackages.include_service')}}
                        </p>
                        <div class="detail-sec">
                            @foreach($package?->services as $service)
                                <div class="service-item p-20 px-0">
                                    <a href="{{route('frontend.service.details', ['slug' => $service?->slug])}}">
                                        <img src="{{ $service?->web_img_thumb_url }}" alt="service" class="br-10">
                                    </a>
                                    <div class="detail w-100">
                                        <div class="service-title">
                                        <a href="{{route('frontend.service.details', ['slug' => $service?->slug])}}">
                                        <h4>{{ $service?->title }}</h4> </a>
                                            <div class="d-flex align-items-center gap-1">
                                                @if(!empty($service?->discount) && $service?->discount > 0)
                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                        <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                        <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                                    @else
                                                        <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                        <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                                    @endif
                                                @else
                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                        <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</small>
                                                    @else
                                                        <small>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="service-detail mt-1">
                                            <ul class="pb-2 b-bottom-dashed">
                                                <li class="time">
                                                    <i class="iconsax" icon-name="clock"></i>
                                                    <span>{{ $service?->duration }} {{ $service?->duration_unit }}</span>
                                                </li>
                                                <li class="service">{{__('frontend::static.servicePackages.min')}} {{ $service?->required_servicemen }} {{__('frontend::static.servicePackages.servicemen_reqiured')}}</li>
                                            </ul>
                                            <p class="mb-0">{{ $service?->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <label class="text-dark mb-0">{{__('frontend::static.servicePackages.disclaimer')}}</label>
                            <p class="text-danger mt-1 mb-0">{{__('frontend::static.servicePackages.disclaimer_note')}}</p>
                        </div>
                        @auth
                            <a class="btn btn-solid spinner-btn" href="{{route('frontend.booking.service-package', $package?->slug)}}">{{__('frontend::static.servicePackages.book_now')}} <span class="spinner-border spinner-border-sm" style="display: none;"></span></a>
                        @endauth
                        @guest
                            <a class="btn btn-solid spinner-btn" href="{{ url('login') }}">{{__('frontend::static.servicePackages.book_now')}} <span class="spinner-border spinner-border-sm" style="display: none;"></span></a>
                        @endguest
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-5 col-12 order-1 order-xl-2">
                <div class="provider-detail sticky">
                    <label class="mb-3 text-dark fw-medium">
                    {{__('frontend::static.servicePackages.provider_details')}}
                    </label>
                    <div class="provider-content">
                        <div class="profile-bg"></div>
                        <div class="profile">
                            <img src="{{ $package?->user?->media?->first()?->getUrl() ?? asset('frontend/images/avatar/8.png') }}" alt="{{ $package?->user?->name }}" class="img">
                            <a href="{{route('frontend.provider.details', ['slug' => $package?->user?->slug])}}">
                                <h3 class="mt-2">{{ $package?->user?->name }}</h3>
                            </a>
                            <div class="rate m-0">
                                 <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                <p>{{ $package?->user?->review_ratings ?? 'Unrated' }}</p>
                            </div>
                        </div>
                        <div class="profile-detail">
                            <ul>
                                <li>
                                    <label for="language">{{__('frontend::static.servicePackages.known_languages')}}</label>
                                    <span>{{ $package?->user?->knownLanguages->pluck('key')->implode(', ') }}</span>
                                </li>
                            </ul>
                        </div>
                        @if($package?->user?->served > 0)
                        <div class="success-light-badge badge">
                            <img src="{{ asset('frontend/images/svg/success.svg') }}" alt="success" class="badge-img">
                            <span>{{ $package?->user?->served }} {{__('frontend::static.servicePackages.service_delivered')}}</span>
                        </div>
                        @endif
                        <div class="danger-light-badge badge">
                            <img src="{{ asset('frontend/images/svg/medal.svg') }}" alt="medal" class="badge-img">
                            <span>{{ $package?->user?->experience_duration }} {{ $package?->user?->experience_interval }} {{__('frontend::static.servicePackages.of_experience')}}</span>
                        </div>
                        <p>
                            {{__('frontend::static.servicePackages.provider_note')}}
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
<!-- Service List Section End -->

@endsection
