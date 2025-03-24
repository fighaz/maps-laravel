<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mining Areas Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        #map {
            width: 100%;
            height: 100vh;
        }

        .legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .legend-item {
            margin-bottom: 5px;
        }

        .legend-color {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        .popup-content {
            max-height: 300px;
            overflow-y: auto;
        }

        .popup-table {
            border-collapse: collapse;
            width: 100%;
        }

        .popup-table th,
        .popup-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .popup-table th {
            background-color: #f2f2f2;
        }

        .layer-control {
            margin: 10px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize map centered on Indonesia
            const map = L.map('map').setView([-2.5, 118], 5);

            // Add base layers
            const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });

            // Layer groups
            const wiupLayer = L.layerGroup().addTo(map);
            const batasLautLayer = L.layerGroup().addTo(map);
            const wilayahTambangLayer = L.layerGroup().addTo(map);

            // Base maps for layer control
            const baseMaps = {
                "OpenStreetMap": osm,
                "Satellite": satellite
            };

            // Overlay maps for layer control
            const overlayMaps = {
                "WIUP (Mining Permits)": wiupLayer,
                "Sea Boundaries": batasLautLayer,
                "Mining Areas": wilayahTambangLayer
            };

            // Add layer control
            L.control.layers(baseMaps, overlayMaps).addTo(map);

            // Function to create popup content
            function createPopupContent(properties) {
                let content = '<div class="popup-content"><table class="popup-table">';

                for (const key in properties) {
                    // Skip some internal fields if needed
                    if (key.startsWith('st_') || key === 'objectid') continue;

                    // Format date fields
                    let value = properties[key];
                    if (typeof value === 'number' && key.toLowerCase().includes('date') && value > 0) {
                        value = new Date(value).toLocaleDateString();
                    }

                    content += `<tr><th>${key}</th><td>${value || '-'}</td></tr>`;
                }

                content += '</table></div>';
                return content;
            }

            // Fetch and display WIUP data
            fetch('/get-wiup-data')
                .then(response => response.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: function () {
                            return {
                                color: '#ff7800',
                                weight: 1,
                                opacity: 0.8,
                                fillOpacity: 0.4
                            };
                        },
                        onEachFeature: function (feature, layer) {
                            const popupContent = createPopupContent(feature.properties);
                            layer.bindPopup(popupContent, { maxWidth: 400 });

                            // Highlight on hover
                            layer.on({
                                mouseover: function (e) {
                                    const layer = e.target;
                                    layer.setStyle({
                                        weight: 3,
                                        color: '#f00',
                                        fillOpacity: 0.7
                                    });
                                },
                                mouseout: function (e) {
                                    const layer = e.target;
                                    layer.setStyle({
                                        weight: 1,
                                        color: '#ff7800',
                                        fillOpacity: 0.4
                                    });
                                }
                            });
                        }
                    }).addTo(wiupLayer);
                });

            // Fetch and display Sea Boundary data
            fetch('/get-batas-laut-data')
                .then(response => response.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: function () {
                            return {
                                color: '#0078ff',
                                weight: 3,
                                opacity: 0.8
                            };
                        },
                        onEachFeature: function (feature, layer) {
                            const popupContent = createPopupContent(feature.properties);
                            layer.bindPopup(popupContent, { maxWidth: 400 });
                        }
                    }).addTo(batasLautLayer);
                });

            // Fetch and display Mining Area data
            fetch('/get-wilayah-tambang-data')
                .then(response => response.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: function () {
                            return {
                                color: '#00aa00',
                                weight: 1,
                                opacity: 0.8,
                                fillOpacity: 0.4
                            };
                        },
                        onEachFeature: function (feature, layer) {
                            const popupContent = createPopupContent(feature.properties);
                            layer.bindPopup(popupContent, { maxWidth: 400 });

                            // Highlight on hover
                            layer.on({
                                mouseover: function (e) {
                                    const layer = e.target;
                                    layer.setStyle({
                                        weight: 3,
                                        color: '#0a0',
                                        fillOpacity: 0.7
                                    });
                                },
                                mouseout: function (e) {
                                    const layer = e.target;
                                    layer.setStyle({
                                        weight: 1,
                                        color: '#00aa00',
                                        fillOpacity: 0.4
                                    });
                                }
                            });
                        }
                    }).addTo(wilayahTambangLayer);
                });

            // Add legend
            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function () {
                const div = L.DomUtil.create('div', 'legend');
                div.innerHTML = `
                    <div class="legend-title"><strong>Legend</strong></div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #ff7800;"></span> Mining Permits (WIUP)
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #0078ff;"></span> Sea Boundaries
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #00aa00;"></span> Mining Areas
                    </div>
                `;
                return div;
            };
            legend.addTo(map);
        });
    </script>
</body>

</html>