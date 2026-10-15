<?php
require_once 'app/auth/config/db_connection.php';

echo "<h3>Votes Table Columns</h3><pre>";
$result = $conn->query("DESCRIBE votes");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
echo "</pre><hr>";

echo "<h3>Testing Fixed INSERT (no competition_id)</h3>";
$stmt = $conn->prepare("INSERT INTO votes (candidate_id, user_id, vote_count, ip_address) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo "<span style='color:red'>PREPARE ERROR: " . $conn->error . "</span>";
} else {
    echo "<span style='color:green'>✅ Prepare OK — fix is verified!</span>";
}
