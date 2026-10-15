<?php
require_once __DIR__ . '/config/db_connection.php';

echo "Starting Migration: Adding 'created_by' column to competitions table...\n";

$sql = "ALTER TABLE `competitions` 
        ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `organization_id`,
        ADD FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL";

if ($conn->query($sql)) {
    echo "Successfully added 'created_by' column to competitions table.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}

$conn->close();
echo "Migration Complete.\n";
?>
