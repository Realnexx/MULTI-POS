<?php
require_once __DIR__ . '/../config/db.php';

class Role
{
    public static function get_all_roles()
    {
        global $conn;
        $query = "SELECT r.*, 
                         COUNT(DISTINCT ur.user_id) as user_count,
                         GROUP_CONCAT(DISTINCT rp.permission_id) as permission_ids
                  FROM roles r 
                  LEFT JOIN user_roles ur ON r.id = ur.role_id 
                  LEFT JOIN role_permissions rp ON r.id = rp.role_id
                  GROUP BY r.id ORDER BY r.id ASC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_all_permissions()
    {
        global $conn;
        $query = "SELECT * FROM permissions ORDER BY module ASC, id ASC";
        $results = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
        $grouped = [];
        $catalog=require __DIR__.'/../config/permissions.php';
        foreach ($results as $row) {
            $row['is_core']=isset($catalog[$row['name']]);
            $grouped[$row['module']][] = $row;
        }
        return $grouped;
    }

    public static function get_role_permissions($role_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($results, 'permission_id');
    }

    public static function create($name, $description, $permissions = [])
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO roles (name, description, is_system) VALUES (?, ?, 0)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            $role_id = $stmt->insert_id;
            $stmt->close();

            if (!empty($permissions)) {
                $stmt_perm = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissions as $p_id) {
                    $stmt_perm->bind_param("ii", $role_id, $p_id);
                    $stmt_perm->execute();
                }
                $stmt_perm->close();
            }
            return $role_id;
        }
        $stmt->close();
        return false;
    }

    public static function update($role_id, $name, $description, $permissions = [])
    {
        global $conn;
        $conn->begin_transaction();
        try {
            $stmt=$conn->prepare('SELECT name FROM roles WHERE id=? FOR UPDATE');
            $stmt->bind_param('i',$role_id);$stmt->execute();$role=$stmt->get_result()->fetch_assoc();$stmt->close();
            if (!$role) { $conn->rollback(); return false; }
            $protected=preg_replace('/[ _-]/','',strtolower($role['name']))==='superadmin';
            if ($protected) { $name=$role['name']; $permissions=array_column($conn->query('SELECT id FROM permissions')->fetch_all(MYSQLI_ASSOC),'id'); }
            $stmt=$conn->prepare('UPDATE roles SET name=?, description=?, is_system=CASE WHEN ?=1 THEN 1 ELSE is_system END WHERE id=?');
            $flag=$protected?1:0;$stmt->bind_param('ssii',$name,$description,$flag,$role_id);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare('DELETE FROM role_permissions WHERE role_id=?');$stmt->bind_param('i',$role_id);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare('INSERT INTO role_permissions(role_id,permission_id) VALUES (?,?)');
            foreach(array_unique(array_map('intval',(array)$permissions)) as $permission) { $stmt->bind_param('ii',$role_id,$permission);$stmt->execute(); }
            $stmt->close();$conn->commit();return true;
        } catch(Throwable $error) { $conn->rollback();throw $error; }
    }

    public static function delete($role_id)
    {
        global $conn;
        // Prevent deleting system roles
        $stmt = $conn->prepare("DELETE FROM roles WHERE id = ? AND is_system = 0 AND LOWER(REPLACE(REPLACE(REPLACE(name, ' ', ''), '_', ''), '-', '')) <> 'superadmin'");
        $stmt->bind_param("i", $role_id);
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }

    public static function add_permission($name, $description, $module)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO permissions (name, description, module) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $description, $module);
        $success = $stmt->execute();
        if ($success) {
            $permission_id=(int)$stmt->insert_id;
            $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT id,".$permission_id." FROM roles WHERE LOWER(REPLACE(REPLACE(REPLACE(name,' ',''),'_',''),'-',''))='superadmin'");
        }
        $stmt->close();
        return $success;
    }

    public static function get_unique_modules()
    {
        global $conn;
        $query = "SELECT DISTINCT module FROM permissions ORDER BY module ASC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function update_permission($id, $name, $description, $module)
    {
        global $conn;
        $catalog=require __DIR__.'/../config/permissions.php';
        $check=$conn->prepare('SELECT name FROM permissions WHERE id=?');$check->bind_param('i',$id);$check->execute();$old=$check->get_result()->fetch_assoc();$check->close();
        if($old && isset($catalog[$old['name']]) && $old['name']!==$name) return false;
        $stmt = $conn->prepare("UPDATE permissions SET name = ?, description = ?, module = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $module, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function delete_permission($id)
    {
        global $conn;
        $catalog=require __DIR__.'/../config/permissions.php';
        $check=$conn->prepare('SELECT name FROM permissions WHERE id=?');$check->bind_param('i',$id);$check->execute();$old=$check->get_result()->fetch_assoc();$check->close();
        if($old && isset($catalog[$old['name']])) return false;
        $stmt = $conn->prepare("DELETE FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
