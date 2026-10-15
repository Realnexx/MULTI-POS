<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get_data') {
    // 1. Get All Competitions (Active)
    $comps = [];
    $res = $conn->query("SELECT id, title FROM competitions WHERE status != 'deleted' AND is_deleted = 0 ORDER BY created_at DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) $comps[] = $row;
    }

    // 2. Get All Tabulators (Role ID 3)
    $tabs = [];
    $res = $conn->query("SELECT id, username, first_name, last_name FROM users WHERE role_id = 3 AND is_deleted = 0 ORDER BY username ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) $tabs[] = $row;
    }

    // 3. Get All Organizers (Role ID 1, 2)
    $orgs = [];
    $res = $conn->query("SELECT id, username, first_name, last_name FROM users WHERE role_id IN (1, 2) AND is_deleted = 0 ORDER BY username ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) $orgs[] = $row;
    }

    // 4. Get Current Assignments (Tabulators)
    $assigns = [];
    $res = $conn->query("SELECT competition_id, user_id FROM tabulator_assignments");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $assigns[$row['competition_id']][] = (int)$row['user_id'];
        }
    }

    // 5. Get Current Assignments (Organizers)
    $org_assigns = [];
    $res = $conn->query("SELECT competition_id, user_id FROM organizer_assignments");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $org_assigns[$row['competition_id']][] = (int)$row['user_id'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'competitions' => $comps,
        'tabulators' => $tabs,
        'assignments' => $assigns,
        'organizers' => $orgs,
        'organizer_assignments' => $org_assigns
    ]);
} 

elseif ($action === 'toggle_assignment') {
    $compId = (int)$_POST['competition_id'];
    $userId = (int)$_POST['user_id'];
    $assigned = $_POST['assigned'] === 'true';
    $type = sanitize($_POST['type'] ?? 'tabulator');

    $table = ($type === 'organizer') ? 'organizer_assignments' : 'tabulator_assignments';

    if ($assigned) {
        // Add Assignment (Ignore if already exists)
        $stmt = $conn->prepare("INSERT IGNORE INTO $table (competition_id, user_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ii", $compId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Remove Assignment
        $stmt = $conn->prepare("DELETE FROM $table WHERE competition_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $compId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode(['status' => 'success']);
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action: ' . $action]);
}
