@csrf
@if(isset($location))
   @method('PUT')
@endif

<div class="space-y-4 max-w-2xl">
   <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input type="text" name="name" value="{{ old('name', $location->name ?? '') }}"
         class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:border-brand-500">
   </div>

   <div>
      <label class="block text-sm font-medium text-gray-700">Latitude</label>
      <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $location->latitude ?? '') }}"
         class="w-full border border-gray-300 rounded-md p-2 bg-gray-50" readonly>
   </div>

   <div>
      <label class="block text-sm font-medium text-gray-700">Longitude</label>
      <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $location->longitude ?? '') }}"
         class="w-full border border-gray-300 rounded-md p-2 bg-gray-50" readonly>
   </div>

   <div>
      <label class="block text-sm font-medium text-gray-700">Type</label>
      <select name="type" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:border-brand-500">
         <option value="city" {{ old('type', $location->type ?? '') === 'city' ? 'selected' : '' }}>City</option>
         <option value="station" {{ old('type', $location->type ?? '') === 'station' ? 'selected' : '' }}>Station</option>
         <option value="institution" {{ old('type', $location->type ?? '') === 'institution' ? 'selected' : '' }}>
            Institution</option>
      </select>
   </div>

   <div>
      <label class="block text-sm font-medium text-gray-700">City</label>
      <select name="city_id" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:border-brand-500">
         <option value="">None</option>
         @foreach($cities as $city)
          <option value="{{ $city->id }}" {{ old('city_id', $location->city_id ?? '') == $city->id ? 'selected' : '' }}>
            {{ $city->name }}
          </option>
       @endforeach
      </select>
   </div>
   <div id="map" class="w-full h-[400px] border border-gray-300 rounded-lg"></div>

   <div class="pt-4 flex gap-2">
      <button type="submit" class="bg-brand-500 text-ink-900 font-semibold px-4 py-2 rounded-md hover:bg-brand-600">
         {{ isset($location) ? 'Update' : 'Create' }} Location
      </button>
      @if(isset($location))
        <button form="delete-form" type="submit"
          class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Delete</button>
     @endif
   </div>

</div>

<script>
   function initMap() {
      let lat = parseFloat(document.getElementById('latitude').value) || 33.8886;
      let lng = parseFloat(document.getElementById('longitude').value) || 35.4955;

      const map = new google.maps.Map(document.getElementById('map'), {
         zoom: 13,
         center: { lat, lng }
      });

      const marker = new google.maps.Marker({
         position: { lat, lng },
         map,
         draggable: true
      });

      google.maps.event.addListener(map, 'click', function (event) {
         marker.setPosition(event.latLng);
         document.getElementById('latitude').value = event.latLng.lat();
         document.getElementById('longitude').value = event.latLng.lng();
      });

      google.maps.event.addListener(marker, 'dragend', function (event) {
         document.getElementById('latitude').value = event.latLng.lat();
         document.getElementById('longitude').value = event.latLng.lng();
      });
   }

   window.initMap = initMap;
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap" async
   defer></script>
