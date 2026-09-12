@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@use('App\Enums\PaymentMethod')
@extends('frontend.layout.master')
@push('css')
<!-- datatables css-->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/select-datatables.min.css') }}">
<!-- Flatpicker css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
<style>
.wallet-offers-list {
    margin-bottom: 20px;
}
.wallet-offers-list .offers:nth-child(2) .offer-card{
    background: linear-gradient(332deg, rgba(255, 116, 86, 1) 0%, rgba(255, 116, 86, 0.8) 100%);
}
.wallet-offers-list .offers:nth-child(2) .offer-card .offer-badge{
    color: rgba(255, 116, 86, 1);
}

.wallet-offers-list .offers:nth-child(3) .offer-card{
    background: linear-gradient(332deg, rgba(72, 191, 253 , 1) 0%,  rgba(72, 191, 253 , 0.8) 100%);
}
.wallet-offers-list .offers:nth-child(3) .offer-card .offer-badge{
    color:  rgba(72, 191, 253 , 1);
}

.wallet-offers-list .offers:nth-child(4) .offer-card{
    background: linear-gradient(332deg, rgba(39, 175, 77 , 1) 0%,  rgba(39, 175, 77 , 0.8) 100%);
}
.wallet-offers-list .offers:nth-child(4) .offer-card .offer-badge{
    color:  rgba(39, 175, 77 , 1);
}

.wallet-offers-list .offers:nth-child(5) .offer-card{
    background: linear-gradient(332deg, rgba(255, 75, 75 , 1) 0%,  rgba(255, 75, 75 , 0.8) 100%);
}
.wallet-offers-list .offers:nth-child(5) .offer-card .offer-badge{
    color:  rgba(255, 75, 75 , 1);
}
.offer-card {
    background: #5465ff;
    border-radius: 8px;
    padding: 20px;
    color: white;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 0;
    background: linear-gradient(332deg, rgba(84, 101, 255 , 1) 0%, rgba(84, 101, 255 , 0.8) 100%);
}
.offer-card img{
    position: absolute;
    bottom: -90px;
    right: -160px;
    height: 250px;
    z-index: -1;
}
.offer-header{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}
.offer-title {
        font-size: 17px;
    font-weight: 600;
    margin: 0;
    color: white;
    margin-top: 6px;
    line-height: 1.4;
    /* margin-inline: auto; */
    /* text-align: center;*/
}
.offer-badge {
    background: rgba(255, 255, 255, 1);
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-inline: auto;
    display: flex;
    justify-content: center;
    align-items: center;
    width: max-content;
    letter-spacing: 0.3px;
    color: #5465ff;
    margin-top: 16px;
    padding: 6px 14px;
}
.offer-description {
    font-size: 14px;
    margin-bottom: 15px;
    opacity: 0.9;
    flex-grow: 1;
    color: white !important;
}
.offer-details {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 500;
}
.offer-min-amount {
        display: flex;
    align-items: center;
    gap: 5px;
    justify-content: center;
    width: 100%;
    color: #fff;
    line-height: 1;
    font-weight: 500;
    letter-spacing: 0.3px;
}
.offer-min-amount i {
       --Iconsax-Size: 20px;
}
.offer-min-amount i svg path{
    fill: #fff !important;
}
</style>
@endpush

@section('title', __('frontend::static.account.wallet'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.wallet') }}</span>
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
                        <div class="card payment m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.my_wallet') }}
                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                            <span class="text-success">{{ Helpers::getDefaultCurrencySymbol() }}{{ auth()?->user()?->wallet?->balance ?? 00 }}</span>
                                        @else
                                            <span class="text-success">{{ auth()?->user()?->wallet?->balance ?? 00 }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                        @endif
                                    </h3>
                                </div>
                                <button type="button" class="edit-option text-theme-color" data-bs-toggle="modal"
                                    data-bs-target="#walletModal">
                                    + {{ __('frontend::static.account.add_balance') }}
                                </button>
                            </div>
                            <div class="card-body wallet-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="select-date">
                                            <h4>{{ __('frontend::static.account.transactions') }}</h4>
                                            <div class="date-pick">
                                                <label>{{ __('frontend::static.account.date') }}</label>
                                                <div class="input-group flatpicker-calender">
                                                    <input class="form-control form-control-white" id="range-date"
                                                        type="text" readonly="readonly" name="date"
                                                        placeholder= {{ __('frontend::static.bookings.select_date') }}>
                                                    <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                </div>
                                                <button id="filter-btn" class="btn btn-solid">{{ __('frontend::static.account.apply') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="wallet-data wallet-table wallet-data-table custom-scrollbar common-table">
                                            <div class="border-0">
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

@php
$paymentMethods = Helpers::getActiveOnlinePaymentMethods() ?? [];
@endphp

<!-- add money modal -->
<div class="modal fade wallet-modal" id="walletModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{route('frontend.wallet.topUp')}}" method="post" id="topUpForm">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="walletModalLabel">{{ __('frontend::static.account.add_money') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="add-money">
                        @if(isset($walletBonuses) && $walletBonuses->count() > 0)
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="mb-2">{{ __('frontend::static.account.wallet_offers') ?? 'Wallet Top-Up Offers' }}</label>
                                <div class="wallet-offers-list">
                                    <div class="row g-3">
                                        @foreach($walletBonuses as $bonus)
                                        @php
                                            $bonusText = '';
                                            $currencySymbol = Helpers::getDefaultCurrencySymbol();
                                            if($bonus['type'] === 'fixed') {
                                                $bonusText = $currencySymbol . number_format($bonus['bonus'], 2);
                                            } else {
                                                $bonusText = number_format($bonus['bonus'], 0) . '%';
                                            }
                                            if($bonus['max_bonus'] > 0) {
                                                $maxBonusText = $currencySymbol . number_format($bonus['max_bonus'], 2);
                                                $bonusText .= ' (Max: ' . $maxBonusText . ')';
                                            }
                                        @endphp
                                        <div class="col-6 offers">
                                            <div class="offer-card" data-min-amount="{{ $bonus['min_top_up_amount'] }}">
                                                <img src="{{ asset('frontend/images/svg/2.svg') }}" alt="" class="img-fluid service-1 lozad" data-loaded="true">
                                                <div class="offer-header">
                                                    <div class="offer-details">
                                                        <span class="offer-min-amount">
                                                            <i class="iconsax" icon-name="wallet-open"></i>
                                                            Min: {{ $currencySymbol }}{{ number_format($bonus['min_top_up_amount'], 2) }}
                                                        </span>
                                                    </div>
                                                    <h5 class="offer-title">{{ $bonus['name'] ?? 'Special Offer' }}</h5>
                                                    <span class="offer-badge">{{ $bonusText }} Bonus</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="mb-2">{{ __('frontend::static.account.add_from') }}</label>
                                <div class="position-relative phone-detail">
                                    <i class="iconsax input-icon" icon-name="wallet-open"></i>
                                    <select class="form-select form-select-white select-2" id="payment_method"
                                        name="payment_method"
                                        data-placeholder="{{ __('frontend::static.account.select_payment_gateway') }}">
                                        <option class="select-placeholder" value=""></option>
                                        @foreach($paymentMethods as $payment)
                                            @if ($payment['slug'] !== PaymentMethod::COD)
                                                <option value="{{$payment['slug']}}">{{ $payment['name'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="mb-2">{{ __('frontend::static.account.amount') }}</label>
                                <div class="input-group">
                                    <input type="number" name="amount"
                                        placeholder="{{ __('frontend::static.account.amount') }}"
                                        class="form-control form-control-white order-0 w-100">
                                    <i class="iconsax input-icon" icon-name="money-in"></i>
                                </div>
                                @error('amount')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        data-bs-dismiss="modal">{{ __('frontend::static.account.cancel') }}</button>
                    <button type="submit" id="addMondayBtn"
                        class="btn btn-solid">{{ __('frontend::static.account.add_money') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

<!-- datatables js -->
<script src="{{ asset('frontend/js/datatables.min.js') }}"></script>

<!-- flatpickr js -->
<script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('frontend/js/flat-pickr/l10n/ru.js') }}"></script>
<script>flatpickr.localize(flatpickr.l10ns.ru);</script>
<script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script>
{!! $dataTable->scripts() !!}
<script>
$(function() {
    "use strict";

    // Make offer cards clickable to auto-fill amount
    @if(isset($walletBonuses) && $walletBonuses->count() > 0)
    $(document).on('click', '.offer-card', function() {
        var minAmount = $(this).data('min-amount');
        if (minAmount) {
            $('input[name="amount"]').val(minAmount).trigger('input');
            // Scroll to amount field
            $('input[name="amount"]')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    @endif

    flatpickr("#range-date", {
        mode: "range",
        dateFormat: "Y-m-d",
    });

    $(document).ready(function () {
        var table = $('#wallet-data').DataTable();

        // Table ne table-responsive div ma wrap karo
        $('#wallet-data').wrap('<div class="table-responsive"></div>');
    });

    $("#topUpForm").validate({
        ignore: [],
        rules: {
            "amount": {
                required: true,
                min: 10,
                max: 10000
            },
            "payment_method": "required"
        },
        messages: {
            "amount": {
                required: "Please enter an amount.",
                min: "The minimum amount is 10.",
                max: "The maximum amount is 10000."
            },
            "payment_method": "Please select a payment method."
        }
    });

    $('#payment_method').on('change', function() {
        $(this).valid();
    });

    $('#addMondayBtn').on('click', function() {
        if ($("#topUpForm").valid()) {
            $('#topUpForm').submit();
        }
    });

    $('#filter-btn').on('click', function() {

        const dateRange = $('#range-date').val();
        if (!dateRange) {
            alert("Please select a date range.");
            return;
        }

        const [startDate, endDate] = $('#range-date').val().split(' to ');
        const url = `{{ route('frontend.account.wallet') }}?start_date=${startDate}&end_date=${endDate}`;
                    $('#wallet-data').DataTable().ajax.url(url).load();
                    $('#range-date').val(dateRange);
    });
});
</script>
@endpush
