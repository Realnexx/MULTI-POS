<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = [];

// 1. Competition Counts
$comp_counts = [
    'total' => 0,
    'active' => 0,
    'upcoming' => 0,
    'completed' => 0
];
$res = $conn->query("SELECT status, COUNT(*) as count FROM competitions WHERE is_deleted = 0 GROUP BY status");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $status = strtolower($row['status']);
        if ($status === 'active' || $status === 'live') $comp_counts['active'] += (int)$row['count'];
        elseif ($status === 'completed' || $status === 'finished') $comp_counts['completed'] += (int)$row['count'];
        elseif ($status === 'upcoming') $comp_counts['upcoming'] += (int)$row['count'];
        $comp_counts['total'] += (int)$row['count'];
    }
}
$data['competitions'] = $comp_counts;

// 2. Total Votes Cast
$res = $conn->query("SELECT SUM(vote_count) as total FROM votes");
$row = $res ? $res->fetch_assoc() : null;
$total_votes = (int)($row['total'] ?? 0);

$res = $conn->query("SELECT SUM(vote_count) as today FROM votes WHERE DATE(created_at) = CURDATE()");
$row = $res ? $res->fetch_assoc() : null;
$votes_today = (int)($row['today'] ?? 0);

$res = $conn->query("SELECT SUM(vote_count) as yesterday FROM votes WHERE DATE(created_at) = SUBDATE(CURDATE(), 1)");
$row = $res ? $res->fetch_assoc() : null;
$votes_yesterday = (int)($row['yesterday'] ?? 0);

$data['votes'] = [
    'total' => $total_votes,
    'today' => $votes_today,
    'yesterday' => $votes_yesterday,
    'growth' => ($votes_yesterday > 0) ? round((($votes_today - $votes_yesterday) / $votes_yesterday) * 100, 1) : 0
];

// 3. Voting Trends (Last 7 Days)
$trends = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i day"));
    $trends[$date] = 0;
}
$res = $conn->query("SELECT DATE(created_at) as day, SUM(vote_count) as count FROM votes WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day ORDER BY day ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $trends[$row['day']] = (int)$row['count'];
    }
}
$data['trends'] = [
    'labels' => array_keys($trends),
    'data' => array_values($trends)
];

// 4. System Activity
$activity = [];
$res = $conn->query("SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 6");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $activity[] = [
            'time' => date('H:i', strtotime($row['created_at'])),
            'action' => $row['action'],
            'module' => $row['module'],
            'details' => $row['details'],
            'admin' => $row['username'] ?? 'System',
            'type' => strtolower($row['action']) // For badge color
        ];
    }
}
$data['activity'] = $activity;

// 5. Recent Judge Submissions
$submissions = [];
$query = "SELECT u.first_name, u.last_name, c.title as comp_title, u.avatar_url,
          MAX(s.created_at) as last_submission, 
          COUNT(DISTINCT s.candidate_id) as scored_count,
          (SELECT COUNT(*) FROM candidates WHERE competition_id = c.id) as total_candidates
          FROM scores s 
          JOIN judge_assignments ja ON s.assignment_id = ja.id 
          JOIN users u ON ja.judge_id = u.id 
          JOIN competitions c ON ja.competition_id = c.id 
          GROUP BY ja.id 
          ORDER BY last_submission DESC 
          LIMIT 5";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $submissions[] = $row;
    }
}
$data['judge_submissions'] = $submissions;

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
