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

switch ($action) {
    case 'list':
        $query = "SELECT l.*, u.username as admin_name 
                  FROM activity_logs l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC";
        $result = $conn->query($query);
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $logs]);
        break;

    case 'delete_bulk':
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            die(json_encode(["status" => "error", "message" => "No logs selected for deletion."]));
        }

        $count = count($ids);
        // Sanitize IDs
        $id_list = implode(',', array_map('intval', $ids));
        $query = "DELETE FROM activity_logs WHERE id IN ($id_list)";
        
        if ($conn->query($query)) {
            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Deleted', 'Logs', "Permanently deleted $count activity log entries");
            echo json_encode(["status" => "success", "message" => "$count logs deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete logs: " . $conn->error]);
        }
        break;

    case 'export':
        // Log the export action
        log_activity($conn, $_SESSION['user_id'] ?? 0, 'Exported', 'Logs', "Exported system activity logs to CSV");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity_logs_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, ['ID', 'Admin', 'Action', 'Module', 'Details', 'IP Address', 'Timestamp']);
        
        $query = "SELECT l.*, u.username as admin_name 
                  FROM activity_logs l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['admin_name'] ?? 'System',
                $row['action'],
                $row['module'],
                $row['details'],
                $row['ip_address'],
                $row['created_at']
            ]);
        }
        fclose($output);
        exit;
        break;

    case 'clear':
        $cutoff_date = sanitize($_POST['date'] ?? '');
        if (empty($cutoff_date)) {
            die(json_encode(["status" => "error", "message" => "Cleanup date is required."]));
        }

        $query = "DELETE FROM activity_logs WHERE created_at < ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cutoff_date);
        
        if ($stmt->execute()) {
            $deleted = $stmt->affected_rows;
            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Cleared', 'Logs', "Cleared $deleted logs older than $cutoff_date");
            echo json_encode(["status" => "success", "message" => "$deleted old logs cleared successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to clear logs: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
