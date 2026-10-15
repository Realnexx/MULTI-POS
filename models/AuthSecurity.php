<?php
class AuthSecurity {
    public static function config() {
        global $appConfig;
        return array_merge(require __DIR__.'/../config/auth.php', $appConfig['auth'] ?? []);
    }
    public static function migrate($db) {
        $db->query("CREATE TABLE IF NOT EXISTS auth_rate_limits (bucket CHAR(64) PRIMARY KEY, attempts INT UNSIGNED NOT NULL DEFAULT 0, expires_at DATETIME NOT NULL, INDEX(expires_at)) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS auth_user_security (user_id INT PRIMARY KEY, token_version INT UNSIGNED NOT NULL DEFAULT 1) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS auth_password_resets (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(user_id), INDEX(expires_at)) ENGINE=InnoDB");
    }
    public static function input($key) {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    }
    public static function ip() { return $_SERVER['REMOTE_ADDR'] ?? 'unknown'; }
    public static function password_error($password) {
        if (!is_string($password) || mb_strlen($password, 'UTF-8') < 12) return 'Use at least 12 characters for your password.';
        if (strlen($password) > 72 || strpos($password,"\0") !== false) return 'This password is too long or contains an unsupported character. Try a shorter passphrase.';
        return null;
    }
    // Reserve a request atomically, with fixed windows unaffected by cookie clearing.
    public static function consume($scope, $identity, $limit, $seconds) {
        global $conn;
        $key=hash('sha256',$scope.'|'.strtolower($identity));
        $conn->begin_transaction();
        try {
            $stmt=$conn->prepare('INSERT IGNORE INTO auth_rate_limits(bucket,attempts,expires_at) VALUES (?,0,DATE_ADD(UTC_TIMESTAMP(),INTERVAL ? SECOND))');$stmt->bind_param('si',$key,$seconds);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare('SELECT attempts,UNIX_TIMESTAMP(expires_at)-UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS wait_seconds FROM auth_rate_limits WHERE bucket=? FOR UPDATE');$stmt->bind_param('s',$key);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
            $expired=(int)$row['wait_seconds']<=0;
            if (!$expired && (int)$row['attempts'] >= $limit) { $conn->commit(); return max(1,(int)$row['wait_seconds']); }
            if ($expired) $stmt=$conn->prepare('UPDATE auth_rate_limits SET attempts=1,expires_at=DATE_ADD(UTC_TIMESTAMP(),INTERVAL ? SECOND) WHERE bucket=?');
            else $stmt=$conn->prepare('UPDATE auth_rate_limits SET attempts=attempts+1 WHERE bucket=?');
            if ($expired) $stmt->bind_param('is',$seconds,$key); else $stmt->bind_param('s',$key);
            $stmt->execute();$stmt->close();$conn->commit();return 0;
        } catch (Throwable $e) {$conn->rollback();throw $e;}
    }
    public static function clear_limit($scope,$identity) {
        global $conn;$key=hash('sha256',$scope.'|'.strtolower($identity));
        $stmt=$conn->prepare('DELETE FROM auth_rate_limits WHERE bucket=?');$stmt->bind_param('s',$key);$stmt->execute();$stmt->close();
    }
    public static function version($id) {
        global $conn;$stmt=$conn->prepare('SELECT token_version FROM auth_user_security WHERE user_id=?');$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();return (int)($row['token_version'] ?? 1);
    }
    public static function establish($id) {
        $_SESSION['auth']=['started_at'=>time(),'last_activity'=>time(),'version'=>self::version($id)];
        unset($_SESSION['csrf_token']);
    }
    public static function issue_reset($id) {
        global $conn;$token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$ttl=(int)self::config()['reset_lifetime'];
        $conn->begin_transaction();
        try {
            // Lock the account so simultaneous requests cannot leave multiple valid links.
            $stmt=$conn->prepare('SELECT id FROM users WHERE id=? AND deleted_at IS NULL FOR UPDATE');$stmt->bind_param('i',$id);$stmt->execute();$exists=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$exists)throw new DomainException('Account unavailable');
            $stmt=$conn->prepare('UPDATE auth_password_resets SET used_at=UTC_TIMESTAMP() WHERE user_id=? AND used_at IS NULL');$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare('INSERT INTO auth_password_resets(user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL ? SECOND))');$stmt->bind_param('isi',$id,$hash,$ttl);$stmt->execute();$stmt->close();$conn->commit();return $token;
        }catch(Throwable $e){$conn->rollback();throw $e;}
    }
    public static function valid_reset($token) {
        global $conn;if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token))return false;$hash=hash('sha256',$token);
        $stmt=$conn->prepare('SELECT r.id FROM auth_password_resets r JOIN users u ON u.id=r.user_id WHERE r.token_hash=? AND r.used_at IS NULL AND r.expires_at>UTC_TIMESTAMP() AND u.deleted_at IS NULL');$stmt->bind_param('s',$hash);$stmt->execute();$valid=(bool)$stmt->get_result()->fetch_assoc();$stmt->close();return $valid;
    }
    public static function revoke_reset($token) {
        global $conn;$hash=hash('sha256',$token);$stmt=$conn->prepare('UPDATE auth_password_resets SET used_at=UTC_TIMESTAMP() WHERE token_hash=?');$stmt->bind_param('s',$hash);$stmt->execute();$stmt->close();
    }
    public static function reset_password($token,$password) {
        global $conn;
        if(self::password_error($password)||!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token))return false;
        $hash=hash('sha256',$token);$conn->begin_transaction();
        try {
            $stmt=$conn->prepare('SELECT user_id FROM auth_password_resets WHERE token_hash=? AND used_at IS NULL AND expires_at>UTC_TIMESTAMP() FOR UPDATE');$stmt->bind_param('s',$hash);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
            if(!$row){$conn->rollback();return false;}$id=(int)$row['user_id'];$passwordHash=password_hash($password,PASSWORD_BCRYPT);
            $stmt=$conn->prepare('UPDATE users SET password=? WHERE id=? AND deleted_at IS NULL');$stmt->bind_param('si',$passwordHash,$id);$stmt->execute();$changed=$stmt->affected_rows;$stmt->close();if(!$changed){$conn->rollback();return false;}
            $stmt=$conn->prepare('INSERT INTO auth_user_security(user_id,token_version) VALUES (?,2) ON DUPLICATE KEY UPDATE token_version=token_version+1');$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare('UPDATE auth_password_resets SET used_at=UTC_TIMESTAMP() WHERE user_id=? AND used_at IS NULL');$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();self::clear_limit('login-account','user:'.$id);$conn->commit();return true;
        }catch(Throwable $e){$conn->rollback();throw $e;}
    }
}
