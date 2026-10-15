<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT id, first_name, last_name, candidate_number FROM candidates WHERE is_deleted = 0");
$out = "";
while($row = $res->fetch_assoc()) {
    $out .= $row['id'] . ": " . $row['first_name'] . " " . $row['last_name'] . " (#" . $row['candidate_number'] . ")\n";
}
file_put_contents('tmp_candidates_list.txt', $out);
echo "Candidates written to tmp_candidates_list.txt\n";
