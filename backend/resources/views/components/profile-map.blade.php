@props(['latitude', 'longitude', 'label'])

@if ($latitude !== null && $longitude !== null && config('services.google_maps.api_key'))
    <div id="profile-map" class="h-64 w-full rounded-xl border border-gray-200" role="img" aria-label="Map showing {{ $label }} location"></div>
    <script>
        window.initProfileMap = function () {
            const position = @json(['lat' => (float) $latitude, 'lng' => (float) $longitude]);
            const map = new google.maps.Map(document.getElementById('profile-map'), {
                center: position,
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
            });
            new google.maps.Marker({ map, position, title: @json($label) });
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initProfileMap" async defer></script>
@else
    <div class="flex h-40 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 text-center text-sm text-gray-500">
        Map preview unavailable.
    </div>
@endif
