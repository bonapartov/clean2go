@extends('backend.layouts.master')
@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('static.provider.providers'))
@section('content')
    @use('App\Enums\PaymentStatus')
    @use('App\Helpers\Helpers')

    @php
        $servicemen= Helpers:: getAllServicemen() ?? [];
        $services = Helpers::getAllServices() ?? []; 
    @endphp
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.provider.providers') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                             {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary import-redirect-btn" data-url="{{ route('backend.import-export.index', 'provider') }}">
                            {{ __('static.import.import') }} <i class="ri-download-2-line"></i>
                        </button>
                        @can('backend.provider.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.provider.create') }}"
                                    class="btn">{{ __('static.provider.create') }}</a>
                            </div>
                        @endcan
                        <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                            style="display: none;" data-url="{{ route('backend.delete.providers') }}">
                            <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="provider-table">
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
                    <h5 class="modal-title" id="myModalLabel">Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
                </div>
                <div class="modal-body">
                    <form id="providerFilterForm">
                        <div class="row mb-3 g-sm-4 g-3">
                            <div class="col-6">
                                <label class="" for="user_id">{{ __('static.daterange') }}</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                                <span id="dateRangeError" class="text-danger" style="display:none;"></span>
                            </div>
                   
                            <div class="col-6">
                                <label class="" for="user_id">{{ __('static.services') }}</label>
                                <select id="filterService" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.notification.select_service') }}" multiple>
                                    <option class="select-placeholder" value=""></option>                            
                                    @foreach ($services as $key => $option ) 
                                        <option value="{{ $option->id }}" image="{{ $option->getFirstMedia('thumbnail')?->getUrl() }}">
                                            {{ $option->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.service.servicemen') }}</label>
                                <select id="filterServicemen" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.wallet.select_consumers') }}" multiple>
                                <option class="select-placeholder" value=""></option>
                                @foreach ($servicemen as $key => $option )
                                    <option value="{{ $option->id }}" sub-title="{{ $option->email }}" image="{{ $option->getFirstMedia('image')?->getUrl() }}">
                                        {{ $option->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.type') }}</label>
                                <select id="provider_type" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.provider.select_type') }}">
                                <option class="select-placeholder" value=""></option>
                                @foreach (['company' => 'Company', 'freelancer' => 'Freelancer'] as $key => $option)
                                    <option value="{{ $key }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="" for="filterStatus">{{ __('static.status') }}</label>
                                <select name="filterStatus" id="filterStatus" class="select-2 form-control" data-placeholder="{{ __('static.provider-document.select_status') }}">
                                        <option class="select-placeholder" value=""></option>      
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer d-flex px-0 pb-0">
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

    <form id="exportForm" method="GET" action="{{ route('backend.download') }}">
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
                
                    if (urlParams.has('services')) {
                        $('#filterService').val(urlParams.get('services').split(',')).trigger('change');
                    }
                
                    if (urlParams.has('servicemen')) {
                        $('#filterServicemen').val(urlParams.get('servicemen').split(',')).trigger('change');
                    }
                    
                    if (urlParams.has('status')) {
                        $('#filterStatus').val(urlParams.get('status').split(',')).trigger('change');
                    }

                    if (urlParams.has('types')) {
                        $('#provider_type').val(urlParams.get('types').split(',')).trigger('change');
                    }
                }

                function initDateRangePicker()
                {
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
                    let services    = $('#filterService').val();
                    let servicemen  = $('#filterServicemen').val();
                    let types       = $('#provider_type').val();
                    let status       = $('#filterStatus').val();

                    if (dateRange) {
                        const dates = dateRange.split(' to ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    }                    
                    if (services && services.length) params.services = services.join(',');
                    if (servicemen && servicemen.length) params.servicemen = servicemen.join(',');
                    if (status && status.length) params.status = status;
                    if (types && types.length) params.types = types;

                    const newUrl = new URL(window.location.href);
                    newUrl.search = new URLSearchParams(params).toString();
                    history.replaceState(null, '', newUrl.toString());

                    location.reload();
                    $('#myModal').modal('hide');
                });

                $('#reset').click(function () {
                    $('#providerFilterForm').trigger('reset');
                    $('.select-2').val(null).trigger('change');
                    $('#dateRange').val('');
                    $('#dateRangeError').hide();

                    const url = window.location.origin + window.location.pathname;
                    window.location.href = url;
                });
            });
        })(jQuery);
    </script>

    <script>
        $('#exportForm').on('submit', function(e) {

            let dateRange = $('#dateRange').val();
            let services    = $('#filterService').val();
            let servicemen  = $('#filterServicemen').val();
            let types       = $('#provider_type').val();
            let status       = $('#filterStatus').val();

            $(this).find('input[name], select[name]').not('#exportFormat').remove();

            if(dateRange){
                const dates = dateRange.split(' to ');
                $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
            }
            if(services?.length) $(this).append(`<input type="hidden" name="services" value="${services.join(',')}">`);
            if(servicemen?.length) $(this).append(`<input type="hidden" name="servicemen" value="${servicemen.join(',')}">`);
            if(status?.length) $(this).append(`<input type="hidden" name="status" value="${status}">`);
            if(types?.length) $(this).append(`<input type="hidden" name="types" value="${types}">`);

            setTimeout(() => {
                $('.spinner-btn').prop('disabled', false);
                $('.spinner-btn .spinner').remove();
                var modal = bootstrap.Modal.getInstance($('#reportExportModal')[0]);
                modal.hide();
            }, 3000);
        });
    </script>

    <script>
        $(document).on('click', '.import-redirect-btn', function() {
            var url = $(this).data('url');
            if (url) {
                window.open(url, '_blank');
            }
        });
    </script>

@endpush
