{{--
  Чеки исполнителей-самозанятых по заказу («Мой налог», ROADMAP Sprint 8).
  Самозанятый обязан передать чек покупателю — показываем номер и ссылку.
  Данные — App\Support\Npd\NpdReceipts::forClient().

  @param \App\Models\Booking $booking
--}}
@php
    $npdReceipts = \App\Support\Npd\NpdReceipts::forClient($booking);
@endphp
@if (count($npdReceipts))
    <div class="col-12">
        <div class="bill-summary npd-receipts">
            <label>{{ __('frontend::static.npd_receipts.title') }}</label>
            <ul class="charge">
                @foreach ($npdReceipts as $receipt)
                    <li>
                        <p>
                            {{ $receipt['executor_name'] }} · {{ __('frontend::static.executor.inn') }} {{ $receipt['executor_inn'] }}
                            <small class="d-block text-muted">
                                {{ $receipt['status_label'] }}
                                @if ($receipt['number'])
                                    · №
                                    @if ($receipt['url'])
                                        <a href="{{ $receipt['url'] }}" target="_blank" rel="noopener">{{ $receipt['number'] }}</a>
                                    @else
                                        {{ $receipt['number'] }}
                                    @endif
                                @endif
                                @if ($receipt['is_test'])
                                    · {{ __('frontend::static.npd_receipts.test') }}
                                @endif
                            </small>
                        </p>
                        <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($receipt['amount'], 2, ',', ' ') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
