@extends('backend.layouts.master')
@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('static.serviceman.servicemen'))

@section('content')
    @use('App\Helpers\Helpers')

    @php
        $providers = Helpers::getAllProviders() ?? []; 
    @endphp
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.serviceman.servicemen') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                            {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary import-redirect-btn" data-url="{{ route('backend.import-export.index', 'serviceman') }}">
                            {{ __('static.import.import') }} <i class="ri-download-2-line"></i>
                        </button>
                        @can('backend.serviceman.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.serviceman.create') }}"
                                    class="btn">{{ __('static.serviceman.create') }}
                                </a>
                            </div>
                        @endcan
                        <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                            style="display: none;" data-url="{{ route('backend.delete.users') }}">
                            <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="serviceman-table">
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
                </div>
                <div class="modal-body">
                    <form id="servicemanFilterForm">
                        <div class="mb-3">
                            <label class="col-md-2" for="user_id">{{ __('static.daterange') }}</label>
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                            <span id="dateRangeError" class="text-danger" style="display:none;"></span>
                     
                            
                            <label class="col-md-2">{{ __('static.home_pages.providers') }}</label>
                            <select id="filterProvider" class="select-2 form-control user-dropdown Dropdown" data-placeholder="{{ __('static.home_pages.select_providers') }}" multiple>
                                <option class="select-placeholder" value=""></option>
                                @foreach ($providers as $key => $option )
                                <option value="{{ $option->id }}" sub-title="{{ $option->email }}" image="{{ $option->getFirstMedia('image')?->getUrl() }}">
                                    {{ $option->name }}
                                </option>
                                @endforeach
                            </select>

                            <label class="col-md-2" for="filterStatus">{{ __('static.status') }}</label>
                            <select name="filterStatus" id="filterStatus" class="select-2 form-control" data-placeholder="{{ __('static.provider-document.select_status') }}">
                                    <option class="select-placeholder" value=""></option>      
                                    <option value="1">Active</option>
                                    <option value="0">Deactive</option>
                            </select>

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

    <form id="exportForm" method="GET" action="{{ route('backend.serviceman.export.data') }}">
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
        $(document).ready(function() {
            $('.toggle-status').click(function() {
                var toggleId = $(this).data('id');
                $('#ConfirmationModal' + toggleId).modal('show');
                return false;
            });
        });
    </script>

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
                
                    if (urlParams.has('providers')) {
                        $('#filterProvider').val(urlParams.get('providers').split(',')).trigger('change');
                    }
                    
                    if (urlParams.has('status')) {
                        $('#filterStatus').val(urlParams.get('status').split(',')).trigger('change');
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
                    let providers = $('#filterProvider').val();
                    let status = $('#filterStatus').val();

                    if (dateRange) {
                        const dates = dateRange.split(' to ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    }                    
                    if (providers && providers.length) params.providers = providers.join(',');
                    if (status && status.length) params.status = status;

                    const newUrl = new URL(window.location.href);
                    newUrl.search = new URLSearchParams(params).toString();
                    history.replaceState(null, '', newUrl.toString());

                    location.reload();
                    $('#myModal').modal('hide');
                });

                $('#reset').click(function () {
                    $('#servicemanFilterForm').trigger('reset'); ;
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
            let providers = $('#filterProvider').val();
            let status = $('#filterStatus').val();

            $(this).find('input[name], select[name]').not('#exportFormat').remove();

            if(dateRange){
                const dates = dateRange.split(' to ');
                $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
            }
            if(providers?.length) $(this).append(`<input type="hidden" name="providers" value="${providers.join(',')}">`);
            if(status?.length) $(this).append(`<input type="hidden" name="status" value="${status}">`);

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
