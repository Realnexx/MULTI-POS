<?php
require_once __DIR__.'/User.php';

class DemoAccount {
    const EXPIRED_MESSAGE = 'This demo account has expired. Demo accounts can only be used for 3 days. Please contact the developer.';

    public static function migrate($db) {
        $db->query('CREATE TABLE IF NOT EXISTS demo_account_access (user_id INT PRIMARY KEY,started_at DATETIME NOT NULL,expires_at DATETIME NOT NULL) ENGINE=InnoDB');
    }

    public static function hasRole($user) {
        foreach (User::get_user_roles($user) as $role) {
            if (strtolower(trim($role['name'])) === 'demo') return true;
        }
        return false;
    }

    // The unique account key makes simultaneous sign-ins share the same 72-hour window.
    public static function start($user) {
        if (!self::hasRole($user)) return ['is_demo'=>false,'expired'=>false];
        global $conn;
        $stmt=$conn->prepare('INSERT IGNORE INTO demo_account_access(user_id,started_at,expires_at) VALUES (?,UTC_TIMESTAMP(),DATE_ADD(UTC_TIMESTAMP(),INTERVAL 3 DAY))');
        $stmt->bind_param('i',$user);$stmt->execute();
        return self::status($user);
    }

    public static function status($user) {
        if (!self::hasRole($user)) return ['is_demo'=>false,'expired'=>false];
        global $conn;
        $stmt=$conn->prepare('SELECT started_at,expires_at,expires_at<=UTC_TIMESTAMP() AS expired FROM demo_account_access WHERE user_id=?');
        $stmt->bind_param('i',$user);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
        return ['is_demo'=>true,'expired'=>$row ? (bool)$row['expired'] : false,'started_at'=>$row['started_at']??null,'expires_at'=>$row['expires_at']??null];
    }
}
