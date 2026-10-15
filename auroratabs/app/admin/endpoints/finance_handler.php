<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$current_user_id = $_SESSION['user_id'] ?? 0;

if (!$current_user_id) {
    die(json_encode(["status" => "error", "message" => "Unauthorized access"]));
}

switch ($action) {
    case 'get_dashboard_stats':
        // Total Revenue
        $res = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'success'");
        $total_revenue = $res->fetch_assoc()['total'] ?? 0;

        // Subscriptions
        $res = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'success' AND (description LIKE '%Plan%' OR description LIKE '%Subscription%')");
        $subscriptions = $res->fetch_assoc()['total'] ?? 0;

        // Vote Bundles
        $res = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'success' AND (description LIKE '%votes%' OR description LIKE '%Bundle%')");
        $vote_bundles = $res->fetch_assoc()['total'] ?? 0;

        // Paying Clients
        $res = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM transactions WHERE status = 'success' AND user_id > 0");
        $paying_clients = $res->fetch_assoc()['total'] ?? 0;

        echo json_encode([
            "status" => "success",
            "data" => [
                "total_revenue" => (float)$total_revenue,
                "subscriptions" => (float)$subscriptions,
                "vote_bundles" => (float)$vote_bundles,
                "paying_clients" => (int)$paying_clients
            ]
        ]);
        break;

    case 'get_transactions':
        $query = "SELECT t.*, u.first_name, u.last_name, u.email 
                  FROM transactions t
                  LEFT JOIN users u ON t.user_id = u.id
                  ORDER BY t.created_at DESC";
        $result = $conn->query($query);
        $txns = [];
        while ($row = $result->fetch_assoc()) {
            $txns[] = $row;
        }
        
        echo json_encode(["status" => "success", "data" => $txns]);
        break;

    case 'export_csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Trans ID', 'Date', 'First Name', 'Last Name', 'Email', 'Description', 'Amount', 'Status', 'Gateway']);
        
        $query = "SELECT t.*, u.first_name, u.last_name, u.email 
                  FROM transactions t
                  LEFT JOIN users u ON t.user_id = u.id
                  ORDER BY t.created_at DESC";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['transaction_code'] ?: $row['id'],
                $row['created_at'],
                $row['first_name'] ?: 'N/A',
                $row['last_name'] ?: 'N/A',
                $row['email'] ?: 'N/A',
                $row['description'],
                $row['amount'],
                ucfirst($row['status']),
                $row['gateway'] ?: 'N/A'
            ]);
        }
        fclose($output);
        exit();
        
    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
