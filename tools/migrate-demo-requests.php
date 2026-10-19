<?php
if(PHP_SAPI!=='cli')exit;
if(!in_array('--apply',$argv,true))exit("Use --apply to install demo request storage.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../models/DemoRequest.php';DemoRequest::migrate($conn);echo "Demo request storage installed.\n";
