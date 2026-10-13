{{--
  Исполнитель услуги до оплаты: ФИО или название, статус, ИНН.
  Агентская модель B2C (ROADMAP, Sprint 6): клиент должен видеть, с кем
  заключает договор. Данные — App\Support\ExecutorDisclosure (из проверки
  ИНН при онбординге), приходят в $checkout['executors'].

  @param array $executors  список из ExecutorDisclosure::forProviderIds()
  @param bool  $compact    одна строка под именем исполнителя (корзина)
--}}
@php $compact = $compact ?? false; @endphp
@foreach ($executors ?? [] as $executor)
  @if ($compact)
    <small class="d-block text-muted executor-disclosure-line">
      @if ($executor['verified'])
        {{ $executor['status_label'] }} · {{ __('frontend::static.executor.inn') }} {{ $executor['inn'] }}
      @else
        {{ __('frontend::static.executor.not_verified') }}
      @endif
    </small>
  @else
    <div class="executor-disclosure mb-2">
      <p class="mb-0 f-w-600">{{ $executor['name'] }}</p>
      @if ($executor['verified'])
        <small class="d-block">{{ $executor['status_label'] }}</small>
        <small class="d-block">{{ __('frontend::static.executor.inn') }} {{ $executor['inn'] }}@if ($executor['ogrn']), {{ __('frontend::static.executor.ogrn') }} {{ $executor['ogrn'] }}@endif</small>
      @else
        <small class="d-block text-muted">{{ __('frontend::static.executor.not_verified') }}</small>
      @endif
    </div>
  @endif
@endforeach
