{{--
  «Застраховать услугу» в корзине (ROADMAP Sprint 6, агентская модель B2C).
  Показывается, только если админ включил опцию (App\Support\ServiceInsurance:
  Настройки → «Общие» → «Страхование услуги»). Выбор хранится в сессии
  (CartController::toggleInsurance) и попадает в расчёт как `insurance`.
--}}
@php($insurance = \App\Support\ServiceInsurance::settings())
@if (\App\Support\ServiceInsurance::isAvailable())
  <form action="{{ route('frontend.cart.insurance') }}" method="POST" class="insurance-option mt-4">
    @csrf
    <input type="hidden" name="insurance" value="0">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="insurance" value="1" id="cart-insurance"
        onchange="this.form.submit()" @checked(session('insurance'))>
      <label class="form-check-label" for="cart-insurance">
        {{ __('frontend::static.insurance.checkbox') }}
        ({{ $insurance['price_type'] === \App\Support\ServiceInsurance::PRICE_PERCENTAGE
              ? $insurance['price'].'%'
              : Helpers::getDefaultCurrencySymbol().Helpers::covertDefaultExchangeRate($insurance['price']) }})
      </label>
    </div>
    <small class="d-block text-muted">
      @if ($insurance['insurer_name'])
        {{ __('frontend::static.insurance.insured_by', ['insurer' => $insurance['insurer_name']]) }}
      @endif
      {{ __('frontend::static.insurance.coverage') }}
      @if ($insurance['terms_url'])
        <a href="{{ $insurance['terms_url'] }}" target="_blank" rel="noopener">{{ __('frontend::static.insurance.terms') }}</a>
      @endif
    </small>
  </form>
@endif
