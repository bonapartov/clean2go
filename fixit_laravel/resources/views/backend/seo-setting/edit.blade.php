@extends('backend.layouts.master')
@section('title', __('static.seo_setting.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.seo_setting.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.seo-setting.update', $SeoSetting->id) }}" id="seoSettingForm" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        @include('backend.seo-setting.fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
