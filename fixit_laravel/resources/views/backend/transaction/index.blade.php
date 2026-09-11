@extends('backend.layouts.master')
@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('static.transaction.transactions'))

@section('content')
    @use('App\Enums\PaymentStatus')
    @use('App\Models\BookingStatus')
    @use('App\Helpers\Helpers')

    @php
        $statuses = BookingStatus::whereNull('deleted_at')->where('status', true)->get();
        $paymentStatuses = PaymentStatus::PAYMENT_STATUS;
        $PaymentMethods = Helpers::getActivePaymentMethods() ?? [];

    @endphp

    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.transaction.transactions') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                            {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="tax-table">
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">{{ __('static.report.filter') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
                </div>
                <div class="modal-body">
                    <form id="transactionFilterForm">
                        <div class="row mb-3 g-sm-4 g-3">
                            <div class="col-6">
                                <label class="" for="user_id">{{ __('static.daterange') }}</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="{{ __('static.common.select_date_range') }}">
                                <span id="dateRangeError" class="text-danger" style="display:none;"></span>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.zone.payment_methods') }}</label>
                                <select id="filterPaymentMethod" class="select-2 form-control"  data-placeholder="{{ __('static.zone.select_payment_methods') }}" multiple>
                                    <option class="select-placeholder" value=""></option>
                                @foreach ($PaymentMethods as $PaymentMethod)
                                        <option value="{{ $PaymentMethod['slug'] }}">
                                            {{ $PaymentMethod['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.booking.payment_status') }}</label>
                                <select id="filterPaymentStatus" class="select-2 form-control"  data-placeholder="{{ __('static.report.select_payment_status') }}" multiple>
                                    <option class="select-placeholder" value=""></option>
                                @foreach ($paymentStatuses as $paymentStatus)
                                        <option value="{{ $paymentStatus }}">
                                            {{ $paymentStatus }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.type') }}</label>
                                <select id="filterType" class="select-2 form-control" data-placeholder="{{ __('static.provider.select_type') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach (['booking' => 'Booking', 'wallet' => 'Wallet'] as $key => $option)
                                    <option value="{{ $key }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                            
                        </div>
                        <div class="modal-footer d-flex pb-0 px-0">
                            <button type="button" class="btn btn-secondary" id="reset">
                                <i class="fa fa-undo"></i> {{ __('static.reset') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="applyFinalFilter">
                                <i class="fa fa-filter"></i> {{ __('static.booking.apply_filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="exportForm" method="GET" action="{{ route('backend.transaction.data.export') }}">
        <div class="modal fade export-modal confirmation-modal" id="reportExportModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('static.modal.export_data') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="modal-body export-data">
                        <div class="main-img">
                            <img src="{{ asset('admin/images/svg/export.svg') }}" />
                        </div>
                        <div class="form-group">
                            <label for="exportFormat">{{ __('static.modal.select_export_format') }}</label>
                            <select id="exportFormat" name="format" class="form-select">
                                <option value="csv">{{ __('static.modal.csv') }}</option>
                                <option value="excel">{{ __('static.modal.excel') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">{{ __('static.modal.close') }}</button>
                        <button type="submit" class="btn btn-primary spinner-btn" id="submitBtn">{{ __('static.modal.export') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="{{ asset('admin/js/select2-custom.js') }}"></script>
    <script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
    {!! $dataTable->scripts() !!}
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                var table = $('#dataTableBuilder').DataTable();
                
                let urlParams = new URLSearchParams(window.location.search);

                if (urlParams.toString()) {
                
                    if (urlParams.has('start_date') && urlParams.has('end_date')) {
                        $('#dateRange').val(urlParams.get('start_date') + " to " + urlParams.get('end_date'));
                    }
                
                    if (urlParams.has('types')) {
                        $('#filterType').val(urlParams.get('types').split(',')).trigger('change');
                    }
                
                    if (urlParams.has('payment_statuses')) {
                        $('#filterPaymentStatus').val(urlParams.get('payment_statuses').split(',')).trigger('change');
                    }
                
                    if (urlParams.has('payment_methods')) {
                        $('#filterPaymentMethod').val(urlParams.get('payment_methods').split(',')).trigger('change');
                    }
                }

                function initDateRangePicker(){
                    flatpickr("#dateRange", {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        onChange: function(selectedDates, dateStr) {
                            if(!dateStr){
                                $('#dateRangeError').hide();
                            }
                            else if(dateStr.split(' to ').length < 2) {
                                $('#dateRangeError').text('Both start date and end date are required').show();
                            } else {
                                $('#dateRangeError').hide();
                            }
                        }
                    });
                } 
                initDateRangePicker();

                $('#myModal').on('hidden.bs.modal', function () {
                    if ($('#dateRange').val() === '') {
                        $('#dateRangeError').hide();
                    }
                });

                $('#applyFilter').click(function () {
                    $('#myModal').modal('show');
                });

                $('#applyFinalFilter').click(function () {
                    let params = {};

                    let dateRange = $('#dateRange').val();
                    let types = $('#filterType').val();
                    let paymentStatuses = $('#filterPaymentStatus').val();
                    let paymentMethods = $('#filterPaymentMethod').val();

                    if (dateRange) {
                        const dates = dateRange.split(' to ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    }                    
                    if (paymentStatuses && paymentStatuses.length) params.payment_statuses = paymentStatuses.join(',');
                    if (paymentMethods && paymentMethods.length) params.payment_methods = paymentMethods.join(',');
                    if (types && types.length) params.types = types;

                    const newUrl = new URL(window.location.href);
                    newUrl.search = new URLSearchParams(params).toString();
                    history.replaceState(null, '', newUrl.toString());

                    location.reload();
                    $('#myModal').modal('hide');
                });

                $('#reset').click(function () {
                    $('#transactionFilterForm').trigger('reset'); ;
                    $('.select-2').val(null).trigger('change');
                    $('#dateRange').val('');

                    const url = window.location.origin + window.location.pathname;
                    window.location.href = url;
                });

            });
        })(jQuery);
    </script>
    
    <script>
        $('#exportForm').on('submit', function(e) {

            let dateRange = $('#dateRange').val();
            let types = $('#filterType').val();
            let paymentStatuses = $('#filterPaymentStatus').val();
            let paymentMethods = $('#filterPaymentMethod').val();

            $(this).find('input[name], select[name]').not('#exportFormat').remove();

            if(dateRange){
                const dates = dateRange.split(' to ');
                $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
            }
            if(paymentStatuses?.length) $(this).append(`<input type="hidden" name="payment_statuses" value="${paymentStatuses.join(',')}">`);
            if(paymentMethods?.length) $(this).append(`<input type="hidden" name="payment_methods" value="${paymentMethods.join(',')}">`);
            if(types?.length) $(this).append(`<input type="hidden" name="types" value="${types}">`);

            setTimeout(() => {
                $('.spinner-btn').prop('disabled', false);
                $('.spinner-btn .spinner').remove();
                var modal = bootstrap.Modal.getInstance($('#reportExportModal')[0]);
                modal.hide();
            }, 3000);
        });
    </script>
@endpush
