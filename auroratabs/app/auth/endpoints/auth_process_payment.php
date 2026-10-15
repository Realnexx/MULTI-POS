<?php
session_start();
header("Content-Type: application/json");
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$data = json_decode(file_get_contents("php://input"), true);
$comp_id = intval($data['comp_id'] ?? 0);
$method = $data['method'] ?? 'later';
$reference = $data['reference'] ?? null;
$amount = floatval($data['amount'] ?? 0);

if (!$comp_id) {
    die(json_encode(["status" => "error", "message" => "Competition ID is missing."]));
}

// 1. Update Competition Table
$is_paid = ($method === 'gcash') ? 1 : 0;
$status = ($method === 'gcash') ? 'paid' : 'unpaid';

$sql = "UPDATE competitions SET is_paid = ?, payment_status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $is_paid, $status, $comp_id);

if ($stmt->execute()) {
    // 2. Create Transaction Record
    $trans_code = 'TXN-' . strtoupper(uniqid());
    $user_id = $_SESSION['reg_user_id'] ?? null;
    $desc = "Payment for Competition ID: " . $comp_id . " via " . ucfirst($method);
    $trans_status = ($method === 'gcash') ? 'success' : 'pending';
    
    $trans_sql = "INSERT INTO transactions (transaction_code, user_id, description, amount, status, gateway, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $t_stmt = $conn->prepare($trans_sql);
    $gateway = ($method === 'gcash') ? 'GCash' : 'Pay Later';
    $t_stmt->bind_param("sisdsss", $trans_code, $user_id, $desc, $amount, $trans_status, $gateway, $reference);
    $t_stmt->execute();
    
    echo json_encode(["status" => "success", "message" => "Payment status updated."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update payment: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
