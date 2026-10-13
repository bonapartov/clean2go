@extends('backend.layouts.master')
@section('title', 'Чеки самозанятых')

{{-- Чеки самозанятых («Мой налог»), ROADMAP Sprint 8. Логика — App\Support\Npd\NpdReceipts. --}}
@section('content')
<div class="contentbox">
    <div class="inside">
        <div class="contentbox-title d-flex justify-content-between align-items-center">
            <div class="contentbox-subtitle">
                <h3>Чеки самозанятых («Мой налог»)</h3>
            </div>
            <form method="POST" action="{{ route('backend.npd-receipts.retry') }}">
                @csrf
                <button class="btn btn-sm btn-primary">Повторить отправку</button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-{{ $enabled ? 'info' : 'warning' }}">
            {{ $enabled ? 'Чеки формируются при оплате заказа.' : 'Формирование чеков выключено.' }}
            Канал: {{ $channel }}.
            Настройки — <a href="{{ route('backend.onboarding.settings') }}">Интеграции РФ</a>, раздел «Чеки самозанятых».
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Все статусы</option>
                    @foreach ($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Заказ</th>
                        <th>Исполнитель</th>
                        <th>ИНН</th>
                        <th>Услуга</th>
                        <th class="text-end">Сумма, ₽</th>
                        <th>Статус</th>
                        <th>Чек</th>
                        <th>Создан</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td>#{{ $receipt->booking?->booking_number }}</td>
                            <td>{{ $receipt->executor?->name }}</td>
                            <td>{{ $receipt->executor_inn }}</td>
                            <td>{{ $receipt->service_name }}</td>
                            <td class="text-end">{{ number_format($receipt->amount, 2, ',', ' ') }}</td>
                            <td>
                                {{ $statusLabels[$receipt->status] ?? $receipt->status }}
                                @if ($receipt->last_error)
                                    <div class="text-danger small">{{ $receipt->last_error }}</div>
                                @endif
                                @if ($receipt->cancel_reason)
                                    <div class="text-muted small">{{ $receipt->cancel_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($receipt->receipt_url)
                                    <a href="{{ $receipt->receipt_url }}" target="_blank" rel="noopener">{{ $receipt->external_id }}</a>
                                @else
                                    {{ $receipt->external_id ?? '—' }}
                                @endif
                            </td>
                            <td>{{ $receipt->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Чеков пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $receipts->links() }}
    </div>
</div>
@endsection
