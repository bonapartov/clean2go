    @extends('backend.layouts.master')
    @use('app\Helpers\Helpers')
    @push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/range-slider.css') }}">
    @endpush
    @section('title', __('static.wallet.wallet_bonus'))

    @section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header flex-align-center">
                    <h5>{{ __('static.wallet.wallet_bonuses') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary" id="applyFilter">
                            {{ __('static.report.filter') }} <i class="ri-filter-2-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportExportModal">
                            {{ __('static.report.export') }} <i class="ri-upload-line"></i>
                        </button>
                        @can('backend.wallet_bonus.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.walletBonus.create') }}"
                                    class="btn">{{ __('static.wallet.create_wallet_bonus') }}</a>
                            </div>
                        @endcan
                        @can('backend.wallet_bonus.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn" style="display: none;"
                            data-url="{{ route('backend.delete.walletBonus') }}">
                            <span id="count-selected-rows">0</span>{{ __('static.deleted_selected') }}</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="wallet-bonus-table">
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
                        <h5 class="modal-title" id="myModalLabel">{{ __('static.report.filter') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ri-close-line"></i></button>
                    </div>
                    <div class="modal-body">
                        <form id="serviceFilterForm">
                            <div class="mb-3">
                                <label class="col-md-2" for="user_id">{{ __('static.daterange') }}</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="{{ __('static.common.select_date_range') }}">
                                <span id="dateRangeError" class="text-danger" style="display:none;"></span>
                                <br>

                                <label class="col-md-2" for="type">{{ __('static.coupon.type') }}</label>
                                <select class="select-2 form-control" id="filterType" name="type" data-placeholder="{{ __('static.coupon.select_type') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach (['fixed' => 'Fixed', 'percentage' => 'Percentage'] as $key => $option)
                                        <option class="option" value="{{ $key }}">{{ $option }}</option>
                                    @endforeach
                                </select>

                                <label class="col-md-4 mt-3">{{ __('frontend::static.services.price_range')}}</label>
                                <input id="price-range" name="price" type="text" class="range-slider" data-min="{{ $minBonus }}" data-max="{{ $maxBonus }}" data-symbol="{{ Helpers::getDefaultCurrencySymbol() }}">

                                
                                <label class="col-md-2" for="filterStatus">{{ __('static.status') }}</label>
                                <select name="filterStatus" id="filterStatus" class="select-2 form-control" data-placeholder="{{ __('static.provider-document.select_status') }}">
                                        <option class="select-placeholder" value=""></option>      
                                        <option value="1">{{ __('static.active') }}</option>
                                        <option value="0">{{ __('static.common.deactive') }}</option>
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

        <form id="exportForm" method="GET" action="{{ route('backend.wallet-bonus.export') }}">
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
        <!-- Swiper js -->
        <script src="{{ asset('frontend/js/swiper.js') }}"></script>
        <script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>

        <!-- Range slider js -->
        <script src="{{ asset('frontend/js/range-slider/ion.rangeSlider.min.js') }}"></script>
        <script src="{{ asset('frontend/js/range-slider/rangeslider-script.js') }}"></script>

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

                        if (urlParams.has('type')) {
                            $('#filterType').val(urlParams.get('type')).trigger('change');
                        }

                        if (urlParams.has('price')) { let price = urlParams.get('price').split(';');
                            let slider = $('#price-range').data('ionRangeSlider');
                            if (slider) {
                                slider.update({
                                    from: parseFloat(price[0]),
                                    to: parseFloat(price[1])
                                });
                            }
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
                        let status = $('#filterStatus').val();
                        let type = $('#filterType').val(); 
                        let price = $('#price-range').val(); 

                        if (dateRange) {
                            const dates = dateRange.split(' to ');
                            params.start_date = dates[0];
                            params.end_date = dates[1];
                        }
                        if (type && type.length) params.type = type;
                        if (price && price.length) params.price = price;
                        if (status && status.length) params.status = status;

                        const newUrl = new URL(window.location.href);
                        newUrl.search = new URLSearchParams(params).toString();
                        history.replaceState(null, '', newUrl.toString());

                        location.reload();
                        $('#myModal').modal('hide');
                    });

                    $('#reset').click(function () {
                        $('#serviceFilterForm').trigger('reset'); ;
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
                let type = $('#filterType').val();
                let price = $('#price-range').val();
                let status = $('#filterStatus').val();

                $(this).find('input[name], select[name]').not('#exportFormat').remove();

                if(dateRange){
                    const dates = dateRange.split(' to ');
                    $(this).append(`<input type="hidden" name="start_date" value="${dates[0]}">`);
                    $(this).append(`<input type="hidden" name="end_date" value="${dates[1]}">`);
                }
                if(type?.length) $(this).append(`<input type="hidden" name="type" value="${type}">`);
                if (price?.length) $(this).append(`<input type="hidden" name="price" value="${price}">`);
                if(status?.length) $(this).append(`<input type="hidden" name="status" value="${status}">`);

                setTimeout(() => {
                    $('.spinner-btn').prop('disabled', false);
                    $('.spinner-btn .spinner').remove();
                    var modal = bootstrap.Modal.getInstance($('#reportExportModal')[0]);
                    modal.hide();
                }, 3000);
            });
        </script>
    @endpush
