@push('js')
<script src="https://api-maps.yandex.ru/2.1/?apikey={{$yandexMapKey}}&lang=ru_RU" type="text/javascript"></script>
<script>
    let map;
    let markers = [];
    let allServicemen = @json($servicemen);
    let balloon;

    ymaps.ready(function() {
        initMap();
    });

    function initMap() {
        const center = [55.751244, 37.618423];

        map = new ymaps.Map('map_canvas', {
            center: center,
            zoom: 12,
            controls: ['zoomControl', 'geolocationControl']
        });

        function generateInfoContent(serviceman) {
            const rating = serviceman.review ? `${serviceman.review.toFixed(1)} / 5` : "UNRATED";
            return `<div style="display: flex; flex-direction: column; align-items: center; font-family: Arial, sans-serif;">
                        <img src="${serviceman.image}" alt="Serviceman Image" width="70" height="70" style="border-radius: 50%; margin-bottom: 10px;">
                        <h3 style="margin: 5px 0; font-size: 16px; color: #333;">${serviceman.name}</h3>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Phone: <strong>${serviceman.phone}</strong></p>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Email: <strong>${serviceman.email}</strong></p>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Rating: <strong>${rating}</strong></p>
                    </div>`;
        }

        function addMarker(serviceman) {
            const placemark = new ymaps.Placemark(
                [serviceman.lat, serviceman.lng],
                {},
                {
                    iconLayout: 'default#image',
                    iconImageHref: serviceman.image,
                    iconImageSize: [50, 50],
                    iconImageOffset: [-25, -25]
                }
            );
            
            const infoContent = generateInfoContent(serviceman);
            
            placemark.events.add('mouseenter', function() {
                balloon = map.balloon;
                balloon.open([serviceman.lat, serviceman.lng], infoContent);
            });
            
            placemark.events.add('mouseleave', function() {
                map.balloon.close();
            });
            
            map.geoObjects.add(placemark);
            markers.push(placemark);
        }

        function showAllServicemenMarkers() {
            map.geoObjects.removeAll();
            markers = [];
            allServicemen.forEach(serviceman => {
                if (serviceman.lat && serviceman.lng) addMarker(serviceman);
            });
            map.setCenter(center);
            map.setZoom(12);
        }

        function fetchAndShowServiceman(servicemanId, $button) {
            $.ajax({
                url: '{{ route("backend.serviceman-cordinates.index", ":id") }}'.replace(':id', servicemanId),
                method: 'GET',
                success: function(response) {
                    if (response.lat && response.lng) {
                        map.geoObjects.removeAll();
                        markers = [];
                        addMarker(response);
                        map.setCenter([response.lat, response.lng]);
                        map.setZoom(15);
                    }
                    toggleButtonLoading($button, false);
                    $('#show-all-servicemen').show();
                },
                error: function() {
                    alert('Location not found');
                    toggleButtonLoading($button, false);
                }
            });
        }

        function toggleButtonLoading($button, isLoading) {
            $button.find('.spinner-border').toggleClass('d-none', !isLoading);
            $button.find('.btn-text').text(isLoading ? 'Loading...' : 'View Location');
            $button.prop('disabled', isLoading);
        }

        // Initial marker setup for all servicemen with locations
        allServicemen.forEach(serviceman => {
            if (serviceman.lat && serviceman.lng) addMarker(serviceman);
        });

        // Handle click event for "View Location" buttons
        $('button.view-location-btn').on('click', function() {
            const $button = $(this);
            const servicemanId = $button.data('serviceman-id');
            toggleButtonLoading($button, true);
            map.geoObjects.removeAll();
            markers = [];
            fetchAndShowServiceman(servicemanId, $button);
        });

        $('#show-all-servicemen').on('click', function() {
            showAllServicemenMarkers();
            $(this).hide();
        });
    }
</script>
@endpush