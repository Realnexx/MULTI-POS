<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id <= 0 && $action !== 'get_active_competitions') {
    die(json_encode(["status" => "error", "message" => "Unauthorized access"]));
}

switch ($action) {
    case 'get_active_competitions':
        $query = "SELECT id, title FROM competitions 
                  WHERE enable_audience_voting = 1 AND status = 'active' AND is_deleted = 0 
                  ORDER BY title ASC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'get_all_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY id DESC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'get_user_info':
        // Get coins (extra votes)
        $stmt = $conn->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $coins = $user_data['coins'] ?? 0;

        // Check if free vote was used today
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes 
                               WHERE user_id = ? AND package_id IS NULL 
                               AND DATE(created_at) = ?");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $vote_data = $stmt->get_result()->fetch_assoc();
        $free_vote_used = ($vote_data['count'] > 0);

        // Get total unique community voters
        $community_stmt = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM votes");
        $community_data = $community_stmt->fetch_assoc();
        $total_community_voters = $community_data['total'] ?? 0;

        // Get few recent voter names for avatars
        $avatar_stmt = $conn->query("SELECT DISTINCT u.first_name, u.last_name 
                                    FROM votes v 
                                    JOIN users u ON v.user_id = u.id 
                                    ORDER BY v.created_at DESC LIMIT 3");
        $voter_samples = [];
        while($row = $avatar_stmt->fetch_assoc()) {
            $voter_samples[] = $row['first_name'] . ' ' . $row['last_name'];
        }

        echo json_encode([
            "status" => "success", 
            "data" => [
                "extra_votes" => (int)$coins,
                "free_vote_available" => !$free_vote_used,
                "total_community_voters" => (int)$total_community_voters,
                "voter_samples" => $voter_samples
            ]
        ]);
        break;

    case 'get_candidates':
        $competition_id = intval($_POST['competition_id'] ?? 0);
        if ($competition_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Competition ID"]));
        }

        // Fetch competition info for the hero section
        $comp_stmt = $conn->prepare("SELECT title, description, start_date, start_time, end_date, end_time FROM competitions WHERE id = ?");
        $comp_stmt->bind_param("i", $competition_id);
        $comp_stmt->execute();
        $comp_info = $comp_stmt->get_result()->fetch_assoc();

        // Updated query to sum vote_count and handle packages
        $query = "SELECT c.id, c.candidate_number, c.first_name, c.last_name, c.gender, c.department, c.photo_url,
                         COALESCE(SUM(CASE WHEN v.package_id IS NOT NULL THEN vp.votes_count ELSE v.vote_count END), 0) as total_votes
                  FROM candidates c
                  LEFT JOIN votes v ON c.id = v.candidate_id
                  LEFT JOIN vote_packages vp ON v.package_id = vp.id
                  WHERE c.competition_id = ? AND c.is_deleted = 0
                  GROUP BY c.id
                  ORDER BY c.department ASC, c.gender ASC, c.candidate_number ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        echo json_encode([
            "status" => "success", 
            "data" => $candidates,
            "competition" => $comp_info
        ]);
        break;

    case 'cast_vote':
        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        $type = sanitize($_POST['type'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 1);
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if ($candidate_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Candidate ID"]));
        }

        // Check if competition has started (Voting ends when competition starts)
        $stmt = $conn->prepare("SELECT c.start_date, c.start_time, c.id as competition_id 
                               FROM competitions c 
                               JOIN candidates cand ON c.id = cand.competition_id 
                               WHERE cand.id = ?");
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $comp = $stmt->get_result()->fetch_assoc();
        
        if ($comp) {
            $start_timestamp = strtotime($comp['start_date'] . ' ' . $comp['start_time']);
            if (time() > $start_timestamp) {
                die(json_encode(["status" => "error", "message" => "Voting for this competition has already ended (Event has started)."]));
            }
            $competition_id = $comp['competition_id'];
        } else {
            die(json_encode(["status" => "error", "message" => "Competition not found."]));
        }

        if ($type === 'free') {
            // Check if already voted today
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes 
                                   WHERE user_id = ? AND package_id IS NULL 
                                   AND DATE(created_at) = ?");
            $stmt->bind_param("is", $user_id, $today);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
                die(json_encode(["status" => "error", "message" => "You have already used your free vote today."]));
            }

            // Insert free vote
            $stmt = $conn->prepare("INSERT INTO votes (candidate_id, user_id, vote_count, ip_address) VALUES (?, ?, 1, ?)");
            $stmt->bind_param("iis", $candidate_id, $user_id, $ip_address);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Free vote cast successfully!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
            }

        } else if ($type === 'paid') {
            // Check coins balance
            $stmt = $conn->prepare("SELECT coins FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $coins = $stmt->get_result()->fetch_assoc()['coins'] ?? 0;

            if ($coins < $quantity) {
                die(json_encode(["status" => "error", "message" => "Insufficient extra votes. Please buy more packages."]));
            }

            // Start transaction
            $conn->begin_transaction();
            try {
                // Deduct coins
                $stmt = $conn->prepare("UPDATE users SET coins = coins - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $user_id);
                $stmt->execute();

                // Insert paid vote record
                $stmt = $conn->prepare("INSERT INTO votes (candidate_id, user_id, vote_count, ip_address) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $candidate_id, $user_id, $quantity, $ip_address);
                $stmt->execute();

                $conn->commit();
                echo json_encode(["status" => "success", "message" => "$quantity votes cast successfully!"]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
            }

        } else {
            echo json_encode(["status" => "error", "message" => "Invalid Vote Type"]);
        }
        break;

    case 'get_rankings':
        $competition_id = intval($_POST['competition_id'] ?? 0);
        if ($competition_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid competition ID"]));
        }

        // Get total votes in this competition for ratio calculation
        $total_stmt = $conn->prepare(
            "SELECT COALESCE(SUM(v.vote_count), 0) AS grand_total
             FROM votes v
             JOIN candidates c ON v.candidate_id = c.id
             WHERE c.competition_id = ? AND c.is_deleted = 0"
        );
        $total_stmt->bind_param("i", $competition_id);
        $total_stmt->execute();
        $grand_total = $total_stmt->get_result()->fetch_assoc()['grand_total'] ?? 1;
        if ($grand_total == 0) $grand_total = 1; // avoid division by zero

        $stmt = $conn->prepare(
            "SELECT c.id, c.candidate_number, c.first_name, c.last_name, c.gender, c.department, c.photo_url,
                    COALESCE(SUM(v.vote_count), 0) AS total_votes,
                    MAX(v.created_at) AS last_vote_at
             FROM candidates c
             LEFT JOIN votes v ON c.id = v.candidate_id
             WHERE c.competition_id = ? AND c.is_deleted = 0
             GROUP BY c.id
             ORDER BY total_votes DESC"
        );
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rankings = [];
        while ($row = $result->fetch_assoc()) {
            $row['total_votes'] = (int)$row['total_votes'];
            $row['popularity'] = round(($row['total_votes'] / $grand_total) * 100, 1);
            $row['last_vote_at'] = $row['last_vote_at']
                ? date('M j, g:i A', strtotime($row['last_vote_at']))
                : 'No votes yet';
            $rankings[] = $row;
        }
        // Get competition info
        $comp_stmt = $conn->prepare("SELECT title, description, venue, start_date, start_time, end_date, end_time FROM competitions WHERE id = ?");
        $comp_stmt->bind_param("i", $competition_id);
        $comp_stmt->execute();
        $comp_info = $comp_stmt->get_result()->fetch_assoc();

        // Get Judges (Signatories)
        $judge_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Official Judge' as role
                        FROM users u
                        JOIN judge_assignments ja ON u.id = ja.judge_id
                        WHERE ja.competition_id = ?
                        GROUP BY u.id";
        $stmt = $conn->prepare($judge_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $judges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get Tabulators
        $tab_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Official Tabulator' as role
                      FROM users u
                      JOIN tabulator_assignments ta ON u.id = ta.user_id
                      WHERE ta.competition_id = ?
                      GROUP BY u.id";
        $stmt = $conn->prepare($tab_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $tabulators = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get Organizers
        $org_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Chairman' as role
                      FROM users u
                      JOIN organizer_assignments oa ON u.id = oa.user_id
                      WHERE oa.competition_id = ?
                      GROUP BY u.id";
        $stmt = $conn->prepare($org_query);
        $stmt->bind_param("i", $competition_id);
        $stmt->execute();
        $organizers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode([
            "status" => "success", 
            "data" => $rankings, 
            "competition" => $comp_info, 
            "judges" => $judges, 
            "tabulators" => $tabulators,
            "organizers" => $organizers
        ]);
        break;

    case 'get_voter_list':
        $query = "SELECT u.first_name, u.last_name, 
                         SUM(CASE WHEN v.package_id IS NOT NULL THEN vp.votes_count ELSE v.vote_count END) as total_votes,
                         MAX(v.created_at) as last_vote
                  FROM votes v
                  JOIN users u ON v.user_id = u.id
                  LEFT JOIN vote_packages vp ON v.package_id = vp.id
                  GROUP BY v.user_id
                  ORDER BY total_votes DESC, last_vote DESC";
        $result = $conn->query($query);
        $voters = [];
        while ($row = $result->fetch_assoc()) {
            $voters[] = [
                "name" => $row['first_name'] . ' ' . $row['last_name'],
                "votes" => (int)$row['total_votes'],
                "last_active" => date('M j, Y h:i A', strtotime($row['last_vote']))
            ];
        }
        echo json_encode(["status" => "success", "data" => $voters]);
        break;

    case 'process_purchase':
        $package_id = (int)($_POST['package_id'] ?? 0);
        if ($package_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid package selected"]));
        }

        // 1. Get package details
        $stmt = $conn->prepare("SELECT name, votes_count, price FROM vote_packages WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $pkg = $stmt->get_result()->fetch_assoc();
        
        if (!$pkg) {
            die(json_encode(["status" => "error", "message" => "Package not found or inactive"]));
        }

        $votes = $pkg['votes_count'];
        $amount = $pkg['price'];
        $pkg_name = $pkg['name'];

        // 2. Start Transaction
        $conn->begin_transaction();

        try {
            // Generate unique transaction code
            $txn_code = "TXN-" . time() . "-" . strtoupper(substr(md5(uniqid()), 0, 6));
            $ref_num = "GCASH-" . strtoupper(substr(md5(uniqid()), 0, 10));
            $description = "Purchased {$votes} votes via GCash ({$pkg_name})";

            // Insert into transactions
            $stmt = $conn->prepare("INSERT INTO transactions (transaction_code, user_id, description, amount, status, gateway, reference_number) 
                                   VALUES (?, ?, ?, ?, 'success', 'GCash', ?)");
            $stmt->bind_param("sisds", $txn_code, $user_id, $description, $amount, $ref_num);
            $stmt->execute();

            // Update user coins
            $stmt = $conn->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
            $stmt->bind_param("ii", $votes, $user_id);
            $stmt->execute();

            // Log activity
            require_once '../../auth/helpers/log_helper.php';
            log_activity($conn, $user_id, 'Purchase', 'Finance', "Successfully purchased {$votes} votes for ₱" . number_format($amount, 2) . " [TXN: {$txn_code}]");

            $conn->commit();
            echo json_encode([
                "status" => "success", 
                "message" => "Payment successful! {$votes} votes added.",
                "data" => [
                    "transaction_code" => $txn_code,
                    "reference_number" => $ref_num,
                    "votes_added" => $votes
                ]
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
        }
        break;

    case 'get_vote_logs':
        $competition_id = isset($_POST['competition_id']) ? intval($_POST['competition_id']) : 0;
        $date_filter = isset($_POST['date']) ? sanitize($_POST['date']) : '';
        
        $where = "TRUE";
        $params = [];
        $types = "";
        
        if ($competition_id > 0) {
            $where .= " AND c.competition_id = ?";
            $params[] = $competition_id;
            $types .= "i";
        }

        if (!empty($date_filter)) {
            $where .= " AND DATE(v.created_at) = ?";
            $params[] = $date_filter;
            $types .= "s";
        }

        $query = "SELECT 
                    v.id AS vote_id,
                    c.first_name AS cand_first, c.last_name AS cand_last, c.candidate_number,
                    u.first_name AS voter_first, u.last_name AS voter_last, u.email AS voter_email,
                    v.vote_count,
                    v.created_at,
                    v.ip_address,
                    t.transaction_code,
                    t.amount,
                    t.gateway
                  FROM votes v
                  LEFT JOIN users u ON v.user_id = u.id
                  JOIN candidates c ON v.candidate_id = c.id
                  LEFT JOIN transactions t ON v.transaction_id = t.id
                  WHERE $where
                  ORDER BY v.created_at DESC";
                  
        $stmt = $conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        echo json_encode(["status" => "success", "data" => $logs]);
        break;


    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
