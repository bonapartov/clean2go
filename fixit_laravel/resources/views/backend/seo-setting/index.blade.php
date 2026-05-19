@extends('backend.layouts.master')

@section('title', __('static.seo_setting.seo_settings'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.seo_setting.seo_settings') }}</h5>
                </div>
                <div class="card-body common-table">
                    <div class="seo-setting-table">
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
@endpush
