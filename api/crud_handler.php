<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration file
require_once '../config/db.php';

// Set content type header to JSON
header('Content-Type: application/json');

// Check if the database connection is established
if (!isset($conn) || $conn->connect_errno) {
    error_log("Database connection failed in crud_handler.php: " . $conn->connect_error);
    echo json_encode(["success" => false, "error" => "Database connection failed."]);
    exit;
}

// Log the incoming POST request data for debugging purposes
error_log("Received a POST request to crud_handler.php");
error_log("POST Data: " . json_encode($_POST));

// Function to sanitize input to prevent SQL injection
function sanitizeInput($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to execute a prepared query and return a JSON response
function executePreparedQuery($stmt, $successMsg, $errorMsg) {
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => $successMsg]);
    } else {
        error_log($errorMsg . ": " . $stmt->error);
        echo json_encode(["success" => false, "error" => $errorMsg . ": " . $stmt->error]);
    }
    exit;
}

// Check if 'operation' and 'table' are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['operation']) && isset($_POST['table'])) {
    $operation = sanitizeInput($conn, $_POST['operation']);
    $table = sanitizeInput($conn, $_POST['table']);

    // Define allowed tables for CRUD operations
    $allowed_tables = ['disaster', 'disaster_history', 'weather_station', 'affected_area', 'river_development_project', 'cleaning_project', 'tourism_project'];

    // Determine primary key based on table
    $primaryKeys = [
        'disaster' => 'Disaster_ID',
        'disaster_history' => 'Event_ID',
        'weather_station' => 'Station_ID',
        'affected_area' => 'Area_ID',
        'river_development_project' => 'Project_ID',
        'cleaning_project' => 'Cleaning_ID',
        'tourism_project' => 'Project_ID'
    ];
    $primaryKey = $primaryKeys[$table] ?? null;

    // Exit if the table is not allowed or the primary key is not found
    if (!in_array($table, $allowed_tables) || $primaryKey === null) {
        echo json_encode(["success" => false, "error" => "Unsupported table for CRUD operation."]);
        exit;
    }

    // Read Operation
    if ($operation === 'read') {
        $read_option = $_POST['read_option'] ?? 'specific';

        if ($read_option === 'all') {
            $sql = "SELECT * FROM $table";
            $result = $conn->query($sql);

            if ($result) {
                $data = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
                echo json_encode(["success" => true, "data" => $data]);
            } else {
                echo json_encode(["success" => false, "error" => "Error fetching data: " . $conn->error]);
            }
        } elseif ($read_option === 'specific' && !empty($_POST['record_id'])) {
            $record_id = sanitizeInput($conn, $_POST['record_id']);
            $sql = "SELECT * FROM $table WHERE $primaryKey = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $record_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $data = $result->num_rows > 0 ? $result->fetch_assoc() : null;
                echo json_encode(["success" => true, "data" => $data]);
            } else {
                echo json_encode(["success" => false, "error" => "Error fetching data: " . $stmt->error]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Invalid read option or missing record ID."]);
        }
        exit;
    }
    // Create Operation
    elseif ($operation === 'create') {
        // Insert logic for each table, based on its fields
        switch ($table) {
            case 'disaster':
                if (isset($_POST['disaster_type'], $_POST['year'], $_POST['cause'], $_POST['severity'], $_POST['river_id'], $_POST['affected_area'])) {
                    $disaster_type = sanitizeInput($conn, $_POST['disaster_type']);
                    $year = sanitizeInput($conn, $_POST['year']);
                    $cause = sanitizeInput($conn, $_POST['cause']);
                    $severity = sanitizeInput($conn, $_POST['severity']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $affected_area = sanitizeInput($conn, $_POST['affected_area']);
    
                    $sql = "INSERT INTO disaster (Disaster_Type, Year, Cause, Severity, River_ID, Affected_Area) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("ssssss", $disaster_type, $year, $cause, $severity, $river_id, $affected_area);
                        // Execute query and show success or error
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Disaster record created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating disaster record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for disaster!"]);
                }
                break;
    
            case 'disaster_history':
                if (isset($_POST['date'], $_POST['damage_estimate'], $_POST['casualties'], $_POST['disaster_id'], $_POST['river_id'])) {
                    $date = sanitizeInput($conn, $_POST['date']);
                    $damage_estimate = sanitizeInput($conn, $_POST['damage_estimate']);
                    $casualties = sanitizeInput($conn, $_POST['casualties']);
                    $disaster_id = sanitizeInput($conn, $_POST['disaster_id']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
    
                    $sql = "INSERT INTO disaster_history (Date, Damage_Estimate, Casualties, Disaster_ID, River_ID) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("ssiii", $date, $damage_estimate, $casualties, $disaster_id, $river_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Disaster history record created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating disaster history record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for disaster history!"]);
                }
                break;
    
            case 'weather_station':
                if (isset($_POST['station_name'], $_POST['coordinates'], $_POST['monitoring_capability'], $_POST['city_id'])) {
                    $station_name = sanitizeInput($conn, $_POST['station_name']);
                    $coordinates = sanitizeInput($conn, $_POST['coordinates']);
                    $monitoring_capability = sanitizeInput($conn, $_POST['monitoring_capability']);
                    $city_id = sanitizeInput($conn, $_POST['city_id']);
    
                    $sql = "INSERT INTO weather_station (Station_Name, Coordinates, Monitoring_Capability, City_ID) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("sssi", $station_name, $coordinates, $monitoring_capability, $city_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Weather station record created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating weather station record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for weather station!"]);
                }
                break;
    
            case 'affected_area':
                if (isset($_POST['name'], $_POST['type'], $_POST['disaster_id'])) {
                    $name = sanitizeInput($conn, $_POST['name']);
                    $type = sanitizeInput($conn, $_POST['type']);
                    $disaster_id = sanitizeInput($conn, $_POST['disaster_id']);
    
                    $sql = "INSERT INTO affected_area (Name, Type, Disaster_ID) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("ssi", $name, $type, $disaster_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Affected area record created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating affected area record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for affected area!"]);
                }
                break;
    
            case 'river_development_project':
                if (isset($_POST['project_name'], $_POST['river_id'], $_POST['city_id'], $_POST['purpose'], $_POST['status'], $_POST['budget_value'])) {
                    $project_name = sanitizeInput($conn, $_POST['project_name']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $city_id = sanitizeInput($conn, $_POST['city_id']);
                    $purpose = sanitizeInput($conn, $_POST['purpose']);
                    $status = sanitizeInput($conn, $_POST['status']);
                    $budget_value = sanitizeInput($conn, $_POST['budget_value']);
    
                    $sql = "INSERT INTO river_development_project (Project_Name, River_ID, City_ID, Purpose, Status, Budget_Value) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("siissd", $project_name, $river_id, $city_id, $purpose, $status, $budget_value);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "River development project created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating river development project: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for river development project!"]);
                }
                break;
    
            case 'cleaning_project':
                if (isset($_POST['cleaning_type'], $_POST['river_id'], $_POST['village_id'], $_POST['duration'], $_POST['impact'], $_POST['budget_value'])) {
                    $cleaning_type = sanitizeInput($conn, $_POST['cleaning_type']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $village_id = sanitizeInput($conn, $_POST['village_id']);
                    $duration = sanitizeInput($conn, $_POST['duration']);
                    $impact = sanitizeInput($conn, $_POST['impact']);
                    $budget_value = sanitizeInput($conn, $_POST['budget_value']);
    
                    $sql = "INSERT INTO cleaning_project (Cleaning_Type, River_ID, Village_ID, Duration, Impact, Budget_Value) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
    
                    if ($stmt) {
                        $stmt->bind_param("siissd", $cleaning_type, $river_id, $village_id, $duration, $impact, $budget_value);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Cleaning project created successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error creating cleaningproject: " . $stmt->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                }
            } else {
                echo json_encode(["success" => false, "error" => "Missing required fields for cleaning project!"]);
            }
            break;

        case 'tourism_project':
            if (isset($_POST['project_name'], $_POST['budget_value'], $_POST['dam_id'], $_POST['city_id'], $_POST['project_type'])) {
                $project_name = sanitizeInput($conn, $_POST['project_name']);
                $budget_value = sanitizeInput($conn, $_POST['budget_value']);
                $dam_id = sanitizeInput($conn, $_POST['dam_id']);
                $city_id = sanitizeInput($conn, $_POST['city_id']);
                $project_type = sanitizeInput($conn, $_POST['project_type']);

                $sql = "INSERT INTO tourism_project (Project_Name, Budget_Value, Dam_ID, City_ID, Project_Type) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("sdiss", $project_name, $budget_value, $dam_id, $city_id, $project_type);
                    if ($stmt->execute()) {
                        echo json_encode(["success" => true, "message" => "Tourism project created successfully!"]);
                    } else {
                        echo json_encode(["success" => false, "error" => "Error creating tourism project: " . $stmt->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Error preparing statement: " . $conn->error]);
                }
            } else {
                echo json_encode(["success" => false, "error" => "Missing required fields for tourism project!"]);
            }
            break;

        // Handle other tables similarly...

        default:
            echo json_encode(["success" => false, "error" => "Unsupported table for create operation."]);
    }
    exit;
}}

 // Update Operation
elseif ($operation === 'update') {
    if (isset($_POST['record_id']) && !empty($_POST['record_id'])) {
        $record_id = sanitizeInput($conn, $_POST['record_id']);

        // Switch statement to handle update logic for each allowed table
        switch ($table) {
            case 'disaster':
                if (isset($_POST['disaster_type'], $_POST['year'], $_POST['cause'], $_POST['severity'], $_POST['river_id'], $_POST['affected_area'])) {
                    $disaster_type = sanitizeInput($conn, $_POST['disaster_type']);
                    $year = sanitizeInput($conn, $_POST['year']);
                    $cause = sanitizeInput($conn, $_POST['cause']);
                    $severity = sanitizeInput($conn, $_POST['severity']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $affected_area = sanitizeInput($conn, $_POST['affected_area']);
            
                    $sql = "UPDATE disaster SET Disaster_Type = ?, Year = ?, Cause = ?, Severity = ?, River_ID = ?, Affected_Area = ? WHERE Disaster_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("ssssssi", $disaster_type, $year, $cause, $severity, $river_id, $affected_area, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Disaster record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for disaster record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for disaster update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for disaster update!"]);
                }
                break;
            
            case 'disaster_history':
                if (isset($_POST['date'], $_POST['damage_estimate'], $_POST['casualties'], $_POST['disaster_id'], $_POST['river_id'])) {
                    $date = sanitizeInput($conn, $_POST['date']);
                    $damage_estimate = sanitizeInput($conn, $_POST['damage_estimate']);
                    $casualties = sanitizeInput($conn, $_POST['casualties']);
                    $disaster_id = sanitizeInput($conn, $_POST['disaster_id']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
            
                    $sql = "UPDATE disaster_history SET Date = ?, Damage_Estimate = ?, Casualties = ?, Disaster_ID = ?, River_ID = ? WHERE Event_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("ssiiii", $date, $damage_estimate, $casualties, $disaster_id, $river_id, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Disaster history record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for disaster history record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for disaster history update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for disaster history update!"]);
                }
                break;
            
            case 'weather_station':
                if (isset($_POST['station_name'], $_POST['coordinates'], $_POST['monitoring_capability'], $_POST['city_id'])) {
                    $station_name = sanitizeInput($conn, $_POST['station_name']);
                    $coordinates = sanitizeInput($conn, $_POST['coordinates']);
                    $monitoring_capability = sanitizeInput($conn, $_POST['monitoring_capability']);
                    $city_id = sanitizeInput($conn, $_POST['city_id']);
            
                    $sql = "UPDATE weather_station SET Station_Name = ?, Coordinates = ?, Monitoring_Capability = ?, City_ID = ? WHERE Station_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("sssii", $station_name, $coordinates, $monitoring_capability, $city_id, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Weather station record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for weather station record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for weather station update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for weather station update!"]);
                }
                break;
            
            case 'affected_area':
                if (isset($_POST['name'], $_POST['type'], $_POST['disaster_id'])) {
                    $name = sanitizeInput($conn, $_POST['name']);
                    $type = sanitizeInput($conn, $_POST['type']);
                    $disaster_id = sanitizeInput($conn, $_POST['disaster_id']);
            
                    $sql = "UPDATE affected_area SET Name = ?, Type = ?, Disaster_ID = ? WHERE Area_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("ssii", $name, $type, $disaster_id, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Affected area record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for affected area record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for affected area update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for affected area update!"]);
                }
                break;
            
            case 'river_development_project':
                if (isset($_POST['project_name'], $_POST['river_id'], $_POST['city_id'], $_POST['purpose'], $_POST['status'], $_POST['budget_value'])) {
                    $project_name = sanitizeInput($conn, $_POST['project_name']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $city_id = sanitizeInput($conn, $_POST['city_id']);
                    $purpose = sanitizeInput($conn, $_POST['purpose']);
                    $status = sanitizeInput($conn, $_POST['status']);
                    $budget_value = sanitizeInput($conn, $_POST['budget_value']);
            
                    $sql = "UPDATE river_development_project SET Project_Name = ?, River_ID = ?, City_ID = ?, Purpose = ?, Status = ?, Budget_Value = ? WHERE Project_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("siissdi", $project_name, $river_id, $city_id, $purpose, $status, $budget_value, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "River development project record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for river development project record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for river development project update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for river development project update!"]);
                }
                break;
            
            case 'cleaning_project':
                if (isset($_POST['cleaning_type'], $_POST['river_id'], $_POST['village_id'], $_POST['duration'], $_POST['impact'], $_POST['budget_value'])) {
                    $cleaning_type = sanitizeInput($conn, $_POST['cleaning_type']);
                    $river_id = sanitizeInput($conn, $_POST['river_id']);
                    $village_id = sanitizeInput($conn, $_POST['village_id']);
                    $duration = sanitizeInput($conn, $_POST['duration']);
                    $impact = sanitizeInput($conn, $_POST['impact']);
                    $budget_value = sanitizeInput($conn, $_POST['budget_value']);
            
                    $sql = "UPDATE cleaning_project SET Cleaning_Type = ?, River_ID = ?, Village_ID = ?, Duration = ?, Impact = ?, Budget_Value = ? WHERE Cleaning_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("siissdi", $cleaning_type, $river_id, $village_id, $duration, $impact, $budget_value, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Cleaning project record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for cleaning project record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for cleaning project update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for cleaning project update!"]);
                }
                break;
            
            case 'tourism_project':
                if (isset($_POST['project_name'], $_POST['budget_value'], $_POST['dam_id'], $_POST['city_id'], $_POST['project_type'])) {
                    $project_name = sanitizeInput($conn, $_POST['project_name']);
                    $budget_value = sanitizeInput($conn, $_POST['budget_value']);
                    $dam_id = sanitizeInput($conn, $_POST['dam_id']);
                    $city_id = sanitizeInput($conn, $_POST['city_id']);
                    $project_type = sanitizeInput($conn, $_POST['project_type']);
            
                    $sql = "UPDATE tourism_project SET Project_Name = ?, Budget_Value = ?, Dam_ID = ?, City_ID = ?, Project_Type = ? WHERE Project_ID = ?";
                    $stmt = $conn->prepare($sql);
            
                    if ($stmt) {
                        $stmt->bind_param("sdissi", $project_name, $budget_value, $dam_id, $city_id, $project_type, $record_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Tourism project record updated successfully!"]);
                        } else {
                            echo json_encode(["success" => false, "error" => "Error executing update for tourism project record: " . $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "error" => "Error preparing statement for tourism project update: " . $conn->error]);
                    }
                } else {
                    echo json_encode(["success" => false, "error" => "Missing required fields for tourism project update!"]);
                }
                break;
            
            
            default:
                echo json_encode(["success" => false, "error" => "Unsupported table for update operation."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Missing ID for update operation."]);
    }
    exit;
}

    // Delete Operation
    elseif ($operation === 'delete') {
        if (!empty($_POST['record_id'])) {
            $record_id = sanitizeInput($conn, $_POST['record_id']);

            $sql = "DELETE FROM $table WHERE $primaryKey = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("i", $record_id);
                executePreparedQuery($stmt, "Record deleted successfully!", "Error deleting record");
            } else {
                echo json_encode(["success" => false, "error" => "Error preparing delete statement: " . $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Missing ID for delete operation."]);
        }
        exit;
    }

// Close the database connection
$conn->close();
error_log("Database connection closed.");
 