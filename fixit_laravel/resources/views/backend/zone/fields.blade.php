@use('app\Helpers\Helpers')
@php
    $payment_methods = Helpers::getPaymentMethodConfigs();

@endphp

    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">
                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($zone))
                        <li>
                            <a href="{{ route('backend.zone.edit', ['zone' => $zone->id, 'locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                    <a href="{{ route('backend.zone.create', ['locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.zone.edit', ['zone' => $zone->id, 'locale' => Session::get('locale', 'en')]) }}"
                            class="language-switcher active" target="blank"><img
                                src="{{ asset('admin/images/flags/LR.png') }}" alt="">{{ __('static.common.english') }}<i
                                data-feather="arrow-up-right"></i></a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>


<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.zone.name') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" id="name" name="name"
            placeholder="{{ __('static.zone.enter_name') }} ({{ request('locale', app()->getLocale()) }})"
            value="{{ isset($zone->name) ? $zone->getTranslation('name', request('locale', app()->getLocale())) : old('name') }}">
        @error('name')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <!-- Copy Icon -->
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
    </div>
</div>

<div class="form-group row">
    <label for="country"
        class="col-md-2">{{ __('static.settings.currency') }}<span>
            *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control select-country"
            id="currency_id" name="currency_id"
            data-placeholder="{{ __('static.settings.select_currency') }}">
            <option class="select-placeholder" value=""></option>
            @forelse ($currencies as $key => $option)
                <option class="option" value={{ $key }}
                    @if(isset($zone))
                        @if($key == ($zone->currency_id ?? old('currency_id'))) selected @endif
                    @elseif(old('currency_id'))
                        @if($key == old('currency_id')) selected @endif
                    @elseif(isset($defaultCurrencyId) && $key == $defaultCurrencyId)
                        selected
                    @endif
                >{{ $option }}</option>
                @empty
                    <option value="" disabled></option>
                @endforelse
        </select>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="payment_methods">{{ __('static.zone.payment_methods') }}<span> *</span> </label>
    <div class="col-md-10 error-div select-dropdown">
        <select id="blog_payment_methods" class="select-2 form-control" id="payment_methods[]" search="true" name="payment_methods[]"
            data-placeholder="{{ __('static.zone.select_payment_methods') }}" multiple>
            <option></option>
            @foreach ($payment_methods as $key => $value)
                <option value="{{ $value['slug'] }}"
                    @if(is_array(old('payment_methods')) && in_array($key, old('payment_methods')))
                        selected
                    @elseif(isset($zone) && is_array($zone->payment_methods) && in_array($value['slug'], $zone->payment_methods))
                        selected
                    @elseif(!isset($zone) && !old('payment_methods') && isset($defaultPaymentMethods) && in_array($value['slug'], $defaultPaymentMethods))
                        selected
                    @endif
                >{{ $value['name'] }}</option>
            @endforeach
        </select>
        @error('payment_methods.*')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<input type="hidden" id="place_points" name="place_points"
    value="{{ isset($zone->locations) ? json_encode($zone->locations, true) : old('place_points') }}">
@error('place_points')
    <div class="alert alert-danger py-2 px-3 mb-3 small">
        <strong>{{ __('static.zone.place_points') }}:</strong> {{ $message }}
    </div>
@enderror

<div class="form-group row">
    <label class="col-md-2" for="search-box">{{ __('static.zone.search_location') }}</label>
    <div class="col-md-10">
        <input id="search-box" class="form-control" type="text"
            placeholder="{{ __('static.zone.search_locations') }}">
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.zone.map') }}</label>
    <div class="col-md-10">
        <div class="map-warper dark-support rounded overflow-hidden">
            <div class="map-container" id="map-container"></div>
        </div>
        <div id="coords"></div>
        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
            <button type="button" id="startZoneBtn" class="btn btn-outline-primary btn-sm">
                <i data-feather="edit-2" class="me-1" style="width:14px;height:14px;"></i>Нарисовать зону
            </button>
            <button type="button" id="finishDrawingBtn" class="btn btn-success btn-sm d-none">
                <i data-feather="check" class="me-1" style="width:14px;height:14px;"></i>Завершить рисование
            </button>
            <button type="button" id="resetZoneBtn" class="btn btn-outline-danger btn-sm">
                <i data-feather="trash-2" class="me-1" style="width:14px;height:14px;"></i>Сбросить
            </button>
            <span id="zone-status" class="small"></span>
        </div>
        <div id="map-hint" class="text-muted small mt-1">Нажмите «Нарисовать зону» и кликайте по карте. Нажмите на первую точку для замыкания.</div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($zone))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $zone->status ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

@push('js')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('app.yandex_map_api_key') }}&lang=ru_RU" type="text/javascript"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#zoneForm").validate({
                    rules: {
                        "name": "required",
                    }
                });

                $('#submitBtn').click(function(e) {
                    e.preventDefault();
                    if (!$('#place_points').val()) {
                        $('#zone-status').html('<span class="text-danger"><i data-feather="alert-circle" style="width:14px;height:14px;vertical-align:middle;"></i> Зона не нарисована — нарисуйте зону на карте</span>');
                        if (typeof feather !== 'undefined') feather.replace();
                        document.getElementById('map-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    if ($("#zoneForm").valid()) {
                        $("#zoneForm").submit();
                    }
                });

                let mapInstance, polygonInstance = null;
                let drawingDone = false;
                let existingPolygon = @json(isset($zone->locations) ? $zone->locations : null);

                ymaps.ready(function() { initMap(); });

                function initMap() {
                    const startLocation = [55.751244, 37.618423];
                    mapInstance = new ymaps.Map('map-container', {
                        center: startLocation, zoom: 13,
                        controls: ['zoomControl', 'searchControl', 'geolocationControl']
                    });
                    mapInstance.controls.get('searchControl').options.set({ provider: 'yandex#search' });

                    $('#startZoneBtn').on('click', startNewZone);

                    $('#finishDrawingBtn').on('click', function() {
                        if (!polygonInstance) return;
                        var coords = polygonInstance.geometry.getCoordinates()[0];
                        if (!coords || coords.length < 3) {
                            alert('Минимум 3 точки для создания зоны');
                            return;
                        }
                        polygonInstance.editor.stopDrawing();
                        switchToEditing();
                    });

                    $('#resetZoneBtn').on('click', function() {
                        if (polygonInstance) { mapInstance.geoObjects.remove(polygonInstance); polygonInstance = null; }
                        drawingDone = false;
                        $('#place_points').val('');
                        updateZoneStatus();
                        $('#finishDrawingBtn').addClass('d-none');
                        $('#startZoneBtn').removeClass('d-none');
                        $('#map-hint').text('Нажмите «Нарисовать зону» и кликайте по карте.');
                    });

                    loadExistingPolygon();
                }

                function startNewZone() {
                    if (polygonInstance) { mapInstance.geoObjects.remove(polygonInstance); polygonInstance = null; }
                    drawingDone = false;
                    polygonInstance = new ymaps.Polygon([], {}, {
                        fillColor: '#0055FF', fillOpacity: 0.2,
                        strokeColor: '#0055FF', strokeWidth: 2,
                    });
                    mapInstance.geoObjects.add(polygonInstance);
                    polygonInstance.editor.events.add('statechange', function() {
                        if (!polygonInstance.editor.state.get('drawing')) {
                            switchToEditing();
                        }
                    });
                    polygonInstance.editor.startDrawing();
                    $('#place_points').val('');
                    updateZoneStatus();
                    $('#startZoneBtn').addClass('d-none');
                    $('#finishDrawingBtn').removeClass('d-none');
                    $('#map-hint').text('Кликайте по карте. Нажмите на первую точку чтобы замкнуть зону, или нажмите «Завершить рисование».');
                }

                function switchToEditing() {
                    if (drawingDone) return;
                    var coords = polygonInstance && polygonInstance.geometry.getCoordinates()[0];
                    if (!coords || coords.length < 3) return;
                    drawingDone = true;
                    polygonInstance.editor.startEditing();
                    polygonInstance.geometry.events.add('change', updateCoordinatesFromPolygon);
                    updateCoordinatesFromPolygon();
                    $('#finishDrawingBtn').addClass('d-none');
                    $('#startZoneBtn').removeClass('d-none');
                    $('#map-hint').text('Зона создана. Перетаскивайте вершины или средние точки для редактирования.');
                }

                function updateCoordinatesFromPolygon() {
                    if (!polygonInstance) return;
                    var coords = polygonInstance.geometry.getCoordinates()[0];
                    if (!coords || coords.length < 3) return;
                    $('#place_points').val(JSON.stringify(
                        coords.map(function(c) { return { lat: c[0], lng: c[1] }; })
                    ));
                    updateZoneStatus();
                }

                function updateZoneStatus() {
                    var val = $('#place_points').val();
                    var $status = $('#zone-status');
                    if (val) {
                        try {
                            var pts = JSON.parse(val);
                            var count = pts.length > 0 && pts[pts.length - 1].lat === pts[0].lat ? pts.length - 1 : pts.length;
                            $status.html('<span class="text-success"><i data-feather="check-circle" style="width:14px;height:14px;vertical-align:middle;"></i> Зона задана (' + count + ' точек)</span>');
                        } catch(e) {
                            $status.html('<span class="text-success"><i data-feather="check-circle" style="width:14px;height:14px;vertical-align:middle;"></i> Зона задана</span>');
                        }
                    } else {
                        $status.html('<span class="text-danger"><i data-feather="alert-circle" style="width:14px;height:14px;vertical-align:middle;"></i> Зона не нарисована</span>');
                    }
                    if (typeof feather !== 'undefined') feather.replace();
                }

                function loadExistingPolygon() {
                    if (!existingPolygon || existingPolygon.length < 3) {
                        updateZoneStatus();
                        return;
                    }
                    drawingDone = true;
                    var coords = existingPolygon.map(function(p) { return [p.lat, p.lng]; });
                    polygonInstance = new ymaps.Polygon([coords], {}, {
                        fillColor: '#0055FF', fillOpacity: 0.2,
                        strokeColor: '#0055FF', strokeWidth: 2,
                    });
                    mapInstance.geoObjects.add(polygonInstance);
                    polygonInstance.editor.startEditing();
                    polygonInstance.geometry.events.add('change', updateCoordinatesFromPolygon);
                    mapInstance.setBounds(polygonInstance.geometry.getBounds());
                    $('#place_points').val(JSON.stringify(existingPolygon));
                    updateZoneStatus();
                    $('#map-hint').text('Зона загружена. Перетаскивайте вершины или средние точки. Нажмите «Сбросить» чтобы нарисовать заново.');
                }
            });
        })(jQuery);
    </script>
@endpush
