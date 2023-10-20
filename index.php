<!DOCTYPE html>
<html lang="en">
<head>
<base target="_top">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visualize Earthquake Data</title>
    <link rel="shortcut icon" type="image/x-icon" href="docs/images/favicon.ico" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header>
        <div class="profile-container">
            <div class="profile-logo">
                <img src="./assets/img/header-bbmkg1.png" alt="Profile Logo">
            </div>
            <div class="profile-text">
                <h1 class="h1">Earthquake Data</h1>
            </div>
        </div>
    </header>

    <div id="container">
        <div id="map-area"> 
            <div id="map" aria-label="Leaflet Map" role="application"></div>
        </div>

        <div id="search-area" >
            <form id="search-form">
                <input type="search" id="search-input" aria-label="Search for earthquakes">
                <button type="submit">Search</button>
            </form>
            <div id="magnitude-filter">
                <label for="magnitude-select">
                    <img src="https://cdn2.iconfinder.com/data/icons/font-awesome/1792/filter-512.png" alt="Filter Icon" style="vertical-align: middle; margin-right: 5px;">
                </label>
                <select id="magnitude-select">
                    <option value="">All</option>
                    <option value="2.5-5.4">2.5 - 5.4</option>
                    <option value="5.5-6.0">5.5 - 6.0</option>
                    <option value="6.1-6.9">6.1 - 6.9</option>
                    <option value="7.0-7.9"> >7 </option>
                </select>
            </div>
            <div id="location-list">
                <table id="locations">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mag</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- This table body will be populated dynamically based on search results -->
                    </tbody>
                </table>
            </div>
            <br>
            <div id="chart-area">
                <canvas id="earthquake-chart"></canvas>
            </div>
        </div>
    </div>

    <script>
        var map = L.map('map').setView([0.7932, 101.3317], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var markers = [];
        var allMarkers = [];

        <?php
        // Koneksi ke database
        $host = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gempa";

        $conn = new mysqli($host, $username, $password, $dbname);

        // Cek koneksi
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }

        // Ambil data gempa dari database
        $sql = "SELECT ot, lat, lon, mag, dept, loc FROM gempa"; // Remove 'dept' from the query
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Create map circles
                echo "var circle = L.circle([" . $row['lat'] . ", " . $row['lon'] . "], {
                    color: 'green',
                    fillColor: 'green',
                    fillOpacity: 0.5,
                    radius: " . 10000 . ",
                    time: '" . $row['ot'] . "'
                }).addTo(map).bindPopup('Magnitude: " . $row['mag'] . "<br>Depth: " . $row['dept'] . "<br>Location: " . $row['loc'] . "');";
                echo "allMarkers.push(circle);"; // Store all circles in the 'allMarkers' array

                // Populate the table dynamically
                echo "var tableRow = document.createElement('tr');";
                echo "var dateTimeCell = document.createElement('td');";
                echo "dateTimeCell.textContent = '" . $row['ot'] . "';";
                echo "tableRow.appendChild(dateTimeCell);";

                echo "var magnitudeCell = document.createElement('td');";
                echo "magnitudeCell.textContent = '" . $row['mag'] . "';";
                echo "tableRow.appendChild(magnitudeCell);";

                echo "var locationCell = document.createElement('td');";
                echo "locationCell.textContent = '" . $row['loc'] . "';";
                echo "tableRow.appendChild(locationCell);";

                echo "document.querySelector('#locations tbody').appendChild(tableRow);";
            }
        } else {
            echo "Tidak ada data gempa.";
        }
        
        // Collect earthquake data and count earthquakes per year
        $earthquakeData = [];
        $sql = "SELECT ot FROM gempa";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $dateTime = new DateTime($row['ot']);
                $year = $dateTime->format('Y');
                if (!isset($earthquakeData[$year])) {
                    $earthquakeData[$year] = 0;
                }
                $earthquakeData[$year]++;
            }
        }

        $conn->close();
        ?>

        function removeLineBreaks(str) {
            return str.replace(/<br>/g, ' ');
        }

        //Fitur Search
        document.getElementById('search-input').addEventListener('input', function() {
            var searchText = this.value.toLowerCase();
            var locationTable = document.getElementById('locations').getElementsByTagName('tbody')[0]; // Get the table body

            // Clear the existing table rows
            while (locationTable.firstChild) {
                locationTable.removeChild(locationTable.firstChild);
            }

            allMarkers.forEach(function(circle) {
                var popupContent = removeLineBreaks(circle.getPopup().getContent()).toLowerCase();

                // Check if the popup content contains the search text
                if (popupContent.includes(searchText)) {
                    
                    // Create a new row at the end of the table
                    var tableRow = locationTable.insertRow(-1); 

                    var magnitudeMatch = popupContent.match(/magnitude: ([\d.]+)/); //extract magnitude
                    var locationMatch = popupContent.match(/location: (.+)/); // extract location

                    // Date is extracted from circle.options.time attribute
                    var dateTime = circle.options.time;

                    // If the matches exist, populate the table
                    if (magnitudeMatch && locationMatch) {
                        var magnitude = magnitudeMatch[1];
                        var location = locationMatch[1];

                        var dateCell = tableRow.insertCell(0);
                        dateCell.textContent = dateTime;

                        var magnitudeCell = tableRow.insertCell(1);
                        magnitudeCell.textContent = magnitude;

                        var locationCell = tableRow.insertCell(2);
                        locationCell.textContent = location;

                        circle.addTo(map);
                    } 
                } else {
                    map.removeLayer(circle); // removing the circle from map
                }
            });
        });

        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
        });

        //Filter Magnitude
        document.getElementById('magnitude-select').addEventListener('change', function() {
            var selectedMagnitudeRange = this.value;
            if (selectedMagnitudeRange === "All") {
                showAllDataInTable(); // Call the function to show all data
            } else {
                filterCirclesByMagnitudeRange(selectedMagnitudeRange); // Apply filtering for other options
            }
        });

        function filterCirclesByMagnitudeRange(magnitudeRange) {
            // Clear the existing table rows in the table body
            var locationTable = document.getElementById('locations').getElementsByTagName('tbody')[0];
            while (locationTable.firstChild) {
                locationTable.removeChild(locationTable.firstChild);
            }

            allMarkers.forEach(function(circle) {
                var popupContent = circle.getPopup().getContent();
                var popupMagnitude = parseFloat(popupContent.match(/Magnitude: ([\d.]+)/)[1]);

                if (isNaN(popupMagnitude)) {
                    return;
                }

                var magnitudeRangeParts = magnitudeRange.split('-');
                var magnitudeRangeStart = parseFloat(magnitudeRangeParts[0]);
                var magnitudeRangeEnd = parseFloat(magnitudeRangeParts[1]);

                if (
                    !isNaN(magnitudeRangeStart) && !isNaN(magnitudeRangeEnd) &&
                    popupMagnitude >= magnitudeRangeStart && popupMagnitude <= magnitudeRangeEnd
                ) {
                    circle.addTo(map);

                    // Populate the table with matching earthquake data
                    var tableRow = locationTable.insertRow(-1);

                    //Date is extracted from circle.options.time attribute
                    var dateTime = circle.options.time;

                    var dateTimeCell = document.createElement('td');
                    dateTimeCell.textContent = dateTime;
                    tableRow.appendChild(dateTimeCell);

                    var magnitudeCell = document.createElement('td');
                    magnitudeCell.textContent = popupMagnitude;
                    tableRow.appendChild(magnitudeCell);

                    var locationCell = document.createElement('td');
                    locationCell.textContent = popupContent.match(/Location: (.+)/)[1];
                    tableRow.appendChild(locationCell);
                } else {
                    map.removeLayer(circle);
                }
            });
        }

        function showAllDataInTable() {
            var locationTable = document.getElementById('locations').getElementsByTagName('tbody')[0];
            while (locationTable.firstChild) {
                locationTable.removeChild(locationTable.firstChild);
            }

            allMarkers.forEach(function(circle) {
                var popupContent = removeLineBreaks(circle.getPopup().getContent()).toLowerCase();

                var tableRow = locationTable.insertRow(-1);
                var magnitudeMatch = popupContent.match(/magnitude: ([\d.]+)/); //extract magnitude
                var locationMatch = popupContent.match(/location: (.+)/); // extract location

                var dateTime = circle.options.time;

                if (magnitudeMatch && locationMatch) {
                    var magnitude = magnitudeMatch[1];
                    var location = locationMatch[1];

                    var dateCell = tableRow.insertCell(0);
                    dateCell.textContent = dateTime;

                    var magnitudeCell = tableRow.insertCell(1);
                    magnitudeCell.textContent = magnitude;

                    var locationCell = tableRow.insertCell(2);
                    locationCell.textContent = location;
                }
            });
        }



        // Generate earthquake count data for the chart
        var earthquakeYears = Object.keys(<?php echo json_encode($earthquakeData); ?>);
        var earthquakeCounts = Object.values(<?php echo json_encode($earthquakeData); ?>);

        // Create a bar chart using Chart.js
        var ctx = document.getElementById('earthquake-chart').getContext('2d');
        var earthquakeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: earthquakeYears,
                datasets: [{
                    label: 'Earthquakes per Year',
                    data: earthquakeCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    </script>
</body>
</html>
