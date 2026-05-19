@extends('backend.layouts.master')
@use('App\Models\Zone')
@use('app\Helpers\Helpers')
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('static.advertisement.advertisements'))

@section('content')
    @php
        $zones = Zone::where('status', true)->pluck('name', 'id');
        $providers = Helpers::getProviders()->get();

    @endphp
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header flex-align-center">
                    <h5>{{ __('static.advertisement.advertisements') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                            {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                        @can('backend.advertisement.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.advertisement.create') }}"
                                    class="btn">{{ __('static.advertisement.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.advertisement.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.advertisements') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            @endcan
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="advertisement-table">
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
                    <form id="serviceFilterForm">
                        <div class="row g-sm-4 g-3 mb-3">
                            <div class="col-6">
                                <label class="" for="user_id">{{ __('static.daterange') }}</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                                <span id="dateRangeError" class="text-danger" style="display:none;"></span>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.home_pages.providers') }}</label>
                                <select id="filterProvider" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.home_pages.select_providers') }}" multiple>
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($providers as $key => $option)
                                        <option value="{{ $option->id }}" sub-title="{{ $option->email }}" image="{{ $option->getFirstMedia('image')?->getUrl() }}" data-type="{{ $option->type }}">
                                            {{ $option->name }}
                                        </option>
                                     @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.advertisement.type') }}</label>
                                <select id="filterType" class="select-2 form-control user-dropdown Dropdown " data-placeholder="{{ __('static.advertisement.select_type') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($advertisementType as $key => $option)
                                        <option value="{{ $key }}">
                                            {{ $option }}
                                        </option>
                                     @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.advertisement.screen') }}</label>
                                <select id="advertisementScreen" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.advertisement.select_screen') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($advertisementScreen as $key => $option)
                                        <option value="{{ $key }}">
                                            {{ $option }}
                                        </option>
                                     @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.zone.zones') }}</label>
                                <select id="zone" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.zone.select-zone') }}" multiple>
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($zones as $key => $option)
                                        <option value="{{ $key}}">
                                            {{ $option }}
                                        </option>
                                     @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="" for="filterStatus">{{ __('static.status') }}</label>
                                <select name="filterStatus" id="filterStatus" class="select-2 form-control"
                                    data-placeholder="{{ __('static.provider-document.select_status') }}">
                                    <option class="select-placeholder" value=""></option>
                                     @foreach ($status as $key => $option)
                                        <option value="{{ $key}}">
                                            {{ $option}}
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

    <form id="exportForm" method="GET" action="{{ route('backend.advertisement.export') }}">
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
                        <button type="button" class="btn btn-outline-primary"
                            data-bs-dismiss="modal">{{ __('static.modal.close') }}</button>
                        <button type="submit" class="btn btn-primary spinner-btn"
                            id="submitBtn">{{ __('static.modal.export') }}</button>
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

                var table = $('#advertisement-table').DataTable();

                let urlParams = new URLSearchParams(window.location.search);

                if (urlParams.toString()) {

                    if (urlParams.has('start_date') && urlParams.has('end_date')) {
                        $('#dateRange').val(urlParams.get('start_date') + " to " + urlParams.get('end_date'));
                    }

                    if (urlParams.has('providers')) {
                        $('#filterProvider').val(urlParams.get('providers')).trigger('change');
                    }

                    if (urlParams.has('type')) {
                        $('#filterType').val(urlParams.get('type')).trigger('change');
                    }

                    if (urlParams.has('advertisementScreen')) {
                        $('#advertisementScreen').val(urlParams.get('advertisementScreen')).trigger('change');
                    }

                    if (urlParams.has('zones')) {
                        $('#zone').val(urlParams.get('zones').split(',')).trigger('change');
                    }

                    if (urlParams.has('status')) {
                        $('#filterStatus').val(urlParams.get('status').split(',')).trigger('change');
                    }
                }

                function initDateRangePicker() {
                    flatpickr("#dateRange", {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        onChange: function(selectedDates, dateStr) {
                            if (!dateStr) {
                                $('#dateRangeError').hide();
                            } else if (dateStr.split(' to ').length < 2) {
                                $('#dateRangeError').text(
                                    'Both start date and end date are required').show();
                            } else {
                                $('#dateRangeError').hide();
                            }
                        }
                    });
                }
                initDateRangePicker();

                $('#myModal').on('hidden.bs.modal', function() {
                    if ($('#dateRange').val() === '') {
                        $('#dateRangeError').hide();
                    }
                });

                $('#applyFilter').click(function() {
                    $('#myModal').modal('show');
                });

                $('#applyFinalFilter').click(function() {
                    let params = {};

                    let dateRange = $('#dateRange').val();
                    let status = $('#filterStatus').val();
                    let providers = $('#filterProvider').val();
                    let type = $('#filterType').val();
                    let zone = $('#zone').val();
                    let advertisementScreen = $('#advertisementScreen').val();

                    if (dateRange) {
                        const dates = dateRange.split(' to ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    }
                    if (type) params.type = type;
                    if (zone && zone.length) params.zones = zone.join(',');
                    if (providers && providers.length) params.providers = providers.join(',');
                    if (status && status.length) params.status = status;
                    if (advertisementScreen) params.advertisementScreen = advertisementScreen;

                    const newUrl = new URL(window.location.href);
                    newUrl.search = new URLSearchParams(params).toString();
                    history.replaceState(null, '', newUrl.toString());

                    location.reload();
                    $('#myModal').modal('hide');
                });

                $('#reset').click(function() {
                    $('#serviceFilterForm').trigger('reset');;
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
            let status = $('#filterStatus').val();
            let providers = $('#filterProvider').val();
            let type = $('#filterType').val();
            let zone = $('#zone').val();
            let advertisementScreen = $('#advertisementScreen').val();

            $(this).find('input[name], select[name]').not('#exportFormat').remove();

            if (dateRange) {
                const dates = dateRange.split(' to ');
                $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
            }
            if (type?.length) $(this).append(`<input type="hidden" name="type" value="${type}">`);
            if (zone?.length)$(this).append(`<input type="hidden" name="zones" value="${zone.join(',')}">`);            
            if(providers?.length) $(this).append(`<input type="hidden" name="providers" value="${providers.join(',')}">`);
            if (advertisementScreen) $(this).append(`<input type="hidden" name="advertisementScreen" value="${advertisementScreen}">`);
            if (status?.length) $(this).append(`<input type="hidden" name="status" value="${status}">`);

            setTimeout(() => {
                $('.spinner-btn').prop('disabled', false);
                $('.spinner-btn .spinner').remove();
                var modal = bootstrap.Modal.getInstance($('#reportExportModal')[0]);
                modal.hide();
            }, 3000);
        });
    </script>
@endpush