<?php
$c = new mysqli("localhost", "root", "", "aurora");
file_put_contents("tmp_schema.txt", "TXN:\n" . $c->query("SHOW CREATE TABLE transactions")->fetch_assoc()["Create Table"] . "\n\nVOTES:\n" . $c->query("SHOW CREATE TABLE votes")->fetch_assoc()["Create Table"]);
?>
