<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to install the three-day demo-account access window.\n");
if(!is_dir(__DIR__.'/../scratch'))mkdir(__DIR__.'/../scratch',0700,true);session_save_path(__DIR__.'/../scratch');
require_once __DIR__.'/../config/db.php';require_once __DIR__.'/../models/DemoAccount.php';
DemoAccount::migrate($conn);
echo "Demo account support installed. The three-day window starts at the first successful sign-in. Existing roles and permissions are unchanged.\n";
