@extends('backend.layouts.master')
@section('title', __('static.custom_ai_models.custom_ai_models'))
@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.custom_ai_models.custom_ai_models') }}</h5>
                    <div class="btn-action">
                        @can('backend.custom_ai_model.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.custom-ai-model.create') }}" class="btn btn-primary">
                                    <i data-feather="plus"></i> {{ __('static.custom_ai_models.create_new') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="custom-ai-table">
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
