@extends('backend.layouts.master')
@section('title', __('static.custom_ai_models.edit'))
@section('content')
<div class="">
    <form id="aiModelForm" action="{{ route('backend.custom-ai-model.update', $model->id) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row g-xl-4 g-3">
            <div class="col-xl-9">
                <div class="left-part">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title flip">
                                <h3>{{ __('static.custom_ai_models.edit') }}</h3>
                            </div>
                            <div class="slide">
                                <div class="form-group row">
                                    <label class="col-md-2" for="name">{{ __('static.custom_ai_models.name') }} <span class="text-danger">*</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" value="{{ old('name', $model->name) }}" type="text" name="name"
                                            placeholder="{{ __('static.custom_ai_models.enter_name') }}" required>
                                        @error('name')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="provider">{{ __('static.custom_ai_models.provider') }} <span class="text-danger">*</span></label>
                                    <div class="col-md-10">
                                        <select class="select-2 form-control" id="provider" name="provider" required>
                                            <option value="">{{ __('static.custom_ai_models.select_provider') }}</option>
                                            <option value="openai" {{ old('provider', $model->provider) == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                            <option value="google" {{ old('provider', $model->provider) == 'google' ? 'selected' : '' }}>Google (Gemini)</option>
                                            <option value="anthropic" {{ old('provider', $model->provider) == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                            <option value="custom" {{ old('provider', $model->provider) == 'custom' ? 'selected' : '' }}>{{ __('static.custom_range') }}</option>
                                        </select>
                                        @error('provider')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="model_name">{{ __('static.custom_ai_models.model_name') }}</label>
                                    <div class="col-md-10">
                                        <input class="form-control" value="{{ old('model_name', $model->model_name) }}" type="text" name="model_name"
                                            placeholder="{{ __('static.custom_ai_models.enter_model_name') }}">
                                        <small class="form-text text-muted">{{ __('static.custom_ai_models.model_name_help') }}</small>
                                        @error('model_name')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="api_key">{{ __('static.custom_ai_models.api_key') }}</label>
                                    <div class="col-md-10">
                                        <input class="form-control" value="{{ old('api_key', $model->api_key) }}" type="text" name="api_key"
                                            placeholder="{{ __('static.custom_ai_models.enter_api_key') }}">
                                        @error('api_key')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="base_url">{{ __('static.custom_ai_models.base_url') }}</label>
                                    <div class="col-md-10">
                                        <input class="form-control" value="{{ old('base_url', $model->base_url) }}" type="text" name="base_url"
                                            placeholder="{{ __('static.custom_ai_models.enter_base_url') }}">
                                        <small class="form-text text-muted">{{ __('static.custom_ai_models.base_url_help') }}</small>
                                        @error('base_url')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="description">{{ __('static.custom_ai_models.description') }}</label>
                                    <div class="col-md-10">
                                        <textarea class="form-control" name="description" rows="3"
                                            placeholder="{{ __('static.custom_ai_models.enter_description') }}">{{ old('description', $model->description) }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="role">{{ __('static.custom_ai_models.is_default') }}</label>
                                    <div class="col-md-10">
                                        <div class="editor-space">
                                            <label class="switch">
                                                @if (isset($model))
                                                    <input class="form-control" type="hidden" name="is_default" value="0">
                                                    <input class="form-check-input" type="checkbox" name="is_default" id="" value="1"
                                                        {{ $model->is_default ? 'checked' : '' }}>
                                                @else
                                                    <input class="form-control" type="hidden" name="is_default" value="0">
                                                    <input class="form-check-input" type="checkbox" name="is_default" id="" value="1"
                                                        checked>
                                                @endif
                                                <span class="switch-state"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('backend.custom-ai-model.partials.headers-params-payload', ['model' => $model])
                </div>
            </div>

            <div class="col-xl-3">
                <div class="p-sticky">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title ">
                                <h3>{{ __('static.publish') }}</h3>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-2 icon-position">
                                                <button type="submit" name="save" class="btn btn-primary">
                                                    <i data-feather="save"></i>
                                                    {{ __('static.save') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-2 icon-position">
                                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testAIModelModal">
                                                    <i data-feather="play"></i>
                                                    {{ __('static.custom_ai_models.test_model') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Test AI Model Modal -->
<div class="modal fade" id="testAIModelModal" tabindex="-1" aria-labelledby="testAIModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testAIModelModalLabel">{{ __('static.custom_ai_models.test_model') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('static.custom_sms_gateways.close') }}"><i class="ri-close-line"></i></button>
            </div>
            <form id="testAIModelForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="test_prompt" class="form-label">{{ __('static.custom_ai_models.test_prompt') }}</label>
                        <textarea class="form-control" id="test_prompt" name="test_prompt" rows="4"
                            placeholder="{{ __('static.custom_ai_models.enter_test_prompt') }}" required>Hello, how are you?</textarea>
                    </div>
                    <div id="testResult" style="display: none;">
                        <div class="alert" id="testResultAlert">
                            <h6>{{ __('static.custom_ai_models.test_result') }}:</h6>
                            <div id="testResultContent"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('static.custom_sms_gateways.close') }}</button>
                    <button type="submit" class="btn btn-primary" id="testSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="testSpinner" role="status"></span>
                        {{ __('static.custom_ai_models.send_test') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    (function($) {
        "use strict";

        $(document).ready(function() {
            $('.add-param').on('click', function() {
                var clonedOption = $('.params .parameters:first').clone().addClass('cloned');
                clonedOption.find('input').val('');
                $('.params').append(clonedOption);
            });

            $('.params').on('click', '.delete-param', function() {
                $(this).closest('.parameters').remove();
            });

            $('.add-header').on('click', function() {
                var clonedOption = $('.headers .head:first').clone();
                clonedOption.find('input').val('');
                $('.headers').append(clonedOption);
            });

            $('.headers').on('click', '.delete-head', function() {
                $(this).closest('.head').remove();
            });

            $('.add-payload').on('click', function() {
                var clonedOption = $('.payload .payload-items:first').clone().addClass('cloned');
                clonedOption.find('input').val('');
                $('.payload').append(clonedOption);
            });

            $('.payload').on('click', '.delete-payload', function() {
                $(this).closest('.payload-items').remove();
            });

            // Test AI Model
            $('#testAIModelForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var submitBtn = $('#testSubmitBtn');
                var spinner = $('#testSpinner');
                var resultDiv = $('#testResult');
                var resultContent = $('#testResultContent');
                var resultAlert = $('#testResultAlert');

                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                resultDiv.hide();

                // Get all form data from the main form (latest values)
                var mainFormData = $('#aiModelForm').serializeArray();
                var testPrompt = $('#test_prompt').val();

                // Build data object with proper array handling
                var allData = {
                    test_prompt: testPrompt,
                    _token: $('input[name="_token"]').val()
                };

                // Process main form data
                mainFormData.forEach(function(item) {
                    if (item.name && item.name !== '_method' && item.name !== '_token') {
                        // Handle array fields (header_key[], param_key[], payload_key[], etc.)
                        if (item.name.endsWith('[]')) {
                            var fieldName = item.name.replace('[]', '');
                            if (!allData[fieldName]) {
                                allData[fieldName] = [];
                            }
                            if (item.value) {
                                allData[fieldName].push(item.value);
                            }
                        } else {
                            allData[item.name] = item.value;
                        }
                    }
                });

                // Handle payload JSON textarea separately
                var payloadTextarea = $('#aiModelForm textarea[name="payload"]');
                if (payloadTextarea.length && payloadTextarea.val()) {
                    allData.payload = payloadTextarea.val();
                }

                $.ajax({
                    url: '{{ route("backend.custom-ai-model.test", $model->id) }}',
                    method: 'POST',
                    data: allData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                        resultDiv.show();

                        if (response.success) {
                            resultAlert.removeClass('alert-danger').addClass('alert-success');
                            var responseText = '';
                            if (typeof response.response === 'object') {
                                responseText = '<pre>' + JSON.stringify(response.response, null, 2) + '</pre>';
                            } else {
                                responseText = '<pre>' + response.raw_response + '</pre>';
                            }
                            resultContent.html('<strong>{{ __('static.common.status_code') }}</strong> ' + response.status_code + '<br><br>' + responseText);
                        } else {
                            resultAlert.removeClass('alert-success').addClass('alert-danger');
                            resultContent.html('<strong>{{ __('static.common.error_colon') }}</strong> ' + (response.error || 'Unknown error') +
                                (response.status_code ? '<br><strong>{{ __('static.common.status_code') }}</strong> ' + response.status_code : '') +
                                (response.response ? '<br><br><pre>' + JSON.stringify(response.response, null, 2) + '</pre>' : ''));
                        }
                    },
                    error: function(xhr) {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                        resultDiv.show();
                        resultAlert.removeClass('alert-success').addClass('alert-danger');
                        var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'An error occurred';
                        resultContent.html('<strong>{{ __('static.common.error_colon') }}</strong> ' + errorMsg);
                    }
                });
            });
        });

    })(jQuery);
</script>
@endpush
