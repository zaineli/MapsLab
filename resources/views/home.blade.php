<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home-GeoCityLite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</head>

<body class="bg-gradient-to-br from-green-400 to-cyan-500 min-h-screen flex items-center justify-center">
    <div class="container mx-auto p-6 bg-white rounded-lg shadow-xl">
        <header class="text-center mb-10">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-cyan-600">
                My Maps
            </h1>
            <p class="mt-4 text-lg text-gray-600">
                This is a simple web application that allows you to view the 5 closest cities to a selected city.
                You can select a city from the dropdown or click on the map to view the 5 closest cities to the selected city.
                The cities are displayed in a table below the map.
            </p>
        </header>

        <!-- Cities Dropdown Selection -->
        <div class="mb-10">
            <label for="city-select" class="block text-lg font-medium text-gray-700 mb-2 text-center">Select City:</label>
            <div class="flex justify-center">
                <select name="city-select" id="city-select" class="w-1/2 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-cyan-500 focus:border-cyan-500">
                    @foreach ($all_cities as $city)
                    <option value="{{ $city->city }}">{{ $city->city }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="map" class="h-96 mb-10 bg-gray-200 rounded-lg shadow-inner">
            <!-- Map will be rendered here -->
        </div>

        <div id="cities">
            <h2 class="text-3xl font-bold text-center mb-6 text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-cyan-600">
                5 Closest Cities
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-green-500 to-cyan-600 text-white">
                            <th class="border px-4 py-2">City</th>
                            <th class="border px-4 py-2">Country</th>
                            <th class="border px-4 py-2">Distance (KM)</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


        <!-- scripts -->
        <script>
            var map = L.map('map').setView([30.37, 69.34], 6);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var popup = L.popup();

            $(document).ready(function() {
                $('#ajaxSubmit').click(function(e) {
                    e.preventDefault();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: '/get-cities',
                        data: {
                            city: $('#city').val(),
                        },
                        success: function(data) {
                            if (data.status === true) {
                                var city = data.city;

                                map.remove();
                                map = L.map('map').setView([city.latitude, city.longitude], 6);
                                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);
                                map.on('click', onMapClick);

                                var cities = data.cities;
                                table = document.getElementById('table-body');
                                table.innerHTML = '';

                                for (var i = 0; i < cities.length; i++) {
                                    if (cities[i].city.city === city.city) {
                                        map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                        popup
                                            .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                            .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                            .openOn(map);
                                        continue;
                                    }

                                    let row = table.insertRow(-1);
                                    let cell1 = row.insertCell(0);
                                    let cell2 = row.insertCell(1);
                                    let cell3 = row.insertCell(2);
                                    cell1.innerHTML = cities[i].city.city;
                                    cell2.innerHTML = cities[i].city.country;
                                    cell3.innerHTML = cities[i].distance;

                                    var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                                }
                            } else {
                                alert('No cities found!');
                            }
                        }
                    });
                });
            });

            function onMapClick(e) {
                popup
                    .setLatLng(e.latlng)
                    .setContent("You clicked the map at " + e.latlng.toString())
                    .openOn(map);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '/get-cities-from-map',
                    data: {
                        lat: e.latlng.lat,
                        lon: e.latlng.lng,
                    },
                    success: function(data) {
                        if (data.status === true) {
                            var city = data.city;


                            map.remove();
                            map = L.map('map').setView([city.latitude, city.longitude], 6);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                            map.on('click', onMapClick);

                            var cities = data.cities;
                            table = document.getElementById('table-body');
                            table.innerHTML = '';



                            for (var i = 0; i < cities.length; i++) {
                                if (cities[i].city.city === city.city) {
                                    map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                    popup
                                        .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = table.insertRow(-1);
                                let cell1 = row.insertCell(0);
                                let cell2 = row.insertCell(1);
                                let cell3 = row.insertCell(2);
                                cell1.innerHTML = cities[i].city.city;
                                cell2.innerHTML = cities[i].city.country;
                                cell3.innerHTML = cities[i].distance;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                            }
                        } else {
                            alert('No cities found!');
                        }
                    }
                });

            }

            map.on('click', onMapClick);

            var city_selected = '';

            var select_element = document.getElementById("city-select");
            var value = select_element.value;
            var text = select_element.options[select_element.selectedIndex].text;
            city_selected = text;

            select_element.addEventListener('change', function() {
                value = select_element.value;
                text = select_element.options[select_element.selectedIndex].text;
                city_selected = text;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '/get-cities-through-select',
                    data: {
                        city: city_selected,
                    },
                    success: function(data) {
                        if (data.status === true) {
                            var city = data.city;


                            map.remove();
                            map = L.map('map').setView([city.latitude, city.longitude], 6);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                            map.on('click', onMapClick);

                            var cities = data.cities;
                            table = document.getElementById('table-body');
                            table.innerHTML = '';



                            for (var i = 0; i < cities.length; i++) {
                                if (cities[i].city.city === city.city) {
                                    map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                    popup
                                        .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = table.insertRow(-1);
                                let cell1 = row.insertCell(0);
                                let cell2 = row.insertCell(1);
                                let cell3 = row.insertCell(2);
                                cell1.innerHTML = cities[i].city.city;
                                cell2.innerHTML = cities[i].city.country;
                                cell3.innerHTML = cities[i].distance;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                            }
                        } else {
                            alert('No cities found!');
                        }
                    }
                });
            });
        </script>

</body>

</html>