<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

header('Content-Type: application/json');

// Auth Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// 1. Get Summary Counts
$counts = [
    'total' => 0,
    'active' => 0,
    'completed' => 0,
    'draft' => 0
];

$res = $conn->query("SELECT status, COUNT(*) as count FROM competitions WHERE is_deleted = 0 GROUP BY status");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $status = strtolower($row['status']);
        if ($status === 'active' || $status === 'live') {
            $counts['active'] += (int)$row['count'];
        } elseif ($status === 'completed' || $status === 'finished') {
            $counts['completed'] += (int)$row['count'];
        } else {
            // Default to draft for anything else not completed or active
            $counts['draft'] += (int)$row['count'];
        }
        $counts['total'] += (int)$row['count'];
    }
}

// 2. Get Detailed Competition List
$competitions = [];
$query = "SELECT c.id, c.title, c.status, c.description,
          (SELECT COUNT(*) FROM candidates WHERE competition_id = c.id) as cand_count,
          (SELECT COUNT(*) FROM judge_assignments WHERE competition_id = c.id) as judge_count
          FROM competitions c 
          WHERE c.is_deleted = 0 
          ORDER BY c.created_at DESC";

$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $competitions[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'counts' => $counts,
    'competitions' => $competitions
]);
?>
