<?php
require_once 'app/auth/config/db_connection.php';

$sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `username_attempted` VARCHAR(255) DEFAULT NULL,
  `attempts` INT DEFAULT 0,
  `last_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `lockout_until` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`ip_address`),
  INDEX (`username_attempted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table login_attempts created or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
?>
