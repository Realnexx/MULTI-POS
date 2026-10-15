<?php
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password
$database = "aurora";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]));
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
