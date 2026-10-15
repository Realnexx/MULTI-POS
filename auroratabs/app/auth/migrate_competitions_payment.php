<?php
require_once __DIR__ . '/config/db_connection.php';

echo "Starting Migration: Adding 'is_paid' column to competitions table...\n";

$sql = "ALTER TABLE `competitions` 
        ADD COLUMN `is_paid` BOOLEAN DEFAULT FALSE AFTER `plan_type`,
        ADD COLUMN `payment_status` ENUM('unpaid', 'paid', 'pending_verification') DEFAULT 'unpaid' AFTER `is_paid`";

if ($conn->query($sql)) {
    echo "Successfully updated competitions table.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}

$conn->close();
echo "Migration Complete.\n";
?>
