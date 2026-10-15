<?php
if(PHP_SAPI!=='cli')exit('CLI only');session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../models/OnlineOrder.php';OnlineOrder::migrate($conn);echo "Online order and cart tables are ready.\n";
