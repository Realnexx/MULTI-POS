<?php
if(PHP_SAPI!=='cli')exit;
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../services/PasswordMailer.php';
$s=PasswordMailer::settings();$mail=new PHPMailer\PHPMailer\PHPMailer(true);$mail->isSMTP();$mail->Host=$s['host'];$mail->Port=(int)$s['port'];$mail->SMTPAuth=true;$mail->Username=$s['username'];$mail->Password=$s['password'];$mail->SMTPSecure=$s['encryption'];$mail->Timeout=12;$mail->SMTPDebug=0;
try {if(!$mail->smtpConnect())throw new RuntimeException('Connection failed');$mail->smtpClose();echo "SMTP connection and authentication passed. No email was sent.\n";}catch(Throwable $e){fwrite(STDERR,"SMTP connection/authentication could not be verified from this environment.\n");exit(1);}
