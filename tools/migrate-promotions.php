<?php
if(PHP_SAPI!=='cli')exit;
if(!in_array('--apply',$argv,true))exit("Use --apply to install promotions.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../models/Promotion.php';Promotion::migrate($conn);echo "Promotions, redemptions, discount approvals and permissions installed.\n";
