<?php
include 'app/auth/config/db_connection.php';
$sql = "ALTER TABLE votes ADD COLUMN vote_count INT DEFAULT 1 AFTER candidate_id";
if ($conn->query($sql)) {
    echo "Column added successfully.";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
