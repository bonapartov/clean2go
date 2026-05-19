@use('app\Helpers\Helpers')

@extends('frontend.layout.master')
@push('css')
<!-- datatables css-->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/select-datatables.min.css') }}">
<!-- Flatpicker css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('title',   __('frontend::static.account.referral'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.referral') }}</span>
</nav>
@endsection
@section('content')
<!-- Service List Section Start -->
<section class="section-b-space">
    <div class="container-fluid-md">
        <div class="profile-body-wrapper">
            <div class="row">
                @includeIf('frontend.account.sidebar')
                <div class="col-xxl-9 col-xl-8">
                    <button class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">
                    {{ __('frontend::static.account.show_menu') }}
                    </button>
                    <div class="profile-main h-100">
                        <div class="card m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.my_referral') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="widgets">
                                    <div class="row g-sm-4 g-3">
                                        <div class="col-sm-6">
                                            <div class="card">
                                                <div class="widget-data">
                                                    <div class="image-wrapper">
                                                        <svg>
                                                            <use xlink:href="{{ asset('frontend/images/svg/referrals.svg#barcode') }}"></use>
                                                        </svg>
                                                    </div>
                                                    <div class="data">
                                                        <h5>{{ __('frontend::static.account.referral_code') }}</h5>
                                                        <h3> {{ auth()?->user()?->referral_code ?? ''}} <i data-feather="copy"></i></h3>
                                                    </div>
                                                    <svg class="bottom-svg">
                                                        <use xlink:href="{{ asset('frontend/images/svg/referrals.svg#code') }}"></use>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="card">
                                                <div class="widget-data">
                                                    <div class="image-wrapper">
                                                        <svg>
                                                            <use xlink:href="{{ asset('frontend/images/svg/referrals.svg#total-referral') }}"></use>
                                                        </svg>
                                                    </div>
                                                    <div class="data">
                                                        <h5>{{ __('frontend::static.account.total_referrals') }}</h5>
                                                        <h3>{{auth()?->user()?->getReferralCountAttribute() ?? 0}} {{ __('frontend::static.account.referral') }}</h3>
                                                    </div>
                                                    <svg class="bottom-svg">
                                                        <use xlink:href="{{ asset('frontend/images/svg/referrals.svg#bottom-total') }}"></use>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card payment m-0">
                                <div class="card-body">
                                    <div class="col-12">
                                            <div class="wallet-data wallet-table wallet-data-table custom-scrollbar common-table">
                                            <div class="table-responsive border-0">
                                                {!! $dataTable->table() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->

@endsection

@push('js')
<!-- datatables js -->
<script src="{{ asset('frontend/js/datatables.min.js') }}"></script>
    {!! $dataTable->scripts() !!}
@endpush
