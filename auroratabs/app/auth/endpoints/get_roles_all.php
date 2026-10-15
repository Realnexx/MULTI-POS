<?php
header("Content-Type: application/json");
require_once '../config/db_connection.php';

try {
    // Fetch all roles 
    $sql = "SELECT id, name, description FROM roles ORDER BY name ASC";
    $result = $conn->query($sql);

    $roles = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Capitalize first letter for display
            $row['display_name'] = ucwords(str_replace('_', ' ', $row['name']));
            $roles[] = $row;
        }
    }

    echo json_encode(["status" => "success", "data" => $roles]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>
