<?php
include 'app/auth/config/db_connection.php';
date_default_timezone_set('Asia/Manila'); // Set to user's likely timezone or system default
echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Current Timestamp: " . time() . "\n\n";

$result = $conn->query("SELECT id, title, start_date, start_time, end_date, end_time FROM competitions WHERE enable_audience_voting = 1 AND status = 'active' AND is_deleted = 0");
while($row = $result->fetch_assoc()) {
    echo "Competition: " . $row['title'] . "\n";
    echo "Start: " . $row['start_date'] . " " . $row['start_time'] . "\n";
    echo "End: " . $row['end_date'] . " " . $row['end_time'] . "\n";
    
    $start_ts = strtotime($row['start_date'] . " " . $row['start_time']);
    $end_ts = strtotime($row['end_date'] . " " . $row['end_time']);
    
    echo "Start TS: " . $start_ts . "\n";
    echo "End TS: " . $end_ts . "\n";
    
    if (time() < $start_ts) echo "STATUS: NOT STARTED YET\n";
    else if (time() > $end_ts) echo "STATUS: ENDED\n";
    else echo "STATUS: ACTIVE\n";
    echo "--------------------------\n";
}
?>
