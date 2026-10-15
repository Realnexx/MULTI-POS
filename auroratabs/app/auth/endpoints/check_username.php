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

$username = sanitize($data['username'] ?? '');
$password = $data['password'] ?? ''; // Don't sanitize password
$role_id = intval($data['role'] ?? 0);

if (empty($username)) {
    die(json_encode(["status" => "error", "message" => "Username is required."]));
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND is_deleted = 0 LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "exists", "message" => "Username is already taken."]);
} else {
    echo json_encode(["status" => "available", "message" => "Username is available."]);
}

$stmt->close();
$conn->close();
?>
