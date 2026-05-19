@extends('backend.layouts.master')
@section('title', __('static.wallet.bonus_create'))
@section('content')
    @can('backend.wallet_bonus.edit')
        <div class="row">
            <div class="m-auto col-xl-10 col-xxl-8">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>{{ __('static.wallet.edit_wallet_bonus') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="walletBonusForm" action="{{ route('backend.walletBonus.update', $walletBonus->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('backend.wallet-bonus.fields')
                            <div class="text-end">
                                <button id='submitBtn' type="submit" class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
