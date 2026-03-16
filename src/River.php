<?php
class River {
    private $conn;

    function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($river_name, $source_location, $end_location) {
        $sql = "INSERT INTO river (River_Name, Source_Location, End_Location) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $river_name, $source_location, $end_location);
        
        if ($stmt->execute()) {
            return ["message" => "New river added successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }

    public function read($river_id) {
        $sql = "SELECT * FROM river WHERE River_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $river_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return ["error" => "No river found with ID " . $river_id];
        }
    }

    public function update($river_id, $river_name, $source_location, $end_location) {
        $sql = "UPDATE river SET River_Name = ?, Source_Location = ?, End_Location = ? WHERE River_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $river_name, $source_location, $end_location, $river_id);

        if ($stmt->execute()) {
            return ["message" => "River updated successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }

    public function delete($river_id) {
        $sql = "DELETE FROM river WHERE River_ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $river_id);

        if ($stmt->execute()) {
            return ["message" => "River deleted successfully"];
        } else {
            return ["error" => "Error: " . $stmt->error];
        }
    }
}
?>
