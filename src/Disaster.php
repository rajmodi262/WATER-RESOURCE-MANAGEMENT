<?php
class Disaster {
    private $conn;

    function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($disaster_type, $year, $affected_area) {
        $sql = "INSERT INTO disaster (Disaster_Type, Year, Affected_Area) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $disaster_type, $year, $affected_area);
        
        if ($stmt->execute()) {
            return ["message" => "New disaster added successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }

    public function read($disaster_id) {
        $sql = "SELECT * FROM disaster WHERE Disaster_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $disaster_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return ["error" => "No disaster found with ID " . $disaster_id];
        }
    }

    public function update($disaster_id, $disaster_type, $year, $affected_area) {
        $sql = "UPDATE disaster SET Disaster_Type = ?, Year = ?, Affected_Area = ? WHERE Disaster_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisi", $disaster_type, $year, $affected_area, $disaster_id);

        if ($stmt->execute()) {
            return ["message" => "Disaster updated successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }

    public function delete($disaster_id) {
        $sql = "DELETE FROM disaster WHERE Disaster_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $disaster_id);

        if ($stmt->execute()) {
            return ["message" => "Disaster deleted successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }
}
?>
