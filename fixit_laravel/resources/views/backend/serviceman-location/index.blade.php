@use('app\Helpers\Helpers')
@php
    $settings = Helpers::getSettings();
    $yandexMapKey = config('app.yandex_map_api_key');
    if (!$yandexMapKey && $settings) {
        $yandexMapKey = $settings['firebase']['yandex_map_api_key'] ?? null;
    }
@endphp

@extends('backend.layouts.master')
@section('title', __('static.serviceman.serviceman_list'))
@section('content')
    <div class="serviceman-location">
        <div class="position-relative">
            <button class="btn toggle-menu d-xl-none">
               <i data-feather="align-left"></i>
            </button>
            <div class="contentbox service-list-box">
                <div class="inside">
                    <div class="contentbox-title">
                        <h3>{{ __('static.serviceman.serviceman_list') }}</h3>
                        <button class="location-close-btn btn d-xl-none">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="search-box-group">
                        <input type="search" placeholder="{{ __('static.common.search_here') }}" name="" class="form-control" id="">
                        <i class="ri-search-line"></i>
                    </div>
                    <ul class="location-list">
                        @foreach ($servicemen as $serviceman)
                            <li class="location-item" data-serviceman-id="{{ $serviceman['id'] }}"
                                data-lat="{{ $serviceman['lat'] ?? '' }}" data-lng="{{ $serviceman['lng'] ?? '' }}">
                                <div class="user-image">
                                    <img src="{{ $serviceman['image'] ?? asset('admin/images/user.png') }}" alt="serviceman"
                                        class="img-fluid">
                                </div>
                                <div class="user-name">
                                    <div>
                                        <h5 class="name">{{ $serviceman['name'] }}</h5>
                                        <div class="rate-box">
                                            <i class="ri-star-fill"></i>
                                            {{ $serviceman['review'] ? number_format($serviceman['review'], 1) : 'Unrated' }}
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary view-location-btn"
                                        data-serviceman-id="{{ $serviceman['id'] }}"
                                        data-lat="{{ $serviceman['lat'] ?? '' }}" data-lng="{{ $serviceman['lng'] ?? '' }}">
                                        <span class="btn-text d-md-block d-none">{{ __('static.serviceman.view_location') }}</span>
                                        <i data-feather="navigation" class="d-md-none"></i>
                                        <span class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if ($yandexMapKey)
                <div class="location-map">
                    <div id="map_canvas"></div>
                    <button id="show-all-servicemen" class="btn btn-primary" style="position: absolute; top: 10px; right: 10px; z-index: 100; display: none;">
                        {{ __('static.serviceman.show_all') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($yandexMapKey)
    @include('backend.serviceman-location.google')
@endif

@push('js')
    <script>
        $(".toggle-menu").click(function() {
            $(".service-list-box").addClass("show");
        });

        $(".location-close-btn").click(function () {
            $(".service-list-box").removeClass("show");
        });
    </script>
@endpush