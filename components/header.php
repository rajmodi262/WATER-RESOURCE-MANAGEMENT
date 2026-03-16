<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Website</title>
    <style>
        
        /* Sidebar styling */
        .sidebar {
            height: 100%;
            width: 250px; /* Fixed width */
            position: fixed;
            top: 0;
            left: 0; /* Start at 0 for transform to work correctly */
            background-image: url('assets/images/header-bg.jpg'); /* Background image for sidebar */
            background-size: cover; /* Ensure the image covers the sidebar */
            background-position: center; /* Center the background image */
            transform: translateX(-100%); /* Initially hidden off-screen */
            transition: transform 0.3s ease; /* Animate transform property */
            z-index: 999;
            color: white; /* Change text color for better visibility */
            padding: 20px; /* Padding for sidebar content */
        }

        .sidebar.open {
            transform: translateX(0); /* Slide in */
        }

        /* Animated hamburger icon styling */
        .container {
            display: inline-block;
            cursor: pointer;
            position: fixed; /* Fixed position for the icon */
            left: 10px; /* Distance from the left */
            top: 20px; /* Distance from the top */
            z-index: 1000; /* Ensure the icon is above the sidebar */
            transition: left 0.3s ease; /* Animate the position */
        }

        .bar1, .bar2, .bar3 {
            width: 35px;
            height: 5px;
            background-color: #333;
            margin: 6px 0;
            transition: 0.4s;
        }

        .change .bar1 {
            transform: translate(0, 11px) rotate(-45deg); /* Transform to X shape */
        }

        .change .bar2 {
            opacity: 0; /* Hide the middle bar */
        }

        .change .bar3 {
            transform: translate(0, -11px) rotate(45deg); /* Transform to X shape */
        }

    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <br>
    <br>
    <a href="index.php">Home</a>
    <a href="crud_operations.php">Manage Data (CRUD)</a>
    <a href="map.php">Map</a>
</div>

<!-- Animated Hamburger Icon to Open/Close Sidebar -->
<div class="container" id="menu-icon" onclick="toggleSidebar(this)">
    <div class="bar1"></div>
    <div class="bar2"></div>
    <div class="bar3"></div>
</div>

<!-- JavaScript for Sidebar Toggle and Icon Animation -->
<script>
    function toggleSidebar(element) {
        var sidebar = document.getElementById("sidebar");

        // Toggle the sidebar visibility
        if (sidebar.classList.contains("open")) {
            sidebar.classList.remove("open"); // Close the sidebar
            element.style.left = "10px"; // Reset icon position to its original
        } else {
            sidebar.classList.add("open"); // Open the sidebar
            element.style.left = "260px"; // Move icon out of view to the right
        }

        // Toggle the animated hamburger icon
        element.classList.toggle("change");
    }
</script>

</body>
</html>
