<?php
include 'app/auth/config/db_connection.php';
$tables = ['votes', 'vote_packages', 'users', 'competitions', 'candidates', 'transactions', 'vote_transactions', 'wallets', 'user_wallets'];
$out = fopen('c:\xampp\htdocs\auroratabs\tmp_db_schema_final.txt', 'w');
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows > 0) {
        fwrite($out, "Table: $table\n");
        $cols = $conn->query("SHOW COLUMNS FROM $table");
        while ($col = $cols->fetch_assoc()) {
            fwrite($out, "  - {$col['Field']} ({$col['Type']})\n");
        }
    }
}
fclose($out);
?>
