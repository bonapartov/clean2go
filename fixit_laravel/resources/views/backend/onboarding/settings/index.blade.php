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

            @php
                $groupLabels = [
                    'general'  => 'Общие',
                    'fns'      => 'ФНС + НПД / DaData',
                    'passport' => 'Паспортная верификация',
                    'fssp'     => 'ФССП',
                    'contract' => 'Договор',
                ];
                $providerAlerts = [
                    'manual'  => ['class' => 'info',    'text' => 'Администратор проверяет паспорт вручную в разделе «Верификации». Исполнитель ожидает решения.'],
                    'suftech' => ['class' => 'warning',  'text' => 'Автоматическая проверка через Суфтех. Укажите API Key и Endpoint ниже.'],
                    'kontur'  => ['class' => 'warning',  'text' => 'Автоматическая проверка через Контур.Фокус. Укажите API Key и Endpoint ниже.'],
                ];
            @endphp

            @foreach($settings as $group => $groupSettings)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $groupLabels[$group] ?? ucfirst($group) }}</h5>
                    </div>
                    <div class="card-body">

                        {{-- Подсказка для паспортного провайдера --}}
                        @if($group === 'passport')
                            @php
                                $currentProvider = $groupSettings->firstWhere('key', 'passport_provider')?->value ?? 'manual';
                                $alert = $providerAlerts[$currentProvider] ?? $providerAlerts['manual'];
                            @endphp
                            <div class="alert alert-{{ $alert['class'] }} mb-3" id="passport-provider-hint">
                                <strong>Текущий режим:</strong> {{ $alert['text'] }}
                            </div>
                        @endif

                        @foreach($groupSettings as $setting)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ $setting->label }}</label>

                                @if($setting->type === 'password')
                                    <input type="password"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           autocomplete="new-password"
                                           value="{{ $setting->value }}"
                                           placeholder="Оставьте пустым, чтобы не менять">

                                @elseif($setting->type === 'toggle')
                                    <label class="switch mt-1">
                                        <input type="hidden" name="{{ $setting->key }}" value="0">
                                        <input type="checkbox"
                                               name="{{ $setting->key }}"
                                               class="form-check-input"
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                        <span class="switch-state"></span>
                                    </label>

                                @elseif($setting->type === 'number')
                                    <input type="number"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           value="{{ $setting->value }}">

                                @elseif($setting->type === 'select' && isset($selectOptions[$setting->key]))
                                    <select name="{{ $setting->key }}"
                                            class="form-select"
                                            id="select-{{ $setting->key }}">
                                        @foreach($selectOptions[$setting->key] as $optVal => $optLabel)
                                            <option value="{{ $optVal }}"
                                                {{ $setting->value === $optVal ? 'selected' : '' }}>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>

                                @else
                                    <input type="text"
                                           name="{{ $setting->key }}"
                                           class="form-control"
                                           value="{{ $setting->value }}">
                                @endif

                                @if($setting->key === 'telegram_admin_chat_id')
                                    <small class="text-muted">
                                        Получить Chat ID: отправьте сообщение боту, затем откройте
                                        <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>
                                    </small>
                                @endif

                                @if($setting->key === 'passport_endpoint')
                                    <small class="text-muted">
                                        Суфтех: <code>https://api.suftech.ru/v1/passport/verify</code> &nbsp;|&nbsp;
                                        Контур: <code>https://focus-api.kontur.ru/api3/req</code>
                                    </small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary px-4">Сохранить настройки</button>
        </form>
    </div>
</div>

<script>
// Обновляем подсказку провайдера без перезагрузки страницы
const providerSelect = document.getElementById('select-passport_provider');
if (providerSelect) {
    const hints = {
        manual:  { cls: 'info',    text: 'Администратор проверяет паспорт вручную в разделе «Верификации». Исполнитель ожидает решения.' },
        suftech: { cls: 'warning', text: 'Автоматическая проверка через Суфтех. Укажите API Key и Endpoint ниже.' },
        kontur:  { cls: 'warning', text: 'Автоматическая проверка через Контур.Фокус. Укажите API Key и Endpoint ниже.' },
    };
    const hintBox = document.getElementById('passport-provider-hint');

    providerSelect.addEventListener('change', function () {
        const h = hints[this.value] || hints.manual;
        hintBox.className = `alert alert-${h.cls} mb-3`;
        hintBox.innerHTML = `<strong>Текущий режим:</strong> ${h.text}`;
    });
}
</script>
@endsection
