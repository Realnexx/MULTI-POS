<?php
require_once("app/auth/config/db_connection.php"); // Uses local $conn (aurora)

$sql = file_get_contents("app/admin/endpoints/ojt.sql");

if ($conn->multi_query($sql)) {
    echo "Success: OJT tables created in Aurora database.";
} else {
    echo "Error: " . $conn->error;
}
?>
