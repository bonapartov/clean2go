{{-- Title --}}
<title>@yield('title', $themeOptions['general']['site_title'] ?? $themeOptions['seo']['meta_title']) - {{$themeOptions['general']['site_tagline'] ?? null}}</title>

{{-- Meta Tags --}}
<meta name="title" content="@yield('title', $themeOptions['seo']['meta_title'] ?? env('APP_NAME'))">
<meta name="description" content="@yield('meta_description', $themeOptions['seo']['meta_description'])">
<meta name="keywords" content="@yield('keywords', $themeOptions['seo']['meta_tags'])">
<meta name="robots" content="index, follow"> <!-- or change to noindex, nofollow as needed -->

{{-- Canonical URL --}}
<link rel="canonical" href="@yield('canonical_url', url()->current())">

{{-- Open Graph Tags --}}
<meta property="og:title" content="@yield('og_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
<meta property="og:description" content="@yield('og_description', $themeOptions['seo']['og_description'])">
<meta property="og:image" content="@yield('og_image', asset($themeOptions['seo']['og_image'] ?? $themeOptions['general']['header_logo']))">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="@yield('og_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
<meta property="og:url" content="@yield('og_url', url()->current())">
<meta property="og:type" content="@yield('og_type', 'website')">
@stack('og_article')

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('twitter_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
<meta name="twitter:description" content="@yield('twitter_description', $themeOptions['seo']['og_description'])">
<meta name="twitter:image" content="@yield('twitter_image', asset($themeOptions['seo']['og_image'] ?? $themeOptions['general']['header_logo']))">
<meta name="twitter:image:alt" content="@yield('twitter_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
@stack('twitter_card')

{{-- Additional Meta Tags --}}
<meta property="og:site_name" content="{{ $themeOptions['general']['site_title'] }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
@stack('additional_meta')

{{-- Structured Data (JSON-LD) --}}
@stack('structured_data')

{{-- Viewport --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- Favicon --}}
<link rel="icon" href="{{ asset(@$themeOptions['general']['favicon_icon'] ?? asset('admin/images/faviconIcon.png')) }}" type="image/x-icon">

{{-- Verification Code (if applicable) --}}
<meta name="msvalidate.01" content="your-verification-code" />