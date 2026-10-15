<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "Testing OJT Database Connection...<br>";
require_once("app/auth/config/ojt_db.php");
if (isset($conn_ojt)) {
    echo "Connection Object Found.<br>";
    if ($conn_ojt->connect_error) {
        echo "Connection Error: " . $conn_ojt->connect_error . "<br>";
    } else {
        echo "Connection Successful!<br>";
        $res = $conn_ojt->query("SELECT COUNT(*) as count FROM students");
        if ($res) {
            $row = $res->fetch_assoc();
            echo "Student count: " . $row['count'] . "<br>";
        } else {
            echo "Query failed: " . $conn_ojt->error . "<br>";
        }
    }
} else {
    echo "Connection Object NOT Found.<br>";
}
?>
