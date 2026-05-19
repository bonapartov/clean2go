@use('app\Helpers\Helpers')
@extends('frontend.layout.master')

@section('title', __('frontend::static.providers.providers'))

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
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.provider.index');
    
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

@section('title', $seoTitle ?? __('frontend::static.providers.providers'))
@section('meta_description', $seoDescription ?? __('frontend::static.providers.providers'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.providers.providers'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.providers.providers'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.provider.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.providers.providers'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.providers.providers'))
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
    <a class="breadcrumb-item" href="{{url('/')}}">{{__('frontend::static.providers.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.providers.providers')}}</span>
</nav>
@endsection

@section('content')
<!-- Provider List Section Start -->
<section class="service-list-section section-b-space">
    <div class="container-fluid-lg">
        <div class="service-list-content">
            <div class="expert-content">
                <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1 g-sm-4 g-3">
                    @forelse ($providers as $provider)
                    <div class="col">
                        <div class="card gray-card">
                            <div class="gray-card-img">
                                @php
                                $profileImg = $provider?->media?->first()?->getUrl();
                                @endphp
                                @if(Helpers::isFileExistsFromURL($profileImg, true))
                                <img src="{{ $profileImg ?? asset('frontend/images/img-not-found.jpg')}}" alt="{{ $provider?->name }}" class="img-fluid profile-pic">
                                @else
                                <span class="profile-name initial-letter">{{ substr($provider?->name, 0, 1) }}</span>
                                @endif
                                @auth
                                <div class="like-icon" id="favouriteDiv" data-provider-id="{{ $provider?->id }}">
                                    <img class="img-fluid icon outline-icon" src="{{ asset('frontend/images/svg/heart-outline.svg')}}"
                                        alt="whishlist">
                                    <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                                </div>
                                @endauth
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
                                        {{ $provider?->primary_address?->country?->name }}
                                    </h5>
                                </div>
                                <div class="card-detail">
                                    <p>{{ $provider?->primary_address?->address }},
                                        {{ $provider?->primary_address?->postal_code }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="no-data-found">
                        <p>{{__('frontend::static.providers.providers_not_found')}}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @if($providers ?? [])
        @if($providers?->lastPage() > 1)
        <div class="row">
            <div class="col-12">
                <div class="pagination-main section-b-space">
                    <ul class="pagination">
                        {!! $providers->links() !!}
                    </ul>
                </div>
            </div>
        </div>
        @endif
        @endif
</section>
<!-- Service List Section End -->
@endsection

@push('js')
@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth
@endpush