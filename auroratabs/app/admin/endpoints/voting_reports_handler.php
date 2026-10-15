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

$action = $_POST['action'] ?? '';

if ($action === 'get_active_competitions') {
    $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY created_at DESC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($action === 'get_reports') {
    $competition_id = isset($_POST['competition_id']) ? $_POST['competition_id'] : null;
    $competition_id = ($competition_id === "" || $competition_id === "null") ? null : (int)$competition_id;
    
    // 1. Summary Stats
    $summary = [
        'total_revenue' => 0,
        'total_votes' => 0,
        'top_performer' => 'None',
        'top_percentage' => 0
    ];

    if ($competition_id !== null) {
        $revenue_query = "SELECT SUM(t.amount) as total 
                         FROM transactions t 
                         JOIN votes v ON t.id = v.transaction_id
                         JOIN candidates c ON v.candidate_id = c.id
                         WHERE t.status = 'success' AND c.competition_id = ?";
        $stmt = $conn->prepare($revenue_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $summary['total_revenue'] = (float)$stmt->get_result()->fetch_assoc()['total'];

        $votes_query = "SELECT SUM(v.vote_count) as total 
                        FROM votes v 
                        JOIN candidates c ON v.candidate_id = c.id
                        WHERE c.competition_id = ?";
        $stmt = $conn->prepare($votes_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $summary['total_votes'] = (int)$stmt->get_result()->fetch_assoc()['total'];
    } else {
        $res = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'success'");
        $summary['total_revenue'] = (float)$res->fetch_assoc()['total'];
        
        $res = $conn->query("SELECT SUM(vote_count) as total FROM votes");
        $summary['total_votes'] = (int)$res->fetch_assoc()['total'];
    }

    // Top Performer
    $top_query = "SELECT c.first_name, c.last_name, SUM(v.vote_count) as total_votes
                  FROM candidates c
                  JOIN votes v ON c.id = v.candidate_id
                  " . ($competition_id !== null ? "WHERE c.competition_id = ?" : "") . "
                  GROUP BY c.id
                  ORDER BY total_votes DESC LIMIT 1";
    
    if ($competition_id !== null) {
        $stmt = $conn->prepare($top_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $top_res = $stmt->get_result()->fetch_assoc();
    } else {
        $top_res = $conn->query($top_query)->fetch_assoc();
    }

    if ($top_res) {
        $summary['top_performer'] = $top_res['first_name'] . ' ' . $top_res['last_name'];
        if ($summary['total_votes'] > 0) {
            $summary['top_percentage'] = round(($top_res['total_votes'] / $summary['total_votes']) * 100, 1);
        }
    }

    // 2. Chart: Vote Distribution
    $dist_query = "SELECT c.first_name, SUM(v.vote_count) as votes
                   FROM candidates c
                   LEFT JOIN votes v ON c.id = v.candidate_id
                   " . ($competition_id !== null ? "WHERE c.competition_id = ?" : "") . "
                   GROUP BY c.id
                   ORDER BY votes DESC";
    
    $dist_data = [];
    if ($competition_id !== null) {
        $stmt = $conn->prepare($dist_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($dist_query);
    }
    while ($row = $res->fetch_assoc()) {
        $dist_data[] = [
            'name' => $row['first_name'],
            'votes' => (int)$row['votes']
        ];
    }

    // 3. Chart: Payment Methods
    $pay_query = "SELECT gateway, SUM(amount) as total
                  FROM transactions
                  WHERE status = 'success'
                  GROUP BY gateway";
    $pay_data = [];
    $res = $conn->query($pay_query);
    while ($row = $res->fetch_assoc()) {
        $pay_data[] = [
            'gateway' => $row['gateway'] ?: 'Unknown',
            'total' => (float)$row['total']
        ];
    }

    // 4. Detailed Table
    $table_query = "SELECT c.id, c.first_name, c.last_name, c.candidate_number, c.photo_url, c.department, c.gender,
                           SUM(v.vote_count) as total_votes,
                           (SELECT SUM(t.amount) FROM transactions t JOIN votes v2 ON t.id = v2.transaction_id WHERE v2.candidate_id = c.id AND t.status = 'success') as revenue
                    FROM candidates c
                    LEFT JOIN votes v ON c.id = v.candidate_id
                    " . ($competition_id !== null ? "WHERE c.competition_id = ?" : "") . "
                    GROUP BY c.id
                    ORDER BY total_votes DESC";
    
    $table_data = [];
    if ($competition_id !== null) {
        $stmt = $conn->prepare($table_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($table_query);
    }
    
    $rank = 1;
    while ($row = $res->fetch_assoc()) {
        $percentage = ($summary['total_votes'] > 0) ? round(($row['total_votes'] / $summary['total_votes']) * 100, 1) : 0;
        $table_data[] = [
            'rank' => $rank++,
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'number' => $row['candidate_number'],
            'photo' => $row['photo_url'],
            'department' => $row['department'],
            'gender' => $row['gender'],
            'votes' => (int)$row['total_votes'],
            'percentage' => $percentage,
            'revenue' => (float)$row['revenue']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'summary' => $summary,
        'dist_chart' => $dist_data,
        'pay_chart' => $pay_data,
        'table' => $table_data
    ]);
    exit;
}
?>
