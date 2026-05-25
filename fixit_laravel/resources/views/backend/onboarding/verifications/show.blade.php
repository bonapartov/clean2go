@extends('backend.layouts.master')
@section('title', 'Заявка #' . $verification->id)

@section('content')
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title d-flex justify-content-between align-items-center">
            <div class="contentbox-subtitle">
                <h3>Заявка #{{ $verification->id }} — {{ $verification->user->name ?? '—' }}</h3>
            </div>
            <a href="{{ route('backend.verifications.index') }}" class="btn btn-sm btn-outline-secondary">← Назад</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            {{-- Блок 1: ИНН и тип --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header"><strong>ИНН и налоговый статус</strong></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">ИНН</dt><dd class="col-7">{{ $verification->inn ?? '—' }}</dd>
                            <dt class="col-5">Тип</dt>
                            <dd class="col-7">
                                @switch($verification->taxpayer_type)
                                    @case('self_employed') Самозанятый @break
                                    @case('individual_entrepreneur') ИП @break
                                    @case('ip_on_npd') ИП на НПД @break
                                    @case('legal_entity') ООО @break
                                    @default —
                                @endswitch
                            </dd>
                            <dt class="col-5">Наименование</dt><dd class="col-7">{{ $verification->legal_name ?? '—' }}</dd>
                            <dt class="col-5">ОГРН</dt><dd class="col-7">{{ $verification->ogrn ?? '—' }}</dd>
                            <dt class="col-5">Статус ИНН</dt><dd class="col-7">{{ $verification->inn_status ?? '—' }}</dd>
                            <dt class="col-5">Статус НПД</dt><dd class="col-7">{{ $verification->npd_status ?? '—' }}</dd>
                            <dt class="col-5">Проверен</dt><dd class="col-7">{{ $verification->inn_verified_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Блок 2: Паспорт --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Паспортные данные</strong></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Серия/Номер</dt>
                            <dd class="col-7">{{ $verification->passport_series }} {{ $verification->passport_number }}</dd>
                            <dt class="col-5">Выдан</dt><dd class="col-7">{{ $verification->passport_issued_by ?? '—' }}</dd>
                            <dt class="col-5">Дата выдачи</dt><dd class="col-7">{{ $verification->passport_issued_date?->format('d.m.Y') ?? '—' }}</dd>
                            <dt class="col-5">Код подразд.</dt><dd class="col-7">{{ $verification->passport_dept_code ?? '—' }}</dd>
                            <dt class="col-5">Статус</dt>
                            <dd class="col-7">
                                @switch($verification->passport_status)
                                    @case('pending') <span class="badge bg-secondary">Ожидает</span> @break
                                    @case('verified') <span class="badge bg-success">Верифицирован</span> @break
                                    @case('rejected') <span class="badge bg-danger">Отклонён</span> @break
                                    @case('manual_review') <span class="badge bg-warning text-dark">Ручная проверка</span> @break
                                    @default —
                                @endswitch
                            </dd>
                        </dl>
                        @if($verification->passport_photo_path)
                            <div class="mt-2">
                                <a href="{{ URL::signedRoute('onboarding.file', ['path' => $verification->passport_photo_path], now()->addMinutes(15)) }}"
                                   target="_blank" class="btn btn-sm btn-outline-secondary">Фото паспорта</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Блок 3: ФССП --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header"><strong>ФССП</strong></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Статус</dt>
                            <dd class="col-7">
                                @switch($verification->fssp_status)
                                    @case('clean') <span class="badge bg-success">Чисто</span> @break
                                    @case('has_debts') <span class="badge bg-danger">Есть долги</span> @break
                                    @case('manual_review') <span class="badge bg-warning text-dark">Ручная проверка</span> @break
                                    @default —
                                @endswitch
                            </dd>
                            <dt class="col-5">Сумма долга</dt>
                            <dd class="col-7">{{ $verification->fssp_debt_amount ? number_format($verification->fssp_debt_amount, 2, ',', ' ') . ' ₽' : '—' }}</dd>
                            <dt class="col-5">Проверен</dt><dd class="col-7">{{ $verification->fssp_checked_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Блок 4: Договор --}}
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Договор</strong></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Тип</dt><dd class="col-7">{{ $verification->contract_type ?? '—' }}</dd>
                            <dt class="col-5">Подписан</dt><dd class="col-7">{{ $verification->contract_signed_at?->format('d.m.Y H:i') ?? 'Не подписан' }}</dd>
                            <dt class="col-5">IP подписания</dt><dd class="col-7">{{ $verification->contract_ip_address ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Действия --}}
        @if(in_array($verification->onboarding_status, ['pending_manual', 'in_progress']))
            <div class="card mb-4">
                <div class="card-header"><strong>Действия</strong></div>
                <div class="card-body d-flex gap-3 flex-wrap">
                    <form method="POST" action="{{ route('backend.verifications.approve', $verification->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Одобрить заявку?')">Одобрить</button>
                    </form>

                    <button type="button" class="btn btn-danger" data-bs-toggle="collapse"
                            data-bs-target="#rejectForm">Отклонить</button>

                    <button type="button" class="btn btn-warning" data-bs-toggle="collapse"
                            data-bs-target="#requestDocsForm">Запросить доп. документы</button>
                </div>

                <div class="collapse px-3 pb-3" id="rejectForm">
                    <form method="POST" action="{{ route('backend.verifications.reject', $verification->id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Причина отказа</label>
                            <textarea name="reason" class="form-control" rows="3" required minlength="10"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm">Подтвердить отказ</button>
                    </form>
                </div>

                <div class="collapse px-3 pb-3" id="requestDocsForm">
                    <form method="POST" action="{{ route('backend.verifications.request-docs', $verification->id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Какие документы нужны</label>
                            <textarea name="reason" class="form-control" rows="3" required minlength="10"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">Отправить запрос</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Лог --}}
        <div class="card">
            <div class="card-header"><strong>История действий</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Дата</th><th>Шаг</th><th>Действие</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d.m H:i') }}</td>
                                <td>{{ $log->step }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted p-3">Лог пуст</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
