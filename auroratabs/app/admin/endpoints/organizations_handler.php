<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';
require_once '../../auth/helpers/log_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$current_user_id = $_SESSION['user_id'] ?? 0;

switch ($action) {
    case 'list':
        $query = "SELECT * FROM organizations WHERE is_deleted = 0 ORDER BY id DESC";
        $result = $conn->query($query);
        $orgs = [];
        while ($row = $result->fetch_assoc()) {
            $orgs[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $orgs]);
        break;

    case 'list_users':
        $query = "SELECT id, first_name, last_name, username FROM users WHERE is_deleted = 0 ORDER BY first_name ASC";
        $result = $conn->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $users]);
        break;

    case 'list_deleted':
        $query = "SELECT * FROM organizations WHERE is_deleted = 1 ORDER BY id DESC";
        $result = $conn->query($query);
        $orgs = [];
        while ($row = $result->fetch_assoc()) {
            $orgs[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $orgs]);
        break;

    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        $owner_id = intval($_POST['owner_id'] ?? 0);

        // If no owner_id provided via input, use current logged in user
        if ($owner_id <= 0) {
            $owner_id = $current_user_id;
        }

        if (empty($name)) {
            die(json_encode(["status" => "error", "message" => "Organization name is required."]));
        }
        
        if ($owner_id <= 0) {
             die(json_encode(["status" => "error", "message" => "A valid owner must be assigned."]));
        }

        $stmt = $conn->prepare("INSERT INTO organizations (name, email, phone, status, owner_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $email, $phone, $status, $owner_id);
        
        if ($stmt->execute()) {
            // Fetch owner name for detailed logging
            $owner_name = "Unknown";
            $owner_q = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $owner_q->bind_param("i", $owner_id);
            if ($owner_q->execute()) {
                $owner_res = $owner_q->get_result()->fetch_assoc();
                if ($owner_res) $owner_name = $owner_res['first_name'] . " " . $owner_res['last_name'];
            }
            
            log_activity($conn, $current_user_id, 'Created', 'Organizations', "Added organization: $name (Owned by: $owner_name)");
            echo json_encode(["status" => "success", "message" => "Organization created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create organization: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        $owner_id = intval($_POST['owner_id'] ?? 0);

        if ($id <= 0 || empty($name)) {
            die(json_encode(["status" => "error", "message" => "Valid ID and name are required."]));
        }

        if ($owner_id <= 0) {
            die(json_encode(["status" => "error", "message" => "A valid owner must be assigned."]));
        }

        $stmt = $conn->prepare("UPDATE organizations SET name = ?, email = ?, phone = ?, status = ?, owner_id = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $name, $email, $phone, $status, $owner_id, $id);

        if ($stmt->execute()) {
            // Fetch owner name for detailed logging
            $owner_name = "Unknown";
            $owner_q = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $owner_q->bind_param("i", $owner_id);
            if ($owner_q->execute()) {
                $owner_res = $owner_q->get_result()->fetch_assoc();
                if ($owner_res) $owner_name = $owner_res['first_name'] . " " . $owner_res['last_name'];
            }

            log_activity($conn, $current_user_id, 'Updated', 'Organizations', "Updated organization: $name (Owned by: $owner_name)");
            echo json_encode(["status" => "success", "message" => "Organization updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update organization: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch org name for log
        $org_name = "ID: $id";
        $org_q = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
        $org_q->bind_param("i", $id);
        if ($org_q->execute()) {
            $org_res = $org_q->get_result()->fetch_assoc();
            if ($org_res) $org_name = $org_res['name'];
        }

        $stmt = $conn->prepare("UPDATE organizations SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Organizations', "Soft-deleted organization: $org_name");
            echo json_encode(["status" => "success", "message" => "Organization moved to archive."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete organization."]);
        }
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch org name for log
        $org_name = "ID: $id";
        $org_q = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
        $org_q->bind_param("i", $id);
        if ($org_q->execute()) {
            $org_res = $org_q->get_result()->fetch_assoc();
            if ($org_res) $org_name = $org_res['name'];
        }

        $stmt = $conn->prepare("UPDATE organizations SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Restored', 'Organizations', "Restored organization: $org_name from archive");
            echo json_encode(["status" => "success", "message" => "Organization restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore organization."]);
        }
        break;

    case 'toggle_status':
        $id = intval($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        if ($id <= 0 || empty($status)) die(json_encode(["status" => "error", "message" => "Invalid parameters."]));

        $new_status = ($status === 'active') ? 'suspended' : 'active';
        
        // Fetch org name for log
        $org_name = "ID: $id";
        $org_q = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
        $org_q->bind_param("i", $id);
        if ($org_q->execute()) {
            $org_res = $org_q->get_result()->fetch_assoc();
            if ($org_res) $org_name = $org_res['name'];
        }

        $stmt = $conn->prepare("UPDATE organizations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated', 'Organizations', "Toggled status for organization: $org_name to $new_status");
            echo json_encode(["status" => "success", "message" => "Status updated to " . $new_status]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update status."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch org name for log before deletion
        $org_name = "ID: $id";
        $org_q = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
        $org_q->bind_param("i", $id);
        if ($org_q->execute()) {
            $org_res = $org_q->get_result()->fetch_assoc();
            if ($org_res) $org_name = $org_res['name'];
        }

        $stmt = $conn->prepare("DELETE FROM organizations WHERE id = ? AND is_deleted = 1");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Organizations', "Permanently deleted organization: $org_name");
            echo json_encode(["status" => "success", "message" => "Organization permanently deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete organization permanently."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
