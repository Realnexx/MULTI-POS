<?php
session_start();
require_once '../../auth/config/db_connection.php';

header('Content-Type: application/json');

// Auth Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

// -----------------------------------------------------------------------
// Action: Get List of Active Competitions
// -----------------------------------------------------------------------
if ($action === 'get_active_competitions') {
    $result = $conn->query("SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY created_at DESC");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// -----------------------------------------------------------------------
// Action: Get Categories for a Competition
// -----------------------------------------------------------------------
if ($action === 'get_categories') {
    $competition_id = $_POST['competition_id'] ?? null;
    if (!$competition_id) {
        echo json_encode(['status' => 'error', 'message' => 'Competition ID required']);
        exit;
    }
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE competition_id = ? AND is_deleted = 0 ORDER BY display_order ASC");
    $stmt->bind_param("i", $competition_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// -----------------------------------------------------------------------
// Action: Get Full Report (Judge Cards + Chart + Log)
// NOTE: judge_assignments.judge_id references users.id (not judges.id)
// -----------------------------------------------------------------------
if ($action === 'get_reports') {
    $competition_id = $_POST['competition_id'] ?? null;
    $category_id    = $_POST['category_id']    ?? null;

    $where_clauses = ["1=1"];
    $types  = "";
    $params = [];

    if ($competition_id && $competition_id !== "") {
        $where_clauses[] = "ja.competition_id = ?";
        $types  .= "i";
        $params[] = (int)$competition_id;
    }
    if ($category_id && $category_id !== "") {
        $where_clauses[] = "crit.category_id = ?";
        $types  .= "i";
        $params[] = (int)$category_id;
    }

    $where_str = implode(" AND ", $where_clauses);

    // Helper: execute a query with optional bound params
    $run = function($sql) use ($conn, $types, $params) {
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $conn->query($sql);
    };

    // ------------------------------------------------------------------
    // 1. Judge Performance Summary
    // ------------------------------------------------------------------
    $sql1 = "SELECT s.score_value,
                     ja.judge_id,
                     CONCAT(u.first_name, ' ', u.last_name) AS judge_name
              FROM scores s
              JOIN judge_assignments ja ON s.assignment_id = ja.id
              JOIN users             u  ON ja.judge_id     = u.id
              JOIN criteria        crit ON s.criteria_id   = crit.id
              WHERE $where_str";

    $res = $run($sql1);

    $judge_stats = [];
    $total_sum   = 0;
    $total_count = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $jid = $row['judge_id'];
            if (!isset($judge_stats[$jid])) {
                $judge_stats[$jid] = ['name' => $row['judge_name'], 'sum' => 0, 'count' => 0];
            }
            $judge_stats[$jid]['sum']   += (float)$row['score_value'];
            $judge_stats[$jid]['count'] ++;
            $total_sum   += (float)$row['score_value'];
            $total_count ++;
        }
    }

    $group_avg   = $total_count > 0 ? $total_sum / $total_count : 0;
    $judges_data = [];

    foreach ($judge_stats as $jid => $stat) {
        $avg       = $stat['count'] > 0 ? $stat['sum'] / $stat['count'] : 0;
        $deviation = $group_avg > 0 ? (($avg - $group_avg) / $group_avg) * 100 : 0;
        $label     = $deviation < -1 ? 'Strict' : ($deviation > 1 ? 'Lenient' : 'Balanced');
        $color     = $deviation < -1 ? 'danger' : ($deviation > 1 ? 'info'    : 'success');

        $judges_data[] = [
            'name'      => $stat['name'],
            'avg'       => round($avg, 2),
            'deviation' => round($deviation, 1),
            'label'     => $label,
            'color'     => $color,
        ];
    }

    // ------------------------------------------------------------------
    // 2. Scoring Trends (per candidate per judge)
    // ------------------------------------------------------------------
    $sql2 = "SELECT c.first_name, c.last_name,
                     CONCAT(u.first_name, ' ', u.last_name) AS judge_name,
                     SUM(s.score_value) AS total_score
              FROM scores s
              JOIN judge_assignments ja ON s.assignment_id = ja.id
              JOIN users             u  ON ja.judge_id     = u.id
              JOIN candidates        c  ON s.candidate_id  = c.id
              JOIN criteria        crit ON s.criteria_id   = crit.id
              WHERE $where_str
              GROUP BY c.id, ja.judge_id
              ORDER BY c.id ASC";

    $res          = $run($sql2);
    $candidates   = [];
    $judge_series = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $cname = $row['first_name'] . ' ' . $row['last_name'];
            if (!in_array($cname, $candidates)) $candidates[] = $cname;

            $jname = $row['judge_name'];
            if (!isset($judge_series[$jname])) $judge_series[$jname] = [];
            $judge_series[$jname][] = (float)$row['total_score'];
        }
    }

    $chart_series = [];
    foreach ($judge_series as $name => $data) {
        $chart_series[] = ['name' => $name, 'data' => $data];
    }

    // ------------------------------------------------------------------
    // 3. Detailed Score Log
    // ------------------------------------------------------------------
    $sql3 = "SELECT s.created_at,
                     CONCAT(u.first_name, ' ', u.last_name) AS judge_name,
                     c.first_name, c.last_name,
                     crit.name AS criteria_name,
                     s.score_value
              FROM scores s
              JOIN judge_assignments ja ON s.assignment_id = ja.id
              JOIN users             u  ON ja.judge_id     = u.id
              JOIN candidates        c  ON s.candidate_id  = c.id
              JOIN criteria        crit ON s.criteria_id   = crit.id
              WHERE $where_str
              ORDER BY s.created_at DESC
              LIMIT 500";

    $res  = $run($sql3);
    $logs = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $logs[] = [
                'time'      => date('h:i:s A', strtotime($row['created_at'])),
                'judge'     => $row['judge_name'],
                'candidate' => $row['first_name'] . ' ' . $row['last_name'],
                'criteria'  => $row['criteria_name'],
                'score'     => (float)$row['score_value'],
                'status'    => 'Logged',
            ];
        }
    }

    echo json_encode([
        'status'  => 'success',
        'judges'  => $judges_data,
        'chart'   => ['categories' => $candidates, 'series' => $chart_series],
        'logs'    => $logs,
    ]);
    exit;
}
