<?php
trait CashierSchedulePlanning {
 public static function planningReady(){
  return self::ready() && (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('cashier_schedule_series','cashier_schedule_blocks','cashier_schedule_events')")[0]['n']===3;
 }
 private static function reason($text){if(!is_string($text)||trim($text)===''||strlen($text)>500)throw new DomainException('Enter a reason of up to 500 characters.');return trim($text);}
 private static function day($value){if(!is_string($value)||!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value))throw new DomainException('Choose a valid date.');return self::stamp($value.' 00:00:00');}
 private static function clock($value){if(!is_string($value)||!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',$value))throw new DomainException('Choose a valid start and end time.');return $value;}
 private static function target($branch,$cashier){
  self::manager($branch);if(!self::planningReady())throw new DomainException('Schedule planning setup is pending.');
  $u=RecipeInventory::rows('SELECT id FROM users WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$cashier]);$ctx=User::access_context($cashier);
  if(!$u||!$ctx['staff']||(!$ctx['global']&&$ctx['branch_id']!==(int)$branch)||!User::has_permission($cashier,'access_pos'))throw new DomainException('Choose a POS cashier assigned to this branch.');
 }
 private static function auditSchedule($action,$branch,$cashier,$before,$after,$reason,$schedule=null,$series=null,$block=null){
  if(!self::planningReady())return;
  RecipeInventory::query('INSERT INTO cashier_schedule_events(schedule_id,series_id,block_id,branch_id,cashier_id,actor_id,action,reason,before_data,after_data) VALUES(?,?,?,?,?,?,?,?,?,?)','iiiiiissss',[$schedule,$series,$block,$branch,$cashier,(int)$_SESSION['user']['id'],$action,$reason,json_encode($before),json_encode($after)]);
 }
 public static function createPlan($branch,$cashier,$mode,$from,$to,$days,$start,$end,$overnight,$notes){
  if(!in_array($mode,['once','weekly'],true))throw new DomainException('Choose one date or weekly recurrence.');
  self::clock($start);self::clock($end);$a=self::day($from);
  if($mode==='once'){$b=self::stamp($from.' '.$end);if((string)$overnight==='1')$b=$b->modify('+1 day');return self::save(0,$branch,$cashier,$from.' '.$start,$b->format('Y-m-d H:i:s'),$notes);}
  return self::series(0,$branch,$cashier,$from,$to,$days,$start,$end,$overnight,$notes,'Create recurring schedule');
 }
 public static function series($id,$branch,$cashier,$from,$to,$days,$start,$end,$overnight,$notes,$reason,$status='active'){
  $id=(int)$id;$branch=(int)$branch;$cashier=(int)$cashier;$reason=self::reason($reason);
  $a=self::day($from);$b=self::day($to);self::clock($start);self::clock($end);
  if($b<$a||$b->diff($a)->days>366)throw new DomainException('Choose an effective range of at most 366 days.');
  if(!is_array($days)||!$days)throw new DomainException('Select at least one weekday.');$week=[];
  foreach($days as $d){if(!is_scalar($d)||!in_array((string)$d,['1','2','3','4','5','6','7'],true))throw new DomainException('Invalid weekday.');$week[]=(int)$d;}$week=array_values(array_unique($week));sort($week);
  if(!in_array((string)$overnight,['0','1'],true)||!in_array($status,['active','cancelled'],true)||!is_string($notes)||strlen($notes)>500)throw new DomainException('Invalid recurring schedule details.');
  $duration=(self::stamp($from.' '.$end)->getTimestamp()-self::stamp($from.' '.$start)->getTimestamp())+((int)$overnight*86400);
  if($duration<=0||$duration>86400)throw new DomainException('Choose a positive shift duration of at most 24 hours; enable next-day ending for overnight work.');
  return RecipeInventory::atomic(function()use($id,$branch,$cashier,$from,$to,$week,$start,$end,$overnight,$notes,$reason,$status,$a,$b){
   self::target($branch,$cashier);$old=$id?RecipeInventory::rows('SELECT * FROM cashier_schedule_series WHERE id=? FOR UPDATE','i',[$id]):[];
   if($id&&(!$old||(int)$old[0]['branch_id']!==$branch||(int)$old[0]['cashier_id']!==$cashier))throw new DomainException('Recurring schedule does not belong to this branch and cashier.');
   $user=(int)$_SESSION['user']['id'];$updating=(bool)$id;
   if($id)RecipeInventory::query('UPDATE cashier_schedule_series SET weekdays=?,date_from=?,date_to=?,start_time=?,end_time=?,overnight=?,status=?,notes=?,updated_by=? WHERE id=?','sssssissii',[implode(',',$week),$from,$to,$start,$end,(int)$overnight,$status,trim($notes),$user,$id]);
   else{RecipeInventory::query('INSERT INTO cashier_schedule_series(branch_id,cashier_id,weekdays,date_from,date_to,start_time,end_time,overnight,status,notes,created_by,updated_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)','iisssssissii',[$branch,$cashier,implode(',',$week),$from,$to,$start,$end,(int)$overnight,$status,trim($notes),$user,$user]);global $conn;$id=(int)$conn->insert_id;}
   $now=(new DateTimeImmutable('now',new DateTimeZone('Asia/Singapore')))->format('Y-m-d H:i:s');
   $existing=RecipeInventory::rows('SELECT * FROM cashier_schedules WHERE series_id=? FOR UPDATE','i',[$id]);$byDay=[];
   foreach($existing as $r){$byDay[$r['occurrence_date']]=$r;if($r['starts_at']>$now&&!$r['is_exception']&&$r['attendance_status']==='scheduled')RecipeInventory::query("UPDATE cashier_schedules SET status='cancelled',updated_by=? WHERE id=?",'ii',[$user,$r['id']]);}
   $count=0;
   if($status==='active')for($date=$a;$date<=$b;$date=$date->modify('+1 day')){
    if(!in_array((int)$date->format('N'),$week,true))continue;$d=$date->format('Y-m-d');$beg=$d.' '.$start.':00';$finish=self::stamp($d.' '.$end);if((int)$overnight)$finish=$finish->modify('+1 day');
    $oldDay=$byDay[$d]??null;
    if($updating&&($beg<=$now||($oldDay&&($oldDay['starts_at']<=$now||$oldDay['is_exception']||$oldDay['attendance_status']!=='scheduled'))))continue;
    $sid=self::save($oldDay?(int)$oldDay['id']:0,$branch,$cashier,$beg,$finish->format('Y-m-d H:i:s'),$notes,'scheduled');
    RecipeInventory::query('UPDATE cashier_schedules SET series_id=?,occurrence_date=?,is_exception=0 WHERE id=?','isi',[$id,$d,$sid]);$count++;
   }
   if(!$updating&&!$count)throw new DomainException('No selected weekdays fall within the effective dates.');
   self::auditSchedule('series_saved',$branch,$cashier,$old,['from'=>$from,'to'=>$to,'weekdays'=>$week,'start'=>$start,'end'=>$end,'overnight'=>$overnight,'status'=>$status,'occurrences'=>$count],$reason,null,$id);return $id;
  });
 }
 public static function editOccurrence($id,$branch,$cashier,$start,$end,$notes,$status,$reason){
  $reason=self::reason($reason);
  return RecipeInventory::atomic(function()use($id,$branch,$cashier,$start,$end,$notes,$status,$reason){self::target((int)$branch,(int)$cashier);$result=self::save($id,$branch,$cashier,$start,$end,$notes,$status);RecipeInventory::query('UPDATE cashier_schedules SET is_exception=1 WHERE id=?','i',[(int)$id]);self::auditSchedule('occurrence_override',(int)$branch,(int)$cashier,null,['starts_at'=>$start,'ends_at'=>$end,'status'=>$status],$reason,(int)$id);return $result;});
 }
 public static function attendance($id,$branch,$cashier,$status,$reason){
  $reason=self::reason($reason);if(!in_array($status,['scheduled','present','absent','on_leave','completed'],true))throw new DomainException('Choose a valid attendance status.');
  return RecipeInventory::atomic(function()use($id,$branch,$cashier,$status,$reason){self::target((int)$branch,(int)$cashier);$r=RecipeInventory::rows('SELECT * FROM cashier_schedules WHERE id=? AND branch_id=? AND cashier_id=? FOR UPDATE','iii',[(int)$id,(int)$branch,(int)$cashier]);if(!$r)throw new DomainException('Schedule not found in this branch.');RecipeInventory::query('UPDATE cashier_schedules SET attendance_status=?,is_exception=1,updated_by=? WHERE id=?','sii',[$status,(int)$_SESSION['user']['id'],(int)$id]);self::auditSchedule('attendance_changed',(int)$branch,(int)$cashier,$r[0],['attendance_status'=>$status],$reason,(int)$id,$r[0]['series_id']);});
 }
 public static function block($branch,$cashier,$start,$end,$category,$reason,$notes='',$schedule=null){
  $branch=(int)$branch;$cashier=(int)$cashier;$reason=self::reason($reason);$a=self::stamp($start);$b=self::stamp($end);
  if($b<=$a||$b->getTimestamp()-$a->getTimestamp()>367*86400)throw new DomainException('Choose a positive block range of at most 366 days.');
  if(!in_array($category,['absence','leave','rest_day','suspension','other'],true)||!is_string($notes)||strlen($notes)>500)throw new DomainException('Invalid block details.');
  return RecipeInventory::atomic(function()use($branch,$cashier,$a,$b,$category,$reason,$notes,$schedule){
   self::target($branch,$cashier);$start=$a->format('Y-m-d H:i:s');$end=$b->format('Y-m-d H:i:s');
   if($schedule){$r=RecipeInventory::rows('SELECT * FROM cashier_schedules WHERE id=? AND branch_id=? AND cashier_id=? FOR UPDATE','iii',[(int)$schedule,$branch,$cashier]);if(!$r)throw new DomainException('Choose a schedule in this branch for this cashier.');$start=$r[0]['starts_at'];$end=$r[0]['ends_at'];}
   $user=(int)$_SESSION['user']['id'];RecipeInventory::query('INSERT INTO cashier_schedule_blocks(branch_id,cashier_id,schedule_id,starts_at,ends_at,category,reason,notes,created_by,updated_by) VALUES(?,?,?,?,?,?,?,?,?,?)','iiisssssii',[$branch,$cashier,$schedule?(int)$schedule:null,$start,$end,$category,$reason,trim($notes),$user,$user]);global $conn;$id=(int)$conn->insert_id;self::auditSchedule('block_created',$branch,$cashier,null,['start'=>$start,'end'=>$end,'category'=>$category,'notes'=>trim($notes)],$reason,$schedule? (int)$schedule:null,null,$id);return $id;
  });
 }
 public static function restoreBlock($id,$branch,$cashier,$reason){
  $reason=self::reason($reason);return RecipeInventory::atomic(function()use($id,$branch,$cashier,$reason){self::target((int)$branch,(int)$cashier);$r=RecipeInventory::rows('SELECT * FROM cashier_schedule_blocks WHERE id=? AND branch_id=? AND cashier_id=? FOR UPDATE','iii',[(int)$id,(int)$branch,(int)$cashier]);if(!$r)throw new DomainException('Block not found in this branch.');if(!$r[0]['active'])return;RecipeInventory::query('UPDATE cashier_schedule_blocks SET active=0,updated_by=? WHERE id=?','ii',[(int)$_SESSION['user']['id'],(int)$id]);self::auditSchedule('block_restored',(int)$branch,(int)$cashier,$r[0],['active'=>0],$reason,$r[0]['schedule_id'],null,(int)$id);});
 }
 public static function planningFilters($input){
  $today=new DateTimeImmutable('now',new DateTimeZone('Asia/Singapore'));$from=$input['schedule_from']??$today->format('Y-m-01');$to=$input['schedule_to']??$today->format('Y-m-t');$a=self::day($from);$b=self::day($to);
  if($b<$a||$b->diff($a)->days>92)throw new DomainException('Filter schedules within a date range of at most 93 days.');
  $status=$input['schedule_status']??'';if(!in_array($status,['','scheduled','present','absent','on_leave','completed','cancelled','blocked'],true))throw new DomainException('Invalid status filter.');
  return ['from'=>$from,'to'=>$to,'cashier'=>(int)($input['schedule_cashier']??0),'status'=>$status,'view'=>($input['schedule_view']??'list')==='calendar'?'calendar':'list'];
 }
 private static function planningScope($branch,$filters,&$types,&$args,$alias='s'){
  $branch=CashShift::scope($branch);$user=(int)$_SESSION['user']['id'];$sql='';$types='';$args=[];
  if($branch){$sql.=" AND $alias.branch_id=?";$types.='i';$args[]=$branch;}
  if(!User::has_permission($user,'manage_cashier_schedules')){$sql.=" AND $alias.cashier_id=?";$types.='i';$args[]=$user;}
  elseif(!empty($filters['cashier'])){$sql.=" AND $alias.cashier_id=?";$types.='i';$args[]=$filters['cashier'];}return $sql;
 }
 public static function filteredListing($branch,$filters){
  if(!self::planningReady())return []; $sql="SELECT s.*,u.name cashier_name,b.name branch_name,EXISTS(SELECT 1 FROM cashier_schedule_blocks x WHERE x.cashier_id=s.cashier_id AND x.branch_id=s.branch_id AND x.active=1 AND (x.schedule_id=s.id OR (x.schedule_id IS NULL AND x.starts_at<s.ends_at AND x.ends_at>s.starts_at))) blocked FROM cashier_schedules s JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE 1=1";
  $sql.=self::planningScope($branch,$filters,$types,$args);$sql.=' AND s.starts_at<? AND s.ends_at>?';$types.='ss';$args[]=self::day($filters['to'])->modify('+1 day')->format('Y-m-d H:i:s');$args[]=$filters['from'].' 00:00:00';
  if($filters['status']==='cancelled')$sql.=" AND s.status='cancelled'";
  elseif($filters['status']!=='blocked'&&$filters['status']!==''){$sql.=" AND s.status='scheduled' AND s.attendance_status=?";$types.='s';$args[]=$filters['status'];}
  if($filters['status']==='blocked')$sql.=' HAVING blocked=1';
  elseif($filters['status']!=='' && $filters['status']!=='cancelled')$sql.=' HAVING blocked=0';
  return RecipeInventory::rows($sql.' ORDER BY s.starts_at,s.cashier_id LIMIT 1000',$types,$args);
 }
 public static function planningRecords($table,$branch,$filters){
  if(!self::planningReady())return [];if(!in_array($table,['cashier_schedule_blocks','cashier_schedule_series','cashier_schedule_events'],true))throw new DomainException('Invalid schedule record type.');
  $sql="SELECT s.*,u.name cashier_name,b.name branch_name FROM $table s JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE 1=1";$sql.=self::planningScope($branch,$filters,$types,$args);
  if($table==='cashier_schedule_blocks'){$sql.=' AND s.starts_at<? AND s.ends_at>?';$types.='ss';$args[]=self::day($filters['to'])->modify('+1 day')->format('Y-m-d H:i:s');$args[]=$filters['from'].' 00:00:00';}
  if($table==='cashier_schedule_series'){$sql.=' AND s.date_from<=? AND s.date_to>=?';$types.='ss';$args[]=$filters['to'];$args[]=$filters['from'];}
  return RecipeInventory::rows($sql.' ORDER BY s.id DESC LIMIT 200',$types,$args);
 }
 public static function auditTable($input){
  $user=(int)($_SESSION['user']['id']??0);$allowed=false;
  foreach(CashShift::permissions() as $p)if(User::has_permission($user,$p)){$allowed=true;break;}
  if(!$allowed||!User::access_context($user)['staff'])throw new DomainException('You cannot view cashier schedule history.');
  if(!self::planningReady())throw new DomainException('Schedule planning setup is pending.');
  foreach(['draw','start','length','cashier_id','branch_id'] as $key)if(isset($input[$key])&&!is_scalar($input[$key]))throw new DomainException('Invalid table request.');
  $draw=max(0,(int)($input['draw']??0));$offset=max(0,(int)($input['start']??0));$length=min(100,max(1,(int)($input['length']??25)));
  $filters=['cashier'=>max(0,(int)($input['cashier_id']??0))];
  $scope=' WHERE 1=1'.self::planningScope($input['branch_id']??null,$filters,$types,$args);
  $joins=' FROM cashier_schedule_events s JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id LEFT JOIN users a ON a.id=s.actor_id';
  $total=(int)RecipeInventory::rows('SELECT COUNT(*) n'.$joins.$scope,$types,$args)[0]['n'];
  $where=$scope;$from=$input['recorded_from']??'';$to=$input['recorded_to']??'';
  if(!is_string($from)||!is_string($to))throw new DomainException('Choose valid history dates.');
  if($from!==''&&$to!==''&&self::day($from)>self::day($to))throw new DomainException('The history end date must follow its start date.');
  if($from!==''){$where.=' AND s.created_at>=?';$types.='s';$args[]=self::day($from)->format('Y-m-d H:i:s');}
  if($to!==''){$where.=' AND s.created_at<?';$types.='s';$args[]=self::day($to)->modify('+1 day')->format('Y-m-d H:i:s');}
  if(isset($input['search'])&&!is_array($input['search']))throw new DomainException('Invalid search request.');
  $term=$input['search']['value']??'';if(!is_string($term)||strlen($term)>200)throw new DomainException('Search text must be within 200 characters.');$term=trim($term);
  if($term!==''){$like='%'.strtr($term,['!'=>'!!','%'=>'!%','_'=>'!_']).'%';$where.=" AND (u.name LIKE ? ESCAPE '!' OR b.name LIKE ? ESCAPE '!' OR a.name LIKE ? ESCAPE '!' OR s.reason LIKE ? ESCAPE '!' OR REPLACE(s.action,'_',' ') LIKE ? ESCAPE '!' OR s.created_at LIKE ? ESCAPE '!')";$types.='ssssss';array_push($args,$like,$like,$like,$like,$like,$like);}
  $filtered=(int)RecipeInventory::rows('SELECT COUNT(*) n'.$joins.$where,$types,$args)[0]['n'];
  $columns=['s.created_at','b.name','u.name','s.action','a.name','s.reason'];
  if(isset($input['order'])&&!is_array($input['order']))throw new DomainException('Invalid sort request.');
  $order=$input['order'][0]??[];
  if(!is_array($order)||isset($order['column'])&&!is_scalar($order['column'])||isset($order['dir'])&&!is_string($order['dir']))throw new DomainException('Invalid sort request.');
  $column=$columns[(int)($order['column']??0)]??$columns[0];$direction=strtolower($order['dir']??'desc')==='asc'?'ASC':'DESC';
  $rows=RecipeInventory::rows("SELECT s.id,s.created_at,b.name branch_name,u.name cashier_name,s.action,COALESCE(a.name,'Former user') actor_name,s.reason".$joins.$where.' ORDER BY '.$column.' '.$direction.',s.id DESC LIMIT ? OFFSET ?',$types.'ii',array_merge($args,[$length,$offset]));
  foreach($rows as &$row)$row['action']=ucwords(str_replace('_',' ',$row['action']));unset($row);
  return ['draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$filtered,'data'=>$rows];
 }

}
