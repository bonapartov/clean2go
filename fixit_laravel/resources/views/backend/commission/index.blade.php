@extends('backend.layouts.master')
@section('title', __('static.commission_history.commission_history'))
@section('content')


    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.commission_history.commission_history') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary import-redirect-btn" data-url="{{ route('backend.import-export.index', 'commission-histories') }}">
                            {{ __('static.import.import') }} <i class="ri-download-2-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="commission-history-table">
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).on('click', '.import-redirect-btn', function() {
            var url = $(this).data('url');
            if (url) {
                window.open(url, '_blank');
            }
        });
    </script>
@endpush