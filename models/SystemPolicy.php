<?php
require_once __DIR__.'/Setting.php';
class SystemPolicy {
    public static function migrate($db) {
        $db->query('ALTER TABLE users MODIFY pos_pin VARCHAR(255) NULL');
        $db->query("CREATE TABLE IF NOT EXISTS auth_factors(user_id INT PRIMARY KEY,secret_cipher TEXT NOT NULL,last_counter BIGINT NOT NULL DEFAULT -1,recovery_hashes TEXT NOT NULL) ENGINE=InnoDB");
        $rows=$db->query("SELECT id,pos_pin FROM users WHERE pos_pin IS NOT NULL AND pos_pin<>''")->fetch_all(MYSQLI_ASSOC);
        foreach($rows as $r)if(preg_match('/^[0-9]{4,6}$/D',$r['pos_pin'])){$hash=password_hash($r['pos_pin'],PASSWORD_DEFAULT);$s=$db->prepare('UPDATE users SET pos_pin=? WHERE id=?');$s->bind_param('si',$hash,$r['id']);$s->execute();}
        $master=Setting::get('master_pos_pin','');if(preg_match('/^[0-9]{4,6}$/D',$master))Setting::set('master_pos_pin',password_hash($master,PASSWORD_DEFAULT),'security');
        // This unused legacy override never protected any action. Remove it rather than imply protection.
        $db->query("DELETE FROM settings WHERE setting_key='admin_key'");
    }
    public static function setPin($id,$pin) {
        if(!is_string($pin)||!preg_match('/^[0-9]{4}$/D',$pin))throw new DomainException('Use a PIN containing 4 digits.');
        $hash=password_hash($pin,PASSWORD_DEFAULT);$s=$GLOBALS['conn']->prepare('UPDATE users SET pos_pin=? WHERE id=? AND deleted_at IS NULL');$s->bind_param('si',$hash,$id);$s->execute();
    }
    public static function verifyPin($pin,$stored) {
        if(!is_string($pin)||!preg_match('/^[0-9]{4,6}$/D',$pin)||!is_string($stored)||$stored==='')return false;
        return password_get_info($stored)['algo']?password_verify($pin,$stored):hash_equals($stored,$pin);
    }
    public static function reservePinAttempt($userId) {
        require_once __DIR__.'/AuthSecurity.php';
        $ipWait=AuthSecurity::consume('pos-pin-ip',AuthSecurity::ip(),20,300);
        if($ipWait)return $ipWait;
        return AuthSecurity::consume('pos-pin','user:'.(int)$userId,5,300);
    }
    public static function clearPinAttempts($userId) {
        require_once __DIR__.'/AuthSecurity.php';
        AuthSecurity::clear_limit('pos-pin','user:'.(int)$userId);
    }
    public static function canOverridePosPin($userId) {
        require_once __DIR__.'/User.php';
        if(!User::find_by_id($userId))return false;
        foreach(User::get_user_roles($userId) as $role){
            $name=strtolower(preg_replace('/[ _-]+/','',$role['name']));
            if(in_array($name,['admin','superadmin'],true))return true;
        }
        return false;
    }
    public static function verifyAccessPin($userId,$pin) {
        require_once __DIR__.'/User.php';
        if(User::verify_pin($userId,$pin))return true;
        return self::canOverridePosPin($userId) && self::verifyPin($pin,Setting::get('master_pos_pin',''));
    }
    public static function saveSecurity(array $data,array $pins,$password,$actor) {
        require_once __DIR__.'/DatabaseBackup.php';DatabaseBackup::authorize($actor,$password);
        $timeout=filter_var($data['session_timeout']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>5,'max_range'=>480]]);
        if($timeout===false)throw new DomainException('Session timeout must be between 5 and 480 minutes.');
        foreach($pins as $id=>$pin)if(!ctype_digit((string)$id)||!is_string($pin)||($pin!==''&&!preg_match('/^[0-9]{4}$/D',$pin)))throw new DomainException('Use a PIN containing 4 digits. Leave blank to keep an existing PIN.');
        foreach($pins as $id=>$pin)if($pin!==''&&(!User::find_by_id((int)$id)||!User::can_manage_user($actor,(int)$id)))throw new DomainException('You cannot change this account’s PIN.');
        $master=$data['master_pos_pin']??'';if(!is_string($master)||($master!==''&&!preg_match('/^[0-9]{4}$/D',$master)))throw new DomainException('Master PIN must contain 4 digits.');
        $wasRequired=Setting::get('2fa_enabled','0')==='1';
        $db=$GLOBALS['conn'];$db->begin_transaction();
        try {
            foreach(['strong_passwords','2fa_enabled','require_pos_pin','stock_transfer_require_approval'] as $key)Setting::set($key,empty($data[$key])?'0':'1','security');
            Setting::set('session_timeout',(string)$timeout,'security');
            if($master!=='')Setting::set('master_pos_pin',password_hash($master,PASSWORD_DEFAULT),'security');
            foreach($pins as $id=>$pin)if($pin!=='')self::setPin((int)$id,$pin);
            // Enabling a factor must also cover already signed-in administrative accounts.
            if(!$wasRequired&&!empty($data['2fa_enabled'])){require_once __DIR__.'/TwoFactor.php';$users=$db->query('SELECT id FROM users WHERE deleted_at IS NULL')->fetch_all(MYSQLI_ASSOC);foreach($users as $u)if(TwoFactor::required($u['id']))AuthSecurity::revoke_sessions($u['id']);}
            $db->commit();
        }catch(Throwable $e){$db->rollback();throw $e;}
    }
}
