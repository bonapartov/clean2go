<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title flip">
            <h3>{{ __('Add Headers') }}</h3>
            <div class="header-action">
                <button type="button" name="save" class="btn btn-primary add-header">
                    {{ __('Add') }}
                </button>
            </div>
        </div>
        <div class="slide">
            <div class="headers mt-2">
                @if (isset($model->headers) && is_array($model->headers))
                @foreach ($model->headers as $key => $header)
                <div class="form-group head">
                    <div>
                        <input class="form-control" value="{{ $key }}" type="text" name="header_key[]"
                            placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                    </div>
                    <div>
                        <input class="form-control" value="{{ $header }}" type="text" name="header_value[]"
                            placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                    </div>
                    <div class="h-100">
                        <button type="button" class="delete-head btn trash-icon-btn">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                @else
                <div class="form-group head">
                    <div>
                        <input class="form-control" value="{{ old('header_key.0') }}" type="text" name="header_key[]"
                            placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                    </div>
                    <div>
                        <input class="form-control" value="{{ old('header_value.0') }}" type="text" name="header_value[]"
                            placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                    </div>
                    <div>
                        <button type="button" class="delete-head btn btn-danger btn-sm w-100">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title flip">
            <h3>{{ __('Add Params') }}</h3>
            <div class="header-action">
                <button type="button" name="save" class="btn btn-primary add-param">
                    {{ __('Add') }}
                </button>
            </div>
        </div>
        <div class="slide">
            <div class="params mt-2">
                @if (isset($model->params) && is_array($model->params))
                @foreach ($model->params as $key => $param)
                <div class="form-group row g-0 parameters">
                    <div class="">
                        <input class="form-control" value="{{ $key }}" type="text" name="param_key[]"
                            placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                    </div>
                    <div class="">
                        <input class="form-control" value="{{ $param }}" type="text" name="param_value[]"
                            placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                    </div>
                    <div class="h-100">
                        <button type="button" class="delete-param btn trash-icon-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12">
                    <div>
                        <div class="form-group parameters">
                            <div>
                                <input class="form-control" value="{{ old('param_key.0') }}" type="text" name="param_key[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                            </div>
                            <div>
                                <input class="form-control" value="{{ old('param_value.0') }}" type="text" name="param_value[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                            </div>
                            <div>
                                <button type="button" class="delete-param btn btn-danger btn-sm w-100">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title flip">
            <h3>{{ __('Add Payload') }}</h3>
        </div>
        <div class="slide position-relative">
            <ul class="nav nav-tabs horizontal-tab custom-scroll mb-sm-4 mb-3" id="account" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile"
                        type="button" role="tab" aria-controls="profile" aria-selected="true">
                        <i class="ri-javascript-line"></i>
                        {{ __('JSON') }}
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address"
                        type="button" role="tab" aria-controls="address" aria-selected="true">
                        <i class="ri-file-text-line"></i>
                        {{ __('Formdata') }}
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="accountContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="form-group row">
                        <div class="col-12">
                            <textarea class="form-control" rows="5" name="payload"
                                placeholder="{{ __('Enter JSON payload here..') }}" cols="80">{{ isset($model->payload) ? json_encode($model->payload, JSON_PRETTY_PRINT) : '' }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                    <button type="button" name="save" class="btn btn-primary add-payload add-payload-btn">
                        {{ __('Add') }}
                    </button>
                    <div class="payload mt-2">
                        @if (isset($model->payload) && is_array($model->payload))
                        @foreach ($model->payload as $key => $payload)
                        <div class="form-group payload-items">
                            <div>
                                <input class="form-control" value="{{ $key }}" type="text" name="payload_key[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                            </div>
                            <div>
                                <input class="form-control" value="{{ is_array($payload) ? json_encode($payload) : $payload }}" type="text" name="payload_value[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                            </div>
                            <div class="h-100">
                                <button type="button" class="delete-payload trash-icon-btn btn">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="form-group payload-items">
                            <div>
                                <input class="form-control" value="{{ old('payload_key.0') }}" type="text" name="payload_key[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_key') }}">
                            </div>
                            <div>
                                <input class="form-control" value="{{ old('payload_value.0') }}" type="text" name="payload_value[]"
                                    placeholder="{{ __('static.custom_ai_models.enter_value') }}">
                            </div>
                            <div>
                                <button type="button" class="w-100 delete-payload btn btn-danger btn-sm">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
