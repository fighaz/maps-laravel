<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Mapbox</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.css" rel="stylesheet" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>

<body>
    <h1>Peta dengan Mapbox</h1>
    <div id="map"></div>

    <script>
        mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [118.0149, -2.5489],
            zoom: 5
        });

        fetch('/api/wilayahpertambangan')
            .then(response => response.json())
            .then(data => {
                map.on('load', function () {
                    map.addSource('wilayah-pertambangan', {
                        'type': 'geojson',
                        'data': data
                    });

                    map.addLayer({
                        'id': 'wilayah-layer',
                        'type': 'fill',
                        'source': 'wilayah-pertambangan',
                        'layout': {},
                        'paint': {
                            'fill-color': '#ff0000',
                            'fill-opacity': 0.5
                        }
                    });
                });
            });
    </script>
</body>

</html>