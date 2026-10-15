<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';
require_once '../../auth/helpers/log_helper.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$types = json_decode($_POST['types'] ?? '[]', true);
$competition_id = $_POST['competition_id'] ?? 'all';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';
$format = $_POST['format'] ?? 'csv';
$current_user_id = $_SESSION['user_id'];

if (empty($types)) {
    die("No data types selected");
}

// Helper: Generate CSV content from query
function generateCsv($conn, $query, $params = [], $types_str = "") {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return "ERROR: SQL Prepare failed: " . $conn->error;
    }

    if (!empty($params)) {
        $stmt->bind_param($types_str, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $output = fopen('php://temp', 'r+');
    
    // Fetch headers
    $first = true;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                fputcsv($output, array_keys($row));
                $first = false;
            }
            // Decode HTML entities (like &amp;) to raw symbols (&) for CSV
            $decoded_row = array_map(function($val) {
                return html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }, array_values($row));
            fputcsv($output, $decoded_row);
        }
    } else {
        fputcsv($output, ["No data found for this selection"]);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    return $csv;
}

$files = [];

foreach ($types as $type) {
    $query = "";
    $params = [];
    $param_types = "";
    $filename = "";

    switch ($type) {
        case 'candidates':
            $query = "SELECT comp.title as competition, c.gender, c.candidate_number as '#', CONCAT_WS(' ', c.first_name, c.last_name) as candidate_fullname, c.department, c.created_at 
                      FROM candidates c 
                      JOIN competitions comp ON c.competition_id = comp.id 
                      WHERE c.is_deleted = 0";
            if ($competition_id !== 'all') {
                $query .= " AND c.competition_id = ?";
                $params[] = $competition_id;
                $param_types .= "i";
            }
            if (!empty($date_from) && !empty($date_to)) {
                $query .= " AND c.created_at BETWEEN ? AND ?";
                $params[] = $date_from . " 00:00:00";
                $params[] = $date_to . " 23:59:59";
                $param_types .= "ss";
            }
            $query .= " ORDER BY comp.title ASC, c.gender DESC, c.candidate_number ASC";
            $filename = "candidates_export.csv";
            break;

        case 'judges':
            $query = "SELECT CONCAT_WS(' ', u.first_name, u.last_name) as judge_fullname, u.email, u.username, u.status, u.created_at
                      FROM users u 
                      WHERE u.role_id = 4 AND u.is_deleted = 0";
            if (!empty($date_from) && !empty($date_to)) {
                $query .= " AND u.created_at BETWEEN ? AND ?";
                $params[] = $date_from . " 00:00:00";
                $params[] = $date_to . " 23:59:59";
                $param_types .= "ss";
            }
            $query .= " ORDER BY judge_fullname ASC";
            $filename = "judges_export.csv";
            break;

        case 'scores':
            $query = "SELECT comp.title as competition, cand.gender, CONCAT_WS(' ', cand.first_name, cand.last_name) as candidate_fullname, 
                             CONCAT_WS(' ', j.first_name, j.last_name) as judge_fullname, 
                             cr.name as criteria, s.score_value, s.created_at
                      FROM scores s 
                      JOIN judge_assignments ja ON s.assignment_id = ja.id 
                      JOIN users j ON ja.judge_id = j.id 
                      JOIN candidates cand ON s.candidate_id = cand.id 
                      JOIN criteria cr ON s.criteria_id = cr.id 
                      JOIN competitions comp ON ja.competition_id = comp.id
                      WHERE 1=1";
            if ($competition_id !== 'all') {
                $query .= " AND ja.competition_id = ?";
                $params[] = $competition_id;
                $param_types .= "i";
            }
            if (!empty($date_from) && !empty($date_to)) {
                $query .= " AND s.created_at BETWEEN ? AND ?";
                $params[] = $date_from . " 00:00:00";
                $params[] = $date_to . " 23:59:59";
                $param_types .= "ss";
            }
            $query .= " ORDER BY comp.title ASC, cand.gender DESC, candidate_fullname ASC, cr.display_order ASC";
            $filename = "scoring_logs_export.csv";
            break;

        case 'votes':
            // Aggregated Votes (Ranked Leaderboard)
            $query = "SELECT comp.title as competition, c.gender, c.candidate_number as '#', 
                             CONCAT_WS(' ', c.first_name, c.last_name) as candidate_fullname, 
                             SUM(v.vote_count) as total_votes
                      FROM votes v 
                      JOIN candidates c ON v.candidate_id = c.id 
                      JOIN competitions comp ON c.competition_id = comp.id
                      WHERE 1=1";
            if ($competition_id !== 'all') {
                $query .= " AND comp.id = ? ";
                $params[] = $competition_id;
                $param_types .= "i";
            }
            if (!empty($date_from) && !empty($date_to)) {
                $query .= " AND v.created_at BETWEEN ? AND ?";
                $params[] = $date_from . " 00:00:00";
                $params[] = $date_to . " 23:59:59";
                $param_types .= "ss";
            }
            $query .= " GROUP BY v.candidate_id, c.id, comp.id
                        ORDER BY comp.title ASC, c.gender DESC, total_votes DESC";
            $filename = "voting_leaderboard_export.csv";
            break;
    }

    if ($query) {
        $files[$filename] = generateCsv($conn, $query, $params, $param_types);
    }
}

// Log Activity
$type_list = implode(', ', $types);
log_activity($conn, $current_user_id, 'Exported', 'Data Central', "Exported $type_list as $format (Competition: $competition_id)");

if (count($files) === 1) {
    // Single file download
    $filename = array_key_first($files);
    // Adjust extension for Excel trick
    if ($format === 'excel') {
        $filename = str_replace('.csv', '.xls', $filename);
        header('Content-Type: application/vnd.ms-excel');
    } else {
        header('Content-Type: text/csv');
    }
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo reset($files);
} else if (count($files) > 1) {
    // Multiple files - ZIP them
    $zipname = "aurora_export_" . date('Y-m-d_His') . ".zip";
    $zip = new ZipArchive();
    $tmp_file = tempnam(sys_get_temp_dir(), 'zip');
    
    if ($zip->open($tmp_file, ZipArchive::CREATE) === TRUE) {
        foreach ($files as $name => $content) {
            if ($format === 'excel') $name = str_replace('.csv', '.xls', $name);
            $zip->addFromString($name, $content);
        }
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipname . '"');
        header('Content-Length: ' . filesize($tmp_file));
        readfile($tmp_file);
        unlink($tmp_file);
    } else {
        die("Failed to create ZIP archive");
    }
} else {
    die("No data found for the selected criteria.");
}
