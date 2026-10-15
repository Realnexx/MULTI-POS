<?php
session_start();
require_once '../auth/config/db_connection.php';
require_once '../auth/helpers/security_helper.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$types = json_decode($_GET['types'] ?? '[]', true);
$competition_id = $_GET['competition_id'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Fetch Competition Info if filtered
$comp_title = "All Competitions";
if ($competition_id !== 'all') {
    $stmt = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
    $stmt->bind_param("i", $competition_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $comp_title = $res ? $res['title'] : "Unknown Competition";
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Export Data Report - Aurora Tabs</title>
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <style>
        @page { size: landscape; margin: 1cm; }
        body { background: white; color: black; font-family: 'Inter', sans-serif; font-size: 11px; }
        .letterhead { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .letterhead img { height: 50px; margin-right: 15px; }
        .report-info { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px; border-left: 4px solid #5d87ff; padding-left: 10px; background: #f0f5ff; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="container-fluid py-4">
        <div class="no-print mb-4 text-center">
            <button class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i> Confirm Print / Save PDF</button>
            <p class="mt-2 small text-muted">Use "Save as PDF" in the printer destination to download the file.</p>
        </div>

        <div class="letterhead text-center">
            <img src="src/assets/images/logos/aurora-logo.png" alt="Logo">
            <div>
                <h3 class="mb-0 fw-bolder">AURORA TABS</h3>
                <p class="mb-0 small text-uppercase" style="letter-spacing: 2px;">Professional Data Export Central</p>
            </div>
        </div>

        <div class="report-info">
            <h4 class="fw-bold"><?php echo htmlspecialchars($comp_title); ?></h4>
            <p class="text-muted">
                <?php if ($date_from && $date_to) echo "Range: $date_from to $date_to | "; ?>
                Generated on: <?php echo date('F d, Y h:i A'); ?>
            </p>
        </div>

        <?php foreach ($types as $type): ?>
            <div class="section-title"><?php echo ucfirst($type); ?> Export</div>
            <table class="table">
                <thead>
                    <?php
                    $query = "";
                    $params = [];
                    $param_types = "";
                    
                    switch ($type) {
                        case 'candidates':
                            $query = "SELECT comp.title as competition, c.gender, c.candidate_number as '#', CONCAT_WS(' ', c.first_name, c.last_name) as candidate_fullname, c.department, c.created_at 
                                      FROM candidates c JOIN competitions comp ON c.competition_id = comp.id WHERE c.is_deleted = 0";
                            if ($competition_id !== 'all') { $query .= " AND c.competition_id = ?"; $params[] = $competition_id; $param_types .= "i"; }
                            if ($date_from && $date_to) { $query .= " AND c.created_at BETWEEN ? AND ?"; $params[] = $date_from." 00:00:00"; $params[] = $date_to." 23:59:59"; $param_types .= "ss"; }
                            $query .= " ORDER BY comp.title ASC, c.gender DESC, c.candidate_number ASC";
                            break;
                            
                        case 'judges':
                            $query = "SELECT CONCAT_WS(' ', u.first_name, u.last_name) as judge_fullname, u.email, u.username, u.status, u.created_at FROM users u WHERE u.role_id = 4 AND u.is_deleted = 0";
                            if ($date_from && $date_to) { $query .= " AND u.created_at BETWEEN ? AND ?"; $params[] = $date_from." 00:00:00"; $params[] = $date_to." 23:59:59"; $param_types .= "ss"; }
                            $query .= " ORDER BY judge_fullname ASC";
                            break;
                            
                        case 'scores':
                            $query = "SELECT comp.title as competition, cand.gender, CONCAT_WS(' ', cand.first_name, cand.last_name) as candidate_fullname, CONCAT_WS(' ', j.first_name, j.last_name) as judge_fullname, cr.name as criteria, s.score_value as score, s.created_at
                                      FROM scores s 
                                      JOIN judge_assignments ja ON s.assignment_id = ja.id JOIN users j ON ja.judge_id = j.id 
                                      JOIN candidates cand ON s.candidate_id = cand.id JOIN criteria cr ON s.criteria_id = cr.id 
                                      JOIN competitions comp ON ja.competition_id = comp.id WHERE 1=1";
                            if ($competition_id !== 'all') { $query .= " AND ja.competition_id = ?"; $params[] = $competition_id; $param_types .= "i"; }
                            if ($date_from && $date_to) { $query .= " AND s.created_at BETWEEN ? AND ?"; $params[] = $date_from." 00:00:00"; $params[] = $date_to." 23:59:59"; $param_types .= "ss"; }
                            $query .= " ORDER BY comp.title ASC, cand.gender DESC, candidate_fullname ASC";
                            break;

                        case 'votes':
                            $query = "SELECT comp.title as competition, c.gender, c.candidate_number as '#', CONCAT_WS(' ', c.first_name, c.last_name) as candidate_fullname, SUM(v.vote_count) as total_votes
                                      FROM votes v JOIN candidates c ON v.candidate_id = c.id JOIN competitions comp ON c.competition_id = comp.id WHERE 1=1";
                            if ($competition_id !== 'all') { $query .= " AND comp.id = ?"; $params[] = $competition_id; $param_types .= "i"; }
                            if ($date_from && $date_to) { $query .= " AND v.created_at BETWEEN ? AND ?"; $params[] = $date_from." 00:00:00"; $params[] = $date_to." 23:59:59"; $param_types .= "ss"; }
                            $query .= " GROUP BY v.candidate_id, c.id, comp.id ORDER BY comp.title ASC, c.gender DESC, total_votes DESC";
                            break;
                    }

                    if ($query) {
                        $stmt = $conn->prepare($query);
                        if (!empty($params)) $stmt->bind_param($param_types, ...$params);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $rows = $result->fetch_all(MYSQLI_ASSOC);

                        if (count($rows) > 0) {
                            echo "<h3 class='text-center mt-4' style='text-transform:uppercase; border-bottom: 2px solid #333; padding-bottom:5px;'>" . str_replace('_', ' ', $type) . " REPORT</h3>";
                            echo "<table class='table table-bordered results-table'><thead><tr class='bg-light'>";
                            foreach (array_keys($rows[0]) as $head) echo "<th>" . str_replace('_', ' ', strtoupper($head)) . "</th>";
                            echo "</tr></thead><tbody>";
                            $current_gender = null;
                            foreach ($rows as $row) {
                                // Add Gender Divider if changed
                                if (isset($row['gender']) && $row['gender'] !== $current_gender) {
                                    $current_gender = $row['gender'];
                                    $display_gender = strtoupper($current_gender) . " DIVISION";
                                    echo "<tr class='gender-divider-row'><td colspan='" . count($row) . "' style='background:#f0f0f0; font-weight:bold; text-align:center; color:#333; letter-spacing:2px;'>" . $display_gender . "</td></tr>";
                                }
                                echo "<tr>";
                                foreach ($row as $val) {
                                    $decoded = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    echo "<td>" . htmlspecialchars($decoded) . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<div class='alert alert-info mt-4'>No data found for " . $type . "</div>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php endforeach; ?>

        <div class="text-center small text-muted mt-5 py-4 border-top">
            Aurora Tabs | Professional Tabulation System | Generated by Reyal Solutions
        </div>
    </div>
</body>
</html>
