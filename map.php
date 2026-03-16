<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Map with Enhanced Search and Filter UI</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/map.css"> <!-- Custom map styles -->
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- General styles -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch/dist/geosearch.css" />
    <style>
        /* Fullscreen map styling */
        #map {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%; /* Full height of the page */
            width: 100%; /* Full width of the page */
            z-index: 1; /* Keep the map behind other content */
        }

        /* Move map zoom controls to bottom-left */
        .leaflet-left .leaflet-control {
            left: 20px; /* Keep them on the left */
            bottom: -500px !important; /* Move them to the bottom */
            top: auto !important; /* Remove top positioning */
        }

        /* Style for filter controls */
        .filter-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            background-color: #f1f1f1; /* Light grey background */
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease-in-out;
            font-family: Arial, sans-serif;
        }

        .filter-controls label {
            margin-bottom: 10px;
            display: block;
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }

        .filter-controls input[type="checkbox"] {
            margin-right: 10px;
        }

        /* Style for the search bar */
        .search-container {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            align-items: center;
            width: 300px;
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .search-container input {
            width: 100%;
            border: none;
            outline: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 25px;
        }

        .search-container input::placeholder {
            color: #aaa;
        }

        .search-container button {
            border: none;
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #0056b3;
        }

        /* Autocomplete dropdown styling */
        .autocomplete-dropdown {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .autocomplete-dropdown div {
            padding: 10px;
            cursor: pointer;
        }

        .autocomplete-dropdown div:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<?php include 'components/header.php'; ?> <!-- Keep the header that contains the sidebar -->

<!-- Filter controls -->
<div class="filter-controls">
    <label><input type="checkbox" id="riverProjects" checked> River</label>
    <label><input type="checkbox" id="tourismProjects" checked> Tourism Projects</label>
    <label><input type="checkbox" id="cleaningProjects" checked> Cleaning Projects</label>
    <label><input type="checkbox" id="damProjects" checked> Dam</label>
    <label><input type="checkbox" id="riverDevelopmentProjects" checked> River Development Projects</label>
    <label><input type="checkbox" id="weatherStations" checked> Weather Stations</label>
</div>

<!-- Search bar for marker locations -->
<div class="search-container">
    <input type="text" id="searchInput" placeholder="Search for a project or location..." oninput="showSuggestions()">
    <button onclick="searchMarkers()">&#128269;</button>
    <div id="autocompleteDropdown" class="autocomplete-dropdown" style="display: none;"></div>
</div>

<!-- Map as background -->
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-geosearch/dist/geosearch.umd.js"></script>
<script>
// Initialize the map and set view
var map = L.map('map').setView([20.5937, 78.9629], 5); // Center of India

// Set the maximum bounds to restrict the zoom and pan to India
var southWest = L.latLng(6.4627, 68.1097); // Approximate coordinates for southwest India
var northEast = L.latLng(35.5133, 97.3956); // Approximate coordinates for northeast India
var bounds = L.latLngBounds(southWest, northEast);
map.setMaxBounds(bounds);
map.on('drag', function() {
    map.panInsideBounds(bounds, { animate: false });
});

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    minZoom: 5, // Prevent zooming out beyond this level
    attribution: '© OpenStreetMap'
}).addTo(map);

// Define custom icons for different categories with reduced size
var icons = {
    river: L.icon({ iconUrl: 'assets/icons/river-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
    tourism: L.icon({ iconUrl: 'assets/icons/tourism-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
    cleaning: L.icon({ iconUrl: 'assets/icons/cleaning-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
    dam: L.icon({ iconUrl: 'assets/icons/dam-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
    riverDev: L.icon({ iconUrl: 'assets/icons/river-dev-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
    weather: L.icon({ iconUrl: 'assets/icons/weather-icon.png', iconSize: [32, 32], iconAnchor: [15, 30], popupAnchor: [1, -20] }),
};

// Define marker cluster group and markers array
var markersCluster = L.markerClusterGroup();
var allMarkers = []; 

// Define markers for different categories
// River Projects Markers
var riverProjectsMarkers = [
    L.marker([30.9875, 79.0934], { icon: icons.river }).bindPopup('Ganga River - Length: 2525 km - Source: Gaumukh, Uttarakhand - End: Bay of Bengal, West Bengal'),
    L.marker([32.4910, 79.0469], { icon: icons.river }).bindPopup('Indus River - Length: 3180 km - Source: Tibet Plateau, China - End: Arabian Sea, Pakistan'),
    L.marker([22.6820, 81.7543], { icon: icons.river }).bindPopup('Narmada River - Length: 1312 km - Source: Amarkantak Plateau, Madhya Pradesh - End: Gulf of Khambhat, Gujarat'),
    L.marker([29.9792, 91.5322], { icon: icons.river }).bindPopup('Brahmaputra River - Length: 2900 km - Source: Tibet, China - End: Bay of Bengal, Bangladesh')
];

// Tourism Projects Markers
var tourismProjectsMarkers = [
    L.marker([29.9457, 78.1642], { icon: icons.tourism }).bindPopup('Tehri Lake Eco-Tourism - Budget: $120 million - Type: Eco-Tourism'),
    L.marker([24.7995, 87.9316], { icon: icons.tourism }).bindPopup('Farakka Barrage Tour - Budget: $80 million - Type: Heritage Tour'),
    L.marker([25.3176, 82.9739], { icon: icons.tourism }).bindPopup('Varanasi Ghat Tourism - Budget: $200 million - Type: Cultural Tourism'),
    L.marker([25.4358, 81.8463], { icon: icons.tourism }).bindPopup('Allahabad Kumbh Mela Preparation - Budget: $300 million - Type: Religious Tourism'),
    L.marker([34.0947, 72.6834], { icon: icons.tourism }).bindPopup('Tarbela Eco-Tourism - Budget: $150 million - Type: Eco-Tourism'),
    L.marker([27.7059, 68.8574], { icon: icons.tourism }).bindPopup('Sukkur Barrage Tour - Budget: $70 million - Type: Heritage Tour'),
    L.marker([24.8607, 67.0011], { icon: icons.tourism }).bindPopup('Karachi Riverfront Development - Budget: $200 million - Type: Tourism and Beautification'),
    L.marker([33.6844, 73.0479], { icon: icons.tourism }).bindPopup('Islamabad River Tourism - Budget: $120 million - Type: Cultural Tourism'),
    L.marker([21.8707, 73.5021], { icon: icons.tourism }).bindPopup('Sardar Sarovar Eco-Tourism - Budget: $120 million - Type: Eco-Tourism'),
    L.marker([23.1815, 79.9864], { icon: icons.tourism }).bindPopup('Bargi Dam Tour - Budget: $80 million - Type: Heritage Tour'),
    L.marker([23.1815, 79.9864], { icon: icons.tourism }).bindPopup('Jabalpur Riverfront Development - Budget: $150 million - Type: Tourism and Beautification'),
    L.marker([22.7196, 75.8577], { icon: icons.tourism }).bindPopup('Indore River Tourism - Budget: $100 million - Type: Cultural Tourism'),
    L.marker([26.1445, 91.7362], { icon: icons.tourism }).bindPopup('Brahmaputra River Cruise - Budget: 50 Million USD - Type: Ecotourism'),
    L.marker([28.0664, 95.3268], { icon: icons.tourism }).bindPopup('Pasighat Adventure Tourism - Budget: 30 Million USD - Type: Adventure Sports'),
    L.marker([26.6518, 92.7926], { icon: icons.tourism }).bindPopup('Tezpur Historical Tours - Budget: 40 Million USD - Type: Cultural Tourism'),
    L.marker([26.1445, 91.7362], { icon: icons.tourism }).bindPopup('Guwahati Boat Rides - Budget: 20 Million USD - Type: Water Recreation'),
    L.marker([27.0076, 94.2247], { icon: icons.tourism }).bindPopup('Majuli Ecotourism - Budget: 35 Million USD - Type: Eco-Friendly Tours'),
    L.marker([27.4728, 94.9110], { icon: icons.tourism }).bindPopup('Dibrugarh Fishing Expeditions - Budget: 15 Million USD - Type: Sport Tourism')
];

// Cleaning Projects Markers
var cleaningProjectsMarkers = [
    L.marker([29.9457, 78.1642], { icon: icons.cleaning }).bindPopup('Plastic Waste Cleanup - Duration: 2 years - Impact: High - Budget: $500,000,000'),
    L.marker([25.3176, 82.9739], { icon: icons.cleaning }).bindPopup('Industrial Waste Removal - Duration: 5 years - Impact: High - Budget: $800,000,000'),
    L.marker([25.5941, 85.1376], { icon: icons.cleaning }).bindPopup('Sewage Treatment Project - Duration: 3 years - Impact: Medium - Budget: $1,200,000,000'),
    L.marker([28.0522, 79.1285], { icon: icons.cleaning }).bindPopup('Ghat Cleaning Project - Duration: 1 year - Impact: High - Budget: $400,000,000'),
    L.marker([31.5497, 74.3436], { icon: icons.cleaning }).bindPopup('Plastic Waste Removal - Duration: 3 years - Impact: High - Budget: $300,000,000'),
    L.marker([25.3960, 68.3578], { icon: icons.cleaning }).bindPopup('Industrial Waste Cleanup - Duration: 4 years - Impact: High - Budget: $500,000,000'),
    L.marker([24.8607, 67.0011], { icon: icons.cleaning }).bindPopup('Sewage Treatment - Duration: 5 years - Impact: Medium - Budget: $800,000,000'),
    L.marker([27.7059, 68.8574], { icon: icons.cleaning }).bindPopup('Ghat and Village Cleanup - Duration: 1 year - Impact: High - Budget: $200,000,000'),
    L.marker([23.1815, 79.9864], { icon: icons.cleaning }).bindPopup('Plastic Waste Removal - Duration: 3 years - Impact: High - Budget: $250,000,000'),
    L.marker([22.7451, 77.7369], { icon: icons.cleaning }).bindPopup('Industrial Waste Cleanup - Duration: 4 years - Impact: High - Budget: $400,000,000'),
    L.marker([21.8707, 73.5021], { icon: icons.cleaning }).bindPopup('Sewage Treatment - Duration: 5 years - Impact: Medium - Budget: $600,000,000'),
    L.marker([22.6786, 76.7465], { icon: icons.cleaning }).bindPopup('Ghat Cleaning - Duration: 1 year - Impact: High - Budget: $150,000,000'),
    L.marker([26.1445, 91.7362], { icon: icons.cleaning }).bindPopup('Riverbank Cleanup - Duration: 6 Months - Impact: Moderate - Budget: $10,000,000'),
    L.marker([26.6518, 92.7926], { icon: icons.cleaning }).bindPopup('Water Pollution Reduction - Duration: 1 Year - Impact: High - Budget: $25,000,000'),
    L.marker([27.4728, 94.9110], { icon: icons.cleaning }).bindPopup('Plastic Waste Removal - Duration: 8 Months - Impact: High - Budget: $15,000,000'),
    L.marker([28.0664, 95.3268], { icon: icons.cleaning }).bindPopup('Industrial Waste Treatment - Duration: 2 Years - Impact: Critical - Budget: $50,000,000'),
    L.marker([27.0076, 94.2247], { icon: icons.cleaning }).bindPopup('Community Cleaning Initiative - Duration: 3 Months - Impact: Low - Budget: $5,000,000'),
    L.marker([26.1445, 91.7362], { icon: icons.cleaning }).bindPopup('Organic Waste Management - Duration: 1 Year - Impact: Moderate - Budget: $20,000,000')
];

// Dam Projects Markers
var damProjectsMarkers = [
    L.marker([29.9457, 78.1642], { icon: icons.dam }).bindPopup('Tehri Dam - Capacity: 3,200 MW - Year Built: 2006 - Risk Level: High'),
    L.marker([24.7995, 87.9316], { icon: icons.dam }).bindPopup('Farakka Barrage - Capacity: 33,200 cusecs - Year Built: 1975 - Risk Level: Medium'),
    L.marker([24.7914, 78.7656], { icon: icons.dam }).bindPopup('Rihand Dam - Capacity: 300 MW - Year Built: 1962 - Risk Level: High'),
    L.marker([28.0522, 79.1285], { icon: icons.dam }).bindPopup('Narora Barrage - Capacity: 200 MW - Year Built: 1991 - Risk Level: Low'),
    L.marker([25.3176, 82.9739], { icon: icons.dam }).bindPopup('Rajghat Dam - Capacity: 95 MW - Year Built: 1976 - Risk Level: Medium'),
    L.marker([25.3176, 78.4136], { icon: icons.dam }).bindPopup('Matatila Dam - Capacity: 30 MW - Year Built: 1958 - Risk Level: Low'),
    L.marker([25.5941, 85.1376], { icon: icons.dam }).bindPopup('Lal Bahadur Shastri Dam - Capacity: 500 MW - Year Built: 1985 - Risk Level: Medium'),
    L.marker([29.7344, 78.5152], { icon: icons.dam }).bindPopup('Kalagarh Dam - Capacity: 50 MW - Year Built: 1983 - Risk Level: Low'),
    L.marker([34.0947, 72.6834], { icon: icons.dam }).bindPopup('Tarbela Dam - Capacity: 4,888 MW - Year Built: 1976 - Risk Level: High'),
    L.marker([33.6844, 73.0479], { icon: icons.dam }).bindPopup('Mangla Dam - Capacity: 1,000 MW - Year Built: 1967 - Risk Level: High'),
    L.marker([32.4296, 71.5003], { icon: icons.dam }).bindPopup('Chashma Barrage - Capacity: 500 MW - Year Built: 1971 - Risk Level: Medium'),
    L.marker([27.7059, 68.8574], { icon: icons.dam }).bindPopup('Guddu Barrage - Capacity: 100 MW - Year Built: 1962 - Risk Level: Medium'),
    L.marker([25.3946, 68.3156], { icon: icons.dam }).bindPopup('Kotri Barrage - Capacity: 150 MW - Year Built: 1955 - Risk Level: Low'),
    L.marker([27.7059, 68.8574], { icon: icons.dam }).bindPopup('Sukkur Barrage - Capacity: 400 MW - Year Built: 1932 - Risk Level: High'),
    L.marker([34.0466, 71.3304], { icon: icons.dam }).bindPopup('Warsak Dam - Capacity: 240 MW - Year Built: 1960 - Risk Level: Medium'),
    L.marker([35.5299, 75.3891], { icon: icons.dam }).bindPopup('Diamer-Bhasha Dam - Capacity: 4,500 MW - Year Built: 2028 - Risk Level: High'),
    L.marker([34.2075, 71.5103], { icon: icons.dam }).bindPopup('Dargai Dam - Capacity: 200 MW - Year Built: 1985 - Risk Level: Low'),
    L.marker([35.2323, 73.1885], { icon: icons.dam }).bindPopup('Dudhnial Dam - Capacity: 50 MW - Year Built: 2015 - Risk Level: Low'),
    L.marker([21.8707, 73.5021], { icon: icons.dam }).bindPopup('Sardar Sarovar Dam - Capacity: 1,450 MW - Year Built: 2006 - Risk Level: High'),
    L.marker([22.7451, 77.7369], { icon: icons.dam }).bindPopup('Indira Sagar Dam - Capacity: 1,000 MW - Year Built: 2005 - Risk Level: Medium'),
    L.marker([22.6786, 76.7465], { icon: icons.dam }).bindPopup('Omkareshwar Dam - Capacity: 520 MW - Year Built: 2007 - Risk Level: Medium'),
    L.marker([23.1815, 79.9864], { icon: icons.dam }).bindPopup('Bargi Dam - Capacity: 105 MW - Year Built: 1990 - Risk Level: Low'),
    L.marker([22.3475, 77.7654], { icon: icons.dam }).bindPopup('Tawa Dam - Capacity: 80 MW - Year Built: 1978 - Risk Level: Medium'),
    L.marker([22.2676, 76.1545], { icon: icons.dam }).bindPopup('Maheshwar Dam - Capacity: 400 MW - Year Built: 2009 - Risk Level: Medium'),
    L.marker([28.1554, 95.3778], { icon: icons.dam }).bindPopup('Subansiri Lower Dam - Capacity: 2,000 MW - Year Built: 2023 - Risk Level: High'),
    L.marker([28.5274, 95.7529], { icon: icons.dam }).bindPopup('Dibang Dam - Capacity: 3,000 MW - Year Built: 2025 - Risk Level: High'),
    L.marker([27.4814, 94.7953], { icon: icons.dam }).bindPopup('Ranganadi Dam - Capacity: 405 MW - Year Built: 2001 - Risk Level: Medium'),
    L.marker([24.4834, 93.2903], { icon: icons.dam }).bindPopup('Tipaimukh Dam - Capacity: 1,500 MW - Year Built: 2010 - Risk Level: High'),
    L.marker([25.5775, 92.1174], { icon: icons.dam }).bindPopup('Kopili Dam - Capacity: 200 MW - Year Built: 1984 - Risk Level: Medium'),
    L.marker([27.4935, 94.9983], { icon: icons.dam }).bindPopup('Bhareli Dam - Capacity: 600 MW - Year Built: 2018 - Risk Level: High'),
    L.marker([26.6743, 92.5989], { icon: icons.dam }).bindPopup('Kurichu Dam - Capacity: 60 MW - Year Built: 2004 - Risk Level: Medium'),
    L.marker([26.7788, 93.6514], { icon: icons.dam }).bindPopup('Dhansiri Dam - Capacity: 100 MW - Year Built: 2019 - Risk Level: Low'),
    L.marker([27.0196, 88.4407], { icon: icons.dam }).bindPopup('Teesta Stage V - Capacity: 510 MW - Year Built: 2008 - Risk Level: High'),
    L.marker([26.7781, 91.6567], { icon: icons.dam }).bindPopup('Sankosh Dam - Capacity: 2,560 MW - Year Built: 2023 - Risk Level: High')
];

// River Development Projects Markers
var riverDevProjectsMarkers = [
    L.marker([25.3176, 82.9739], { icon: icons.riverDev }).bindPopup('Clean Ganga Project - Purpose: Pollution Control and Clean-up - Status: Ongoing'),
    L.marker([24.7995, 87.9316], { icon: icons.riverDev }).bindPopup('Farakka Barrage Renovation - Purpose: Water Flow Control - Status: Completed'),
    L.marker([26.4499, 80.3319], { icon: icons.riverDev }).bindPopup('Ganga Riverfront Development - Purpose: Tourism and Beautification - Status: Ongoing'),
    L.marker([25.5941, 85.1376], { icon: icons.riverDev }).bindPopup('Patna Flood Control Project - Purpose: Flood Control - Status: In Progress'),
    L.marker([31.5497, 74.3436], { icon: icons.riverDev }).bindPopup('Indus River Clean-Up - Purpose: Pollution Control - Status: Ongoing'),
    L.marker([34.0947, 72.6834], { icon: icons.riverDev }).bindPopup('Tarbela Dam Renovation - Purpose: Dam Safety and Power Generation - Status: In Progress'),
    L.marker([24.8607, 67.0011], { icon: icons.riverDev }).bindPopup('Karachi Flood Prevention - Purpose: Flood Control - Status: Completed'),
    L.marker([27.7059, 68.8574], { icon: icons.riverDev }).bindPopup('Sukkur Barrage Strengthening - Purpose: Barrage Maintenance - Status: Ongoing'),
    L.marker([22.7451, 77.7369], { icon: icons.riverDev }).bindPopup('Narmada Pollution Control - Purpose: Pollution Control - Status: Ongoing'),
    L.marker([21.8707, 73.5021], { icon: icons.riverDev }).bindPopup('Sardar Sarovar Dam Safety - Purpose: Dam Safety - Status: In Progress'),
    L.marker([23.1815, 79.9864], { icon: icons.riverDev }).bindPopup('Jabalpur Flood Management - Purpose: Flood Control - Status: Completed'),
    L.marker([21.8707, 73.5021], { icon: icons.riverDev }).bindPopup('Rajpipla Barrage Upgrade - Purpose: Barrage Strengthening - Status: Ongoing'),
    L.marker([26.1445, 91.7362], { icon: icons.riverDev }).bindPopup('Brahmaputra Riverfront Development - Purpose: Tourism and Flood Protection - Status: Ongoing'),
    L.marker([26.6518, 92.7926], { icon: icons.riverDev }).bindPopup('Brahmaputra Dredging Project - Purpose: Navigation Improvement - Status: Completed'),
    L.marker([26.6518, 92.7926], { icon: icons.riverDev }).bindPopup('Tezpur Bridge Construction - Purpose: Transportation - Status: Ongoing'),
    L.marker([28.0664, 95.3268], { icon: icons.riverDev }).bindPopup('Pasighat Flood Control Wall - Purpose: Flood Protection - Status: Completed'),
    L.marker([26.1445, 91.7362], { icon: icons.riverDev }).bindPopup('Guwahati Riverbank Beautification - Purpose: Tourism - Status: Ongoing'),
    L.marker([27.4728, 94.9110], { icon: icons.riverDev }).bindPopup('Dibrugarh Flood Control Dams - Purpose: Flood Prevention - Status: Proposed')
];

// Weather Stations Markers
var weatherStationsMarkers = [
    L.marker([29.9457, 78.1642], { icon: icons.weather }).bindPopup('Haridwar Weather Station - Flood and Rainfall Monitoring'),
    L.marker([26.4499, 80.3319], { icon: icons.weather }).bindPopup('Kanpur Weather Station - Monsoon Monitoring'),
    L.marker([25.5941, 85.1376], { icon: icons.weather }).bindPopup('Patna Weather Station - Flood Prediction'),
    L.marker([25.4358, 81.8463], { icon: icons.weather }).bindPopup('Allahabad Weather Station - Flood Monitoring'),
    L.marker([22.5726, 88.3639], { icon: icons.weather }).bindPopup('Kolkata Weather Station - Cyclone Monitoring'),
    L.marker([25.3176, 82.9739], { icon: icons.weather }).bindPopup('Varanasi Weather Station - Rainfall Monitoring'),
    L.marker([31.5497, 74.3436], { icon: icons.weather }).bindPopup('Lahore Weather Station - Flood Monitoring'),
    L.marker([24.8607, 67.0011], { icon: icons.weather }).bindPopup('Karachi Weather Station - Cyclone and Rainfall Monitoring'),
    L.marker([25.3960, 68.3578], { icon: icons.weather }).bindPopup('Hyderabad Weather Station - Flood Prediction'),
    L.marker([27.7059, 68.8574], { icon: icons.weather }).bindPopup('Sukkur Weather Station - Rainfall Monitoring'),
    L.marker([30.1575, 71.5249], { icon: icons.weather }).bindPopup('Multan Weather Station - Drought Monitoring'),
    L.marker([33.6844, 73.0479], { icon: icons.weather }).bindPopup('Rawalpindi Weather Station - Flood Prediction'),
    L.marker([33.6844, 73.0479], { icon: icons.weather }).bindPopup('Islamabad Weather Station - Flood and Earthquake Monitoring'),
    L.marker([23.1815, 79.9864], { icon: icons.weather }).bindPopup('Jabalpur Weather Station - Flood Monitoring'),
    L.marker([22.7451, 77.7369], { icon: icons.weather }).bindPopup('Hoshangabad Weather Station - Rainfall and Flood Monitoring'),
    L.marker([22.0356, 74.8990], { icon: icons.weather }).bindPopup('Barwani Weather Station - Drought Prediction'),
    L.marker([21.8653, 73.5040], { icon: icons.weather }).bindPopup('Rajpipla Weather Station - Flood and Cyclone Monitoring'),
    L.marker([21.7051, 72.9959], { icon: icons.weather }).bindPopup('Bharuch Weather Station - Monsoon Monitoring'),
    L.marker([22.5986, 80.3725], { icon: icons.weather }).bindPopup('Mandla Weather Station - Flood Prediction'),
    L.marker([26.1445, 91.7362], { icon: icons.weather }).bindPopup('Guwahati Weather Station - Flood and Monsoon Monitoring'),
    L.marker([26.6518, 92.7926], { icon: icons.weather }).bindPopup('Tezpur Weather Station - River Erosion Monitoring'),
    L.marker([27.4728, 94.9110], { icon: icons.weather }).bindPopup('Dibrugarh Weather Station - Flood Monitoring'),
    L.marker([28.0664, 95.3268], { icon: icons.weather }).bindPopup('Pasighat Weather Station - Rainfall and Landslide Monitoring'),
    L.marker([25.5788, 91.8933], { icon: icons.weather }).bindPopup('Shillong Weather Station - Monsoon and Earthquake Monitoring'),
    L.marker([27.0076, 94.2247], { icon: icons.weather }).bindPopup('Majuli Weather Station - Flood Monitoring'),
    L.marker([24.8333, 92.7789], { icon: icons.weather }).bindPopup('Silchar Weather Station - Cyclone and Rainfall Monitoring')
];

// Combine all markers into an array
allMarkers.push(
    ...riverProjectsMarkers, 
    ...tourismProjectsMarkers, 
    ...cleaningProjectsMarkers, 
    ...damProjectsMarkers, 
    ...riverDevProjectsMarkers, 
    ...weatherStationsMarkers
);

// Add markers to the cluster group initially
[riverProjectsMarkers, tourismProjectsMarkers, cleaningProjectsMarkers, damProjectsMarkers, riverDevProjectsMarkers, weatherStationsMarkers].forEach(markerArray => {
    markerArray.forEach(marker => markersCluster.addLayer(marker));
});

map.addLayer(markersCluster); // Add all markers as a cluster to the map

// Event listeners for checkboxes
['riverProjects', 'tourismProjects', 'cleaningProjects', 'damProjects', 'riverDevelopmentProjects', 'weatherStations'].forEach(filter => {
    document.getElementById(filter).addEventListener('change', applyFilters);
});

function applyFilters() {
    markersCluster.clearLayers(); // Clear all markers from the map

    if (document.getElementById('riverProjects').checked) riverProjectsMarkers.forEach(marker => markersCluster.addLayer(marker));
    if (document.getElementById('tourismProjects').checked) tourismProjectsMarkers.forEach(marker => markersCluster.addLayer(marker));
    if (document.getElementById('cleaningProjects').checked) cleaningProjectsMarkers.forEach(marker => markersCluster.addLayer(marker));
    if (document.getElementById('damProjects').checked) damProjectsMarkers.forEach(marker => markersCluster.addLayer(marker));
    if (document.getElementById('riverDevelopmentProjects').checked) riverDevProjectsMarkers.forEach(marker => markersCluster.addLayer(marker));
    if (document.getElementById('weatherStations').checked) weatherStationsMarkers.forEach(marker => markersCluster.addLayer(marker));

    map.addLayer(markersCluster); // Re-add the filtered markers to the map
}

// Initialize the OpenStreetMap geosearch provider
const provider = new GeoSearch.OpenStreetMapProvider();

// Function to show suggestions in the autocomplete dropdown
async function showSuggestions() {
    var input = document.getElementById("searchInput").value.toLowerCase();
    var dropdown = document.getElementById("autocompleteDropdown");

    // Clear existing suggestions
    dropdown.innerHTML = "";

    // Collect matching markers for suggestions
    allMarkers.forEach(marker => {
        var popupContent = marker.getPopup().getContent().toLowerCase();
        if (popupContent.includes(input) && input.length > 0) {
            var suggestion = document.createElement("div");
            suggestion.textContent = marker.getPopup().getContent();
            suggestion.onclick = function() {
                map.setView(marker.getLatLng(), 12);
                marker.openPopup();
                dropdown.style.display = "none";
            };
            dropdown.appendChild(suggestion);
        }
    });

    // Add OpenStreetMap location suggestions
    try {
        if (input.length > 0) {
            const results = await provider.search({ query: input });
            results.forEach(result => {
                const lat = result.y;
                const lng = result.x;
                
                // Filtering to only include locations within India's bounding box
                if (lat >= 6.4627 && lat <= 35.5133 && lng >= 68.1097 && lng <= 97.3956) {
                    var suggestion = document.createElement("div");
                    suggestion.textContent = result.label;
                    suggestion.onclick = function() {
                        map.setView([result.y, result.x], 12);
                        dropdown.style.display = "none";
                    };
                    dropdown.appendChild(suggestion);
                }
            });
        }
    } catch (error) {
        console.error("Geocoding error: ", error);
    }

    // Show dropdown if there are suggestions
    if (dropdown.innerHTML.trim() !== "") {
        dropdown.style.display = "block";
    } else {
        dropdown.style.display = "none";
    }
}

</script>
<script src="assets/js/api.js"></script>
</html>
