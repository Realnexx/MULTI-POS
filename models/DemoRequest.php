<?php
class DemoRequest {
 public static function migrate($db){$db->query("CREATE TABLE IF NOT EXISTS demo_requests(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,request_key CHAR(64) NOT NULL UNIQUE,full_name VARCHAR(150) NOT NULL,email VARCHAR(254) NOT NULL,phone VARCHAR(40) NOT NULL,business_name VARCHAR(150) NOT NULL,business_type VARCHAR(80) NOT NULL,branch_count SMALLINT UNSIGNED NOT NULL,message TEXT NOT NULL,status VARCHAR(16) NOT NULL DEFAULT 'new',created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NULL,INDEX(status,created_at)) ENGINE=InnoDB");}
 public static function validate(array $input){
  $data=[];foreach(['full_name'=>150,'email'=>254,'phone'=>40,'business_name'=>150,'business_type'=>80,'message'=>3000] as $key=>$max){$v=$input[$key]??'';if(!is_string($v)||mb_strlen(trim($v))>$max)throw new DomainException('Please check the length of your details.');$data[$key]=trim($v);}
  if($data['full_name']===''||$data['business_name']===''||$data['business_type']==='')throw new DomainException('Enter your name, business name and business type.');
  if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL))throw new DomainException('Enter a valid email address.');
  if(!preg_match('/^[+0-9 ()-]{7,40}$/D',$data['phone']))throw new DomainException('Enter a valid contact number.');
  $data['branch_count']=filter_var($input['branch_count']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]);
  if($data['branch_count']===false)throw new DomainException('Enter a branch count between 1 and 10,000.');
  if(($input['consent']??'')!=='1')throw new DomainException('Please agree to be contacted about your demo request.');
  return $data;
 }
 public static function submit(array $input,$key){
  if(!is_string($key)||!preg_match('/^[a-f0-9]{64}$/D',$key))throw new DomainException('Reload the page and try again.');
  $d=self::validate($input);
  if(!empty($input['pricing_tier'])){require_once __DIR__.'/Pricing.php';$plan=is_string($input['pricing_tier'])?Pricing::find($input['pricing_tier']):null;if(!$plan)throw new DomainException('This plan is no longer available. Please review pricing.');$d['message']='Selected plan: '.$plan['name'].' | PHP '.number_format((float)$plan['price'],2).' | '.$plan['billing']."\nScope: ".$plan['scope']."\n\n".$d['message'];}
  global $conn;
  $s=$conn->prepare('INSERT INTO demo_requests(request_key,full_name,email,phone,business_name,business_type,branch_count,message) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
  $s->bind_param('ssssssis',$key,$d['full_name'],$d['email'],$d['phone'],$d['business_name'],$d['business_type'],$d['branch_count'],$d['message']);$s->execute();return (int)$conn->insert_id;
 }
 public static function recent(){return $GLOBALS['conn']->query('SELECT * FROM demo_requests ORDER BY id DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);}
 public static function updateStatus($id,$status){if(!in_array($status,['new','contacted','closed'],true))throw new DomainException('Choose a valid request status.');$id=filter_var($id,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$id)throw new DomainException('Invalid request.');$s=$GLOBALS['conn']->prepare('UPDATE demo_requests SET status=?,updated_at=UTC_TIMESTAMP() WHERE id=?');$s->bind_param('si',$status,$id);$s->execute();}
 public static function counts(){return $GLOBALS['conn']->query("SELECT COUNT(*) total,COALESCE(SUM(status='new'),0) new,COALESCE(SUM(status='contacted'),0) contacted,COALESCE(SUM(status='closed'),0) closed FROM demo_requests")->fetch_assoc();}
 public static function dataTable(array $params){
  global $conn;$start=max(0,(int)($params['start']??0));$length=max(1,min(100,(int)($params['length']??10)));$draw=max(0,(int)($params['draw']??0));
  $search=is_string($params['search']['value']??null)?mb_substr(trim($params['search']['value']),0,150):'';$status=$params['status']??'';$where=[];$types='';$args=[];
  if(in_array($status,['new','contacted','closed'],true)){$where[]='status=?';$types.='s';$args[]=$status;}
  if($search!==''){$where[]='(business_name LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR business_type LIKE ? OR message LIKE ?)';for($i=0;$i<6;$i++){$types.='s';$args[]='%'.$search.'%';}}
  $filter=$where?' WHERE '.implode(' AND ',$where):'';$counts=self::counts();$stmt=$conn->prepare('SELECT COUNT(*) n FROM demo_requests'.$filter);if($args)$stmt->bind_param($types,...$args);$stmt->execute();$filtered=(int)$stmt->get_result()->fetch_assoc()['n'];
  $columns=['id','business_name','full_name','business_type','branch_count','status','created_at'];$column=$columns[(int)($params['order'][0]['column']??0)]??'id';$direction=strtolower($params['order'][0]['dir']??'desc')==='asc'?'ASC':'DESC';
  $stmt=$conn->prepare('SELECT id,full_name,email,phone,business_name,business_type,branch_count,message,status,created_at FROM demo_requests'.$filter." ORDER BY $column $direction,id DESC LIMIT ? OFFSET ?");$types.='ii';$args[]=$length;$args[]=$start;$stmt->bind_param($types,...$args);$stmt->execute();
  return ['draw'=>$draw,'recordsTotal'=>(int)$counts['total'],'recordsFiltered'=>$filtered,'data'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC),'summary'=>$counts];
 }

}
