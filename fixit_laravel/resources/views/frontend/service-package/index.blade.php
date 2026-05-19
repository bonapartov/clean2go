@use('app\Helpers\Helpers')
@use('App\Enums\FrontEnum')
@use('App\Enums\SymbolPositionEnum')
@extends('frontend.layout.master')

@section('title', __('frontend::static.servicePackages.servicePackages'))

@php
    $categories = Helpers::getCategories();
    $locale = app()->getLocale();
    $seoTitle = isset($seoSetting) ? $seoSetting->getTranslation('meta_title', $locale) : null;
    $seoDescription = isset($seoSetting) ? $seoSetting->getTranslation('meta_description', $locale) : null;
    $seoKeywords = isset($seoSetting) ? $seoSetting->meta_keywords : null;
    $seoOgTitle = isset($seoSetting) ? $seoSetting->getTranslation('og_title', $locale) : null;
    $seoOgDescription = isset($seoSetting) ? $seoSetting->getTranslation('og_description', $locale) : null;
    $seoTwitterTitle = isset($seoSetting) && $seoSetting->twitter_title ? $seoSetting->getTranslation('twitter_title', $locale) : null;
    $seoTwitterDescription = isset($seoSetting) && $seoSetting->twitter_description ? $seoSetting->getTranslation('twitter_description', $locale) : null;
    $seoRobots = isset($seoSetting) ? $seoSetting->robots : 'index,follow';
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.service-package.index');
    
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
@section('og_url', route('frontend.service-package.index'))
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
  <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.servicePackages.home')}}</a>
  <span class="breadcrumb-item active">{{ __('frontend::static.servicePackages.servicePackages')}}</span>
</nav>
@endsection

@section('content')
<!-- Service Packages List Section Start -->
<section class="service-package-section section-b-space">
  <div class="container-fluid-lg">
    <div class="service-package-content">
      <div class="row g-sm-4 g-3">
        @forelse ($servicePackages as $servicePackage)
        <div class="col-xxl-3 col-lg-4 col-sm-6">
          <a href="{{ route('frontend.service-package.details', $servicePackage?->slug) }}" class="service-bg-{{ $servicePackage?->bg_color ?? 'primary' }} service-bg d-block">
            <img src="{{ asset('frontend/images/svg/2.svg') }}"
              alt="{{ $servicePackage?->name }}" class="img-fluid service-1">
            <div class="service-detail">
              <div class="service-icon">
                @php
                    $locale =  app()->getLocale();
                    $mediaItems = $servicePackage->getMedia('image')->filter(function ($media) use ($locale) {
                        return $media->getCustomProperty('language') === $locale;
                    });
                    $imageUrl = $mediaItems->count() > 0  ? $mediaItems->first()->getUrl() : FrontEnum::getPlaceholderImageUrl();
                @endphp
                <img src="{{ Helpers::isFileExistsFromURL($imageUrl, true) }}"
                  alt="{{ $servicePackage?->services?->first()?->categories?->first()?->name }}"
                  class="img-fluid">
              </div>
              <h3>{{ $servicePackage?->title }}</h3>
              <div class="price">
                @php
                $salePrice = Helpers::getServicePackageSalePrice($servicePackage?->id);
                @endphp
                 @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                      <span class="text-white">{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($salePrice) }}</span>
                  @else
                      <span class="text-white">{{ Helpers::covertDefaultExchangeRate($salePrice) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                  @endif
                  <i class="iconsax" icon-name="arrow-right"></i>
                </span>
              </div>
            </div>
          </a>
        </div>
        @empty
        <div class="no-data-found">
          <svg class="no-data-img">
            <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
          </svg>
          {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
          <p>{{ __('frontend::static.servicePackages.not_found')}}</p>
        </div>
        @endforelse
      </div>
    </div>
    @if($servicePackages ?? [])
    @if($servicePackages?->lastPage() > 1)
    <div class="pagination-main pt-0 ">
      <ul class="pagination-box">
        {!! $servicePackages->links() !!}
      </ul>
    </div>
    @endif
    @endif
</section>
<!-- Service Package List Section End -->
@endsection
