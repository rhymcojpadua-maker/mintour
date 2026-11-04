import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.L = L;

window.mapPage = () => {
    const mapElement = document.getElementById('fullMap');
    const featuresElement = document.getElementById('mapInitialFeatures');
    const mapRoot = document.querySelector('[data-map-features-url]');

    if (!mapElement || !featuresElement || !mapRoot) {
        throw new Error('The interactive map could not be initialized.');
    }

    return {
        active: JSON.parse(document.getElementById('mapActiveCategories').textContent),
        featuresUrl: mapRoot.dataset.mapFeaturesUrl,
        tileUrl: mapRoot.dataset.mapTileUrl,
        initialFeatures: JSON.parse(featuresElement.textContent),
        map: null,
        layer: null,
        locationLayer: null,
        locating: false,
        status: 'Showing mapped destinations, stays, and dining places.',
        error: '',

        init() {
            this.map = L.map(mapElement, { scrollWheelZoom: true }).setView([13.1733849, 121.2787746], 13);
            L.tileLayer(this.tileUrl, {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
                maxZoom: 19,
            }).addTo(this.map);
            this.layer = L.layerGroup().addTo(this.map);
            this.locationLayer = L.layerGroup().addTo(this.map);
            this.renderFeatures(this.initialFeatures);
        },

        destroy() {
            this.map?.remove();
            this.map = null;
        },

        async refresh() {
            this.error = '';
            if (!this.active.length) {
                this.layer.clearLayers();
                this.status = 'No listing types selected. Select a type to show map pins.';
                return;
            }

            const url = new URL(this.featuresUrl, window.location.href);
            this.active.forEach(category => url.searchParams.append('categories[]', category));

            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error(`The map listing request failed (${response.status}).`);
                const { features } = await response.json();
                this.renderFeatures(features);
            } catch (error) {
                this.error = error.message || 'Could not load map listings. Please try again.';
            }
        },

        renderFeatures(features) {
            this.layer.clearLayers();
            features.forEach(feature => {
                L.circleMarker([feature.lat, feature.lng], {
                    radius: 9,
                    color: '#fff',
                    weight: 3,
                    fillColor: feature.color,
                    fillOpacity: 1,
                }).addTo(this.layer).bindPopup(feature.popup);
            });
            if (features.length === 1) {
                this.map.setView([features[0].lat, features[0].lng], 15);
            } else if (features.length > 1) {
                this.map.fitBounds(
                    L.latLngBounds(features.map(feature => [feature.lat, feature.lng])),
                    { padding: [32, 32], maxZoom: 14 },
                );
            }
            this.status = `${features.length} ${features.length === 1 ? 'listing' : 'listings'} with coordinates shown on the map.`;
        },

        locateMe() {
            this.error = '';
            if (!navigator.geolocation) {
                this.error = 'Location is not supported by this browser.';
                return;
            }

            this.locating = true;
            this.status = 'Requesting your location…';
            navigator.geolocation.getCurrentPosition(
                position => {
                    const { latitude, longitude, accuracy } = position.coords;
                    this.locationLayer.clearLayers();
                    L.circle([latitude, longitude], {
                        radius: accuracy,
                        color: '#0f766e',
                        weight: 1,
                        fillColor: '#14b8a6',
                        fillOpacity: 0.12,
                    }).addTo(this.locationLayer);
                    L.circleMarker([latitude, longitude], {
                        radius: 8,
                        color: '#fff',
                        weight: 3,
                        fillColor: '#0f766e',
                        fillOpacity: 1,
                    }).addTo(this.locationLayer).bindPopup('You are here');
                    this.map.setView([latitude, longitude], 15);
                    this.status = 'Showing your current location and mapped listings.';
                    this.locating = false;
                },
                locationError => {
                    const messages = {
                        1: 'Location access was denied. Allow location access in your browser settings to use this feature.',
                        2: 'Your location could not be determined. Check your device location settings and try again.',
                        3: 'The location request timed out. Please try again.',
                    };
                    this.error = messages[locationError.code] || 'Could not get your location. Please try again.';
                    this.status = 'Showing mapped destinations, stays, and dining places.';
                    this.locating = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 },
            );
        },
    };
};
