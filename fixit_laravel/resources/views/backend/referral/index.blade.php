@extends('backend.layouts.master')
@section('title', __('static.dashboard.referral'))

@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-align-center">
                <h5>{{ __('static.dashboard.referral') }}
                    @role('provider')
                        @if(auth()->user()->referral_code)
                            <span class="ms-2 badge d-flex badge-version-primary referral-code-badge cursor-pointer" data-referral-code="{{ auth()->user()->referral_code }}" title="{{ __('static.click_to_copy') }}">{{ __('static.referral_code') }}: {{ auth()->user()->referral_code }}</span>
                        @endif
                    @endrole
                </h5>
            </div>
            <div class="card-body common-table">
                <div class="banner-table">
                    <div class="table-responsive">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var badge = document.querySelector('.referral-code-badge');
            if (badge) {
                badge.addEventListener('click', function() {
                    var code = this.getAttribute('data-referral-code');
                    if (!code) return;
                    var originalText = this.textContent;
                    navigator.clipboard.writeText(code).then(function() {
                        badge.textContent = '{{ __("static.copied") }}';
                        badge.classList.add('text-success');
                        setTimeout(function() {
                            badge.textContent = originalText;
                            badge.classList.remove('text-success');
                        }, 1500);
                    });
                });
            }
        });
    </script>
@endpush
