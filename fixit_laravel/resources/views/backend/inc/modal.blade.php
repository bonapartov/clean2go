<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-start">
                <div class="main-img">
                    <i data-feather="help-circle"></i>
                </div>
                <div class="text-center">
                    <div class="modal-title"> {{__('static.delete_confirmation.title')}}</div>
                    <p>{{__('static.delete_confirmation.confirmation_proceed')}}</p>
                </div>
            </div>
            <div class="modal-footer category-footer">
                <button type="button" class="btn cancel" data-bs-dismiss="modal">{{ __('static.cancel') }}</button>
                <button type="button" class="btn btn-primary confirm">{{ __('static.confirm') }}</button>
            </div>
        </div>
    </div>
</div>
