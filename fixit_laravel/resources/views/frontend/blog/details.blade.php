@use('app\Helpers\Helpers')
@extends('frontend.layout.master')

@section('title', @$blog?->meta_title ?? @$blog?->title)
@section('meta_description', @$blog?->meta_description ?? @$blog?->description)
@section('keywords', @$blog?->tags && $blog->tags->isNotEmpty() ? $blog->tags->pluck('name')->implode(', ') : '')
@section('canonical_url', route('frontend.blog.details', $blog?->slug))

{{-- Open Graph Tags for Facebook --}}
@section('og_title', @$blog?->meta_title ?? @$blog?->title)
@section('og_description', @$blog?->meta_description ?? @$blog?->description)
@section('og_image', @$blog?->media?->first()?->getUrl())
@section('og_url', route('frontend.blog.details', $blog?->slug))
@section('og_type', 'article')

{{-- Article-specific Open Graph Tags --}}
@push('og_article')
    @if($blog)
        <meta property="article:published_time" content="{{ $blog->created_at->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $blog->updated_at->toIso8601String() }}">
        @if($blog->created_by)
            <meta property="article:author" content="{{ $blog->created_by->name }}">
        @endif
        @if($blog->categories->isNotEmpty())
            @foreach($blog->categories as $category)
                <meta property="article:section" content="{{ $category->title }}">
            @endforeach
        @endif
        @if($blog->tags->isNotEmpty())
            @foreach($blog->tags as $tag)
                <meta property="article:tag" content="{{ $tag->name }}">
            @endforeach
        @endif
    @endif
@endpush

{{-- Twitter Card Tags --}}
@section('twitter_title', @$blog?->meta_title ?? @$blog?->title)
@section('twitter_description', @$blog?->meta_description ?? @$blog?->description)
@section('twitter_image', @$blog?->media?->first()?->getUrl())
@push('twitter_card')
    @if($blog && $blog->created_by)
        <meta name="twitter:creator" content="@{{ $blog->created_by->name }}">
    @endif
@endpush

{{-- Additional Meta Tags --}}
@push('additional_meta')
    @if($blog)
        <meta name="author" content="{{ $blog->created_by->name ?? 'Admin' }}">
        <meta name="article:author" content="{{ $blog->created_by->name ?? 'Admin' }}">
        <meta name="publish_date" property="og:publish_date" content="{{ $blog->created_at->toIso8601String() }}">
        <meta name="article:published_time" content="{{ $blog->created_at->toIso8601String() }}">
        <meta name="article:modified_time" content="{{ $blog->updated_at->toIso8601String() }}">
        @if($blog->categories && $blog->categories->isNotEmpty() && $blog->categories->first())
            <meta name="article:section" content="{{ $blog->categories->first()->title }}">
        @endif
    @endif
@endpush

{{-- Schema.org JSON-LD Structured Data for Article --}}
@push('structured_data')
    @if($blog)
        @php
            $themeOptions = Helpers::getThemeOptions();
            $schemaData = [
                "@context" => "https://schema.org",
                "@type" => "BlogPosting",
                "headline" => addslashes($blog->meta_title ?? $blog->title),
                "description" => addslashes(strip_tags($blog->meta_description ?? $blog->description)),
                "image" => $blog->media?->first()?->getUrl() ?? asset('admin/images/No-image-found.jpg'),
                "datePublished" => $blog->created_at->toIso8601String(),
                "dateModified" => $blog->updated_at->toIso8601String(),
                "author" => [
                    "@type" => "Person",
                    "name" => $blog->created_by->name ?? 'Admin'
                ],
                "publisher" => [
                    "@type" => "Organization",
                    "name" => $themeOptions['general']['site_title'] ?? config('app.name'),
                    "logo" => [
                        "@type" => "ImageObject",
                        "url" => asset($themeOptions['general']['header_logo'] ?? 'admin/images/logo.png')
                    ]
                ],
                "mainEntityOfPage" => [
                    "@type" => "WebPage",
                    "@id" => route('frontend.blog.details', $blog->slug)
                ],
                "wordCount" => str_word_count(strip_tags($blog->content ?? '')),
                "inLanguage" => app()->getLocale()
            ];
            
            if($blog->categories && $blog->categories->isNotEmpty() && $blog->categories->first()) {
                $schemaData["articleSection"] = $blog->categories->first()->title;
            }
            
            if($blog->tags && $blog->tags->isNotEmpty()) {
                $schemaData["keywords"] = $blog->tags->pluck('name')->implode(', ');
            }
        @endphp
        <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
@endpush


@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.blogs.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.blog.index') }}">{{__('frontend::static.blogs.blogs')}}</a>
    <span class="breadcrumb-item active">{{ $blog->title }}</span>
</nav>
@endsection
@section('content')
@if ($blog)
<!-- Blog Section Start -->
<section class="blog-section">
    <div class="container-fluid-md">
        <div class="blog-details-image">
            <img src="{{ $blog?->web_img_thumb_url }}" alt="{{ $blog?->title }}" class="img-fluid">
        </div>
        <div class="detail-content">
            <div class="title">
                <h4>{{ $blog?->title }}</h4>
                @if($blog?->tags && $blog->tags->isNotEmpty() && $blog->tags->first())
                <span class="badge primary-light-badge d-sm-flex d-none">
                    {{ $blog->tags->first()->name }}
                </span>
                @endif
            </div>
            <div
                class="d-flex align-items-sm-center align-items-start gap-1 justify-content-between flex-sm-row flex-column">
                <ul class="blog-detail">
                    @if($blog?->categories && $blog->categories->isNotEmpty() && $blog->categories->first())
                        <li>{{ $blog->categories->first()->title }}</li>
                    @endif
                    <li>{{ Helpers::dateTimeFormat($blog?->created_at, 'd M, Y') }}</li>
                </ul>
                <span class="text-light">
                    - {{__('frontend::static.blogs.by')}} {{ $blog?->created_by?->name ?? 'unknown' }}
                </span>
            </div>

            <div class="detail-sec">
                <div class="details-title">
                    <h3>{{__('frontend::static.blogs.description')}}</h3>
                    <p>{{ $blog?->description }}</p>
                </div>

                <ul class="overview-list">
                    {!! $blog?->content !!}
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- Blog Section End -->

@if(count($blog?->comments ?? []))
<section class="review-section">
    <div class="container-fluid-md">
        <div class="title">
            <h3>{{__('frontend::static.blogs.comments')}} ({{ $blog->comments->where('parent_id', null)->count() }})
            </h3>
        </div>
        <ul class="review-content">
            @foreach($blog->comments as $comment)
            @if(is_null($comment->parent_id))
            @include('frontend.layout.comment', ['comment' => $comment])
            @endif
            @endforeach
        </ul>
    </div>
</section>
@endif

<!-- Create Comments Section Start -->
<section class="comment-section">
    <div class="container-fluid-md">
        <div class="title">
            <h3>{{__('frontend::static.blogs.leave_a_comment')}}</h3>
        </div>
        <form action="{{ route('frontend.comments.store', $blog->id) }}" class="" method="POST" id="commentForm">
            @csrf
            <div class="row g-md-4 g-sm-2 g-1">
                <div class="col-12">
                    <div class="form-group">
                        <label for="email">{{__('frontend::static.blogs.message')}}</label>
                        <i class="iconsax" icon-name="mail"></i>
                        <textarea class="form-control form-control-white" name="message" id="message"
                            placeholder="{{__('frontend::static.blogs.enter_message')}}"></textarea>
                        @error('message')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                @auth
                    <div class="col-12">
                        <button type="submit"
                            class="btn btn-solid mt-2">{{__('frontend::static.blogs.post_comment')}}</button>
                    </div>
                @endauth
                @guest
                    <div class="col-12">
                        <a href="{{ url('login') }}" class="btn btn-solid mt-2">{{__('frontend::static.blogs.post_comment')}}</a>
                    </div>
                @endguest
            </div>
        </form>
    </div>
</section>
<!-- Create Comments Section End -->
@endif

<!-- Recent Blog Section Start -->
<section class="blog-section section-b-space">
    <div class="container-fluid-md">
        <div class="title">
            <h2>{{__('frontend::static.blogs.recent_blogs')}}</h2>
            <a class="view-all" href="{{ route('frontend.blog.index') }}">
                {{__('frontend::static.home_page.view_all')}}
                <i class="iconsax" icon-name="arrow-right"></i>
            </a>
        </div>
        <div class="blog-content">
            <div class="row row-cols-1 row-cols-lg-3 g-3 ratio2_1 g-3">
                @forelse ($recentBlogs as $recentBlog)
                <div class="col">
                    <div class="blog-main">
                        <div class="card">
                            <div class="overflow-hidden b-r-5">
                                <a href="{{ route('frontend.blog.details', $recentBlog?->slug) }}" class="card-img">
                                    <img src="{{ $recentBlog?->web_img_thumb_url }}" alt="{{ $recentBlog?->title }}"
                                        class="bg-img">
                                </a>
                            </div>
                            <div class="card-body">
                                <h4>
                                    <a href="{{ route('frontend.blog.details', $recentBlog?->slug) }}">{{ $recentBlog?->title }}
                                    </a>
                                </h4>
                                <ul class="blog-detail">
                                    @if($recentBlog?->categories && $recentBlog->categories->isNotEmpty() && $recentBlog->categories->first())
                                        <li>{{ $recentBlog->categories->first()->title }}</li>
                                    @endif
                                    <li> {{ Helpers::dateTimeFormat($recentBlog?->created_at, 'd M, Y') }}</li>
                                </ul>
                                <div class="blog-footer">
                                    <div>
                                        <i class="iconsax" icon-name="message-dots"></i>
                                        <span>{{ $recentBlog?->comments_count }}</span>
                                    </div>
                                    <span>
                                        - By {{ $recentBlog?->created_by?->name ?? 'unknown' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <p> {{__('frontend::static.blogs.not_found')}}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
<!-- Recent Blog Section End -->
@endsection

@push('js')
<script>
$(document).ready(function() {
    "use strict";

    $("#commentForm").validate({
        ignore: [],
        rules: {
            "message": "required",
        }
    });
});
</script>
@endpush