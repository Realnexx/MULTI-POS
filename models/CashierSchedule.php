<?php
require_once __DIR__.'/CashShift.php';
require_once __DIR__.'/CashierSchedulePlanning.php';
/** Schedule times are explicitly Asia/Singapore, independent of PHP/MySQL host timezone. */
class CashierSchedule {
 use CashierSchedulePlanning;
 public static function ready() {
  return (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('cashier_schedules','cashier_schedule_policies')")[0]['n']===2;
 }
 private static function manager($branch) {
  if(!User::has_permission($_SESSION['user']['id'] ?? 0,'manage_cashier_schedules'))throw new DomainException('You do not have permission to configure cashier schedules.');
  CashShift::scope($branch);
  if(!RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[$branch]))throw new DomainException('Choose an active branch.');
  if(!self::ready())throw new DomainException('Cashier schedule setup is pending.');
 }
 private static function stamp($value) {
  if(!is_string($value))throw new DomainException('Enter a valid schedule date and time.');
  $value=str_replace('T',' ',$value);$format=strlen($value)===16?'!Y-m-d H:i':'!Y-m-d H:i:s';
  $d=DateTimeImmutable::createFromFormat($format,$value,new DateTimeZone('Asia/Singapore'));
  if(!$d || $d->format(strlen($value)===16?'Y-m-d H:i':'Y-m-d H:i:s')!==$value)throw new DomainException('Enter a valid schedule date and time.');
  return $d;
 }
 public static function policy($branch) {
  $r=RecipeInventory::rows('SELECT * FROM cashier_schedule_policies WHERE branch_id=?','i',[$branch]);return $r?$r[0]:['early_minutes'=>0,'late_minutes'=>0];
 }
 public static function configure($branch,$early,$late) {
  $branch=(int)$branch;self::manager($branch);
  foreach([$early,$late] as $v)if(!ctype_digit((string)$v)||(int)$v>120)throw new DomainException('Grace periods must be between 0 and 120 minutes.');
  return RecipeInventory::atomic(function()use($branch,$early,$late){
   $before=self::policy($branch);$user=(int)$_SESSION['user']['id'];
   RecipeInventory::query('INSERT INTO cashier_schedule_policies(branch_id,early_minutes,late_minutes,updated_by) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE early_minutes=VALUES(early_minutes),late_minutes=VALUES(late_minutes),updated_by=VALUES(updated_by)','iiii',[$branch,(int)$early,(int)$late,$user]);
   ActivityLog::log('Cashier schedule policy','Branch #'.$branch.'; before '.json_encode($before).'; early '.$early.'; late '.$late);
  });
 }
 public static function save($id,$branch,$cashier,$start,$end,$notes,$status='scheduled') {
  $branch=(int)$branch;$cashier=(int)$cashier;$id=(int)$id;self::manager($branch);
  $a=self::stamp($start);$b=self::stamp($end);
  if($b<=$a || $b->getTimestamp()-$a->getTimestamp()>86400)throw new DomainException('Schedule end must follow its start, within 24 hours. For overnight shifts, choose the following date.');
  if(!is_string($notes)||strlen($notes)>500||!in_array($status,['scheduled','cancelled'],true))throw new DomainException('Invalid schedule details.');
  return RecipeInventory::atomic(function()use($id,$branch,$cashier,$a,$b,$notes,$status){
   // Same cashier mutex as checkout: cancellation/edits cannot race a completed sale.
   $u=RecipeInventory::rows('SELECT id FROM users WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$cashier]);$ctx=User::access_context($cashier);
   if(!$u||!$ctx['staff']||(!$ctx['global']&&$ctx['branch_id']!==$branch)||!User::has_permission($cashier,'access_pos'))throw new DomainException('Choose a POS cashier assigned to this branch.');
   $old=$id?RecipeInventory::rows('SELECT * FROM cashier_schedules WHERE id=? FOR UPDATE','i',[$id]):[];
   if($id&&(!$old||(int)$old[0]['branch_id']!==$branch||(int)$old[0]['cashier_id']!==$cashier))throw new DomainException('This schedule does not match the selected branch and cashier.');
   $start=$a->format('Y-m-d H:i:s');$end=$b->format('Y-m-d H:i:s');
   if($status==='scheduled'&&RecipeInventory::rows("SELECT id FROM cashier_schedules WHERE cashier_id=? AND status='scheduled' AND id<>? AND starts_at<? AND ends_at>? FOR UPDATE",'iiss',[$cashier,$id,$end,$start]))throw new DomainException('This cashier already has an overlapping schedule, possibly in another branch.');
   $user=(int)$_SESSION['user']['id'];
   if($id)RecipeInventory::query('UPDATE cashier_schedules SET starts_at=?,ends_at=?,notes=?,status=?,updated_by=? WHERE id=?','ssssii',[$start,$end,trim($notes),$status,$user,$id]);
   else {RecipeInventory::query('INSERT INTO cashier_schedules(branch_id,cashier_id,starts_at,ends_at,notes,status,created_by,updated_by) VALUES(?,?,?,?,?,?,?,?)','iissssii',[$branch,$cashier,$start,$end,trim($notes),$status,$user,$user]);global $conn;$id=(int)$conn->insert_id;}
   self::auditSchedule('schedule_saved',$branch,$cashier,$old,['start'=>$start,'end'=>$end,'status'=>$status,'notes'=>trim($notes)],$id?'Schedule saved':'Schedule created',$id);
   ActivityLog::log('Cashier schedule saved','Schedule #'.$id.'; branch #'.$branch.'; cashier #'.$cashier.'; before '.json_encode($old).'; '.json_encode([$start,$end,$status,trim($notes)]));return $id;
  });
 }
 public static function current($cashier,$branch) {
  if(!self::planningReady())throw new DomainException('Cashier schedule planning setup is pending. Contact your administrator.');
  $now=(new DateTimeImmutable('now',new DateTimeZone('Asia/Singapore')))->format('Y-m-d H:i:s');
  // Separate locking reads avoid a stale repeatable-read snapshot in a nested block subquery.
  $blocks=RecipeInventory::rows('SELECT schedule_id,starts_at,ends_at FROM cashier_schedule_blocks WHERE cashier_id=? AND branch_id=? AND active=1 FOR UPDATE','ii',[$cashier,$branch]);
  $candidates=RecipeInventory::rows("SELECT s.* FROM cashier_schedules s LEFT JOIN cashier_schedule_policies p ON p.branch_id=s.branch_id WHERE s.cashier_id=? AND s.branch_id=? AND s.status='scheduled' AND s.attendance_status IN ('scheduled','present') AND DATE_SUB(s.starts_at,INTERVAL COALESCE(p.early_minutes,0) MINUTE)<=? AND DATE_ADD(s.ends_at,INTERVAL COALESCE(p.late_minutes,0) MINUTE)>? ORDER BY s.starts_at FOR UPDATE",'iiss',[$cashier,$branch,$now,$now]);
  foreach($candidates as $candidate){$blocked=false;foreach($blocks as $block){if(($block['schedule_id']!==null && (int)$block['schedule_id']===(int)$candidate['id']) || ($block['schedule_id']===null && $block['starts_at']<=$now && $block['ends_at']>$now)){$blocked=true;break;}}if(!$blocked)return [$candidate];}
  return [];
 }
 public static function assertAccess($cashier,$branch) {
  // Explicit test/rollout switch, defaults to enforcement. No per-role bypass.
  if(Setting::get('cashier_schedule_enforcement','1')!=='1')return;
  if(!self::current($cashier,$branch))throw new DomainException('No active cashier schedule for this branch. Ask your manager to assign a schedule. You can still close an existing drawer in Cashier shifts & drawers.');
 }
 public static function listing($branch) {
  if(!self::ready())return [];$branch=CashShift::scope($branch);$user=(int)$_SESSION['user']['id'];
  $sql='SELECT s.*,u.name cashier_name,b.name branch_name FROM cashier_schedules s JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE 1=1';$types='';$args=[];
  if($branch){$sql.=' AND s.branch_id=?';$types.='i';$args[]=$branch;}
  if(!User::has_permission($user,'manage_cashier_schedules')){$sql.=' AND s.cashier_id=?';$types.='i';$args[]=$user;}
  return RecipeInventory::rows($sql.' ORDER BY s.starts_at DESC LIMIT 200',$types,$args);
 }
}
