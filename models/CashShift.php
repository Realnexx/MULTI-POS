<?php
require_once __DIR__.'/RecipeInventory.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/ActivityLog.php';
require_once __DIR__.'/Setting.php';

class CashShift
{
    public static function permissions() { return ['manage_cashier_schedules','view_own_shifts','open_cash_shift','close_cash_shift','cash_drawer_movements','view_cash_shifts','manage_cash_shifts','approve_cash_shifts','manage_registers','view_shift_reports']; }
    public static function ready() {
        return RecipeInventory::ready() && (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('cash_registers','cash_shift_policies','cash_shifts','cash_drawer_entries','cash_shift_events')")[0]['n']===5;
    }
    private static function actor($permissions,$branch=null) {
        $user=(int)($_SESSION['user']['id'] ?? 0); $allowed=false;
        foreach ((array)$permissions as $p) if (User::has_permission($user,$p)) $allowed=true;
        $ctx=User::access_context($user);
        if (!$allowed || !$ctx['staff']) throw new DomainException('You do not have permission for this shift operation.');
        if (!$ctx['global'] && (!$ctx['branch_id'] || ($branch!==null && (int)$branch!==$ctx['branch_id']))) throw new DomainException('You can only manage shifts in your assigned branch.');
        if (!self::ready()) throw new DomainException('Cash shift setup is pending.');
        return $user;
    }
    public static function scope($branch=null) {
        $ctx=User::access_context($_SESSION['user']['id'] ?? 0);
        if (!$ctx['global']) {
            if (!$ctx['branch_id'] || ($branch!==null && $branch!=='' && (int)$branch!==$ctx['branch_id'])) throw new DomainException('Choose your assigned branch.');
            return $ctx['branch_id'];
        }
        if ($branch===null || $branch==='') return null;
        return self::activeBranch($branch);
    }
    private static function activeBranch($branch) {
        if (!ctype_digit((string)$branch) || !RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[(int)$branch])) throw new DomainException('Choose an active branch.');
        return (int)$branch;
    }
    private static function lockBranch($branch) {
        if (!RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active' FOR UPDATE",'i',[$branch])) throw new DomainException('This branch is no longer active.');
    }
    private static function text($v,$required=true,$max=2000) {
        if (!is_scalar($v)) throw new DomainException('Invalid text value.'); $v=trim((string)$v);
        if (strlen($v)>$max || ($required && $v==='')) throw new DomainException('Enter a reason within the field length limit.'); return $v;
    }
    public static function cents($value,$signed=false) {
        $s=(string)$value; $negative=$signed && substr($s,0,1)==='-';
        $qty=InventoryQuantity::parse($negative?substr($s,1):$s,true);
        if ($qty%100) throw new DomainException('Cash amounts support two decimal places.');
        return intdiv($qty,100)*($negative?-1:1);
    }
    public static function money($cents) { return ($cents<0?'-':'').intdiv(abs($cents),100).'.'.str_pad((string)(abs($cents)%100),2,'0',STR_PAD_LEFT); }
    public static function policy($branch) {
        $rows=RecipeInventory::rows('SELECT * FROM cash_shift_policies WHERE branch_id=?','i',[$branch]);
        return $rows?$rows[0]:['require_open'=>0,'require_approval'=>1];
    }
    private static function event($shift,$branch,$action,$actor,$op,$details) {
        RecipeInventory::query('INSERT INTO cash_shift_events(shift_id,branch_id,action,actor_id,operation_id,details) VALUES(?,?,?,?,?,?)','iisiis',[$shift,$branch,$action,$actor,$op,json_encode($details)]);
        ActivityLog::log('Cash Shift '.ucwords(str_replace('_',' ',$action)),'Shift #'.($shift ?? '—').'; branch #'.$branch.'; '.$action);
        if ($op!==null) RecipeInventory::complete($op,$shift ?? $branch,$details);
    }
    public static function configure($branch,$required,$approval,$key) {
        $branch=self::activeBranch($branch); $user=self::actor('manage_registers',$branch);
        if (!in_array((string)$required,['0','1'],true) || !in_array((string)$approval,['0','1'],true)) throw new DomainException('Invalid shift policy.');
        return RecipeInventory::atomic(function()use($branch,$required,$approval,$key,$user){
            self::lockBranch($branch);
            $op=RecipeInventory::operation('cash_shift_policy',$key,[$branch,$required,$approval],$user,$branch); if ($op['result_id']!==null) return;
            $before=self::policy($branch);
            if ((string)$required==='1' && !RecipeInventory::rows("SELECT id FROM cash_registers WHERE branch_id=? AND status='active'",'i',[$branch])) throw new DomainException('Create an active register before requiring shifts.');
            RecipeInventory::query('INSERT INTO cash_shift_policies(branch_id,require_open,require_approval,updated_by) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE require_open=VALUES(require_open),require_approval=VALUES(require_approval),updated_by=VALUES(updated_by)','iiii',[$branch,(int)$required,(int)$approval,$user]);
            self::event(null,$branch,'policy_changed',$user,$op['id'],['before'=>$before,'require_open'=>$required,'require_approval'=>$approval]);
        });
    }
    public static function register($id,$branch,$name,$status,$key) {
        $branch=self::activeBranch($branch); $user=self::actor('manage_registers',$branch); $name=self::text($name,true,100);
        if (!in_array($status,['active','inactive'],true)) throw new DomainException('Invalid register status.');
        return RecipeInventory::atomic(function()use($id,$branch,$name,$status,$key,$user){
            self::lockBranch($branch);
            $op=RecipeInventory::operation('cash_shift_register',$key,[$id,$branch,$name,$status],$user,$branch); if ($op['result_id']!==null) return;
            if (RecipeInventory::rows('SELECT id FROM cash_registers WHERE branch_id=? AND name=? AND id<>?','isi',[$branch,$name,$id])) throw new DomainException('A register with this name already exists in this branch.');
            if ($id) {
                $r=RecipeInventory::rows('SELECT * FROM cash_registers WHERE id=? AND branch_id=? FOR UPDATE','ii',[$id,$branch]);
                if (!$r) throw new DomainException('Register is not available in this branch.');
                if (RecipeInventory::rows('SELECT id FROM cash_shifts WHERE active_register=?','i',[$id])) throw new DomainException('Close the active shift before editing this register.');
                if ($status==='inactive' && self::policy($branch)['require_open'] && !RecipeInventory::rows("SELECT id FROM cash_registers WHERE branch_id=? AND id<>? AND status='active'",'ii',[$branch,$id])) throw new DomainException('Keep an active register while shifts are mandatory.');
                RecipeInventory::query('UPDATE cash_registers SET name=?,status=? WHERE id=?','ssi',[$name,$status,$id]);
            } else {
                RecipeInventory::query('INSERT INTO cash_registers(branch_id,name,status,created_by) VALUES(?,?,?,?)','issi',[$branch,$name,$status,$user]);
                global $conn; $id=(int)$conn->insert_id;
            }
            self::event(null,$branch,'register_saved',$user,$op['id'],['register_id'=>$id,'name'=>$name,'status'=>$status]);
        });
    }
    private static function lockCashier($cashier) {
        if (!RecipeInventory::rows('SELECT id FROM users WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$cashier])) throw new DomainException('Cashier is not available.');
    }
    public static function open($register,$cashier,$float,$key,$previousShift=0) {
        $r=RecipeInventory::rows('SELECT * FROM cash_registers WHERE id=?','i',[$register]);
        if (!$r) throw new DomainException('Choose a register.'); $branch=(int)$r[0]['branch_id'];
        self::activeBranch($branch); $user=self::actor(['open_cash_shift','manage_cash_shifts'],$branch);
        if ($cashier!==$user && !User::has_permission($user,'manage_cash_shifts')) throw new DomainException('You can only open your own shift.');
        $ctx=User::access_context($cashier);
        if (!$ctx['staff'] || (!$ctx['global'] && $ctx['branch_id']!==$branch) || !User::has_permission($cashier,'access_pos')) throw new DomainException('Assign a POS cashier from this branch.');
        $float=self::cents($float);$previousShift=(int)$previousShift;
        return RecipeInventory::atomic(function()use($register,$cashier,$float,$key,$branch,$user,$previousShift){
            self::lockCashier($cashier);
            $current=User::access_context($cashier);
            if (!$current['staff'] || (!$current['global'] && $current['branch_id']!==$branch) || !User::has_permission($cashier,'access_pos')) throw new DomainException('Cashier assignment changed. Choose a current branch cashier.');
            $r=RecipeInventory::rows('SELECT * FROM cash_registers WHERE id=? AND branch_id=? FOR UPDATE','ii',[$register,$branch]);
            if (!$r || $r[0]['status']!=='active') throw new DomainException('Choose an active register.');
            $payload=[$register,$cashier,$float];if($previousShift)$payload[]=$previousShift;
            $op=RecipeInventory::operation('cash_shift_open',$key,$payload,$user,$branch); if ($op['result_id']!==null) return (int)$op['result_id'];
            if (RecipeInventory::rows('SELECT id FROM cash_shifts WHERE active_cashier=? OR active_register=?','ii',[$cashier,$register])) throw new DomainException('The cashier or register already has an active shift.');
            if($previousShift) {
                $prior=RecipeInventory::rows("SELECT * FROM cash_shifts WHERE register_id=? AND branch_id=? AND status<>'open' ORDER BY id DESC LIMIT 1 FOR UPDATE",'ii',[$register,$branch]);
                if(!$prior || (int)$prior[0]['id']!==$previousShift || (int)$prior[0]['cashier_id']!==$cashier || $cashier!==$user || $prior[0]['counted_cash']===null || self::cents($prior[0]['counted_cash'])!==$float)throw new DomainException('The previous counted cash changed or is unavailable for this register. Refresh and count the opening cash again.');
            }
            $policy=self::policy($branch);
            RecipeInventory::query('INSERT INTO cash_shifts(branch_id,register_id,cashier_id,opened_by,opening_float,require_approval,active_cashier,active_register) VALUES(?,?,?,?,?,?,?,?)','iiiisiii',[$branch,$register,$cashier,$user,self::money($float),(int)$policy['require_approval'],$cashier,$register]);
            global $conn; $id=(int)$conn->insert_id;
            self::event($id,$branch,'opened',$user,$op['id'],['opening_float'=>self::money($float),'cashier'=>$cashier,'register'=>$register,'previous_counted_shift'=>$previousShift?:null]); return $id;
        });
    }
    /** Called inside checkout/refund's inventory transaction, before product locks. */
    public static function context($cashier,$branch,$force=false) {
        if (!self::ready()) return null;
        if ((int)($_SESSION['user']['id'] ?? 0)!==(int)$cashier) throw new DomainException('Cashier session does not match this payment.');
        self::lockCashier($cashier);
        if ($force && self::posIntegrated()) { require_once __DIR__.'/CashierSchedule.php'; CashierSchedule::assertAccess($cashier,$branch); }
        $s=RecipeInventory::rows("SELECT * FROM cash_shifts WHERE active_cashier=? FOR UPDATE",'i',[$cashier]);
        if ($s && (int)$s[0]['branch_id']!==(int)$branch) throw new DomainException('Your active shift belongs to another branch. Close it before changing terminals.');
        if ($s) self::assertUnlocked($s[0]);
        if (!$s && ($force || self::policy($branch)['require_open'])) throw new DomainException('Open a cashier shift and select a register before recording this payment or refund.');
        return $s?$s[0]:null;
    }
    public static function sale($shift,$sale,$payment,$amount,$actor) {
        if (!$shift) return;
        RecipeInventory::query('INSERT INTO cash_drawer_entries(shift_id,kind,amount,payment_method,sale_id,actor_id,reason) VALUES(?,?,?,?,?,?,?)','isssiis',[$shift['id'],$payment==='Cash'?'cash_sale':'electronic_sale',$amount,$payment,$sale,$actor,'Completed sale #'.$sale]);
    }
    public static function refund($sale,$actor) {
        if (!self::ready() || $sale['status']!=='Completed') return;
        if (RecipeInventory::rows("SELECT id FROM cash_drawer_entries WHERE sale_id=? AND kind IN ('cash_refund','electronic_refund')",'i',[$sale['id']])) return;
        $tracked=RecipeInventory::rows("SELECT id FROM cash_drawer_entries WHERE sale_id=? AND kind IN ('cash_sale','electronic_sale')",'i',[$sale['id']]);
        $s=self::context($actor,(int)$sale['branch_id'],(bool)$tracked); if (!$s) return;
        $amount=self::cents($sale['final_amount']);
        if ($sale['payment_method']==='Cash' && self::totals($s)['expected_cents']<$amount) throw new DomainException('The current drawer has insufficient cash for this refund. Record an authorized cash-in first.');
        RecipeInventory::query('INSERT INTO cash_drawer_entries(shift_id,kind,amount,payment_method,sale_id,actor_id,reason) VALUES(?,?,?,?,?,?,?)','isssiis',[$s['id'],$sale['payment_method']==='Cash'?'cash_refund':'electronic_refund',self::money(-$amount),$sale['payment_method'],$sale['id'],$actor,'Full refund for sale #'.$sale['id']]);
    }
    private static function locked($id) {
        $row=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=?','i',[$id]);
        if (!$row) throw new DomainException('Shift not found.');
        // This read must be made before the transaction starts by the public method.
        return $row[0];
    }
    private static function owned($s,$permission) {
        $user=self::actor([$permission,'manage_cash_shifts'],(int)$s['branch_id']);
        if ((int)$s['cashier_id']!==$user && !User::has_permission($user,'manage_cash_shifts')) throw new DomainException('You can only operate your own drawer.'); return $user;
    }
    public static function totals($s) {
        $r=['opening_cents'=>self::cents($s['opening_float']),'cash_sales_cents'=>0,'electronic_sales_cents'=>0,'cash_refunds_cents'=>0,'electronic_refunds_cents'=>0,'cash_in_cents'=>0,'cash_out_cents'=>0,'correction_cents'=>0,'payments'=>[]];
        // Current reads prevent a waiting cash-out/refund from using an older balance.
        foreach (RecipeInventory::rows('SELECT kind,payment_method,amount FROM cash_drawer_entries WHERE shift_id=? ORDER BY id FOR UPDATE','i',[$s['id']]) as $e) {
            $v=self::cents($e['amount'],true);
            $k=['cash_sale'=>'cash_sales_cents','electronic_sale'=>'electronic_sales_cents','cash_refund'=>'cash_refunds_cents','electronic_refund'=>'electronic_refunds_cents','cash_in'=>'cash_in_cents','cash_out'=>'cash_out_cents','correction'=>'correction_cents'][$e['kind']];
            $r[$k]+=$v;
            if (in_array($e['kind'],['cash_sale','electronic_sale','cash_refund','electronic_refund'],true)) $r['payments'][$e['payment_method']]=($r['payments'][$e['payment_method']] ?? 0)+$v;
        }
        $r['expected_cents']=$r['opening_cents']+$r['cash_sales_cents']+$r['cash_refunds_cents']+$r['cash_in_cents']+$r['cash_out_cents']+$r['correction_cents']; return $r;
    }
    public static function move($id,$kind,$direction,$amount,$reason,$key) {
        $initial=self::locked($id); $reason=self::text($reason); $amount=self::cents($amount);
        if (!$amount || !in_array($kind,['cash_in','cash_out','correction'],true) || !in_array($direction,['in','out'],true)) throw new DomainException('Choose a valid cash movement and positive amount.');
        if ($kind==='cash_out' || ($kind==='correction' && $direction==='out')) $amount=-$amount;
        return RecipeInventory::atomic(function()use($initial,$id,$kind,$amount,$reason,$key){
            self::lockCashier($initial['cashier_id']); $s=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=? FOR UPDATE','i',[$id])[0];
            $user=self::owned($s,$kind==='correction'?'manage_cash_shifts':'cash_drawer_movements');
            $op=RecipeInventory::operation('cash_shift_move',$key,[$id,$kind,$amount,$reason],$user,(int)$s['branch_id']); if ($op['result_id']!==null) return;
            if ($s['status']!=='open') throw new DomainException('Closed shift ledgers cannot be changed.');
            self::assertUnlocked($s);
            if (self::totals($s)['expected_cents']+$amount<0) throw new DomainException('Cash-out cannot exceed the cash available in this drawer.');
            RecipeInventory::query('INSERT INTO cash_drawer_entries(shift_id,kind,amount,actor_id,reason) VALUES(?,?,?,?,?)','issis',[$id,$kind,self::money($amount),$user,$reason]);
            self::event($id,(int)$s['branch_id'],$kind,$user,$op['id'],['amount'=>self::money($amount),'reason'=>$reason]);
        });
    }
    public static function close($id,$counted,$notes,$key) {
        $initial=self::locked($id); $counted=self::cents($counted); $notes=self::text($notes,false);
        return RecipeInventory::atomic(function()use($initial,$id,$counted,$notes,$key){
            self::lockCashier($initial['cashier_id']); $s=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=? FOR UPDATE','i',[$id])[0]; $user=self::owned($s,'close_cash_shift');
            $op=RecipeInventory::operation('cash_shift_close',$key,[$id,$counted,$notes],$user,(int)$s['branch_id']); if ($op['result_id']!==null) return;
            if ($s['status']!=='open') throw new DomainException('This shift is already closed.');
            if ((int)$s['cashier_id']===$user) self::assertUnlocked($s);
            $t=self::totals($s); $difference=$counted-$t['expected_cents'];
            if ($difference!==0 && $notes==='') throw new DomainException('Explain the cash overage or shortage before closing.');
            $status=$s['require_approval']?'pending_review':'closed';
            RecipeInventory::query('UPDATE cash_shifts SET status=?,closed_by=?,closed_at=NOW(),expected_cash=?,counted_cash=?,difference=?,close_notes=?,active_cashier=NULL,active_register=NULL WHERE id=?','sissssi',[$status,$user,self::money($t['expected_cents']),self::money($counted),self::money($difference),$notes,$id]);
            self::event($id,(int)$s['branch_id'],'closed',$user,$op['id'],['totals'=>$t,'counted'=>self::money($counted),'difference'=>self::money($difference),'notes'=>$notes]);
        });
    }
    public static function review($id,$action,$counted,$notes,$key) {
        $notes=self::text($notes); if (!in_array($action,['approve','recount'],true)) throw new DomainException('Invalid review action.');
        return RecipeInventory::atomic(function()use($id,$action,$counted,$notes,$key){
            $s=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=? FOR UPDATE','i',[$id]); if (!$s) throw new DomainException('Shift not found.'); $s=$s[0];
            $user=self::actor($action==='approve'?'approve_cash_shifts':'manage_cash_shifts',(int)$s['branch_id']);
            if ($user===(int)$s['cashier_id'] || $user===(int)$s['closed_by']) throw new DomainException('Another authorized manager must review this shift.');
            $value=$action==='recount'?self::cents($counted):null;
            $op=RecipeInventory::operation('cash_shift_'.$action,$key,[$id,$value,$notes],$user,(int)$s['branch_id']); if ($op['result_id']!==null) return;
            if ($s['status']!=='pending_review') throw new DomainException('Only shifts awaiting review can be approved or recounted.');
            if ($action==='approve') RecipeInventory::query("UPDATE cash_shifts SET status='approved',approved_by=?,approved_at=NOW() WHERE id=?",'ii',[$user,$id]);
            else RecipeInventory::query('UPDATE cash_shifts SET counted_cash=?,difference=?,close_notes=? WHERE id=?','sssi',[self::money($value),self::money($value-self::cents($s['expected_cash'],true)),$notes,$id]);
            self::event($id,(int)$s['branch_id'],$action,$user,$op['id'],['previous_count'=>$s['counted_cash'],'previous_difference'=>$s['difference'],'counted'=>$value!==null?self::money($value):$s['counted_cash'],'notes'=>$notes]);
        });
    }
    private static function allVisible($user) {
        foreach (['view_cash_shifts','manage_cash_shifts','approve_cash_shifts','view_shift_reports'] as $p) if (User::has_permission($user,$p)) return true; return false;
    }
    public static function listing($branch=null) {
        $user=self::actor(self::permissions()); $branch=self::scope($branch); $params=[]; $types='';
        $sql='SELECT s.*,r.name register_name,u.name cashier_name,b.name branch_name FROM cash_shifts s JOIN cash_registers r ON r.id=s.register_id JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE 1=1';
        if ($branch) { $sql.=' AND s.branch_id=?';$types.='i';$params[]=$branch; }
        if (!self::allVisible($user)) { $sql.=' AND s.cashier_id=?';$types.='i';$params[]=$user; }
        return RecipeInventory::rows($sql." ORDER BY (s.status='open') DESC,s.id DESC LIMIT 200",$types,$params);
    }
    public static function detail($id) {
        $s=RecipeInventory::rows('SELECT s.*,r.name register_name,u.name cashier_name,b.name branch_name FROM cash_shifts s JOIN cash_registers r ON r.id=s.register_id JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE s.id=?','i',[$id]);
        if (!$s) throw new DomainException('Shift not found.'); $s=$s[0]; $user=self::actor(self::permissions(),(int)$s['branch_id']);
        if (!self::allVisible($user) && (int)$s['cashier_id']!==$user) throw new DomainException('You can only view your own shifts.');
        $s['totals']=self::totals($s);
        $s['entries']=RecipeInventory::rows('SELECT e.*,u.name actor_name FROM cash_drawer_entries e LEFT JOIN users u ON u.id=e.actor_id WHERE shift_id=? ORDER BY e.id','i',[$id]);
        $s['events']=RecipeInventory::rows('SELECT e.*,u.name actor_name FROM cash_shift_events e LEFT JOIN users u ON u.id=e.actor_id WHERE shift_id=? ORDER BY e.id','i',[$id]); return $s;
    }
    public static function registers($branch=null) {
        self::actor(self::permissions()); $branch=self::scope($branch);
        return RecipeInventory::rows('SELECT r.*,b.name branch_name,(SELECT id FROM cash_shifts WHERE active_register=r.id) active_shift FROM cash_registers r JOIN branches b ON b.id=r.branch_id'.($branch?' WHERE r.branch_id=?':'').' ORDER BY b.name,r.name',$branch?'i':'',$branch?[$branch]:[]);
    }
    public static function report($branch,$from,$to) {
        self::actor('view_shift_reports'); $branch=self::scope($branch);
        foreach ([$from,$to] as $date) { $d=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$date); if (!$d || $d->format('Y-m-d')!==$date) throw new DomainException('Choose valid report dates.'); }
        if ($from>$to) throw new DomainException('Start date must not follow end date.');
        $sql="SELECT s.*,r.name register_name,u.name cashier_name,b.name branch_name FROM cash_shifts s JOIN cash_registers r ON r.id=s.register_id JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE s.closed_at>=? AND s.closed_at<DATE_ADD(?,INTERVAL 1 DAY)";
        $types='ss';$params=[$from,$to]; if ($branch) { $sql.=' AND s.branch_id=?';$types.='i';$params[]=$branch; }
        return RecipeInventory::rows($sql.' ORDER BY s.closed_at,s.id',$types,$params);
    }
    public static function drawerReady() { return (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='cash_shift_locks'")[0]['n']===1; }
    public static function posIntegrated() { return self::ready() && self::drawerReady() && Setting::get('pos_drawer_integration','1')==='1'; }
    private static function assertUnlocked($s) {
        if (self::drawerReady() && RecipeInventory::rows('SELECT shift_id FROM cash_shift_locks WHERE shift_id=? AND locked=1 FOR UPDATE','i',[$s['id']])) throw new DomainException('This drawer is locked. Unlock it on the POS before continuing.');
    }
    public static function lockDrawer($id,$key) {
        $initial=self::locked($id);
        return RecipeInventory::atomic(function()use($initial,$id,$key){
            self::lockCashier($initial['cashier_id']); $s=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=? FOR UPDATE','i',[$id])[0];
            $user=self::actor('lock_cash_drawer',(int)$s['branch_id']);
            if ((int)$s['cashier_id']!==$user || $s['status']!=='open') throw new DomainException('Only the cashier can lock their active drawer.');
            $op=RecipeInventory::operation('cash_drawer_lock',$key,[$id],$user,(int)$s['branch_id']); if($op['result_id']!==null)return;
            $old=RecipeInventory::rows('SELECT * FROM cash_shift_locks WHERE shift_id=? FOR UPDATE','i',[$id]);
            if($old && $old[0]['locked']) { RecipeInventory::complete($op['id'],$id,['already_locked'=>true]);return; }
            RecipeInventory::query('INSERT INTO cash_shift_locks(shift_id,locked,locked_by,locked_at) VALUES(?,1,?,NOW()) ON DUPLICATE KEY UPDATE locked=1,locked_by=VALUES(locked_by),locked_at=NOW(),failed_attempts=0,blocked_until=NULL','ii',[$id,$user]);
            self::event($id,(int)$s['branch_id'],'drawer_locked',$user,$op['id'],[]);
        });
    }
    public static function unlockDrawer($id,$secret) {
        if (!is_string($secret) || strlen($secret)>200 || $secret==='') throw new DomainException('Enter your personal POS PIN or account password.');
        $initial=self::locked($id);
        require_once __DIR__.'/SystemPolicy.php';
        $actor=self::actor('lock_cash_drawer',(int)$initial['branch_id']);
        if((int)$initial['cashier_id']!==$actor)throw new DomainException('Only the assigned cashier can unlock this drawer.');
        $wait=SystemPolicy::reservePinAttempt($actor);
        if($wait)throw new DomainException('Too many authentication attempts. Try again in '.$wait.' seconds. Refreshing will not reset this limit.');
        $result=RecipeInventory::atomic(function()use($initial,$id,$secret){
            self::lockCashier($initial['cashier_id']); $s=RecipeInventory::rows('SELECT * FROM cash_shifts WHERE id=? FOR UPDATE','i',[$id])[0];
            $user=self::actor('lock_cash_drawer',(int)$s['branch_id']);
            if((int)$s['cashier_id']!==$user || $s['status']!=='open')throw new DomainException('Only the assigned cashier can unlock this drawer.');
            $rows=RecipeInventory::rows('SELECT *,blocked_until>NOW() is_blocked FROM cash_shift_locks WHERE shift_id=? FOR UPDATE','i',[$id]);
            if(!$rows || !$rows[0]['locked'])return 'ok'; $lock=$rows[0];
            if($lock['is_blocked'])return 'blocked';
            $account=User::find_by_id($user);
            require_once __DIR__.'/SystemPolicy.php';
            if(!SystemPolicy::verifyAccessPin($user,$secret) && !password_verify($secret,$account['password'])) {
                $attempts=$lock['blocked_until']!==null?1:(int)$lock['failed_attempts']+1;
                RecipeInventory::query('UPDATE cash_shift_locks SET failed_attempts=?,blocked_until=IF(? >= 5,DATE_ADD(NOW(),INTERVAL 60 SECOND),NULL) WHERE shift_id=?','iii',[$attempts,$attempts,$id]);
                self::event($id,(int)$s['branch_id'],'unlock_failed',$user,null,['attempt'=>$attempts]); return $attempts>=5?'blocked':'invalid';
            }
            RecipeInventory::query('UPDATE cash_shift_locks SET locked=0,failed_attempts=0,blocked_until=NULL WHERE shift_id=?','i',[$id]);
            self::event($id,(int)$s['branch_id'],'drawer_unlocked',$user,null,[]); return 'ok';
        });
        if($result!=='ok')throw new DomainException($result==='blocked'?'Too many attempts. Wait one minute before trying again.':'Incorrect PIN or password. The drawer remains locked.');
        SystemPolicy::clearPinAttempts($actor);
    }
    public static function posState($branch,$transactions=false) {
        $branch=self::activeBranch($branch); $user=self::actor('access_pos',$branch);
        $rows=RecipeInventory::rows('SELECT s.*,r.name register_name,b.name branch_name FROM cash_shifts s JOIN cash_registers r ON r.id=s.register_id JOIN branches b ON b.id=s.branch_id WHERE active_cashier=?','i',[$user]);
        $perms=[];foreach(['open_cash_shift','close_cash_shift','cash_drawer_movements','lock_cash_drawer'] as $p)$perms[$p]=User::has_permission($user,$p) || (in_array($p,['open_cash_shift','close_cash_shift','cash_drawer_movements'],true) && User::has_permission($user,'manage_cash_shifts'));
        $state=['shift'=>null,'permissions'=>$perms,'registers'=>RecipeInventory::rows("SELECT r.id,r.name FROM cash_registers r WHERE branch_id=? AND status='active' AND NOT EXISTS(SELECT 1 FROM cash_shifts s WHERE s.active_register=r.id) ORDER BY name",'i',[$branch])];
        require_once __DIR__.'/CashierSchedule.php';
        $state['schedule_allowed']=true;
        try { CashierSchedule::assertAccess($user,$branch); } catch(DomainException $e) { $state['schedule_allowed']=false;$state['schedule_message']=$e->getMessage(); }
        foreach($state['registers'] as &$register) {
            $prior=RecipeInventory::rows("SELECT id,cashier_id,counted_cash,closed_at FROM cash_shifts WHERE register_id=? AND branch_id=? AND status<>'open' ORDER BY id DESC LIMIT 1",'ii',[(int)$register['id'],$branch]);
            $register['previous_cash']=$prior && (int)$prior[0]['cashier_id']===$user && $prior[0]['counted_cash']!==null ? ['shift_id'=>(int)$prior[0]['id'],'amount'=>$prior[0]['counted_cash'],'closed_at'=>$prior[0]['closed_at']]:null;
        } unset($register);
        if(!$rows)return $state; $s=$rows[0];
        $locks=RecipeInventory::rows('SELECT locked FROM cash_shift_locks WHERE shift_id=?','i',[$s['id']]); $locked=$locks && (bool)$locks[0]['locked'];
        $state['shift']=['id'=>$s['id'],'branch_id'=>$s['branch_id'],'branch_name'=>$s['branch_name'],'register_name'=>$s['register_name'],'opened_at'=>$s['opened_at'],'locked'=>$locked,'matches_branch'=>(int)$s['branch_id']===$branch];
        if(!$locked && (int)$s['branch_id']===$branch) {
            $state['shift']['totals']=self::totals($s);
            if($transactions)$state['transactions']=RecipeInventory::rows('SELECT id,kind,amount,payment_method,sale_id,reason,created_at FROM cash_drawer_entries WHERE shift_id=? ORDER BY id DESC LIMIT 200','i',[$s['id']]);
        } elseif($transactions)throw new DomainException('Unlock the drawer in its assigned branch before viewing transactions.');
        return $state;
    }
}
