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
                                src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i
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
                <option class="option" value={{ $key }} @if (@$zone?->currency_id ?? old('currency_id')) @if ($key == @$zone?->currency_id) selected @endif @endif>{{ $option }}</option>
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
                    {{ (is_array(old('payment_methods')) && in_array($key, old('payment_methods'))) || (isset($zone?->payment_methods) && in_array($value['slug'], $zone?->payment_methods)) ? 'selected' : '' }}>
                    {{ $value['name'] }}</option>
            @endforeach
        </select>
        @error('payment_methods.*')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="">{{ __('static.zone.place_points') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="text" id="place_points" name="place_points"
            placeholder="{{ __('static.zone.select_place_points') }}"
            value="{{ isset($zone->locations) ? json_encode($zone->locations, true) : old('place_points') }}" readonly>
        @error('place_points')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

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
                    ignore: [],
                    rules: {
                        "name": "required",
                        "place_points": "required",
                    }
                });

                $('#submitBtn').click(function(e) {
                    e.preventDefault();

                    if ($("#zoneForm").valid()) {
                        $("#zoneForm").submit();
                    }
                });

                let mapInstance, polygonInstance = null;
                let previewPolyline = null, rubberBandPolyline = null;
                let existingPolygon = @json(isset($zone->locations) ? $zone->locations : null);
                let isDrawing = false;
                let drawingPoints = [];

                ymaps.ready(function() { initMap(); });

                function clearPreviewLines() {
                    if (previewPolyline)    { mapInstance.geoObjects.remove(previewPolyline);    previewPolyline = null; }
                    if (rubberBandPolyline) { mapInstance.geoObjects.remove(rubberBandPolyline); rubberBandPolyline = null; }
                }

                function removePointMarkers() {
                    var toRemove = [];
                    mapInstance.geoObjects.each(function(obj) {
                        if (obj !== polygonInstance && obj !== previewPolyline && obj !== rubberBandPolyline) {
                            toRemove.push(obj);
                        }
                    });
                    toRemove.forEach(function(obj) { mapInstance.geoObjects.remove(obj); });
                }

                function updatePreview(mouseCoords) {
                    clearPreviewLines();
                    if (drawingPoints.length >= 2) {
                        previewPolyline = new ymaps.Polyline(drawingPoints, {}, {
                            strokeColor: '#0055FF', strokeWidth: 2,
                            interactivityModel: 'default#transparent'
                        });
                        mapInstance.geoObjects.add(previewPolyline);
                    }
                    if (mouseCoords && drawingPoints.length >= 1) {
                        rubberBandPolyline = new ymaps.Polyline(
                            [drawingPoints[drawingPoints.length - 1], mouseCoords], {}, {
                            strokeColor: '#0055FF', strokeWidth: 2, strokeStyle: 'dash',
                            interactivityModel: 'default#transparent'
                        });
                        mapInstance.geoObjects.add(rubberBandPolyline);
                    }
                }

                function initMap() {
                    const startLocation = [55.751244, 37.618423];
                    mapInstance = new ymaps.Map('map-container', {
                        center: startLocation, zoom: 13,
                        controls: ['zoomControl', 'searchControl', 'geolocationControl']
                    });
                    mapInstance.controls.get('searchControl').options.set({ provider: 'yandex#search' });
                    loadExistingPolygon();

                    mapInstance.events.add('click', function(e) {
                        if (!isDrawing) startDrawing();
                        var coords = e.get('coords');
                        drawingPoints.push(coords);
                        addPointMarker(coords);
                        updatePreview(null);
                        $('#map-hint').text('Точек: ' + drawingPoints.length + '. Нажмите «Завершить зону» для сохранения.');
                    });

                    mapInstance.events.add('mousemove', function(e) {
                        if (!isDrawing || drawingPoints.length === 0) return;
                        updatePreview(e.get('coords'));
                    });

                    $('#finishZoneBtn').on('click', function() { finishDrawing(); });

                    $('#resetZoneBtn').on('click', function() {
                        isDrawing = false; drawingPoints = [];
                        if (polygonInstance) { mapInstance.geoObjects.remove(polygonInstance); polygonInstance = null; }
                        clearPreviewLines(); removePointMarkers();
                        $('#place_points').val(''); $('#finishZoneBtn').addClass('d-none');
                        $('#map-hint').text('Кликайте на карту для добавления точек зоны');
                    });
                }

                function startDrawing() {
                    isDrawing = true; drawingPoints = [];
                    if (polygonInstance) { mapInstance.geoObjects.remove(polygonInstance); polygonInstance = null; }
                    clearPreviewLines(); removePointMarkers();
                    $('#place_points').val(''); $('#finishZoneBtn').removeClass('d-none');
                    $('#map-hint').text('Кликайте на карту для добавления точек зоны');
                }

                function addPointMarker(coords) {
                    mapInstance.geoObjects.add(new ymaps.Placemark(coords, {}, {
                        preset: 'islands#redDotIcon', interactivityModel: 'default#transparent'
                    }));
                }

                function finishDrawing() {
                    if (drawingPoints.length < 3) { alert('Минимум 3 точки для создания зоны'); return; }
                    isDrawing = false; clearPreviewLines(); removePointMarkers();
                    $('#finishZoneBtn').addClass('d-none');
                    var first = drawingPoints[0], last = drawingPoints[drawingPoints.length - 1];
                    if (first[0] !== last[0] || first[1] !== last[1]) drawingPoints.push([first[0], first[1]]);
                    polygonInstance = new ymaps.Polygon([drawingPoints], {}, {
                        fillColor: '#0055FF', fillOpacity: 0.2, strokeColor: '#0055FF', strokeWidth: 2,
                    });
                    mapInstance.geoObjects.add(polygonInstance);
                    polygonInstance.editor.startEditing();
                    polygonInstance.events.add('geometrychange', function() { updateCoordinatesFromPolygon(); });
                    updateCoordinatesFromPolygon();
                    $('#map-hint').text('Зона создана. Перетащите вершины для редактирования или нажмите «Сбросить».');
                }

                function updateCoordinatesFromPolygon() {
                    if (!polygonInstance) return;
                    var coords = polygonInstance.geometry.getCoordinates()[0];
                    $('#place_points').val(JSON.stringify(
                        coords.map(function(c) { return { lat: c[0], lng: c[1] }; })
                    ));
                }

                function loadExistingPolygon() {
                    if (!existingPolygon || existingPolygon.length < 3) return;
                    var coords = existingPolygon.map(function(p) { return [p.lat, p.lng]; });
                    polygonInstance = new ymaps.Polygon([coords], {}, {
                        fillColor: '#0055FF', fillOpacity: 0.2, strokeColor: '#0055FF', strokeWidth: 2,
                    });
                    mapInstance.geoObjects.add(polygonInstance);
                    polygonInstance.editor.startEditing();
                    polygonInstance.events.add('geometrychange', function() { updateCoordinatesFromPolygon(); });
                    mapInstance.setBounds(polygonInstance.geometry.getBounds());
                    $('#place_points').val(JSON.stringify(existingPolygon));
                    $('#map-hint').text('Зона загружена. Перетащите точки для редактирования или нажмите «Сбросить».');
                }
            });
        })(jQuery);
    </script>
@endpush
