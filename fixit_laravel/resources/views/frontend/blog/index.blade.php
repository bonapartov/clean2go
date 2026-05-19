@use('app\Helpers\Helpers')
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
    $seoCanonical = isset($seoSetting) && $seoSetting->canonical_url ? $seoSetting->canonical_url : route('frontend.blog.index');
    
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

@section('title', $seoTitle ?? __('frontend::static.blogs.blogs'))
@section('meta_description', $seoDescription ?? __('frontend::static.blogs.blogs'))
@section('keywords', $seoKeywords ?? '')
@section('canonical_url', $seoCanonical)

{{-- Robots Meta Tag --}}
@if(isset($seoSetting) && $seoSetting->robots)
<meta name="robots" content="{{ $seoSetting->robots }}">
@endif

{{-- Open Graph Tags --}}
@section('og_title', $seoOgTitle ?? $seoTitle ?? __('frontend::static.blogs.blogs'))
@section('og_description', $seoOgDescription ?? $seoDescription ?? __('frontend::static.blogs.blogs'))
@section('og_image', $ogImage ?? $metaImage)
@section('og_url', route('frontend.blog.index'))
@section('og_type', 'website')

{{-- Twitter Card Tags --}}
@section('twitter_title', $seoTwitterTitle ?? $seoOgTitle ?? $seoTitle ?? __('frontend::static.blogs.blogs'))
@section('twitter_description', $seoTwitterDescription ?? $seoOgDescription ?? $seoDescription ?? __('frontend::static.blogs.blogs'))
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
    <a class="breadcrumb-item" href="{{url('/')}}"> {{__('frontend::static.blogs.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.blogs.blogs')}}</span>
</nav>
@endsection
@section('content')
<!-- Blog Section Start -->
<section class="blog-section section-b-space" id="blog">
    <div class="container-fluid-lg">
        <div class="blog-content">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-sm-4 g-3 ratio2_1">
                @forelse ($blogs as $blog)
                <div class="col ">
                    <div class="blog-main">
                        <div class="card">
                            <div class="overflow-hidden b-r-5">
                                <a href="{{ route('frontend.blog.details', $blog?->slug) }}" class="card-img">
                                    <img src="{{ $blog?->web_img_thumb_url }}" alt="{{ $blog?->title }}"
                                        class="bg-img">
                                </a>
                            </div>
                            <div class="card-body">
                                <h4>
                                    <a href="{{ route('frontend.blog.details', $blog?->slug) }}">{{ $blog?->title }}</a>
                                </h4>

                                <ul class="blog-detail">
                                    <li>{{ $blog?->categories?->first()?->title }}</li>
                                    <li> {{ Helpers::dateTimeFormat($blog?->created_at, 'd M, Y') }}</li>
                                </ul>
                                <div class="blog-footer">
                                    <div>
                                        <i class="iconsax" icon-name="message-dots"></i>
                                        <span>{{$blog?->comments_count}}</span>
                                    </div>
                                    <span>
                                        - {{__('frontend::static.blogs.by')}} {{$blog?->created_by?->name ?? 'unknown'}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <svg class="no-data-img">
                        <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                    </svg>
                    {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                    <p>{{__('frontend::static.blogs.not_found')}} </p>
                </div>
                @endforelse
            </div>
        </div>
        @if(count($blogs ?? []))
        @if($blogs?->lastPage() > 1)
        <div class="pagination-main">
            <ul class="pagination">
                {!! $blogs->links() !!}
            </ul>
        </div>
        @endif
        @endif
    </div>
</section>
<!-- Blog Section End -->
@endsection