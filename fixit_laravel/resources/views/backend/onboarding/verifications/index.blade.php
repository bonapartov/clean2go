@extends('backend.layouts.master')
@section('title', 'Верификация исполнителей')

@section('content')
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title d-flex justify-content-between align-items-center">
            <div class="contentbox-subtitle">
                <h3>Заявки исполнителей</h3>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Все статусы</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                    <option value="pending_manual" {{ request('status') === 'pending_manual' ? 'selected' : '' }}>Ожидает проверки</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Одобрено</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Отклонено</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Все типы</option>
                    <option value="self_employed" {{ request('type') === 'self_employed' ? 'selected' : '' }}>Самозанятый</option>
                    <option value="individual_entrepreneur" {{ request('type') === 'individual_entrepreneur' ? 'selected' : '' }}>ИП</option>
                    <option value="ip_on_npd" {{ request('type') === 'ip_on_npd' ? 'selected' : '' }}>ИП на НПД</option>
                    <option value="legal_entity" {{ request('type') === 'legal_entity' ? 'selected' : '' }}>ООО</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Фильтр</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Исполнитель</th>
                        <th>ИНН</th>
                        <th>Тип</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($verifications as $v)
                        <tr>
                            <td>{{ $v->id }}</td>
                            <td>{{ $v->user->name ?? '—' }}</td>
                            <td>{{ $v->inn ?? '—' }}</td>
                            <td>
                                @switch($v->taxpayer_type)
                                    @case('self_employed') Самозанятый @break
                                    @case('individual_entrepreneur') ИП @break
                                    @case('ip_on_npd') ИП на НПД @break
                                    @case('legal_entity') ООО @break
                                    @default —
                                @endswitch
                            </td>
                            <td>
                                @switch($v->onboarding_status)
                                    @case('in_progress') <span class="badge bg-secondary">В процессе</span> @break
                                    @case('pending_manual') <span class="badge bg-warning text-dark">Ожидает проверки</span> @break
                                    @case('approved') <span class="badge bg-success">Одобрено</span> @break
                                    @case('rejected') <span class="badge bg-danger">Отклонено</span> @break
                                @endswitch
                            </td>
                            <td>{{ $v->created_at->format('d.m.Y') }}</td>
                            <td>
                                <a href="{{ route('backend.verifications.show', $v->id) }}"
                                   class="btn btn-sm btn-outline-primary">Открыть</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Заявки не найдены</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $verifications->withQueryString()->links() }}
    </div>
</div>
@endsection
