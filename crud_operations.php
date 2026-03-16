<?php
// Start session and include DB connection
session_start();
include 'config/db.php';  // Corrected path to include the db.php file in the config folder

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mapping of tables to their primary key columns
$tablePrimaryKeys = [
    'disaster' => 'Disaster_ID',
    'disaster_history' => 'Event_ID',
    'affected_area' => 'Area_ID',
    'river_dev_project' => 'Project_ID',
    'cleaning_project' => 'Cleaning_ID',
    'tourism_project' => 'Project_ID',
    'weather_station' => 'Station_ID',
];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input values
    $operation = $_POST['operation'] ?? '';
    $table = $_POST['table'] ?? '';
    $record_id = $_POST['record_id'] ?? '';
    $read_option = $_POST['read_option'] ?? 'specific'; // Default to 'specific'

    // Log the form input for debugging
    error_log("Operation: $operation, Table: $table, Record ID: $record_id, Read Option: $read_option");

    // Check if the inputs are valid
    if (!empty($operation) && !empty($table)) {
        // Get the primary key column for the selected table
        $primaryKey = $tablePrimaryKeys[$table] ?? 'id';  // Default to 'id' if table not found

        // Prepare SQL based on the operation
        switch ($operation) {
            case 'create':
                $sql = '';
                $stmt = null;
        
                if ($table === 'affected_area') {
                    $name = $_POST['name'] ?? '';
                    $type = $_POST['type'] ?? '';
                    $disaster_id = $_POST['disaster_id'] ?? null;
        
                    $sql = "INSERT INTO affected_area (Name, Type, Disaster_ID) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $name, $type, $disaster_id);
                } elseif ($table === 'city') {
                    $city_name = $_POST['city_name'] ?? '';
                    $population = $_POST['population'] ?? 0;
                    $state_id = $_POST['state_id'] ?? null;
                    $river_proximity = $_POST['river_proximity'] ?? '';
                    $disaster_affected = $_POST['disaster_affected'] ?? 0;
                    $project_count = $_POST['project_count'] ?? 0;
        
                    $sql = "INSERT INTO city (City_Name, Population, State_ID, River_Proximity, Disaster_Affected, Project_Count) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siissi", $city_name, $population, $state_id, $river_proximity, $disaster_affected, $project_count);
                } elseif ($table === 'cleaning_project') {
                    $cleaning_type = $_POST['cleaning_type'] ?? '';
                    $river_id = $_POST['river_id'] ?? null;
                    $village_id = $_POST['village_id'] ?? null;
                    $duration = $_POST['duration'] ?? '';
                    $impact = $_POST['impact'] ?? '';
                    $budget_value = $_POST['budget_value'] ?? 0.0;
        
                    $sql = "INSERT INTO cleaning_project (Cleaning_Type, River_ID, Village_ID, Duration, Impact, Budget_Value) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siissd", $cleaning_type, $river_id, $village_id, $duration, $impact, $budget_value);
                } elseif ($table === 'dam') {
                    $dam_name = $_POST['dam_name'] ?? '';
                    $capacity = $_POST['capacity'] ?? '';
                    $year_built = $_POST['year_built'] ?? 0;
                    $river_id = $_POST['river_id'] ?? null;
                    $city_id = $_POST['city_id'] ?? null;
                    $risk_level = $_POST['risk_level'] ?? '';
        
                    $sql = "INSERT INTO dam (Dam_Name, Capacity, Year_Built, River_ID, City_ID, Risk_Level) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssiiis", $dam_name, $capacity, $year_built, $river_id, $city_id, $risk_level);
                } elseif ($table === 'disaster') {
                    $disaster_type = $_POST['disaster_type'] ?? '';
                    $year = $_POST['year'] ?? '';
                    $cause = $_POST['cause'] ?? '';
                    $severity = $_POST['severity'] ?? '';
                    $river_id = $_POST['river_id'] ?? null;
                    $affected_area = $_POST['affected_area'] ?? '';
        
                    if ($river_id !== null) {
                        $sql = "INSERT INTO disaster (Disaster_Type, Year, Cause, Severity, River_ID, Affected_Area) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sisisi", $disaster_type, $year, $cause, $severity, $river_id, $affected_area);
                    } else {
                        $sql = "INSERT INTO disaster (Disaster_Type, Year, Cause, Severity, Affected_Area) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sisis", $disaster_type, $year, $cause, $severity, $affected_area);
                    }
                } elseif ($table === 'disaster_history') {
                    $date = $_POST['date'] ?? '';
                    $damage_estimate = $_POST['damage_estimate'] ?? '';
                    $casualties = $_POST['casualties'] ?? '';
                    $disaster_id = $_POST['disaster_id'] ?? null;
                    $river_id = $_POST['river_id'] ?? null;
        
                    $sql = "INSERT INTO disaster_history (Date, Damage_Estimate, Casualties, Disaster_ID, River_ID) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssiii", $date, $damage_estimate, $casualties, $disaster_id, $river_id);
                } elseif ($table === 'irrigation_canal') {
                    $canal_name = $_POST['canal_name'] ?? '';
                    $river_id = $_POST['river_id'] ?? null;
                    $city_id = $_POST['city_id'] ?? null;
                    $capacity = $_POST['capacity'] ?? '';
                    $length = $_POST['length'] ?? 0;
        
                    $sql = "INSERT INTO irrigation_canal (Canal_Name, River_ID, City_ID, Capacity, Length) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siisi", $canal_name, $river_id, $city_id, $capacity, $length);
                } elseif ($table === 'river') {
                    $river_name = $_POST['river_name'] ?? '';
                    $length = $_POST['length'] ?? 0;
                    $source_location = $_POST['source_location'] ?? '';
                    $end_location = $_POST['end_location'] ?? '';
        
                    $sql = "INSERT INTO river (River_Name, Length, Source_Location, End_Location) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siss", $river_name, $length, $source_location, $end_location);
                } elseif ($table === 'river_development_project') {
                    $project_name = $_POST['project_name'] ?? '';
                    $river_id = $_POST['river_id'] ?? null;
                    $city_id = $_POST['city_id'] ?? null;
                    $purpose = $_POST['purpose'] ?? '';
                    $status = $_POST['status'] ?? '';
                    $budget_value = $_POST['budget_value'] ?? 0.0;
        
                    $sql = "INSERT INTO river_development_project (Project_Name, River_ID, City_ID, Purpose, Status, Budget_Value) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siissd", $project_name, $river_id, $city_id, $purpose, $status, $budget_value);
                } elseif ($table === 'state') {
                    $state_name = $_POST['state_name'] ?? '';
        
                    $sql = "INSERT INTO state (State_Name) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $state_name);
                } elseif ($table === 'tourism_project') {
                    $project_name = $_POST['project_name'] ?? '';
                    $budget_value = $_POST['budget_value'] ?? 0.0;
                    $dam_id = $_POST['dam_id'] ?? null;
                    $city_id = $_POST['city_id'] ?? null;
                    $project_type = $_POST['project_type'] ?? '';
        
                    $sql = "INSERT INTO tourism_project (Project_Name, Budget_Value, Dam_ID, City_ID, Project_Type) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sdiss", $project_name, $budget_value, $dam_id, $city_id, $project_type);
                } elseif ($table === 'tributary') {
                    $tributary_name = $_POST['tributary_name'] ?? '';
                    $river_id = $_POST['river_id'] ?? null;
                    $length = $_POST['length'] ?? 0;
                    $dam_id = $_POST['dam_id'] ?? null;
        
                    $sql = "INSERT INTO tributary (Tributary_Name, River_ID, Length, Dam_ID) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siii", $tributary_name, $river_id, $length, $dam_id);
                } elseif ($table === 'village') {
                    $village_name = $_POST['village_name'] ?? '';
                    $population = $_POST['population'] ?? 0;
                    $city_id = $_POST['city_id'] ?? null;
                    $river_proximity = $_POST['river_proximity'] ?? '';
                    $disaster_affected = $_POST['disaster_affected'] ?? 0;
        
                    $sql = "INSERT INTO village (Village_Name, Population, City_ID, River_Proximity, Disaster_Affected) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siisi", $village_name, $population, $city_id, $river_proximity, $disaster_affected);
                } elseif ($table === 'weather_station') {
                    $station_name = $_POST['station_name'] ?? '';
                    $coordinates = $_POST['coordinates'] ?? '';
                    $monitoring_capability = $_POST['monitoring_capability'] ?? '';
                    $city_id = $_POST['city_id'] ?? null;
        
                    $sql = "INSERT INTO weather_station (Station_Name, Coordinates, Monitoring_Capability, City_ID) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssi", $station_name, $coordinates, $monitoring_capability, $city_id);
                }
        
                // Execute and return success or error message
                if ($stmt && $stmt->execute()) {
                    echo json_encode(["success" => true, "message" => "New record added successfully!"]);
                } else {
                    echo json_encode(["success" => false, "error" => "Error adding record: " . $stmt->error]);
                }
                break;
           

            case 'read':
                if ($read_option === 'all') {
                    // Fetch all records from the specified table
                    $sql = "SELECT * FROM $table";
                    $result = $conn->query($sql);

                    if ($result) {
                        if ($result->num_rows > 0) {
                            $data = $result->fetch_all(MYSQLI_ASSOC);
                            echo json_encode(["success" => true, "data" => $data]);
                        } else {
                            echo json_encode(["success" => false, "error" => "No records found in $table."]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error fetching data: " . $conn->error]);
                    }
                } elseif ($read_option === 'specific' && !empty($record_id)) {
                    // Fetch specific record from the specified table
                    $sql = "SELECT * FROM $table WHERE $primaryKey = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $record_id);

                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $data = $result->fetch_all(MYSQLI_ASSOC);
                            echo json_encode(["success" => true, "data" => $data]);
                        } else {
                            echo json_encode(["success" => false, "error" => "No records found with ID $record_id in $table."]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error fetching data: " . $stmt->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Invalid read option or missing record ID."]);
                }
                break;

                case 'update':
                    if (!empty($record_id)) {
                        // Get the primary key for the table
                        $primaryKey = $tablePrimaryKeys[$table] ?? 'id';
                        
                        // Collect fields to update from POST data, excluding non-field values
                        $updateFields = [];
                        $updateValues = [];
                        foreach ($_POST as $field => $value) {
                            if (!in_array($field, ['operation', 'table', 'record_id', 'read_option'])) {  // Exclude non-database fields
                                $updateFields[] = "$field = ?";
                                $updateValues[] = $value;
                            }
                        }
                        
                        // Add the primary key as the last value for WHERE clause binding
                        $updateValues[] = $record_id;
                        
                        // Prepare SQL update statement
                        $sql = "UPDATE $table SET " . implode(', ', $updateFields) . " WHERE $primaryKey = ?";
                        $stmt = $conn->prepare($sql);
                        
                        // Dynamically generate parameter types for bind_param
                        $paramTypes = str_repeat("s", count($updateValues) - 1) . "i";  // Assume all fields are strings except for ID
                        
                        if ($stmt) {
                            $stmt->bind_param($paramTypes, ...$updateValues);
                            if ($stmt->execute()) {
                                echo json_encode(["success" => true, "message" => "Record updated successfully!"]);
                            } else {
                                echo json_encode(["success" => false, "error" => "Error executing update: " . $stmt->error]);
                            }
                        } else {
                            echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Record ID is required for update operation."]);
                    }
                    break;
                                         
                    
            case 'delete':
                // Delete the record based on the correct primary key column
                $sql = "DELETE FROM $table WHERE $primaryKey = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $record_id);

                if ($stmt->execute()) {
                    echo json_encode(["success" => true, "message" => "Record deleted successfully!"]);
                } else {
                    echo json_encode(["success" => false, "error" => "Error deleting record: " . $stmt->error]);
                }
                break;

            default:
                echo json_encode(["success" => false, "error" => "Invalid operation selected."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Please provide valid inputs."]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Operations</title>
    <link rel="stylesheet" href="assets/css/crud.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background-image: url('assets/images/background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>
<?php include 'components/header.php'; ?>
<body>
<section class="crud-section">
    <div class="crud-container">
        <h1>Manage Data (CRUD Operations)</h1>
        <form id="crud-form">
            <div class="form-group">
                <label for="operation">Select Operation:</label>
                <select id="operation" name="operation" class="form-control" required>
                    <option value="">Select Operation</option>
                    <option value="create">Create</option>
                    <option value="read">Read</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                </select>
            </div>
            <div class="form-group">
                <label for="table">Select Table:</label>
                <select id="table" name="table" class="form-control" required>
                    <option value="">Select Table</option>
                    <option value="disaster">Disaster</option>
                    <option value="disaster_history">Disaster History</option>
                    <option value="affected_area">Affected Area</option>
                    <option value="river_development_project">River Development Projects</option>
                    <option value="cleaning_project">Cleaning Projects</option>
                    <option value="tourism_project">Tourism Projects</option>
                    <option value="weather_station">Weather Station</option>
                </select>
            </div>

            <!-- Dropdown to specify read type when read is selected -->
            <div id="read-options" class="form-group" style="display: none;">
                <label for="read-options-select">How would you like to see data?</label>
                <select id="read-options-select" name="read_option" class="form-control">
                    <option value="">Select Option</option>
                    <option value="specific">Only Specific Record</option>
                    <option value="all">All Records</option>
                </select>
            </div>

            <!-- Record ID input, shown for specific reads, updates, and deletes -->
            <div id="record-id" class="form-group" style="display: none;">
                <label for="record_id">Record ID:</label>
                <input type="number" id="record_id" name="record_id" class="form-control">
            </div>

            <!-- Dynamic input fields for different operations -->
            <div id="optional-inputs" class="optional-inputs" style="display: none;">
                <!-- Fields will be dynamically inserted here based on the selected table -->
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</section>

<!-- Moved data display section below the form -->
<section id="result-section">
    <div id="messages" class="result-message"></div>
</section>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('operation').addEventListener('change', function() {
        const optionalInputs = document.getElementById('optional-inputs');
        const readOptionsField = document.getElementById('read-options');
        const recordIDField = document.getElementById('record-id');

        optionalInputs.style.display = (this.value === 'create' || this.value === 'update') ? 'block' : 'none';
        readOptionsField.style.display = (this.value === 'read') ? 'block' : 'none';
        recordIDField.style.display = (this.value === 'update' || this.value === 'delete') ? 'block' : 'none';
    });

    document.getElementById('read-options-select').addEventListener('change', function() {
        const recordIDField = document.getElementById('record-id');
        recordIDField.style.display = this.value === 'specific' ? 'block' : 'none';
    });

    document.getElementById('table').addEventListener('change', function() {
        const table = document.getElementById('table').value;
        const operation = document.getElementById('operation').value;
        const dynamicFields = document.getElementById('optional-inputs');

        dynamicFields.innerHTML = '';

        if (operation === 'create' || operation === 'update') {
            if (table === 'disaster') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="disaster_type">Disaster Type:</label>
                        <input type="text" id="disaster_type" name="disaster_type" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <input type="number" id="year" name="year" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cause">Cause:</label>
                        <input type="text" id="cause" name="cause" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="severity">Severity:</label>
                        <input type="text" id="severity" name="severity" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="river_id">River ID:</label>
                        <input type="number" id="river_id" name="river_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="affected_area">Affected Area:</label>
                        <input type="text" id="affected_area" name="affected_area" class="form-control">
                    </div>
                `;
            } else if (table === 'disaster_history') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="damage_estimate">Damage Estimate:</label>
                        <input type="text" id="damage_estimate" name="damage_estimate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="casualties">Casualties:</label>
                        <input type="number" id="casualties" name="casualties" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="disaster_id">Disaster ID:</label>
                        <input type="number" id="disaster_id" name="disaster_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="river_id">River ID:</label>
                        <input type="number" id="river_id" name="river_id" class="form-control">
                    </div>
                `;
            } else if (table === 'affected_area') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <input type="text" id="type" name="type" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="disaster_id">Disaster ID:</label>
                        <input type="number" id="disaster_id" name="disaster_id" class="form-control">
                    </div>
                `;
            } else if (table === 'river_development_project') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="project_name">Project Name:</label>
                        <input type="text" id="project_name" name="project_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="river_id">River ID:</label>
                        <input type="number" id="river_id" name="river_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city_id">City ID:</label>
                        <input type="number" id="city_id" name="city_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="purpose">Purpose:</label>
                        <input type="text" id="purpose" name="purpose" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <input type="text" id="status" name="status" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="budget_value">Budget Value:</label>
                        <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control">
                    </div>
                `;
            } else if (table === 'cleaning_project') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="cleaning_type">Cleaning Type:</label>
                        <input type="text" id="cleaning_type" name="cleaning_type" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="river_id">River ID:</label>
                        <input type="number" id="river_id" name="river_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="village_id">Village ID:</label>
                        <input type="number" id="village_id" name="village_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration:</label>
                        <input type="text" id="duration" name="duration" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="impact">Impact:</label>
                        <input type="text" id="impact" name="impact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="budget_value">Budget Value:</label>
                        <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control">
                    </div>
                `;
            } else if (table === 'tourism_project') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="project_name">Project Name:</label>
                        <input type="text" id="project_name" name="project_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="budget_value">Budget Value:</label>
                        <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="dam_id">Dam ID:</label>
                        <input type="number" id="dam_id" name="dam_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city_id">City ID:</label>
                        <input type="number" id="city_id" name="city_id" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="project_type">Project Type:</label>
                        <input type="text" id="project_type" name="project_type" class="form-control">
                    </div>
                `;
            } else if (table === 'weather_station') {
                dynamicFields.innerHTML += `
                    <div class="form-group">
                        <label for="station_name">Station Name:</label>
                        <input type="text" id="station_name" name="station_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="coordinates">Coordinates:</label>
                        <input type="text" id="coordinates" name="coordinates" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="monitoring_capability">Monitoring Capability:</label>
                        <input type="text" id="monitoring_capability" name="monitoring_capability" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city_id">City ID:</label>
                        <input type="number" id="city_id" name="city_id" class="form-control">
                    </div>
                `;
            }
        }
    });

    document.getElementById('crud-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        const formData = new FormData(this);  // Collect form data

        fetch('crud_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(jsonResponse => {
            const resultSection = document.getElementById('result-section');
            const messagesDiv = document.getElementById('messages');

            if (jsonResponse.success) {
                resultSection.classList.add('show');

                let messageHtml = `<p class="success-msg">${jsonResponse.message || "Operation completed successfully!"}</p>`;

                if (jsonResponse.data) {
                    messageHtml += '<h2 class="fetched-data-title">Fetched Data:</h2><div class="table-wrapper"><table><tr>';

                    // Extract column headers
                    for (let key in jsonResponse.data[0]) {
                        messageHtml += '<th>' + key + '</th>';
                    }
                    messageHtml += '</tr>';

                    // Extract row data
                    jsonResponse.data.forEach(function(row) {
                        messageHtml += '<tr>';
                        for (let key in row) {
                            messageHtml += '<td>' + row[key] + '</td>';
                        }
                        messageHtml += '</tr>';
                    });

                    messageHtml += '</table></div>';
                }

                messagesDiv.innerHTML = messageHtml;
            } else {
                resultSection.classList.add('show');
                messagesDiv.innerHTML = `<p class="error-msg">Error: ${jsonResponse.error}</p>`;
            }
        })
        .catch(error => {
            const resultSection = document.getElementById('result-section');
            const messagesDiv = document.getElementById('messages');

            resultSection.classList.add('show');
            messagesDiv.innerHTML = `<p class="error-msg">Error: ${error.message}</p>`;
        });
    });

    // Additional logging for debugging purposes
    $(document).ready(function () {
        const operationSelect = $('#operation');
        const tableSelect = $('#table');
        const recordIDField = $('#record_id');
        const dynamicFields = $('#optional-inputs');

        // Show/hide elements based on selected operation
        operationSelect.change(function () {
            const operation = operationSelect.val();
            recordIDField.toggle(operation === 'update' || operation === 'delete');
            dynamicFields.toggle(operation === 'create' || operation === 'update');
            confirmButton.toggle(operation === 'update');
        });

        // Fetch existing data for update
        recordIDField.change(function () {
            const selectedTable = tableSelect.val();
            const recordId = $(this).val();

            if (selectedTable && recordId) {
                $.ajax({
                    url: 'api/crud_handler.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        operation: 'read',
                        table: selectedTable,
                        record_id: recordId,
                        read_option: 'specific'
                    },
                    success: function (response) {
                        if (response.success) {
                            populateFormFields(response.data);
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function () {
                        alert("Error fetching record data.");
                    }
                });
            }
        });

        // Populate form fields with existing data
        function populateFormFields(data) {
            dynamicFields.empty();
            const table = tableSelect.val();

            Object.keys(data).forEach((key) => {
                dynamicFields.append(`
                    <div class="form-group">
                        <label for="${key}">${key.replace('_', ' ').toUpperCase()}:</label>
                        <input type="text" id="${key}" name="${key}" class="form-control" value="${data[key] || ''}">
                    </div>
                `);
            });
        }

    });

    // Function to populate form fields with existing data
    function populateFormFields(data) {
        console.log("Populating form fields with data:", data);
        const table = document.getElementById('table').value;  // Get the selected table
        const dynamicFields = document.getElementById('optional-inputs');  // Container for form fields
        dynamicFields.innerHTML = '';  // Clear existing fields

        // Populate fields based on the selected table
        if (table === 'disaster') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="disaster_type">Disaster Type:</label>
                    <input type="text" id="disaster_type" name="disaster_type" class="form-control" value="${data.Disaster_Type || ''}">
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" id="year" name="year" class="form-control" value="${data.Year || ''}">
                </div>
                <div class="form-group">
                    <label for="cause">Cause:</label>
                    <input type="text" id="cause" name="cause" class="form-control" value="${data.Cause || ''}">
                </div>
                <div class="form-group">
                    <label for="severity">Severity:</label>
                    <input type="text" id="severity" name="severity" class="form-control" value="${data.Severity || ''}">
                </div>
                <div class="form-group">
                    <label for="river_id">River ID:</label>
                    <input type="number" id="river_id" name="river_id" class="form-control" value="${data.River_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="affected_area">Affected Area:</label>
                    <input type="text" id="affected_area" name="affected_area" class="form-control" value="${data.Affected_Area || ''}">
                </div>
            `;
        } else if (table === 'disaster_history') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" class="form-control" value="${data.Date || ''}">
                </div>
                <div class="form-group">
                    <label for="damage_estimate">Damage Estimate:</label>
                    <input type="text" id="damage_estimate" name="damage_estimate" class="form-control" value="${data.Damage_Estimate || ''}">
                </div>
                <div class="form-group">
                    <label for="casualties">Casualties:</label>
                    <input type="number" id="casualties" name="casualties" class="form-control" value="${data.Casualties || ''}">
                </div>
                <div class="form-group">
                    <label for="disaster_id">Disaster ID:</label>
                    <input type="number" id="disaster_id" name="disaster_id" class="form-control" value="${data.Disaster_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="river_id">River ID:</label>
                    <input type="number" id="river_id" name="river_id" class="form-control" value="${data.River_ID || ''}">
                </div>
            `;
        } else if (table === 'weather_station') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="station_name">Station Name:</label>
                    <input type="text" id="station_name" name="station_name" class="form-control" value="${data.Station_Name || ''}">
                </div>
                <div class="form-group">
                    <label for="coordinates">Coordinates:</label>
                    <input type="text" id="coordinates" name="coordinates" class="form-control" value="${data.Coordinates || ''}">
                </div>
                <div class="form-group">
                    <label for="monitoring_capability">Monitoring Capability:</label>
                    <input type="text" id="monitoring_capability" name="monitoring_capability" class="form-control" value="${data.Monitoring_Capability || ''}">
                </div>
                <div class="form-group">
                    <label for="city_id">City ID:</label>
                    <input type="number" id="city_id" name="city_id" class="form-control" value="${data.City_ID || ''}">
                </div>
            `;
        } else if (table === 'affected_area') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" value="${data.Name || ''}">
                </div>
                <div class="form-group">
                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" class="form-control" value="${data.Type || ''}">
                </div>
                <div class="form-group">
                    <label for="disaster_id">Disaster ID:</label>
                    <input type="number" id="disaster_id" name="disaster_id" class="form-control" value="${data.Disaster_ID || ''}">
                </div>
            `;
        } else if (table === 'river_development_project') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" class="form-control" value="${data.Project_Name || ''}">
                </div>
                <div class="form-group">
                    <label for="river_id">River ID:</label>
                    <input type="number" id="river_id" name="river_id" class="form-control" value="${data.River_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="city_id">City ID:</label>
                    <input type="number" id="city_id" name="city_id" class="form-control" value="${data.City_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose:</label>
                    <input type="text" id="purpose" name="purpose" class="form-control" value="${data.Purpose || ''}">
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <input type="text" id="status" name="status" class="form-control" value="${data.Status || ''}">
                </div>
                <div class="form-group">
                    <label for="budget_value">Budget Value:</label>
                    <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control" value="${data.Budget_Value || ''}">
                </div>
            `;
        } else if (table === 'cleaning_project') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="cleaning_type">Cleaning Type:</label>
                    <input type="text" id="cleaning_type" name="cleaning_type" class="form-control" value="${data.Cleaning_Type || ''}">
                </div>
                <div class="form-group">
                    <label for="river_id">River ID:</label>
                    <input type="number" id="river_id" name="river_id" class="form-control" value="${data.River_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="village_id">Village ID:</label>
                    <input type="number" id="village_id" name="village_id" class="form-control" value="${data.Village_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="duration">Duration:</label>
                    <input type="text" id="duration" name="duration" class="form-control" value="${data.Duration || ''}">
                </div>
                <div class="form-group">
                    <label for="impact">Impact:</label>
                    <input type="text" id="impact" name="impact" class="form-control" value="${data.Impact || ''}">
                </div>
                <div class="form-group">
                    <label for="budget_value">Budget Value:</label>
                    <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control" value="${data.Budget_Value || ''}">
                </div>
            `;
        } else if (table === 'tourism_project') {
            dynamicFields.innerHTML += `
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" class="form-control" value="${data.Project_Name || ''}">
                </div>
                <div class="form-group">
                    <label for="budget_value">Budget Value:</label>
                    <input type="number" step="0.01" id="budget_value" name="budget_value" class="form-control" value="${data.Budget_Value || ''}">
                </div>
                <div class="form-group">
                    <label for="dam_id">Dam ID:</label>
                    <input type="number" id="dam_id" name="dam_id" class="form-control" value="${data.Dam_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="city_id">City ID:</label>
                    <input type="number" id="city_id" name="city_id" class="form-control" value="${data.City_ID || ''}">
                </div>
                <div class="form-group">
                    <label for="project_type">Project Type:</label>
                    <input type="text" id="project_type" name="project_type" class="form-control" value="${data.Project_Type || ''}">
                </div>
            `;
        }
    }
</script>


<?php include 'components/footer.php'; ?>
</body>
</html>
