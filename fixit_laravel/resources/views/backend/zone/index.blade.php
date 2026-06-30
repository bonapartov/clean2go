@extends('backend.layouts.master')

@section('title', __('static.zone.all'))

@section('content')

    {{-- Карта всех зон --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ __('static.zone.map') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleMapBtn">
                        <i data-feather="eye-off" style="width:14px;height:14px;"></i> Скрыть карту
                    </button>
                </div>
                <div class="card-body p-0" id="zoneMapWrapper">
                    <div id="zone-overview-map" style="width:100%;height:420px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.zone.all') }}</h5>
                    <div class="btn-action">
                        <button type="button" class="btn btn-outline-primary import-redirect-btn" data-url="{{ route('backend.import-export.index', 'zones') }}">
                            {{ __('static.import.import') }} <i class="ri-download-2-line"></i>
                        </button>
                        @can('backend.zone.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.zone.create') }}" class="btn">{{ __('static.zone.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.zone.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.zones') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.deleted_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="zone-table">
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
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('app.yandex_map_api_key') }}&lang=ru_RU" type="text/javascript"></script>
    {!! $dataTable->scripts() !!}
    <script>
        var zoneMapInstance = null;
        var zonePolygons = [];
        var mapReady = false;
        var mapVisible = true;

        var ZONE_COLORS = [
            '#0055FF', '#FF5500', '#00AA44', '#AA00FF',
            '#FF0088', '#00AAAA', '#FF8800', '#5500AA'
        ];

        ymaps.ready(function() {
            zoneMapInstance = new ymaps.Map('zone-overview-map', {
                center: [55.751244, 37.618423],
                zoom: 9,
                controls: ['zoomControl', 'geolocationControl']
            });
            mapReady = true;
            loadZonesOnMap();
            setupMapClickHandler();
        });

        function loadZonesOnMap() {
            if (!mapReady || !mapVisible) return;
            $.getJSON('{{ route('backend.zone.map-data') }}', function(zones) {
                zonePolygons.forEach(function(p) { zoneMapInstance.geoObjects.remove(p); });
                zonePolygons = [];
                zoneMapInstance.balloon.close();

                if (!zones.length) return;

                var bounds = null;
                zones.forEach(function(zone, idx) {
                    if (!zone.locations || zone.locations.length < 3) return;
                    var coords = zone.locations.map(function(p) { return [p.lat, p.lng]; });
                    var color = ZONE_COLORS[idx % ZONE_COLORS.length];
                    var polygon = new ymaps.Polygon([coords], {}, {
                        fillColor: color,
                        fillOpacity: 0.18,
                        strokeColor: color,
                        strokeWidth: 2,
                        interactivityModel: 'default#transparent'
                    });
                    polygon._zoneName = zone.name;
                    zoneMapInstance.geoObjects.add(polygon);
                    zonePolygons.push(polygon);

                    var b = polygon.geometry.getBounds();
                    if (b) bounds = bounds ? ymaps.util.bounds.fromPoints([
                        [Math.min(bounds[0][0], b[0][0]), Math.min(bounds[0][1], b[0][1])],
                        [Math.max(bounds[1][0], b[1][0]), Math.max(bounds[1][1], b[1][1])]
                    ]) : b;
                });

                if (bounds) zoneMapInstance.setBounds(bounds, { checkZoomRange: true, zoomMargin: 40 });
            });
        }

        // Клик по карте — собираем все зоны, содержащие точку клика
        function setupMapClickHandler() {
            zoneMapInstance.events.add('click', function(e) {
                var coords = e.get('coords');
                var matched = [];
                zonePolygons.forEach(function(polygon) {
                    if (polygon.geometry.contains(coords)) {
                        matched.push(polygon._zoneName);
                    }
                });
                if (matched.length) {
                    var content = matched.map(function(name, i) {
                        return '<div style="' + (i > 0 ? 'border-top:1px solid #eee;margin-top:6px;padding-top:6px;' : '') + '"><b>' + name + '</b></div>';
                    }).join('');
                    zoneMapInstance.balloon.open(coords, { content: content });
                }
            });
        }

        $('#toggleMapBtn').on('click', function() {
            mapVisible = !mapVisible;
            if (mapVisible) {
                $('#zoneMapWrapper').slideDown(200, function() {
                    zoneMapInstance && zoneMapInstance.container.fitToViewport();
                    loadZonesOnMap();
                });
                $(this).html('<i data-feather="eye-off" style="width:14px;height:14px;"></i> Скрыть карту');
            } else {
                $('#zoneMapWrapper').slideUp(200);
                $(this).html('<i data-feather="eye" style="width:14px;height:14px;"></i> Показать карту');
            }
            if (typeof feather !== 'undefined') feather.replace();
        });

        $(document).on('click', '.import-redirect-btn', function() {
            var url = $(this).data('url');
            if (url) {
                window.open(url, '_blank');
            }
        });

        // Обновляем карту при каждой перерисовке таблицы (удаление, изменение статуса и т.д.)
        $(document).on('draw.dt', '#zone-table', function() {
            loadZonesOnMap();
        });
    </script>
@endpush
