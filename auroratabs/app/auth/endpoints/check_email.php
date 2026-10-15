<?php
header("Content-Type: application/json");
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

require_once '../helpers/security_helper.php';
$email = sanitize($data['email'] ?? '');

if (empty($email)) {
    die(json_encode(["status" => "error", "message" => "Email is required."]));
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "exists", "message" => "Email is already taken."]);
} else {
    echo json_encode(["status" => "available", "message" => "Email is available."]);
}

$stmt->close();
$conn->close();
?>
