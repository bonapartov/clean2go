@extends('backend.layouts.master')
@section('title', __('static.custom_ai_models.custom_ai_models'))
@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-align-center">
                <h5>{{ __('static.custom_ai_models.custom_ai_models') }}</h5>
                <div class="btn-action">
                    <div class="btn-popup mb-0">
                        <a href="{{ route('backend.custom-ai-model.create') }}" class="btn btn-primary">
                            <i data-feather="plus"></i> {{ __('static.custom_ai_models.create_new') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('static.custom_ai_models.name') }}</th>
                                <th>{{ __('static.custom_ai_models.provider') }}</th>
                                <th>{{ __('static.custom_ai_models.model_name') }}</th>
                                <th>{{ __('static.custom_ai_models.base_url') }}</th>
                                <th>{{ __('static.custom_ai_models.is_default') }}</th>
                                <th>{{ __('static.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($models as $model)
                                <tr>
                                    <td>{{ $model->name }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($model->provider) }}</span></td>
                                    <td>{{ $model->model_name ?? '-' }}</td>
                                    <td>{{ $model->base_url ?? '-' }}</td>
                                    <td>
                                        @if ($model->is_default)
                                            <span class="badge bg-success">{{ __('static.yes') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('static.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if (!$model->is_default)
                                                <form action="{{ route('backend.custom-ai-model.set-default', $model->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="btn btn-sm btn-info" title="{{ __('static.custom_ai_models.set_as_default') }}">
                                                        <i data-feather="star"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('backend.custom-ai-model.edit', $model->id) }}" class="btn btn-sm btn-primary">
                                                <i data-feather="edit"></i>
                                            </a>
                                            <form action="{{ route('backend.custom-ai-model.destroy', $model->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('static.custom_ai_models.delete_confirmation') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('static.custom_ai_models.no_models') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
