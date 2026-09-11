@forelse($categories as $index => $category)
        @forelse($category->services?->whereNull('parent_id') as $service)
            <div class="modal fade booking-modal" id="bookNowModal-{{ $service?->id }}">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title m-0">{{ __('static.common.book_your_service') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                          <form method="post" id="bookingForm">
                            <div class="add-person-box">
                                <h4 class="service-title">{{ __('static.common.add_required_person') }}</h4>
                                <div class="select-servicemen">
                                    <p>{{ __('static.common.how_many_persons_required') }}</p>
                                    <div class="plus-minus">
                                        <i data-feather="minus" id="minus-{{ $service?->id }}" class="minus"></i>
                                        <input id="quantityInput-{{ $service?->id }}" name="required_servicemen" type="number" value="{{ $service?->required_servicemen }}" min="{{ $service?->required_servicemen }}" max="100" readonly="">
                                        <i data-feather="plus" id="add-{{ $service?->id }}" class="add"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="service-title">
                                <h4>{{ __('static.common.select_service_delivery_location') }}</h4>
                                <button class="btn" type="button" data-bs-target="#newAddress" data-bs-toggle="modal">{{ __('static.common.add_new_address') }}</button>
                            </div>

                            <div class="consumer-addresses-list">
                                @includeIf('backend.pos.addresses' , ['user' => null ])
                            </div>

                            <div class="form-group" id="">
                                <label for="start_end_date">{{ __('static.pos.select_date_time') }}</label>
                                <input type="text" class="form-control" id="date-time" name="date_time" placeholder="{{ __('static.service_package.select_date') }}">
                            </div>

                            <input type="hidden" value="{{ $service?->id }}" name="service_id">

                            <div class="form-group row">
                                <div class="col-12">
                                    <select class="select-2 form-control user-dropdown" name="serviceman_id[]" id="servicemanDropdown" data-placeholder="{{ __('static.pos.select_servicemen') }}" multiple>
                                        <option class="select-placeholder" value=""></option>
                                        @foreach ($service?->user?->servicemans as $serviceman)
                                            <option value="{{ $serviceman->id }}" sub-title="{{ $serviceman->email }}" image="{{ $serviceman->getFirstMedia('image')?->getUrl() }}" {{ $serviceman->id == request()->query('serviceman_id') ? 'selected' : '' }}>
                                                {{ $serviceman->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('serviceman_id')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-12" for="phone">{{ __('static.common.custom_message') }} <span>*</span></label>
                                <div class="col-12">
                                    <textarea class="form-control" type="text" id="custom_message" name="custom_message" value=""
                                        placeholder="{{ __('static.common.enter_custom_message') }}"></textarea>
                                </div>
                            </div>
                          </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary submit-booking-button">{{ __('static.common.save_changes') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
        @endforelse
    @empty
    @endforelse
