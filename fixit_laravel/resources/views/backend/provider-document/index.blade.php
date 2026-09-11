@extends('backend.layouts.master')
@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('static.provider-document.provider-documents'))

@section('content')
    @use('App\Helpers\Helpers')

    @php
        $providers = Helpers::getProviders()->get();
    @endphp
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.provider-document.provider-documents') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                            {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary import-redirect-btn" data-url="{{ route('backend.import-export.index', 'provider-documents') }}">
                            {{ __('static.import.import') }} <i class="ri-download-2-line"></i>
                        </button>
                        @can('backend.provider_document.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.provider-document.create') }}"
                                    class="btn">{{ __('static.provider-document.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.provider_document.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.providerDocuments') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            @endcan
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="provider-document-table">
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
                    <form id="providerDocumentFilterForm">
                        <div class="row g-sm-4 g-3 mb-3">
                            <div class="col-6">
                                <label class="" for="user_id">{{ __('static.daterange') }}</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="{{ __('static.common.select_date_range') }}">
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
                                <label class="">{{ __('static.provider-document.document') }}</label>
                                <select id="filterDocument" class="select-2 form-control" data-placeholder="{{ __('static.provider-document.select-document') }}" multiple>
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($documents as $doc)
                                        <option value="{{ $doc->id }}">
                                            {{ $doc->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="">{{ __('static.provider-document.status') }}</label>
                                <select id="filterStatus" class="select-2 form-control" data-placeholder="{{ __('static.provider-document.select_status') }}">
                                <option class="select-placeholder" value=""></option>
                                @foreach (['pending' =>  __('static.provider-document.pending'), 'approved' => __('static.provider-document.approved'), 'rejected' => __('static.provider-document.rejected')] as $key => $option)
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

    <form id="exportForm" method="GET" action="{{ route('backend.provider-document.data.export') }}">
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
                
                    if (urlParams.has('providers')) {
                       $('#filterProvider').val(urlParams.get('providers').split(',')).trigger('change');
                    }
                
                    if (urlParams.has('documents')) {
                        $('#filterDocument').val(urlParams.get('documents').split(',')).trigger('change');
                    }
                    
                    if (urlParams.has('status')) {
                        $('#filterStatus').val(urlParams.get('status').split(',')).trigger('change');
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
                    let providers    = $('#filterProvider').val();
                    let documents  = $('#filterDocument').val();
                    let status       = $('#filterStatus').val();

                    if (dateRange) {
                        const dates = dateRange.split(' to ');
                        params.start_date = dates[0];
                        params.end_date = dates[1];
                    }                    
                    if (providers && providers.length) params.providers = providers.join(',');
                    if (documents && documents.length) params.documents = documents.join(',');
                    if (status && status.length) params.status = status;

                    const newUrl = new URL(window.location.href);
                    newUrl.search = new URLSearchParams(params).toString();
                    history.replaceState(null, '', newUrl.toString());

                    location.reload();
                    $('#myModal').modal('hide');
                });

                $('#reset').click(function () {
                    $('#providerDocumentFilterForm').trigger('reset');
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
            let providers    = $('#filterProvider').val();
            let documents  = $('#filterDocument').val();
            let status       = $('#filterStatus').val();

            $(this).find('input[name], select[name]').not('#exportFormat').remove();

            if(dateRange){
                const dates = dateRange.split(' to ');
                $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
            }
            if(providers?.length) $(this).append(`<input type="hidden" name="provider" value="${providers.join(',')}">`);
            if(documents?.length) $(this).append(`<input type="hidden" name="document" value="${documents.join(',')}">`);
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
