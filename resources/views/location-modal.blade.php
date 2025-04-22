<!-- Location Modal -->
<div id="locationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <h2 class="text-xl font-semibold text-white">Change Location</h2>
                <button onclick="toggleLocationModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4">
                <!-- Search Field -->
                <div class="mb-4">
                    <div class="flex space-x-2">
                        <div class="flex-1 relative">
                            <input type="text" 
                                   id="locationSearch" 
                                   placeholder="Search for a location..." 
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                                   autocomplete="off">
                            <div id="searchResults" class="absolute z-10 w-full bg-gray-800 rounded-lg mt-1 shadow-lg hidden max-h-48 overflow-y-auto"></div>
                        </div>
                        <button type="button" 
                                id="searchButton" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-search mr-2"></i>
                            Search
                            <div id="loadingSpinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="relative h-96 mb-4">
                    <div id="map" class="w-full h-full rounded-lg"></div>
                    <button onclick="getCurrentLocation()" class="absolute top-4 right-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-location-dot"></i>
                        <span>Use Current Location</span>
                    </button>
                </div>
                <div class="text-sm text-gray-400 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Click on the map or drag the marker to select a location
                </div>
                <form id="mapForm" onsubmit="handleMapSubmit(event)">
                    <input type="hidden" id="mapLatitude" name="latitude">
                    <input type="hidden" id="mapLongitude" name="longitude">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Selected Location</label>
                        <input type="text" id="mapLocationName" name="locationName" readonly class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Confirm Location
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let map, marker;
    let debounceTimer;

    function toggleLocationModal() {
        const modal = document.getElementById('locationModal');
        modal.classList.toggle('hidden');
    }

    function initMap() {
        map = L.map('map').setView([28.6139, 77.2090], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([28.6139, 77.2090], { draggable: true }).addTo(map);

        marker.on('dragend', function(e) {
            const lat = e.target.getLatLng().lat;
            const lng = e.target.getLatLng().lng;
            updateLocationName(lat, lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLocationName(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateLocationName(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('mapLocationName').value = data.display_name;
                document.getElementById('mapLatitude').value = lat;
                document.getElementById('mapLongitude').value = lng;
            })
            .catch(error => console.error('Error:', error));
    }

    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        const loadingSpinner = document.createElement('div');
        loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting location...';
        document.getElementById('map').appendChild(loadingSpinner);

        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 13);
                marker.setLatLng([lat, lng]);
                updateLocationName(lat, lng);
                loadingSpinner.remove();
            },
            error => {
                loadingSpinner.remove();
                alert('Error getting location: ' + error.message);
            }
        );
    }

    function handleMapSubmit(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        updateLocation(formData);
    }

    function updateLocation(formData) {
        const params = new URLSearchParams(formData);
        window.location.href = `/?${params.toString()}`;
    }

    function searchLocation() {
        const searchInput = document.getElementById('locationSearch');
        const searchResults = document.getElementById('searchResults');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const searchButton = document.getElementById('searchButton');
        
        if (!searchInput || !searchResults || !loadingSpinner || !searchButton) {
            console.error('Required elements not found');
            return;
        }
        
        const searchQuery = searchInput.value.trim();
        
        if (!searchQuery) {
            searchResults.innerHTML = '<div class="p-2 text-gray-400">Please enter a location name</div>';
            searchResults.classList.remove('hidden');
            return;
        }

        // Show loading state
        loadingSpinner.classList.remove('hidden');
        searchButton.disabled = true;
        searchResults.innerHTML = '';

        // Use OpenStreetMap Nominatim API
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=5`, {
            headers: {
                'Accept-Language': 'en-US,en;q=0.9',
                'User-Agent': 'EnergyApp/1.0'
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.classList.add('hidden');
            searchButton.disabled = false;

            if (!Array.isArray(data) || data.length === 0) {
                searchResults.innerHTML = '<div class="p-2 text-gray-400">No locations found</div>';
                searchResults.classList.remove('hidden');
                return;
            }

            // Clear previous results
            searchResults.innerHTML = '';

            // Add each result as a clickable div
            data.forEach(result => {
                const div = document.createElement('div');
                div.className = 'p-2 hover:bg-gray-700 cursor-pointer text-sm';
                div.textContent = result.display_name;
                div.onclick = () => {
                    // Update map view and marker
                    map.setView([result.lat, result.lon], 13);
                    marker.setLatLng([result.lat, result.lon]);
                    
                    // Update location name and coordinates
                    document.getElementById('mapLocationName').value = result.display_name;
                    document.getElementById('mapLatitude').value = result.lat;
                    document.getElementById('mapLongitude').value = result.lon;
                    
                    // Clear search and hide results
                    searchInput.value = '';
                    searchResults.classList.add('hidden');
                };
                searchResults.appendChild(div);
            });

            // Show results
            searchResults.classList.remove('hidden');
        })
        .catch(error => {
            loadingSpinner.classList.add('hidden');
            searchButton.disabled = false;
            searchResults.innerHTML = `<div class="p-2 text-red-400">Error searching location: ${error.message}</div>`;
            searchResults.classList.remove('hidden');
        });
    }

    // Initialize event listeners when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        const locationSearch = document.getElementById('locationSearch');
        const searchButton = document.getElementById('searchButton');
        const searchResults = document.getElementById('searchResults');

        // Initialize map
        initMap();

        // Add click event listener for search button
        if (searchButton) {
            searchButton.addEventListener('click', searchLocation);
        }

        // Add input event listener for real-time suggestions
        if (locationSearch) {
            locationSearch.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const searchText = e.target.value.trim();
                
                if (searchText.length >= 2) {
                    debounceTimer = setTimeout(searchLocation, 300);
                } else {
                    searchResults.classList.add('hidden');
                }
            });

            // Add keypress event listener for Enter key
            locationSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchLocation();
                }
            });

            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!locationSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
    });

    // Initialize the map when the modal is opened
    document.getElementById('locationModal').addEventListener('shown.bs.modal', function() {
        if (!map) {
            initMap();
        }
    });
</script> 