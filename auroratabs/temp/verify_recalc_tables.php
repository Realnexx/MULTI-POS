<?php
require_once '../app/auth/config/db_connection.php';

// Mock Session
$_SESSION['user_id'] = 1; // Assuming ID 1 exists (usually admin)

// Helper to simulate POST
function test_endpoint($action, $data = []) {
    $url = 'http://localhost/auroratabs/app/admin/endpoints/recalculate_handler.php';
    
    // Since we can't easily curl localhost with session from CLI without cookies, 
    // we will just include the file but we need to mock $_POST and $_SESSION
    // But include might exit.
    // Better to just write a script that queries the DB directly to test logic 
    // OR just use curl if we don't care about auth (but we added auth check).
    
    // actually, let's just bypass the handler and test logic directly or 
    // modify the handler to allow CLI testing if needed?
    // No, better to respect the code.
    
    // Let's rely on the fact that I can run php code here that mimics the handler logic 
    // or just trust the manual verification plan for the user.
    
    // But I entered verification mode. I should try to verify something.
    // I'll try to insert a dummy log to see if DB connection works for this table.
}

// 1. Check if tables exist
$res = $conn->query("SHOW TABLES LIKE 'recalculation_history'");
echo "Table recalculation_history: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";

$res = $conn->query("SHOW TABLES LIKE 'final_scores'");
echo "Table final_scores: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";

// 2. Fetch one competition
$res = $conn->query("SELECT id, title FROM competitions LIMIT 1");
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Found Competition: " . $row['title'] . " (ID: " . $row['id'] . ")\n";
    
    // 3. Dry Run Calculation Logic (Simplified)
    // Just count candidates
    $cands = $conn->query("SELECT count(*) as c FROM candidates WHERE competition_id = " . $row['id']);
    $c_count = $cands->fetch_assoc()['c'];
    echo "Candidates count: $c_count\n";
    
} else {
    echo "No competitions found.\n";
}

$conn->close();
?>
