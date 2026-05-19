@extends('backend.layouts.master')

@section('title', __('static.serviceman-document.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.serviceman-document.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.serviceman-document.update', $servicemanDocument->id) }}"
                        method="POST" enctype="multipart/form-data" id="providerDocumentForm">
                        @method('PUT')
                        @csrf
                        @include('backend.serviceman-document.fields')
                        <div class="text-end">
                            <button class="btn btn-primary spinner-btn ms-auto"
                                type="submit">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
