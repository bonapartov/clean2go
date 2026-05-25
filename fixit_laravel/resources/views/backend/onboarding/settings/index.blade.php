@extends('backend.layouts.master')
@section('title', 'Интеграции РФ — Онбординг')

@section('content')
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title">
            <div class="contentbox-subtitle">
                <h3>Настройки интеграций — Онбординг исполнителей</h3>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('backend.onboarding.settings.update') }}">
            @csrf
            @method('POST')

            @foreach($settings as $group => $groupSettings)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            @switch($group)
                                @case('general') Общие @break
                                @case('fns') ФНС / DaData @break
                                @case('npd') НПД / Plat.ru @break
                                @case('passport') Паспортная верификация @break
                                @case('fssp') ФССП @break
                                @case('contract') Договор @break
                                @default {{ ucfirst($group) }}
                            @endswitch
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($groupSettings as $setting)
                            <div class="mb-3">
                                <label class="form-label">{{ $setting->label }}</label>
                                @if($setting->type === 'password')
                                    <input type="password"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           placeholder="Оставьте пустым, чтобы не менять">
                                @elseif($setting->type === 'toggle')
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="{{ $setting->key }}" value="0">
                                        <input type="checkbox"
                                               name="{{ $setting->key }}"
                                               class="form-check-input"
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                    </div>
                                @elseif($setting->type === 'number')
                                    <input type="number"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           value="{{ $setting->value }}">
                                @else
                                    <input type="text"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           value="{{ $setting->value }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Сохранить настройки</button>
        </form>
    </div>
</div>
@endsection
