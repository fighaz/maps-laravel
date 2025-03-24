<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Wilayah Pertambangan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        #map {
            height: 600px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .info-popup {
            max-width: 350px;
        }

        .info-popup table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-popup th,
        .info-popup td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .info-popup tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .legend {
            padding: 8px 10px;
            background: white;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            line-height: 24px;
            color: #333;
        }

        .legend h4 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .legend i {
            width: 24px;
            height: 24px;
            float: left;
            margin-right: 8px;
            opacity: 0.8;
            border: 1px solid #999;
        }

        .info-control {
            padding: 8px 10px;
            background: white;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            max-width: 250px;
        }

        #layer-control {
            margin-bottom: 15px;
            text-align: center;
        }

        .control-button {
            padding: 8px 12px;
            margin: 0 5px;
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }

        .control-button.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <h1>Peta Wilayah Pertambangan Indonesia</h1>

    <div id="layer-control">
        <button id="all-btn" class="control-button active">Semua Layer</button>
        <button id="wiup-btn" class="control-button">WIUP</button>
        <button id="wp-btn" class="control-button">Wilayah Pertambangan</button>
    </div>

    <div id="map"></div>

    <script>
        // Initialize the map centered on Indonesia
        var map = L.map('map').setView([-2.5489, 118.0149], 5);

        // Add base map layers with options
        var baseMaps = {
            "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }),
            "Satellite": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 19
            }),
            "Terrain": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community',
                maxZoom: 19
            })
        };

        // Set default base map
        baseMaps.OpenStreetMap.addTo(map);

        // Add layer control
        L.control.layers(baseMaps).addTo(map);

        // Create layer groups for different data
        var wiupLayer = L.layerGroup().addTo(map);
        var wpLayer = L.layerGroup().addTo(map);

        // Define different colors for different data sources
        const colors = {
            wiup: '#3388ff',
            wilayahPertambangan: '#ff7800'
        };

        // Add a legend
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function (map) {
            const div = L.DomUtil.create('div', 'legend');
            div.innerHTML =
                '<h4>Legenda</h4>' +
                '<i style="background:' + colors.wiup + '"></i> WIUP (Wilayah Izin Usaha Pertambangan)<br>' +
                '<i style="background:' + colors.wilayahPertambangan + '"></i> Wilayah Pertambangan<br>';
            return div;
        };
        legend.addTo(map);

        // // Add info control
        // const info = L.control({ position: 'topleft' });
        // info.onAdd = function (map) {
        //     const div = L.DomUtil.create('div', 'info-control');
        //     div.innerHTML = '<h4>Informasi Wilayah</h4>' +
        //         '<p>Klik pada wilayah pertambangan atau marker untuk melihat informasi detail.</p>' +
        //         '<p>Zoom masuk untuk melihat detail lebih jelas.</p>';
        //     return div;
        // };
        // info.addTo(map);

        // Load WIUP data
        fetch('/api/wiup')
            .then(response => response.json())
            .then(data => {
                displayWIUPData(data);
            })
            .catch(error => {
                console.error('Error loading WIUP data:', error);
            });

        // Load Wilayah Pertambangan data
        fetch('/api/wilayahpertambangan')
            .then(response => response.json())
            .then(data => {
                displayWilayahPertambanganData(data);
            })
            .catch(error => {
                console.error('Error loading Wilayah Pertambangan data:', error);
            });

        // Function to display WIUP polygons on the map
        function displayWIUPData(data) {
            if (data && data.features) {
                data.features.forEach(feature => {
                    if (feature.geometry && feature.geometry.rings) {
                        // Convert ESRI rings to Leaflet latlngs
                        const polygonCoords = feature.geometry.rings.map(ring => {
                            return ring.map(coord => {
                                // Convert Web Mercator (EPSG:3857) to WGS84 (EPSG:4326)
                                const lng = coord[0] * 180 / 20037508.34;
                                let lat = coord[1] * 180 / 20037508.34;
                                lat = 180 / Math.PI * (2 * Math.atan(Math.exp(lat * Math.PI / 180)) - Math.PI / 2);
                                return [lat, lng];
                            });
                        });

                        // Create polygon with styled appearance
                        const polygon = L.polygon(polygonCoords, {
                            color: '#1a237e',
                            fillColor: colors.wiup,
                            fillOpacity: 0.6,
                            weight: 2,
                            dashArray: '',
                            opacity: 1
                        }).addTo(wiupLayer);

                        // Create popup content with attributes
                        const popupContent = createWIUPPopupContent(feature.attributes);

                        // Bind popup to polygon
                        polygon.bindPopup(popupContent, {
                            maxWidth: 400,
                            className: 'info-popup'
                        });

                        // Highlight on hover
                        polygon.on('mouseover', function (e) {
                            this.setStyle({
                                weight: 4,
                                color: '#0000ff',
                                dashArray: '',
                                fillOpacity: 0.8
                            });
                            if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                                this.bringToFront();
                            }
                        });

                        polygon.on('mouseout', function (e) {
                            this.setStyle({
                                weight: 2,
                                color: '#1a237e',
                                dashArray: '',
                                fillOpacity: 0.6
                            });
                        });

                        // Add a marker at the center of the polygon
                        if (polygonCoords.length > 0 && polygonCoords[0].length > 0) {
                            // Calculate center of the polygon
                            let centerLat = 0, centerLng = 0;
                            let points = polygonCoords[0];
                            for (let i = 0; i < points.length; i++) {
                                centerLat += points[i][0];
                                centerLng += points[i][1];
                            }
                            centerLat /= points.length;
                            centerLng /= points.length;

                            // Create a custom icon for WIUP
                            const wiupIcon = L.divIcon({
                                html: '<div style="background-color:#3388ff; width:10px; height:10px; border-radius:50%; border:2px solid white;"></div>',
                                className: 'wiup-marker',
                                iconSize: [14, 14],
                                iconAnchor: [7, 7]
                            });

                            // Add marker with custom icon
                            const marker = L.marker([centerLat, centerLng], {
                                icon: wiupIcon
                            }).addTo(wiupLayer);

                            // Bind the same popup to the marker
                            marker.bindPopup(popupContent, {
                                maxWidth: 400,
                                className: 'info-popup'
                            });
                        }
                    }
                });
            }
        }

        // Function to display Wilayah Pertambangan polygons on the map
        function displayWilayahPertambanganData(data) {
            if (data && data.features) {
                const bounds = [];

                data.features.forEach(feature => {
                    if (feature.geometry && feature.geometry.rings) {
                        // Convert ESRI rings to Leaflet latlngs
                        const polygonCoords = feature.geometry.rings.map(ring => {
                            return ring.map(coord => {
                                // Convert Web Mercator (EPSG:3857) to WGS84 (EPSG:4326)
                                const lng = coord[0] * 180 / 20037508.34;
                                let lat = coord[1] * 180 / 20037508.34;
                                lat = 180 / Math.PI * (2 * Math.atan(Math.exp(lat * Math.PI / 180)) - Math.PI / 2);
                                bounds.push([lat, lng]);
                                return [lat, lng];
                            });
                        });

                        // Create polygon with enhanced styling
                        const polygon = L.polygon(polygonCoords, {
                            color: '#e65100', // darker border
                            fillColor: colors.wilayahPertambangan,
                            fillOpacity: 0.7,
                            weight: 3,
                            dashArray: '5,5', // dashed line for distinction
                            opacity: 1
                        }).addTo(wpLayer);

                        // Create popup content with attributes
                        const popupContent = createWilayahPertambanganPopupContent(feature.attributes);

                        // Bind popup to polygon
                        polygon.bindPopup(popupContent, {
                            maxWidth: 400,
                            className: 'info-popup'
                        });

                        // Highlight on hover
                        polygon.on('mouseover', function (e) {
                            this.setStyle({
                                weight: 5,
                                color: '#ff0000',
                                dashArray: '',
                                fillOpacity: 0.8
                            });
                            if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                                this.bringToFront();
                            }
                        });

                        polygon.on('mouseout', function (e) {
                            this.setStyle({
                                color: '#e65100',
                                weight: 3,
                                dashArray: '5,5',
                                fillOpacity: 0.7
                            });
                        });

                        // Add a marker at the center of the polygon
                        if (polygonCoords.length > 0 && polygonCoords[0].length > 0) {
                            // Calculate center of the polygon
                            let centerLat = 0, centerLng = 0;
                            let points = polygonCoords[0];
                            for (let i = 0; i < points.length; i++) {
                                centerLat += points[i][0];
                                centerLng += points[i][1];
                            }
                            centerLat /= points.length;
                            centerLng /= points.length;

                            // Create a custom icon for WP
                            const wpIcon = L.divIcon({
                                html: '<div style="background-color:#ff7800; width:12px; height:12px; border-radius:50%; border:2px solid white;"></div>',
                                className: 'wp-marker',
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });

                            // Add marker with custom icon
                            const marker = L.marker([centerLat, centerLng], {
                                icon: wpIcon
                            }).addTo(wpLayer);

                            // Bind the same popup to the marker
                            marker.bindPopup(popupContent, {
                                maxWidth: 400,
                                className: 'info-popup'
                            });
                        }
                    }
                });

                // Fit map to bounds if there are features
                if (bounds.length > 0) {
                    map.fitBounds(bounds);
                }
            }
        }

        // Function to create popup content for WIUP data
        function createWIUPPopupContent(attributes) {
            let content = '<div class="info-popup"><table>';
            content += '<tr><th colspan="2" style="text-align:center; background-color:#3388ff; color:white; padding:10px;">' +
                (attributes.nama_usaha || 'WIUP') + '</th></tr>';

            // Add important fields first
            const importantFields = [
                { key: 'kode_wiup', label: 'Single ID' },
                { key: 'jenis_izin', label: 'Jenis Izin' },
                { key: 'komoditas', label: 'Komoditas' },
                { key: 'kegiatan', label: 'Tahapan Kegiatan' },
                { key: 'nama_prov', label: 'Provinsi' },
                { key: 'nama_kab', label: 'Kabupaten' },
                { key: 'lokasi', label: 'Lokasi Tambang' },
                { key: 'luas_sk', label: 'Luas Wilayah (Ha)' },
                { key: 'sk_iup', label: 'Nomor SK' }
            ];

            importantFields.forEach(field => {
                if (attributes[field.key]) {
                    content += '<tr><td><strong>' + field.label + '</strong></td><td>' +
                        (field.key === 'luas_sk' ? attributes[field.key].toLocaleString() : attributes[field.key]) +
                        '</td></tr>';
                }
            });

            // Add dates with proper formatting
            if (attributes.tgl_berlaku) {
                const tglBerlaku = new Date(attributes.tgl_berlaku);
                content += '<tr><td><strong>Tanggal Berlaku</strong></td><td>' +
                    tglBerlaku.toLocaleDateString('id-ID') + '</td></tr>';
            }

            if (attributes.tgl_akhir) {
                const tglAkhir = new Date(attributes.tgl_akhir);
                content += '<tr><td><strong>Tanggal Berakhir</strong></td><td>' +
                    tglAkhir.toLocaleDateString('id-ID') + '</td></tr>';
            }

            content += '</table></div>';
            return content;
        }

        // Function to create popup content for Wilayah Pertambangan data
        function createWilayahPertambanganPopupContent(attributes) {
            let content = '<div class="info-popup"><table>';
            content += '<tr><th colspan="2" style="text-align:center; background-color:#ff7800; color:white; padding:10px;">' +
                'Wilayah Pertambangan' + '</th></tr>';

            // Add fields for Wilayah Pertambangan
            const fields = [
                { key: 'objectid', label: 'Object ID' },
                { key: 'region', label: 'Region' },
                { key: 'wilayah', label: 'Wilayah' },
                { key: 'provinsi', label: 'Provinsi' },
                { key: 'lokasi', label: 'Lokasi' },
                { key: 'luas_ha', label: 'Luas (Ha)' },
                { key: 'remark', label: 'Keterangan' }
            ];

            fields.forEach(field => {
                if (attributes[field.key] !== undefined && attributes[field.key] !== null) {
                    content += '<tr><td><strong>' + field.label + '</strong></td><td>' +
                        (field.key === 'luas_ha' ? attributes[field.key].toLocaleString() : attributes[field.key]) +
                        '</td></tr>';
                }
            });

            // Add dates with proper formatting
            if (attributes.created_date && attributes.created_date !== null) {
                const createdDate = new Date(attributes.created_date);
                content += '<tr><td><strong>Tanggal Dibuat</strong></td><td>' +
                    createdDate.toLocaleDateString('id-ID') + '</td></tr>';
            }

            if (attributes.last_edited_date && attributes.last_edited_date !== null) {
                const editedDate = new Date(attributes.last_edited_date);
                content += '<tr><td><strong>Tanggal Diedit</strong></td><td>' +
                    editedDate.toLocaleDateString('id-ID') + '</td></tr>';
            }

            content += '</table></div>';
            return content;
        }

        // Layer control buttons
        document.getElementById('all-btn').addEventListener('click', function () {
            map.addLayer(wiupLayer);
            map.addLayer(wpLayer);
            setActiveButton('all-btn');
        });

        document.getElementById('wiup-btn').addEventListener('click', function () {
            map.addLayer(wiupLayer);
            map.removeLayer(wpLayer);
            setActiveButton('wiup-btn');
        });

        document.getElementById('wp-btn').addEventListener('click', function () {
            map.removeLayer(wiupLayer);
            map.addLayer(wpLayer);
            setActiveButton('wp-btn');
        });

        function setActiveButton(id) {
            document.querySelectorAll('.control-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(id).classList.add('active');
        }

    </script>
</body>

</html>