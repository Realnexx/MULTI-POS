<?php
require_once __DIR__.'/SystemPolicy.php';
require_once __DIR__.'/AuthSecurity.php';
class TwoFactor {
    private static function key() {
        $p=__DIR__.'/../config/security.key';if(!is_file($p)){$f=@fopen($p,'x');if($f){fwrite($f,base64_encode(random_bytes(32)));fclose($f);@chmod($p,0600);}}
        $key=base64_decode((string)@file_get_contents($p),true);if($key===false||strlen($key)!==32)throw new RuntimeException('Security key unavailable.');return $key;
    }
    public static function secret() {
        $bits='';foreach(str_split(random_bytes(20)) as $c)$bits.=str_pad(decbin(ord($c)),8,'0',STR_PAD_LEFT);$out='';foreach(str_split($bits,5) as $part)$out.='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'[bindec($part)];return $out;
    }
    private static function decode($value) {
        $bits='';foreach(str_split($value) as $c){$v=strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',$c);if($v===false)throw new DomainException('Invalid authenticator secret.');$bits.=str_pad(decbin($v),5,'0',STR_PAD_LEFT);}$out='';foreach(str_split($bits,8) as $part)if(strlen($part)===8)$out.=chr(bindec($part));return $out;
    }
    public static function code($secret,$counter) {
        $raw=hash_hmac('sha1',pack('N2',0,$counter),self::decode($secret),true);$offset=ord($raw[19])&15;$n=unpack('N',substr($raw,$offset,4))[1]&0x7fffffff;return str_pad((string)($n%1000000),6,'0',STR_PAD_LEFT);
    }
    public static function required($user) {
        return Setting::get('2fa_enabled','0')==='1' && (User::has_all_branch_access($user)||User::has_permission($user,'manage_settings')||User::has_permission($user,'manage_users'));
    }
    public static function factor($user) {$s=$GLOBALS['conn']->prepare('SELECT * FROM auth_factors WHERE user_id=?');$s->bind_param('i',$user);$s->execute();return $s->get_result()->fetch_assoc();}
    private static function crypt($value,$decrypt=false) {
        if($decrypt){$raw=base64_decode($value,true);if($raw===false||strlen($raw)<28)throw new RuntimeException('Authenticator unavailable.');$out=openssl_decrypt(substr($raw,28),'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));}
        else{$iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($value,'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,$iv,$tag);$out=$cipher===false?false:base64_encode($iv.$tag.$cipher);}
        if($out===false)throw new RuntimeException('Authenticator encryption failed.');return $out;
    }
    public static function verify($user,$code,$enrollment=null) {
        if(!is_string($code))return false;
        if(AuthSecurity::consume('mfa-user',(string)$user,5,300))throw new DomainException('Too many codes. Try again in five minutes.');
        $db=$GLOBALS['conn'];$db->begin_transaction();
        try {
            $s=$db->prepare('SELECT * FROM auth_factors WHERE user_id=? FOR UPDATE');$s->bind_param('i',$user);$s->execute();$factor=$s->get_result()->fetch_assoc();
            $secret=$factor?self::crypt($factor['secret_cipher'],true):$enrollment;
            if(!$secret){$db->rollback();return false;}
            $counter=null;$now=intdiv(time(),30);for($n=$now-1;$n<=$now+1;$n++)if(preg_match('/^[0-9]{6}$/D',$code)&&hash_equals(self::code($secret,$n),$code)&&$n>(int)($factor['last_counter']??-1)){$counter=$n;break;}
            $recovery=$factor?json_decode($factor['recovery_hashes'],true):[];
            if($counter===null&&$factor){foreach($recovery as $i=>$hash)if(password_verify($code,$hash)){unset($recovery[$i]);$counter=(int)$factor['last_counter'];break;}}
            if($counter===null){$db->rollback();return false;}
            $codes=[];
            if(!$factor){for($i=0;$i<8;$i++){$codes[]=bin2hex(random_bytes(8));}$recovery=array_map(function($c){return password_hash($c,PASSWORD_DEFAULT);},$codes);}
            $cipher=$factor['secret_cipher']??self::crypt($secret);$hashes=json_encode(array_values($recovery));
            $s=$db->prepare('INSERT INTO auth_factors(user_id,secret_cipher,last_counter,recovery_hashes) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE last_counter=VALUES(last_counter),recovery_hashes=VALUES(recovery_hashes)');$s->bind_param('isis',$user,$cipher,$counter,$hashes);$s->execute();$db->commit();return ['recovery'=>$codes];
        }catch(Throwable $e){$db->rollback();throw $e;}
    }
}
