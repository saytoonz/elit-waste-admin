<x-app-layout>
    @section('header')
        Edit Customer: {{ $customer->name }}
    @endsection

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <!-- Section: Service Info -->
                    <div class="col-span-full">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Service Information</h2>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Customer Type</label>
                        <div class="mt-2">
                            <select id="type" name="type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:max-w-xs sm:text-sm sm:leading-6">
                                <option value="Residential" {{ $customer->type == 'Residential' ? 'selected' : '' }}>Residential</option>
                                <option value="Commercial" {{ $customer->type == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="service_plan_id" class="block text-sm font-medium leading-6 text-gray-900">Service Plan</label>
                        <div class="mt-2 text-sm text-gray-500">
                             @if($customer->subscription && $customer->subscription->servicePlan)
                                <span>Current: <span class="font-semibold text-gray-900">{{ $customer->subscription->servicePlan->name }}</span> ({{ $customer->subscription->billing_cycle }})</span>
                                <p class="mt-1 text-xs">To change plan, please use the Subscriptions tab.</p>
                             @else
                                <span class="text-yellow-600">No active subscription plan.</span>
                             @endif
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="zone_id" class="block text-sm font-medium leading-6 text-gray-900">Zone / Area</label>
                        <div class="mt-2">
                             <select id="zone_id" name="zone_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:max-w-xs sm:text-sm sm:leading-6">
                                <option value="">Select a zone</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ $customer->zone_id == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                                @endforeach
                            </select>
                             <x-input-error :messages="$errors->get('zone_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full border-t border-gray-900/10 my-4"></div>

                    <!-- Section: Personal Info -->
                    <div class="col-span-full">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Basic Details</h2>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Full Name / Business Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone Number</label>
                        <div class="mt-2">
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="secondary_phone" class="block text-sm font-medium leading-6 text-gray-900">Secondary Phone (Optional)</label>
                        <div class="mt-2">
                            <input type="text" name="secondary_phone" id="secondary_phone" value="{{ old('secondary_phone', $customer->secondary_phone) }}"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Address / Description</label>
                        <div class="mt-2">
                            <textarea id="address" name="address" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('address', $customer->address) }}</textarea>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="landmark" class="block text-sm font-medium leading-6 text-gray-900">Landmark</label>
                        <div class="mt-2">
                            <input type="text" name="landmark" id="landmark" value="{{ old('landmark', $customer->landmark) }}"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="gps_coordinates" class="block text-sm font-medium leading-6 text-gray-900">GPS Coordinates</label>
                        <div class="mt-2 flex shadow-sm rounded-md">
                            <input type="text" name="gps_coordinates" id="gps_coordinates" value="{{ old('gps_coordinates', $customer->gps_coordinates) }}" placeholder="Latency, Longitude"
                                class="block w-full rounded-none rounded-l-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <button type="button" onclick="openMap()" class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                </svg>
                                Pick
                            </button>
                        </div>
                    </div>

                    <div class="col-span-full border-t border-gray-900/10 my-4"></div>

                     <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Internal Notes</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="col-span-full">
                        <div class="relative flex gap-x-3">
                            <div class="flex h-6 items-center">
                                <input id="is_active" name="is_active" type="checkbox" value="1" {{ $customer->is_active ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            </div>
                            <div class="text-sm leading-6">
                                <label for="is_active" class="font-medium text-gray-900">Active Customer</label>
                                <p class="text-gray-500">Uncheck if service is suspended.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8">
                 @if(auth()->user()->can('delete customers'))
                    <button type="button" onclick="confirm('Are you sure?') || event.preventDefault(); document.getElementById('delete-customer-form').submit();" class="text-sm font-semibold leading-6 text-red-600 hover:text-red-500">Delete Customer</button>
                 @else
                    <div></div>
                 @endif

                <div class="flex items-center gap-x-6">
                    <a href="{{ route('customers.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                    <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Save Changes</button>
                </div>
            </div>
        </form>

         <form id="delete-customer-form" action="{{ route('customers.destroy', $customer) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <!-- Map Modal with AlpineJS -->
    <div x-data="{ open: false }"
         @open-map.window="open = true; $nextTick(() => initMap())"
         @close-map.window="open = false"
         x-show="open"
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
         
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="open = false"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6 z-50">
                 
                 <div>
                    <div id="map" style="height: 400px;" class="w-full rounded-lg bg-gray-100"></div>
                 </div>
                 
                 <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="button" @click="open = false" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-2">Use Location</button>
                    <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Cancel</button>
                 </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style> .leaflet-pane { z-index: 50; } </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let mapInstance;
            let markerInstance;

            function initMap() {
                 if (typeof L === 'undefined') {
                    console.error('Leaflet not loaded');
                    return;
                }
                
                if (!mapInstance) {
                    // Default to Goaso, Ahafo Region
                    let initialLat = 6.8028;
                    let initialLng = -2.5186;

                    // Safely parse initial coordinates
                    const gpsString = @json($customer->gps_coordinates);
                    
                    if (gpsString && gpsString.includes(',')) {
                        const parts = gpsString.split(',');
                        if (parts.length === 2) {
                            initialLat = parseFloat(parts[0]);
                            initialLng = parseFloat(parts[1]);
                        }
                    }
                    
                    mapInstance = L.map('map').setView([initialLat, initialLng], 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(mapInstance);

                    // Add existing marker
                    if (document.getElementById('gps_coordinates').value) {
                         markerInstance = L.marker([initialLat, initialLng]).addTo(mapInstance);
                    }

                    mapInstance.on('click', function(e) {
                         if(markerInstance) mapInstance.removeLayer(markerInstance);
                         markerInstance = L.marker(e.latlng).addTo(mapInstance);
                         const lat = e.latlng.lat.toFixed(6);
                         const lng = e.latlng.lng.toFixed(6);
                         document.getElementById('gps_coordinates').value = lat + ", " + lng;
                    });
                }
                
                // Invalidate size to ensure render
                setTimeout(() => { mapInstance.invalidateSize(); }, 100);
            }
            
            // Global trigger
            window.openMap = function() {
                window.dispatchEvent(new CustomEvent('open-map'));
            }
        </script>
    @endpush
</x-app-layout>
