<?php
header('Content-Type: text/html; charset=UTF-8');
$conn_ojt = new mysqli("ecr.bnsc.edu.ph","root","Bnscadmin_2021","mlangit_bnsc");
// Check connection
if ($conn_ojt -> connect_errno) {
  echo "Failed to connect to MySQL: " . $conn_ojt -> connect_error;
  exit();
}//else{echo "Connected.";}
$conn_ojt->set_charset("utf8mb4");

error_reporting(1);




?>