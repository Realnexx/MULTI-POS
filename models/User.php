<?php
require_once __DIR__ . '/../config/db.php';

class User {
    public static function find_by_email($email) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public static function find_by_username_or_email($input) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND deleted_at IS NULL");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public static function find_by_id($id) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public static function get_dt_users($start, $length, $search, $order_col, $order_dir) {
        global $conn;
        
        $sql = "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as roles 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE u.deleted_at IS NULL";
        
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR r.name LIKE ?)";
        }
        
        $sql .= " GROUP BY u.id";
        
        $order_cols = ['u.name', 'u.email', 'roles', 'u.created_at'];
        $order_by = $order_cols[$order_col] ?? 'u.id';
        $sql .= " ORDER BY $order_by $order_dir";
        
        $sql .= " LIMIT ?, ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($search)) {
            $search_param = "%$search%";
            $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $start, $length);
        } else {
            $stmt->bind_param("ii", $start, $length);
        }
        
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public static function count_all_users() {
        global $conn;
        $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
        return $res->fetch_assoc()['count'];
    }

    public static function count_filtered_users($search) {
        global $conn;
        if (empty($search)) return self::count_all_users();
        
        $sql = "SELECT COUNT(DISTINCT u.id) as count 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE u.deleted_at IS NULL AND (u.name LIKE ? OR u.email LIKE ? OR r.name LIKE ?)";
        
        $stmt = $conn->prepare($sql);
        $search_param = "%$search%";
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        return $count;
    }

    public static function get_role_distribution() {
        global $conn;
        $sql = "SELECT r.name, COUNT(ur.user_id) as count 
                FROM roles r 
                LEFT JOIN user_roles ur ON r.id = ur.role_id 
                LEFT JOIN users u ON ur.user_id = u.id AND u.deleted_at IS NULL
                GROUP BY r.id";
        return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_all_users() {
        global $conn;
        $query = "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as roles 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.deleted_at IS NULL
                  GROUP BY u.id ORDER BY u.id DESC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function create($firstname, $middlename, $lastname, $extname, $username, $email, $password, $role_ids = [], $image = null) {
        global $conn;
        $name_parts = array_filter([$firstname, $middlename, $lastname, $extname]);
        $name = implode(' ', $name_parts);
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, firstname, middlename, lastname, extname, username, email, password, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $firstname, $middlename, $lastname, $extname, $username, $email, $hashed, $image);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            if (!empty($role_ids)) {
                $stmt_role = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                foreach ($role_ids as $r_id) {
                    $stmt_role->bind_param("ii", $user_id, $r_id);
                    $stmt_role->execute();
                }
                $stmt_role->close();
            }
            return $user_id;
        }
        $stmt->close();
        return false;
    }

    public static function update($id, $firstname, $middlename, $lastname, $extname, $email, $password = null, $role_ids = [], $image = null) {
        global $conn;
        $name_parts = array_filter([$firstname, $middlename, $lastname, $extname]);
        $name = implode(' ', $name_parts);

        if ($password) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            if ($image) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, firstname = ?, middlename = ?, lastname = ?, extname = ?, email = ?, password = ?, image = ? WHERE id = ?");
                $stmt->bind_param("ssssssssi", $name, $firstname, $middlename, $lastname, $extname, $email, $hashed, $image, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, firstname = ?, middlename = ?, lastname = ?, extname = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $name, $firstname, $middlename, $lastname, $extname, $email, $hashed, $id);
            }
        } else {
            if ($image) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, firstname = ?, middlename = ?, lastname = ?, extname = ?, email = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $name, $firstname, $middlename, $lastname, $extname, $email, $image, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, firstname = ?, middlename = ?, lastname = ?, extname = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $name, $firstname, $middlename, $lastname, $extname, $email, $id);
            }
        }
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $conn->query("DELETE FROM user_roles WHERE user_id = " . intval($id));
            if (!empty($role_ids)) {
                $stmt_role = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                foreach ($role_ids as $r_id) {
                    $stmt_role->bind_param("ii", $id, $r_id);
                    $stmt_role->execute();
                }
                $stmt_role->close();
            }
        }
        return $success;
    }

    public static function update_profile($id, $firstname, $middlename, $lastname, $extname, $email, $birthdate, $bio, $layers, $address, $purok, $barangay, $town, $city, $province, $country, $zip, $store, $avatar_url = null) {
        global $conn;
        $name_parts = array_filter([$firstname, $middlename, $lastname, $extname]);
        $real_name = implode(' ', $name_parts);

        $stmt = $conn->prepare("UPDATE users SET 
            name = ?, 
            email = ?, 
            firstname = ?, 
            middlename = ?, 
            lastname = ?, 
            extname = ?, 
            birthdate = ?, 
            bio = ?, 
            layers = ?, 
            address = ?, 
            purok = ?, 
            barangay = ?, 
            town = ?, 
            city = ?, 
            province = ?, 
            country = ?, 
            zip = ?, 
            store = ?,
            avatar_url = ?
            WHERE id = ?");
        
        $stmt->bind_param("sssssssssssssssssssi", 
            $real_name, 
            $email, 
            $firstname, 
            $middlename, 
            $lastname, 
            $extname, 
            $birthdate, 
            $bio, 
            $layers, 
            $address, 
            $purok, 
            $barangay, 
            $town, 
            $city, 
            $province, 
            $country, 
            $zip, 
            $store,
            $avatar_url,
            $id
        );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function log_activity($user_id, $action, $description = null) {
        global $conn;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $action, $description, $ip);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function set_status($id, $status) {
        global $conn;
        if (!in_array($status, ['online', 'offline'])) return false;
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function delete($id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function get_user_roles($user_id) {
        global $conn;
        $stmt = $conn->prepare("SELECT r.* FROM roles r INNER JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public static function has_permission($user_id, $permission_name) {
        global $conn;
        $stmt = $conn->prepare("SELECT COUNT(*) as count 
                                FROM user_roles ur 
                                INNER JOIN role_permissions rp ON ur.role_id = rp.role_id 
                                INNER JOIN permissions p ON rp.permission_id = p.id 
                                WHERE ur.user_id = ? AND p.name = ?");
        $stmt->bind_param("is", $user_id, $permission_name);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['count'] > 0;
    }

    public static function update_password($id, $new_password) {
        global $conn;
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
