<?php
// Database connection settings
$servername = "localhost";  // Or your server's IP
$username = "root";         // Your database username
$password = "1092";         // Your database password
$dbname = "project_resource";  // Your database name

// Log the incoming request
error_log("Accessed db.php");

// Create connection
error_log("Attempting to connect to the database...");
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the database connection is established
if ($conn->connect_error) {
    error_log("Database connection failed in db.php: " . $conn->connect_error);
    die(json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]));
} else {
    error_log("Database connection established successfully in db.php.");
}

// Proceed with the original content of the file
?>
