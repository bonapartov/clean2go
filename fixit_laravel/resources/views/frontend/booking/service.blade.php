@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@use('App\Enums\ServiceTypeEnum')
@extends('frontend.layout.master')
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/jquery-datetimepicker/jquery.datetimepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('frontend/css/vendors/xdshoft-datetimerpicker/xdsoft-picker.css') }}" />
@endpush

@php
    $maxSelected = session('cart')['required_servicemen'];
    $perServicemenCharge = Helpers::getPerServicemen($cartItem['service']); 
@endphp

@section('title', __('frontend::static.bookings.service_boking'))

@section('breadcrumb')
    <nav class="breadcrumb breadcrumb-icon">
        <a class="breadcrumb-item" href="{{ route('frontend.home') }}">{{ __('frontend::static.bookings.home') }}</a>
        <a class="breadcrumb-item" href="{{ route('frontend.service.index') }}">{{ __('frontend::static.bookings.services') }}</a>
        <a class="breadcrumb-item">{{ __('frontend::static.bookings.service_booking') }}</a>
    </nav>
@endsection

@section('content')
    <!-- Service List Section Start -->
    <section class="section-b-space service-section">
        <div class="container-fluid-lg">
            <div class="row g-4">
                <div class="col-xxl-8 col-xl-7">
                    <div class="cart basic-wizard important-validation br-10">
                        <form id="serviceBookingForm" action="{{ route('frontend.booking.service.store') }}" method="POST">
                            @csrf
                            <div class="stepper-horizontal" id="stepper1">
                                <div class="step-heading stepper-one stepper step editing" data-step="1">
                                    <div class="step-circle"><span>01</span></div>
                                    <div class="step-title">{{ __('frontend::static.bookings.book_date_and_time') }}</div>
                                </div>
                                <div class="step-heading stepper-two step" data-step="2">
                                    <div class="step-circle"><span>02</span></div>
                                    <div class="step-title">{{ __('frontend::static.bookings.bill_summary') }}</div>
                                </div>
                            </div>
                            <div id="msform">
                                <input type="hidden" name="service_id" value="{{ $cartItem['service']?->id }}" />
                                <div class="step-content stepper-one needs-validation custom-input" id="step1Div" data-step="1">
                                    <div class="py-0 service-booking">
                                        <ul>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>

                                                <div class="booking-data">
                                                    @includeIf('frontend.booking.select-address')
                                                </div>
                                                @error('address_id')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </li>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">
                                                        {{ __('frontend::static.bookings.date_and_time') }}</h3>
                                                    
                                                    @php
                                                        $isScheduledService = isset($cartItem['service']) && $cartItem['service']->type === ServiceTypeEnum::SCHEDULED;
                                                    @endphp

                                                    @if($isScheduledService)
                                                        {{-- Scheduled Service Booking Fields --}}
                                                        <div class="scheduled-booking-section">
                                                            {{-- Service Frequency Selection --}}
                                                            <div class="mb-3">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.booking_frequency') }}</strong></label>
                                                                <div class="booking-form-group">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="booking_frequency" id="frequencyDaily" value="daily" checked>
                                                                        <label class="form-check-label" for="frequencyDaily">{{ __('frontend::static.bookings.daily') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="booking_frequency" id="frequencyWeekly" value="weekly">
                                                                        <label class="form-check-label" for="frequencyWeekly">{{ __('frontend::static.bookings.weekly') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="booking_frequency" id="frequencyMonthly" value="monthly">
                                                                        <label class="form-check-label" for="frequencyMonthly">{{ __('frontend::static.bookings.monthly') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="booking_frequency" id="frequencyYearly" value="yearly">
                                                                        <label class="form-check-label" for="frequencyYearly">{{ __('frontend::static.bookings.yearly') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="booking_frequency" id="frequencyCustom" value="custom">
                                                                        <label class="form-check-label" for="frequencyCustom">{{ __('frontend::static.bookings.custom') }}</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Date Range Selection --}}
                                                            <div class="mb-3">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.service_duration') }}</strong></label>
                                                                <div class="row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="input-group flatpicker-calender">
                                                                            <input class="form-control form-control-gray" type="text" id="schedule_start_date" name="schedule_start_date" readonly placeholder="{{ __('frontend::static.bookings.start_date') }}" required>
                                                                            <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="input-group flatpicker-calender">
                                                                            <input class="form-control form-control-gray" type="text" id="schedule_end_date" name="schedule_end_date" readonly placeholder="{{ __('frontend::static.bookings.end_date') }}" required>
                                                                            <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Weekdays Selection (for Daily with selected days) --}}
                                                            <div class="mb-3" id="weekdaysSelection" style="display: none;">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.select_weekdays') }}</strong></label>
                                                                <div class="booking-form-group">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdayMonday" value="monday">
                                                                        <label class="form-check-label" for="weekdayMonday">{{ __('frontend::static.bookings.monday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdayTuesday" value="tuesday">
                                                                        <label class="form-check-label" for="weekdayTuesday">{{ __('frontend::static.bookings.tuesday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdayWednesday" value="wednesday">
                                                                        <label class="form-check-label" for="weekdayWednesday">{{ __('frontend::static.bookings.wednesday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdayThursday" value="thursday">
                                                                        <label class="form-check-label" for="weekdayThursday">{{ __('frontend::static.bookings.thursday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdayFriday" value="friday">
                                                                        <label class="form-check-label" for="weekdayFriday">{{ __('frontend::static.bookings.friday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdaySaturday" value="saturday">
                                                                        <label class="form-check-label" for="weekdaySaturday">{{ __('frontend::static.bookings.saturday') }}</label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="selected_weekdays[]" id="weekdaySunday" value="sunday">
                                                                        <label class="form-check-label" for="weekdaySunday">{{ __('frontend::static.bookings.sunday') }}</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Custom Date Selection (for Custom frequency) --}}
                                                            <div class="mb-3" id="customDateSelection" style="display: none;">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.select_custom_dates') }}</strong></label>
                                                                <div class="input-group flatpicker-calender">
                                                                    <input class="form-control form-control-gray" type="text" id="custom_dates_picker" name="custom_dates" readonly placeholder="{{ __('frontend::static.bookings.pick_multiple_dates') }}">
                                                                    <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                                </div>
                                                                <small class="text-muted">{{ __('frontend::static.bookings.select_multiple_dates_hint') }}</small>
                                                            </div>

                                                            {{-- Time Selection for Scheduled Services --}}
                                                            <div class="mb-3">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.service_time') }}</strong></label>
                                                                <div class="input-group flatpicker-calender">
                                                                    <input class="form-control form-control-gray time-local" id="schedule_time_picker" type="time" name="schedule_time" placeholder="{{ __('frontend::static.bookings.select_time') }}" required>
                                                                    <i class="iconsax input-icon" icon-name="clock"></i>
                                                                </div>
                                                            </div>

                                                            {{-- Scheduled List Preview --}}
                                                            <div class="mb-3" id="scheduledListPreview" style="display: none;">
                                                                <label class="form-label mb-2"><strong>{{ __('frontend::static.bookings.scheduled_list') }} (<span id="totalScheduledCount">0</span>)</strong></label>
                                                                <div class="scheduled-dates-list border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                                                    <div id="scheduledDatesContainer" class="d-flex flex-column gap-2">
                                                                        <p class="text-muted mb-0">{{ __('frontend::static.bookings.no_dates_selected') }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Hidden inputs for scheduled booking data --}}
                                                            <input type="hidden" name="is_scheduled_booking" value="1">
                                                            <input type="hidden" name="scheduled_dates_json" id="scheduledDatesJson" value="">
                                                            <input type="hidden" name="scheduled_services_count" id="scheduledServicesCount" value="0">
                                                            <input type="hidden" name="date_time" id="dateTimeInput" value="">
                                                        </div>
                                                    @else
                                                        {{-- Regular Service Booking Fields --}}
                                                        <div class="date-time-picket-sec">
                                                            <div class="select-option">
                                                                <div class="form-check mb-0">
                                                                    <input type="radio" id="customTimeSlot01" value="custom" name="select_date_time_2" checked class="form-radio-input custom_date_time_1">
                                                                    <label for="customTimeSlot01">{{ __('frontend::static.bookings.custom_date_time') }}</label>
                                                                </div>
                                                                
                                                                <div class="d-flex align-items-start gap-sm-3 gap-2">
                                                                    <div class="form-check mb-0">
                                                                        <input type="radio" data-bs-toggle="modal" data-bs-target="#datetimeModal" id="providerTimeSlot01" value="timeslot" name="select_date_time_2" class="form-radio-input custom_date_time_1">
                                                                        <label for="providerTimeSlot01">{{ __('frontend::static.bookings.as_per_provider_time_slot') }}</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="date-time-picker">
                                                                <div class="input-group flatpicker-calender">
                                                                    <input class="form-control form-control-gray datetime-local" id="datetime-local" type="text" readonly="readonly" placeholder="Select Date">
                                                                    <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                                </div>

                                                                <div class="input-group flatpicker-calender">
                                                                    <input class="form-control form-control-gray time-local" id="time-picker" type="time" placeholder="Select time">
                                                                    <i class="iconsax input-icon" icon-name="clock"></i>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="date_time" id="dateTimeInput" value="">
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('date_time')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </li>
                                            @isset($cartItem['service'])
                                                @if ($cartItem['select_serviceman'] == 'as_per_my_choice')
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <div class="select-servicemen-div selectServicemenDiv"
                                                                id="selectServicemenDiv">
                                                                <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </h3>
                                                                <button id="selectServicemenBtn" type="button"
                                                                    class="servicemen-lists mt-2" data-bs-toggle="modal"
                                                                    data-bs-target="#checkservicemenListModal">
                                                                    +
                                                                    {{ __('frontend::static.bookings.select_servicemen') }}
                                                                </button>
                                                            </div>
                                                            <div class="selected-servicemen-div selectedServicemenDiv"
                                                                id="selectedServicemenDiv" style="display:none;">
                                                                <div
                                                                    class="d-flex align-items-center gap-1 justify-content-between mb-2">
                                                                    <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                    </h3>
                                                                    <button type="button" class="edit-option"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#checkservicemenListModal">{{ __('frontend::static.bookings.edit') }}
                                                                    </button>
                                                                </div>
                                                                <div class="selected-men">

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="required_servicemen"
                                                            value="{{ $maxSelected }}" />
                                                        <input type="hidden" name="select_serviceman"
                                                            value="{{ $cartItem['select_serviceman'] }}" />
                                                    </li>
                                                @endif
                                            @endisset

                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">{{ __('frontend::static.bookings.custom_message') }}</h3>
                                                    <textarea class="form-control form-control-white" name="description" placeholder="Write here.." rows="3"></textarea>
                                                    <p class="mb-4 mt-2 text-light">{{ __('frontend::static.bookings.service_booking_note') }}</p>
                                                </div>
                                            </li>
                                            <!-- Additional Services Section -->
                                            @isset($cartItem['additional_services'])
                                                @if ($cartItem['service'] && $cartItem['additional_services'])
                                                    <div>
                                                        <h4 class="service-title">{{ __('frontend::static.modal.add_ons') }}</h4>
                                                        <div class="select-additional">
                                                            @foreach ($cartItem['additional_services'] as $index => $addOn)
                                                            @if (isset($addOn['id']))    
                                                                @php
                                                                    $additionalService = Helpers::getAdditionalServiceById($addOn['id']);
                                                                @endphp
                                                                    <div class="form-check">
                                                                        <input type="checkbox" id="additional-{{ $additionalService->id }}" name="additional_services[{{ $index }}][id]" value="{{ $additionalService->id }}" class="form-check-input" checked>
                                                                        <input type="hidden" name="additional_services[{{ $index }}][qty]" value="{{ $addOn['qty'] }}">
                                                                        @php
                                                                            $totalAddOnPrice = $additionalService->price * $addOn['qty']
                                                                        @endphp
                                                                        <label for="additional-{{ $additionalService->id }}">
                                                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                                {{ $additionalService->title }} - {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($additionalService->price) }}
                                                                            @else
                                                                                {{ $additionalService->title }} - {{ Helpers::covertDefaultExchangeRate($additionalService->price) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                                            @endif
                                                                            <div class="additional">
                                                                                <div for="additional-{{ $additionalService->id }}">
                                                                                {{ __('static.service.qty') }} {{ $addOn['qty'] }} 
                                                                                </div>
                                                                                <div>
                                                                                    {{ __('static.service.add_on_total_price') }}
                                                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                                        {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($totalAddOnPrice) }}
                                                                                    @else
                                                                                        {{ Helpers::covertDefaultExchangeRate($totalAddOnPrice) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endisset
                                        </ul>
                                    </div>
                                </div>
                                <div class="step-content stepper-two needs-validation custom-input" data-step="2" id="step2Div" style="display: none;">
                                    <div class="py-0 service-booking">
                                        <ul>
                                            @isset($cartItem['service'])
                                                @if ($cartItem['select_serviceman'] == 'as_per_my_choice')
                                                    <input type="hidden" id="serviceman_id" name="serviceman_id" value="" />
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <div class="select-servicemen-div selectServicemenDiv" id="selectServicemenDiv">
                                                                <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </h3>
                                                                <button id="selectServicemenBtn" type="button" class="servicemen-lists mt-2" data-bs-toggle="modal" data-bs-target="#checkservicemenListModal">
                                                                    +{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </button>
                                                            </div>
                                                            <div class="selected-servicemen-div selectedServicemenDiv" id="selectedServicemenDiv" style="display:none;">
                                                                <div class="d-flex align-items-center gap-1 justify-content-between mb-2">
                                                                    <h3>{{ __('frontend::static.bookings.selected_servicemen') }}
                                                                    </h3>
                                                                    <button type="button" class="edit-option" data-bs-toggle="modal" data-bs-target="#checkservicemenListModal">{{ __('frontend::static.bookings.edit') }}</button>
                                                                </div>
                                                                <div class="selected-men">

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @else
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <h3>{{ __('frontend::static.bookings.service_delivery_location') }}</h3>
                                                            <div class="input-group">
                                                                <input class="form-control form-control-white" value="As you previously said, the app will select your servicemen." type="text">
                                                                <i class="iconsax input-icon" icon-name="info-circle"></i>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endisset
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">Date and Time</h3>
                                                    <p class="text-light mb-2">{{ __('frontend::static.bookings.take_around') }}</p>

                                                    @if($isScheduledService)
                                                        {{-- Scheduled Service - Show Summary Only --}}
                                                        <div class="scheduled-booking-summary">
                                                            <div class="alert alert-info">
                                                                <strong>{{ __('frontend::static.bookings.scheduled_service') }}</strong>
                                                                <p class="mb-0 mt-2" id="scheduledSummaryText">{{ __('frontend::static.bookings.configured_in_step_1') }}</p>
                                                            </div>
                                                            <input type="hidden" name="select_date_time" value="scheduled">
                                                        </div>
                                                    @else
                                                        {{-- Regular Service Booking Fields --}}
                                                        <div class="date-time-picket-sec">
                                                            <div class="select-option">
                                                                <div class="form-check">
                                                                    <input type="radio" id="customTimeSlot" value="custom" name="select_date_time" class="form-radio-input" checked>
                                                                    <label for="customTimeSlot">{{ __('frontend::static.bookings.custom_date_time') }}</label>
                                                                </div>
                                                                <div class="d-flex align-items-start gap-sm-3 gap-2">
                                                                    <div class="form-check">
                                                                        <button type="button" class="time-slot" data-bs-toggle="modal" data-bs-target="#datetimeModal">
                                                                            <input type="radio" value="timeslot" id="providerTimeSlot" name="select_date_time" class="form-radio-input select_time_slot_2">
                                                                        </button>
                                                                    </div>
                                                                    <label for="providerTimeSlot">{{ __('frontend::static.bookings.as_per_provider_time_slot') }}</label>
                                                                </div>
                                                                @error('select_date_time')
                                                                    <span class="invalid-feedback d-block" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>

                                                            <div class="date-time-picker">
                                                                <div class="input-group flatpicker-calender">
                                                                    <input class="form-control form-control-gray datetime-local datetime-local-1" id="datetime-local" type="text" readonly="readonly" placeholder="Select Date">
                                                                    <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                                </div>
                                                                <div class="input-group">
                                                                    <input class="form-control form-control-gray time-picker time-local-1" id="time-picker" type="time" placeholder="Select time">
                                                                    <i class="iconsax input-icon" icon-name="clock"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-start justify-content-between gap-1 mb-2">
                                                        <h3 class="mb-0">{{ __('frontend::static.bookings.bill_summary') }}</h3>
                                                        @auth
                                                            <span class="text-success">{{ __('frontend::static.bookings.wallet') }}
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <small>: {{ Helpers::getDefaultCurrencySymbol() }}{{ auth()?->user()?->wallet?->balance }}</small>
                                                                @else
                                                                    <small>: {{ auth()?->user()?->wallet?->balance }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                                                @endif
                                                            </span>
                                                        @endauth
                                                    </div>

                                                    @php
                                                        $perAmount = Helpers::covertDefaultExchangeRate(
                                                            $perServicemenCharge,
                                                        );
                                                        $total = $perAmount * $maxSelected;
                                                        $symbol = Helpers::getDefaultCurrencySymbol();
                                                        $additionalTotal = 0;
                                                        $additionalCount = 0;
                                                    @endphp
                                                    @isset($cartItem['additional_services'])
                                                        @foreach ($cartItem['additional_services'] ?? [] as $addOn)
                                                            @if (isset($addOn['id']))
                                                                @php
                                                                    $additionalService = Helpers::getAdditionalServiceById($addOn['id']);
                                                                    $addOnTotalPrice = $additionalService->price * $addOn['qty'];
                                                                    $additionalTotal += Helpers::covertDefaultExchangeRate($addOnTotalPrice);
                                                                    $additionalCount++;
                                                                @endphp
                                                            @endif
                                                        @endforeach
                                                    @endisset
                                                    <div class="bill-summary">
                                                        <ul class="charge">
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.service_price') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($cartItem['service']['price']) }}</span>
                                                                @else
                                                                    <span>{{ Helpers::covertDefaultExchangeRate($cartItem['service']['price']) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            @if($cartItem['service']['discount'] > 0) 
                                                                @php
                                                                    $servicePrice = $cartItem['service']['price'];
                                                                    $discountPercent = $cartItem['service']['discount'];
                                                                    $discountAmount = ($servicePrice * $discountPercent) / 100;
                                                                @endphp
                                                                <li>
                                                                    <p>{{ __('frontend::static.bookings.service_discount') }}</p>
                                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                        <span style="color: red">
                                                                            -{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($discountAmount) }} ({{ $discountPercent }}%)
                                                                        </span>
                                                                    @else
                                                                        <span style="color:red;">
                                                                            -{{ Helpers::covertDefaultExchangeRate($discountAmount) }} {{ Helpers::getDefaultCurrencySymbol() }} ({{ $discountPercent }}%)
                                                                        </span>
                                                                    @endif
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.service_rate') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($cartItem['service']['service_rate']) }}</span>
                                                                @else
                                                                    <span>{{ Helpers::covertDefaultExchangeRate($cartItem['service']['service_rate']) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.per_servicemen_charge') }}</p>
                                                                <span>{{ $symbol }}{{ $perAmount }}</span>
                                                            </li>
                                                            <li>
                                                                <p>{{ $maxSelected }}{{ __('frontend::static.bookings.servicemen') }}({{ $symbol }} {{ $perAmount }}.00*{{ $maxSelected }})</p>
                                                                <span id="servicemenTotalDisplay">{{ $symbol }} {{ $total }}</span>
                                                            </li>
                                                            @if($isScheduledService)
                                                            <li id="scheduledServicesCountLi" style="display: none;">
                                                                <p>{{ __('frontend::static.bookings.total_scheduled_services') }}: <span id="scheduledServicesCountDisplay">0</span></p>
                                                                <span></span>
                                                            </li>
                                                            @endif
                                                            <li>
                                                                <p>{{ isset($cartItem['additional_services']) ? $additionalCount : 0 }}{{ __('frontend::static.bookings.add_ons') }}</p>
                                                                <span id="addonsTotalDisplay">{{ $symbol }}{{ isset($cartItem['additional_services']) ? number_format($additionalTotal, 2) : '0.00' }}</span>
                                                            </li>
                                                             {{-- ✅ Individual Add-on Details with Unit Price * Qty = Total --}}
                                                            @isset($cartItem['additional_services'])
                                                                @foreach ($cartItem['additional_services'] as $addOn)
                                                                    @if (isset($addOn['id']))
                                                                        @php
                                                                            $additionalService = Helpers::getAdditionalServiceById($addOn['id']);
                                                                            $unitPrice         = Helpers::covertDefaultExchangeRate($additionalService->price);
                                                                            $addOnTotalPrice   = $unitPrice * $addOn['qty'];
                                                                        @endphp
                                                                        <li class="ps-4"> {{-- indent to group under Add-ons --}}
                                                                            <p class="mb-0">
                                                                                {{ $additionalService->title }}
                                                                                ({{ __('static.service.qty') }} {{ $addOn['qty'] }})
                                                                            </p>
                                                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                                <span>
                                                                                    {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                    {{ $unitPrice }}
                                                                                    × {{ $addOn['qty'] }}
                                                                                    = {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                    {{ number_format($addOnTotalPrice, 2) }}
                                                                                </span>
                                                                            @else
                                                                                <span>
                                                                                    {{ $unitPrice }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                    × {{ $addOn['qty'] }}
                                                                                    = {{ number_format($addOnTotalPrice, 2) }}
                                                                                    {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                </span>
                                                                            @endif
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            @endisset
                                                            {{-- <li>
                                                                <p>{{ __('frontend::static.bookings.tax') }}</p>
                                                                <span>{{ __('frontend::static.bookings.cost_at_checkout') }}</span>
                                                            </li> --}}
                                                        </ul>
                                                        <ul class="total">
                                                            @php
                                                                // Base calculations (for one service instance)
                                                                $baseTotal = $total + $additionalTotal;
                                                                $baseServiceRate = Helpers::covertDefaultExchangeRate($cartItem['service']['service_rate']);
                                                                $baseServicemenTotal = $total;
                                                                $baseAddonsTotal = $additionalTotal;
                                                                
                                                                // For scheduled services, these will be multiplied by count in JavaScript
                                                                // For regular services, use base values
                                                                $grandTotal = $baseTotal;
                                                                
                                                                // Check if service has advance payment enabled
                                                                $serviceModel = $cartItem['service'] ?? null;
                                                                $isAdvanceEnabled = false;
                                                                $advancePercentage = null;
                                                                $advancePaymentAmount = $grandTotal;
                                                                $remainingPaymentAmount = 0;
                                                                
                                                                if ($serviceModel && isset($serviceModel->id)) {
                                                                    // $cartItem['service'] is already a Service model instance
                                                                    $service = $serviceModel;
                                                                    if ($service && $service->is_advance_payment_enabled && $service->advance_payment_percentage > 0) {
                                                                        $isAdvanceEnabled = true;
                                                                        $advancePercentage = $service->advance_payment_percentage;
                                                                        $advancePaymentAmount = round(($grandTotal * $advancePercentage) / 100, 2);
                                                                        $remainingPaymentAmount = round($grandTotal - $advancePaymentAmount, 2);
                                                                    }
                                                                }
                                                            @endphp
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.total_amount') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span id="grandTotalDisplay">{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($grandTotal, 2) }}</span>
                                                                @else
                                                                    <span id="grandTotalDisplay">{{ number_format($grandTotal, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            @if($isAdvanceEnabled)
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.advance_payment') }} ({{ $advancePercentage }}%):</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span id="advancePaymentDisplay">{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($advancePaymentAmount, 2) }} <small>({{ __('frontend::static.bookings.pay_now') }})</small></span>
                                                                @else
                                                                    <span id="advancePaymentDisplay">{{ number_format($advancePaymentAmount, 2) }} {{ Helpers::getDefaultCurrencySymbol() }} <small>({{ __('frontend::static.bookings.pay_now') }})</small></span>
                                                                @endif
                                                            </li>
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.remaining_payment') }}:</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span id="remainingPaymentDisplay">{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($remainingPaymentAmount, 2) }} <small>({{ __('frontend::static.bookings.pay_after_service') }})</small></span>
                                                                @else
                                                                    <span id="remainingPaymentDisplay">{{ number_format($remainingPaymentAmount, 2) }} {{ Helpers::getDefaultCurrencySymbol() }} <small>({{ __('frontend::static.bookings.pay_after_service') }})</small></span>
                                                                @endif
                                                            </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="dashed-border"></div>
                                                    <div class="danger-note">
                                                        <h3>
                                                            {{ __('frontend::static.bookings.cancellation_policy') }}
                                                        </h3>
                                                        <p class="m-0">
                                                            {{ __('frontend::static.bookings.cancellation_policy_note') }}
                                                        </p>
                                                    </div>

                                                    <div class="note">
                                                        <label>{{ __('frontend::static.bookings.disclaimer') }}</label>
                                                        <p>{{ __('frontend::static.bookings.disclaimer_note') }}</p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="wizard-footer service-booking-footer">
                                <button type="button" class="btn btn-outline" id="backbtn" style="display: none;">{{ __('frontend::static.bookings.back') }}</button>
                                <button type="button" class="btn btn-solid next-btn" id="nextbtn">{{ __('frontend::static.bookings.next_step') }}</button>
                                <button type="submit" class="btn btn-solid spinner-btn" id="confirmBookingBtn" style="display: none;">{{ __('frontend::static.bookings.confirm_booking') }}<span class="spinner-border spinner-border-sm" style="display: none;"></span></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-xxl-4 col-xl-5">
                    <div class="service-right-box">
                        @php
                            $cart = session('service_bookings', []);
                        @endphp
                        <div class="cart br-10">
                            <div class="cart-header">
                                <h3 class="mb-0 f-w-600">{{ __('frontend::static.bookings.your_cart') }}</h3>
                                <span>({{ count($cart) }} {{ __('frontend::static.bookings.item_added') }})</span>
                            </div>
                            <div class="cart-body">
                                @forelse($cart as $serviceItem)
                                    @php
                                        $service = Helpers::getServiceById($serviceItem['service_id']);
                                        $provider = Helpers::getProviderById($service?->user_id);
                                    @endphp
                                    <div class="cart-item">
                                        <div class="cart-heading">
                                            <div class="cart-title">
                                    @php
                                       $media = $service->getFirstMedia('images');
                                     @endphp

                                    @if($media)
                                         <img src="{{ $media->getUrl() }}" alt="{{ $service->name }}">
                                        @else
                                        <img src="{{ asset('frontend/images/default-service.jpg') }}" alt="{{ $service->name }}" class="img-fluid">
                                    @endif

                                                <div>
                                                    <p class="mb-1">{{ $provider?->name }}</p>
                                                    <div class="rate">
                                                        <img src="{{ asset('frontend/images/svg/star.svg') }}"
                                                            alt="star" class="img-fluid star">
                                                        <small>{{ $provider?->review_ratings }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart-detail">
                                            <div class="selected-service">
                                                <img src="{{ $service?->web_img_thumb_url }}"
                                                    alt="{{ $service?->title }}" class="mw-80">
                                                <div class="service-info">
                                                    <h3>{{ $service?->title }}</h3>
                                                    <div class="mt-1">
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <span class="price">{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</span>
                                                        @else
                                                            <span class="price">{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                        @endif
                                                        @if ($service?->discount)
                                                            <small class="discount">({{ $service?->discount }}% off)</small>
                                                        @endif
                                                    </div>
                                                    @if ($serviceItem['date_time'])
                                                        <ul class="date">
                                                            <li class="d-flex align-items-center gap-1">
                                                                <i class="iconsax" icon-name="calendar-1"></i>
                                                                <span>{{ \Carbon\Carbon::parse($serviceItem['date_time'])?->format('j F, Y') }}</span>
                                                            </li>
                                                            <li class="d-flex align-items-center gap-1">
                                                                <i class="iconsax" icon-name="clock"></i>
                                                                <span>{{ \Carbon\Carbon::parse($serviceItem['date_time'])?->format('g:i A') }}</span>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($cartItem['select_serviceman'] == 'user_choose')
                                                <ul class="date-time pt-3">
                                                    <li>
                                                        <span>{{ __('frontend::static.bookings.selected_servicemen') }}</span>
                                                        <small class="text-primary">{{ $cartItem['required_servicemen'] }} {{ __('frontend::static.bookings.servicemen') }}</small>
                                                    </li>
                                                </ul>
                                            @endif
                                            <div class="note m-0">
                                                <p class="mt-1">
                                                    {{ __('frontend::static.bookings.user_choose_note') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center">
                                        <div class="cart-img my-5">
                                            <img src="{{ asset('frontend/images/cart/1.png') }}" alt="no cart">
                                        </div>
                                        <div class="no-cart-found">
                                            <h3>{{ __('frontend::static.bookings.oops_nothing_added') }}</h3>
                                            <p class="text-light">
                                                {{ __('frontend::static.bookings.nothing_item_cart') }}</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Service List Section End -->

    <!-- Start Add Address Modal -->
    <div class="modal fade address-modal" id="locationModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('frontend.address.store') }}" id="addressForm" method="post">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addaddressModalLabel">{{ __('static.address.add') }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        @include('frontend.address.fields')
                    </div>
                    <div class="modal-footer pt-0">
                        <button type="button" class="btn btn-outline"
                            data-bs-dismiss="modal">{{ __('frontend::static.bookings.close') }}</button>
                        <button type="submit"
                            class="btn btn-solid submitBtn spinner-btn">{{ __('frontend::static.bookings.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @includeIf('frontend.inc.modal')

    <!-- Date Time modal (Hidden for Scheduled Services) -->
    @if(!($isScheduledService ?? false))
    <div class="modal fade date-time-modal" id="datetimeModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="datetimeModalLabel">{{ __('frontend::static.bookings.select_provider_date_time_slot') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="row g-3">
                        <div class="date-time-slot-box">
                            <input id="datetimepicker" type="date" class="form-control flatpicker-calender" placeholder="Select Date" />
                        </div>
                        <div class="col-12">
                            <div id="timeSlotsContainer" class="time-slot-main-box"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block">
                    {{-- <div class="inline-picker-btn w-100"> --}}
                    <button type="button" id="providerDateTimeBtn" class="btn btn-solid">{{ __('frontend::static.bookings.select_date_time') }}</button>
                    {{-- </div> --}}
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('js')
    <!-- Booking Form js -->
    <script src="{{ asset('frontend/js/form-wizard.js/booking-form.js') }}"></script>

    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker.js') }}"></script>
    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker-2.js') }}"></script>

    <!-- Flat-picker js -->
    <script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
    {{-- <script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script> --}}
    <script>
            const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_booking_days'] ?? 30 }};
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + maxBookingDays);
                
                function getMinTime() {
                    const now = new Date();
                    now.setHours(now.getHours() + 2);
                    return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                flatpickr("#datetimepicker", {
                    dateFormat: "d-m-Y",
                    minDate: "today",
                    maxDate: maxDate,
                    placeholder: "Select Date",
                    disableMobile: true,
                });

                flatpickr("#time-picker", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: getMinTime(),
                   placeholder: "Select Time",
                    disableMobile: true,
                });
                
                $(".datetime-local").each(function() {
                flatpickr(this, {
                    dateFormat: "Y-m-d",
                    minDate: new Date(),
                    maxDate: maxDate,
                    placeholder: "Select Date",
                    disableMobile: true,
                });
            });
        const providerTimeSlot = @json($providerTimeSlot ?? null);
        @if(!($isScheduledService ?? false) && isset($providerTimeSlot))
        if (document.getElementById('datetimepicker')) {
            document.getElementById('datetimepicker').addEventListener('change', function () {
            const [day, month, year] = this.value.split('-');
            const selectedDate = new Date(`${year}-${month}-${day}`);
            const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();
            const daySlot = providerTimeSlot.time_slots.find(slot => slot.day === dayName && slot.is_active === 1);
            const container = document.getElementById('timeSlotsContainer');
            container.innerHTML = '';

            if (daySlot && daySlot.slots.length > 0) {
                daySlot.slots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline-primary';
                    btn.textContent = time;
                    btn.setAttribute('type', 'button');
                    btn.setAttribute('data-time', time);
                    container.appendChild(btn);
                });
            } else {
                container.innerHTML = '<p class="no-data">No slots available for this day.</p>';
            }
            
            });
        }
        @endif

        $(document).on('click', '#timeSlotsContainer button', function () {
            $('#timeSlotsContainer button').removeClass('active');
            $(this).addClass('active');
            selectedSlotTime = $(this).data('time');
        });

        // On clicking select button
        $('#providerDateTimeBtn').click(function () {
            const selectedDateStr = $('#datetimepicker').val(); 
            const [day, month, year] = selectedDateStr.split('-');
            const selectedDateObj = new Date(`${year}-${month}-${day}`);

            if (!selectedDateObj || !selectedSlotTime) {
                alert("Please select a date and time slot.");
                return;
            }

            const formattedDate = selectedDateObj.toISOString().split('T')[0]; 
            const formattedTime = selectedSlotTime; 

            // Set values to fields
            $('.datetime-local, .datetime-local-1').val(formattedDate);
            $('.time-local, .time-local-1').val(formattedTime);
            $('#dateTimeInput').val(`${formattedDate} ${formattedTime}`);
            $('#datetimeModal').modal('hide');
        });

    </script>

    <script>        
        $(document).ready(function() {

            // Initialize form validation
            const isScheduledBooking = $('input[name="is_scheduled_booking"]').length > 0 && $('input[name="is_scheduled_booking"]').val() === '1';
            
            $("#serviceBookingForm").validate({
                ignore: [], // Don't ignore any fields, including hidden ones
                rules: {
                    'address_id': {
                        required: true
                    },
                    'select_date_time': {
                        required: function() {
                            return !isScheduledBooking;
                        }
                    },
                    'date_time': {
                        required: function() {
                            if (isScheduledBooking) return false;
                            return $('input[name="select_date_time"]:checked').val() === 'custom';
                        }
                    },
                    'schedule_start_date': {
                        required: function() {
                            return isScheduledBooking;
                        }
                    },
                    'schedule_end_date': {
                        required: function() {
                            return isScheduledBooking;
                        }
                    },
                    'schedule_time': {
                        required: function() {
                            return isScheduledBooking;
                        }
                    },
                    'booking_frequency': {
                        required: function() {
                            return isScheduledBooking;
                        }
                    },
                    'selected_weekdays[]': {
                        required: function() {
                            if (!isScheduledBooking) return false;
                            const frequency = $('input[name="booking_frequency"]:checked').val();
                            return frequency === 'daily';
                        }
                    },
                    'custom_dates': {
                        required: function() {
                            if (!isScheduledBooking) return false;
                            const frequency = $('input[name="booking_frequency"]:checked').val();
                            return frequency === 'custom';
                        }
                    },
                    'scheduled_dates_json': {
                        required: function() {
                            return isScheduledBooking;
                        }
                    },
                    'select_serviceman': {
                        required: function() {
                            return $('#selectServicemenDiv').is(':visible');
                        }
                    },
                    'serviceman_id': {
                        required: function() {
                            return $('#selectServicemenDiv').is(':visible');
                        }
                    },
                    'description': {
                        required: false
                    }, // Description is optional
                },
                messages: {
                    'address_id': {
                        required: "Please select an address"
                    },
                    'select_date_time': {
                        required: "Please choose a date and time"
                    },
                    'date_time': {
                        required: "Please select a date and time"
                    },
                    'schedule_start_date': {
                        required: "Please select a start date for scheduled service"
                    },
                    'schedule_end_date': {
                        required: "Please select an end date for scheduled service"
                    },
                    'schedule_time': {
                        required: "Please select a time for scheduled service"
                    },
                    'booking_frequency': {
                        required: "Please select a booking frequency"
                    },
                    'selected_weekdays[]': {
                        required: "Please select at least one weekday for daily booking"
                    },
                    'custom_dates': {
                        required: "Please select at least one date for custom booking"
                    },
                    'scheduled_dates_json': {
                        required: "Please configure your scheduled dates"
                    },
                    'select_serviceman': {
                        required: "Please select a serviceman"
                    },
                    'serviceman_id': {
                        required: "Please select a serviceman"
                    },
                },
                submitHandler: function(form) {
                    // Handle the form submission (if needed)
                    form.submit();
                }
            });
            // Function to update bill summary for scheduled services
            function updateBillSummaryForScheduled() {
                @if($isScheduledService ?? false)
                const scheduledCount = scheduledDatesList ? scheduledDatesList.length : 0;
                
                if (scheduledCount > 0) {
                    // Base values from PHP (for one service instance)
                    const baseServicemenTotal = {{ $total }};
                    const baseAddonsTotal = {{ $additionalTotal }};
                    const baseGrandTotal = {{ $baseTotal }};
                    const symbol = '{{ $symbol }}';
                    const symbolPosition = '{{ Helpers::getDefaultCurrency()->symbol_position->value }}';
                    const isAdvanceEnabled = {{ $isAdvanceEnabled ? 'true' : 'false' }};
                    const advancePercentage = {{ $advancePercentage ?? 0 }};
                    
                    // Calculate totals multiplied by scheduled services count
                    const servicemenTotal = baseServicemenTotal * scheduledCount;
                    const addonsTotal = baseAddonsTotal * scheduledCount;
                    const grandTotal = baseGrandTotal * scheduledCount;
                    
                    // Update servicemen total display
                    if (symbolPosition === 'left') {
                        $('#servicemenTotalDisplay').text(symbol + ' ' + servicemenTotal.toFixed(2));
                        $('#addonsTotalDisplay').text(symbol + addonsTotal.toFixed(2));
                        $('#grandTotalDisplay').text(symbol + grandTotal.toFixed(2));
                    } else {
                        $('#servicemenTotalDisplay').text(servicemenTotal.toFixed(2) + ' ' + symbol);
                        $('#addonsTotalDisplay').text(addonsTotal.toFixed(2) + ' ' + symbol);
                        $('#grandTotalDisplay').text(grandTotal.toFixed(2) + ' ' + symbol);
                    }
                    
                    // Show and update scheduled services count line
                    $('#scheduledServicesCountLi').show();
                    $('#scheduledServicesCountDisplay').text(scheduledCount);
                    
                    // Update advance payment if enabled
                    if (isAdvanceEnabled && advancePercentage > 0) {
                        const advancePaymentAmount = (grandTotal * advancePercentage) / 100;
                        const remainingPaymentAmount = grandTotal - advancePaymentAmount;
                        
                        if (symbolPosition === 'left') {
                            $('#advancePaymentDisplay').html(symbol + advancePaymentAmount.toFixed(2) + ' <small>({{ __('frontend::static.bookings.pay_now') }})</small>');
                            $('#remainingPaymentDisplay').html(symbol + remainingPaymentAmount.toFixed(2) + ' <small>({{ __('frontend::static.bookings.pay_after_service') }})</small>');
                        } else {
                            $('#advancePaymentDisplay').html(advancePaymentAmount.toFixed(2) + ' ' + symbol + ' <small>({{ __('frontend::static.bookings.pay_now') }})</small>');
                            $('#remainingPaymentDisplay').html(remainingPaymentAmount.toFixed(2) + ' ' + symbol + ' <small>({{ __('frontend::static.bookings.pay_after_service') }})</small>');
                        }
                    }
                } else {
                    // Hide scheduled services count line if no services
                    $('#scheduledServicesCountLi').hide();
                }
                @endif
            }
            
            // Handle Next button click
            $('#nextbtn').on('click', function() {
                @if($isScheduledService ?? false)
                    // For scheduled services, update summary in step 2
                    if (scheduledDatesList && scheduledDatesList.length > 0) {
                        const frequency = $('input[name="booking_frequency"]:checked').val();
                        const startDate = $('#schedule_start_date').val();
                        const endDate = $('#schedule_end_date').val();
                        const time = $('#schedule_time_picker').val();
                        
                        let summaryText = `{{ __('frontend::static.bookings.booking_frequency') }}: ${frequency.charAt(0).toUpperCase() + frequency.slice(1)}<br>`;
                        summaryText += `{{ __('frontend::static.bookings.from') }}: ${startDate} {{ __('frontend::static.bookings.to') }} ${endDate}<br>`;
                        summaryText += `{{ __('frontend::static.bookings.time') }}: ${window.formatTime ? window.formatTime(time) : time}<br>`;
                        summaryText += `{{ __('frontend::static.bookings.total_services') }}: ${scheduledDatesList.length}`;
                        
                        $('#scheduledSummaryText').html(summaryText);
                        
                        // Update bill summary
                        updateBillSummaryForScheduled();
                    }
                @else
                    let selectedValue = $('input[name="select_date_time_2"]:checked').val();
                    $('input[name="select_date_time"][value="' + selectedValue + '"]').prop('checked', true).trigger('change');
                @endif

                var currentStepDiv = $(".step-content:visible");
                var currentStep = currentStepDiv.data('step');

                if ($("#serviceBookingForm").valid()) {
                    var nextStep = currentStep + 1;
                    if (nextStep <= totalSteps) {
                        updateWizard(nextStep);
                    }
                } else {
                    console.log("Validation failed. Stay on the current step.");
                }
            });

            // Handle Back button click
            $('#backbtn').on('click', function() {
                let selectedValue = $('input[name="select_date_time"]:checked').val();
                $('input[name="select_date_time_2"][value="' + selectedValue + '"]').prop('checked', true).trigger('change');
                var currentStepDiv = $(".step-content:visible");
                var currentStep = currentStepDiv.data('step');
                var prevStep = currentStep - 1;
                if (prevStep >= 1) {
                    updateWizard(prevStep);
                }
            });

            // Handle the wizard step changes
            const steps = $('.step-content'),
                headings = $('.step-heading');
            let currentStep = 1,
                totalSteps = steps.length;

            function updateWizard(step) {
                steps.hide().filter(`[data-step="${step}"]`).show();
                headings.removeClass('editing').filter(`[data-step="${step}"]`).addClass('editing');
                $('#backbtn').toggle(step > 1);
                $('#nextbtn').toggle(step < totalSteps);
                $('#confirmBookingBtn').toggle(step === totalSteps);
                
                // Update bill summary for scheduled services when moving to step 2
                @if($isScheduledService ?? false)
                if (step === 2 && typeof updateBillSummaryForScheduled === 'function') {
                    setTimeout(function() {
                        updateBillSummaryForScheduled();
                    }, 100);
                }
                @endif
            }

            // Initialize the first step
            updateWizard(currentStep);

            // Make sure only a limited number of servicemen can be selected
            const maxSelected = <?php echo $maxSelected; ?>;
            $('input[name="servicemen-list"]').change(function() {
                const checkedCount = $('input[name="servicemen-list"]:checked').length;
                if (checkedCount > maxSelected) {
                    $(this).prop('checked', false);
                    alert(`You can only select up to ${maxSelected} servicemen.`);
                }
            });

            // Synchronize input values between related fields
            function syncInput(sourceSelector, targetSelector) {
                $(sourceSelector).on('change', function() {
                    var value = $(this).val();
                    $(targetSelector).val(value);
                    updateDateTimeInput();
                });
            }

            syncInput('.datetime-local', '.datetime-local-1');
            syncInput('.time-local', '.time-local-1');
            syncInput('.datetime-local-1', '.datetime-local');
            syncInput('.time-local-1', '.time-local');

            // Update the date and time input
            function updateDateTimeInput() {
                var date = $('.datetime-local-1').val();
                var time = $('.time-local-1').val();
                if (date && time) {
                    $('#dateTimeInput').val(date + ' ' + time);
                }
            }

            // ========== SCHEDULED BOOKING FUNCTIONALITY ==========
            @if($isScheduledService ?? false)
            const isScheduledService = true;
            let scheduledDatesList = [];

            // Initialize date pickers for scheduled booking
            const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_booking_days'] ?? 365 }};
            const today = new Date();
            const maxScheduledDate = new Date();
            maxScheduledDate.setDate(today.getDate() + maxBookingDays);

            // Start and End Date Pickers
            flatpickr("#schedule_start_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: maxScheduledDate,
                placeholder: "{{ __('frontend::static.bookings.start_date') }}",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        flatpickr("#schedule_end_date", {
                            minDate: selectedDates[0],
                            maxDate: maxScheduledDate
                        });
                        generateScheduledDates();
                    }
                }
            });

            flatpickr("#schedule_end_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: maxScheduledDate,
                placeholder: "{{ __('frontend::static.bookings.end_date') }}",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    generateScheduledDates();
                }
            });

            // Custom Dates Picker (Multiple dates)
            flatpickr("#custom_dates_picker", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: maxScheduledDate,
                placeholder: "{{ __('frontend::static.bookings.pick_multiple_dates') }}",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        scheduledDatesList = selectedDates.map(date => {
                            const time = $('#schedule_time_picker').val() || '09:00';
                            return {
                                date: date.toISOString().split('T')[0],
                                time: time
                            };
                        });
                        updateScheduledListPreview();
                    }
                }
            });

            // Time Picker for Scheduled Services
            flatpickr("#schedule_time_picker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: false,
                placeholder: "{{ __('frontend::static.bookings.select_time') }}",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (scheduledDatesList.length > 0) {
                        scheduledDatesList.forEach(item => {
                            item.time = dateStr;
                        });
                        updateScheduledListPreview();
                    }
                }
            });

            // Handle Booking Frequency Changes
            $('input[name="booking_frequency"]').on('change', function() {
                const frequency = $(this).val();
                const weekdaysDiv = $('#weekdaysSelection');
                const customDatesDiv = $('#customDateSelection');

                // Reset visibility
                weekdaysDiv.hide();
                customDatesDiv.hide();
                scheduledDatesList = [];
                updateScheduledListPreview();

                if (frequency === 'daily') {
                    weekdaysDiv.show();
                } else if (frequency === 'custom') {
                    customDatesDiv.show();
                }

                generateScheduledDates();
            });

            // Handle Weekdays Selection
            $('input[name="selected_weekdays[]"]').on('change', function() {
                generateScheduledDates();
            });

            // Generate Scheduled Dates based on frequency and settings
            function generateScheduledDates() {
                const frequency = $('input[name="booking_frequency"]:checked').val();
                const startDate = $('#schedule_start_date').val();
                const endDate = $('#schedule_end_date').val();
                const time = $('#schedule_time_picker').val() || '09:00';

                if (!startDate || !endDate) {
                    scheduledDatesList = [];
                    updateScheduledListPreview();
                    return;
                }

                const start = new Date(startDate);
                const end = new Date(endDate);
                scheduledDatesList = [];

                if (frequency === 'custom') {
                    // Custom dates are handled by the custom_dates_picker onChange
                    return;
                }

                const selectedWeekdays = [];
                if (frequency === 'daily') {
                    $('input[name="selected_weekdays[]"]:checked').each(function() {
                        selectedWeekdays.push($(this).val());
                    });
                }

                let currentDate = new Date(start);
                const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                while (currentDate <= end) {
                    const dayName = dayNames[currentDate.getDay()];
                    let shouldInclude = false;

                    switch (frequency) {
                        case 'daily':
                            shouldInclude = selectedWeekdays.length === 0 || selectedWeekdays.includes(dayName);
                            break;
                        case 'weekly':
                            // Same day of week each week
                            if (currentDate.getDay() === start.getDay()) {
                                shouldInclude = true;
                            }
                            break;
                        case 'monthly':
                            // Same date each month
                            if (currentDate.getDate() === start.getDate()) {
                                shouldInclude = true;
                            }
                            break;
                        case 'yearly':
                            // Same date each year
                            if (currentDate.getDate() === start.getDate() && currentDate.getMonth() === start.getMonth()) {
                                shouldInclude = true;
                            }
                            break;
                    }

                    if (shouldInclude) {
                        scheduledDatesList.push({
                            date: currentDate.toISOString().split('T')[0],
                            time: time
                        });
                    }

                    // Move to next day
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                updateScheduledListPreview();
            }

            // Update Scheduled List Preview
            function updateScheduledListPreview() {
                const container = $('#scheduledDatesContainer');
                const previewDiv = $('#scheduledListPreview');
                const countSpan = $('#totalScheduledCount');

                if (scheduledDatesList.length === 0) {
                    container.html('<p class="text-muted mb-0">{{ __('frontend::static.bookings.no_dates_selected') }}</p>');
                    previewDiv.hide();
                    $('#scheduledDatesJson').val('');
                    return;
                }

                countSpan.text(scheduledDatesList.length);
                previewDiv.show();

                let html = '';
                scheduledDatesList.forEach((item, index) => {
                    const dateObj = new Date(item.date);
                    const formattedDate = dateObj.toLocaleDateString('en-US', { 
                        day: 'numeric', 
                        month: 'short', 
                        year: 'numeric' 
                    });
                    const formattedTime = formatTime(item.time);
                    
                    html += `
                        <div class="d-flex align-items-center border-bottom pb-2 mb-2 scheduled-item" data-index="${index}">
                            <div>
                                <span class="d-block fw-semibold">${formattedDate}</span>
                                <span class="text-muted small">${formattedTime}</span>
                            </div>
                        </div>
                    `;
                });

                container.html(html);

                // Store as JSON
                $('#scheduledDatesJson').val(JSON.stringify(scheduledDatesList));
                
                // Update scheduled services count
                $('#scheduledServicesCount').val(scheduledDatesList.length);
                
                // Update bill summary if we're on step 2 or if scheduled dates changed
                if (typeof updateBillSummaryForScheduled === 'function') {
                    updateBillSummaryForScheduled();
                }
            }

            // Format time helper (global for use in step 2)
            window.formatTime = function(time24) {
                if (!time24) return '';
                const [hours, minutes] = time24.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes} ${ampm}`;
            };

            // Watch for date range and time changes
            $('#schedule_start_date, #schedule_end_date, #schedule_time_picker').on('change', function() {
                generateScheduledDates();
                // Update bill summary when dates change
                setTimeout(function() {
                    updateBillSummaryForScheduled();
                }, 100);
            });

            // Initialize on page load
            if ($('input[name="booking_frequency"]:checked').val() === 'daily') {
                $('#weekdaysSelection').show();
            }
            
            // Make updateBillSummaryForScheduled available globally
            window.updateBillSummaryForScheduled = updateBillSummaryForScheduled;
            @endif
            // ========== END SCHEDULED BOOKING FUNCTIONALITY ==========
        });
    </script>
@endpush




