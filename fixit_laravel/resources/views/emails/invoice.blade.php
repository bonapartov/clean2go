@use('App\Helpers\Helpers')
@php
    $currency = Helpers::getDefaultCurrency()?->symbol;
    $addons = $booking?->additional_services ?? collect();
    $addonsChargeAmount = $addons->sum(fn($a) => $a->pivot?->total_price ?? (($a->pivot?->price ?? 0) * ($a->pivot?->qty ?? 1)));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ env('APP_NAME') }}</title>
</head>
<style type="text/css">
    body{
        font-family: 'DejaVu Sans', sans-serif;
    }
    .m-0{margin:0;}
    .p-0{padding:0;}
    .pt-5{padding-top:5px;}
    .mt-10{margin-top:10px;}
    .text-center{text-align:center!important;}
    .w-100{width:100%;}
    .w-50{width:50%;}
    .w-85{width:85%;}
    .w-15{width:15%;}
    .logo img{width:200px;height:60px;}
    .gray-color{color:#52a750a4;}
    .text-bold{font-weight:bold;}
    .border{border:1px solid black;}
    table tr,th,td{
        border:1px solid #d2d2d2;
        border-collapse:collapse;
        padding:7px 8px;
    }
    table tr th{
        background:#F4F4F4;
        font-size:15px;
    }
    table tr td{font-size:13px;}
    table{border-collapse:collapse;}
    .box-text p{line-height:10px;}
    .float-left{float:left;}
    .total-part{font-size:16px;line-height:12px;}
    .total-right p{padding-right:20px;}
</style>
<body>
<div class="head-title">
    <h1 class="text-center m-0 p-0">{{ __('static.booking.invoice') }}</h1>
</div>

<div class="add-detail mt-10">
    <div class="w-50 float-left mt-10">
        <p class="m-0 pt-5 text-bold w-100">{{ __('static.booking.order_id_title') }}
            <span class="gray-color">{{$booking?->booking_number}}</span></p>
        <p class="m-0 pt-5 text-bold w-100">{{ __('static.booking.order_date_title') }}
            <span class="gray-color">{{$booking?->created_at->format("d/m/Y")}}</span></p>
        <p class="m-0 pt-5 text-bold w-100">{{ __('static.booking.payment_method_title') }}
            <span class="gray-color">{{$booking?->payment_method}}</span></p>
    </div>
    <div style="clear:both;"></div>
</div>

<div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
        <tr>
            <th class="w-50">{{ __('static.booking.customer_address') }}</th>
        </tr>
        <tr>
            <td>
                <div class="box-text">
                    <p>{{$booking?->consumer?->getPrimaryAddressAttribute()?->area}}</p>
                    <p>{{$booking?->consumer?->getPrimaryAddressAttribute()?->postal_code}},</p>
                    <p>{{$booking?->consumer?->getPrimaryAddressAttribute()?->city}},</p>
                    <p>{{$booking?->consumer?->getPrimaryAddressAttribute()?->state?->name}},
                        {{$booking?->consumer?->getPrimaryAddressAttribute()?->country?->name}}</p>
                    <p>Contact: ({{$booking?->consumer?->code}}) {{$booking?->consumer?->phone}}</p>
                    <p>Customer Name: {{$booking?->consumer?->name}}</p>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
        <tr>
            <th>{{ __('static.booking.invoice_no') }}</th>
            <th>{{ __('static.booking.invoice_service_name') }}</th>
            <th>{{ __('static.booking.invoice_price') }}</th>
            <th>{{ __('static.booking.invoice_per_serviceman_charge') }}</th>
            <th>{{ __('static.booking.invoice_extra_charge') }}</th>
            <th>{{ __('static.booking.invoice_platform_fees') }}</th>
            <th>{{ __('static.booking.invoice_subtotal') }}</th>
            <th>{{ __('static.booking.invoice_grand_total') }}</th>
        </tr>
        <tr align="center">
            <td>1</td>
            <td>{{$booking?->service?->title}}</td>
            <td>{{$booking?->service_price}}</td>
            <td>{{$booking?->per_serviceman_charge}}</td>
            <td>{{ $booking?->extra_charges->isNotEmpty() ? $booking?->extra_charges->sum('total') : '0.00' }}</td>
            <td>{{$booking?->platform_fees}}</td>
            <td>{{$booking?->subtotal}}</td>
            <td>
                {{ $booking?->extra_charges->isNotEmpty()
                    ? $booking?->subtotal + $booking?->tax + $booking?->extra_charges->sum('total')
                    : $booking?->subtotal + $booking?->tax }}
            </td>
        </tr>

        {{-- Inline Add-ons --}}
        @if($addons->count() > 0)
            <tr>
                <td colspan="8">
                    <p class="text-bold mt-10">{{ __('static.booking.invoice_add_ons') }}</p>
                    @foreach($addons as $index => $addon)
                        @php
                            $qty = $addon->pivot?->qty ?? 1;
                            $price = $addon->pivot?->price ?? 0;
                            $total = $addon->pivot?->total_price ?? ($qty * $price);
                        @endphp
                        <p>{{ $index + 1 }}. {{ $addon->title ?? '-' }} — {{ $qty }} × {{ $currency }}{{ number_format($price, 2) }} = {{ $currency }}{{ number_format($total, 2) }}</p>
                    @endforeach
                </td>
            </tr>
        @endif

        <tr>
            <td colspan="8">
                <div class="total-part">
                    <div class="total-left w-85 float-left" align="right">
                        <p>{{ __('static.booking.invoice_sub_total') }}</p>
                        <p>{{ __('static.booking.invoice_tax') }}</p>
                        <p>{{ __('static.booking.invoice_platform_fees') }}</p>
                        <p>{{ __('static.booking.invoice_extra_charge') }}</p>
                        <p>{{ __('static.booking.invoice_add_ons') }}</p>
                        <p>{{ __('static.booking.invoice_total_payable') }}</p>
                    </div>
                    <div class="total-right w-15 float-left text-bold" align="right">
                        <p>{{ $currency }}{{$booking?->subtotal}}</p>
                        <p>{{ $currency }}{{$booking?->tax}}</p>
                        <p>{{ $currency }}{{$booking?->platform_fees}}</p>
                        <p>{{ $currency }}{{ $booking?->extra_charges?->sum('total') ?? 0 }}</p>
                        <p>{{ $currency }}{{ number_format($addonsChargeAmount, 2) }}</p>
                        <p>{{ $currency }}{{ number_format($booking?->total + ($booking?->extra_charges?->sum('total') ?? 0) + $addonsChargeAmount, 2) }}</p>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>