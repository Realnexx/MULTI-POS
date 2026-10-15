<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/log_helper.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'fetch':
        $query = "SELECT * FROM vote_packages ORDER BY is_active DESC, price ASC";
        $result = $conn->query($query);
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'votes_count' => (int)$row['votes_count'],
                'price' => (float)$row['price'],
                'promo_label' => $row['promo_label'],
                'is_active' => (bool)$row['is_active']
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $packages]);
        break;

    case 'save':
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $votes = $_POST['votes'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $promo = $_POST['promo_label'] ?? null;
        $active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if (empty($name) || $votes <= 0 || $price <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields correctly.']);
            exit();
        }

        if ($id) {
            // Update
            $stmt = $conn->prepare("UPDATE vote_packages SET name = ?, votes_count = ?, price = ?, promo_label = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sidsii", $name, $votes, $price, $promo, $active, $id);
            $log_action = "Updated";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO vote_packages (name, votes_count, price, promo_label, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sidsi", $name, $votes, $price, $promo, $active);
            $log_action = "Created";
        }

        if ($stmt->execute()) {
            $status_text = $active ? 'Active' : 'Inactive';
            $details = "{$log_action} vote package: {$name} ({$votes} votes for ₱" . number_format($price, 2) . "). Status: {$status_text}";
            if ($id) $details .= " (ID: {$id})";
            
            log_activity($conn, $user_id, $log_action, "Vote Packages", $details);
            
            echo json_encode(['status' => 'success', 'message' => "Package {$log_action} successfully."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Package ID missing.']);
            exit();
        }

        // Get package name for logging before deletion
        $name_stmt = $conn->prepare("SELECT name FROM vote_packages WHERE id = ?");
        $name_stmt->bind_param("i", $id);
        $name_stmt->execute();
        $name_res = $name_stmt->get_result();
        $pkg_name = $name_res->fetch_assoc()['name'] ?? "Unknown Package";
        $name_stmt->close();

        $stmt = $conn->prepare("DELETE FROM vote_packages WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $user_id, "Deleted", "Vote Packages", "Deleted vote package: {$pkg_name} (ID: {$id})");
            echo json_encode(['status' => 'success', 'message' => 'Package deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete package. It may be referenced in existing votes.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
