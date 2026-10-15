<?php
session_start();
require 'app/auth/config/db_connection.php';

// Check what user is in session
$uid = $_SESSION['user_id'] ?? 0;
echo "Session user_id: $uid\n";

// Step 1: role check
$role_query = "SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.is_deleted = 0";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $uid);
$role_stmt->execute();
$role_res = $role_stmt->get_result()->fetch_assoc();
echo "Role: " . ($role_res['name'] ?? 'NOT FOUND') . "\n";

// Step 2: competition
$query = "SELECT comp.id as competition_id, comp.title as competition_title, ja.id as assignment_id
          FROM judge_assignments ja
          JOIN competitions comp ON ja.competition_id = comp.id
          WHERE ja.judge_id = ? AND comp.is_deleted = 0 AND comp.status = 'active'
          LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
echo "Assignment: "; print_r($assignment); echo "\n";

if (!$assignment) {
    echo "NO ASSIGNMENT - stopping here\n";
    exit;
}

$comp_id = $assignment['competition_id'];

// Step 3: Categories query
$cat_query = "SELECT c.id, c.name, c.level, c.type, 
                     (SELECT jl.is_locked FROM judge_locks jl 
                      WHERE jl.judge_id = ? AND jl.competition_id = ? AND jl.category_id = c.id AND jl.candidate_id IS NULL) as is_locked
              FROM categories c 
              WHERE c.competition_id = ? AND c.is_deleted = 0 
              ORDER BY c.display_order ASC";
$cat_stmt = $conn->prepare($cat_query);
if (!$cat_stmt) {
    echo "PREPARE ERROR: " . $conn->error . "\n";
    exit;
}
$cat_stmt->bind_param("iii", $uid, $comp_id, $comp_id);
$exec_result = $cat_stmt->execute();
if (!$exec_result) {
    echo "EXECUTE ERROR: " . $cat_stmt->error . "\n";
    exit;
}
$res = $cat_stmt->get_result();
if (!$res) {
    echo "GET_RESULT ERROR: " . $cat_stmt->error . "\n";
    exit;
}
$categories = $res->fetch_all(MYSQLI_ASSOC);
echo "Categories count: " . count($categories) . "\n";
?>
