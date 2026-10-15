<?php
require_once __DIR__ . '/../config/db.php';

class Branch
{
    public static function for_current_user($activeOnly = true) {
        require_once __DIR__ . '/User.php';
        $rows = $activeOnly ? self::get_all_active() : self::get_all();
        $context = User::access_context($_SESSION['user']['id'] ?? 0);
        if ($context['global']) return $rows;
        return array_values(array_filter($rows, function($row) use ($context) { return (int)$row['id'] === $context['branch_id']; }));
    }
    public static function get_all_active()
    {
        global $conn;
        $result = $conn->query("SELECT * FROM branches WHERE status = 'Active' ORDER BY name ASC");
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_all()
    {
        global $conn;
        $result = $conn->query("SELECT b.*, u.name as manager_name,
                                (SELECT GROUP_CONCAT(CONCAT(users.name, ':', r.name)) 
                                 FROM users 
                                 JOIN user_roles ur ON users.id = ur.user_id 
                                 JOIN roles r ON ur.role_id = r.id 
                                 WHERE users.branch_id = b.id) as staff_list
                                FROM branches b 
                                LEFT JOIN users u ON b.manager_id = u.id 
                                ORDER BY b.id DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function get_potential_managers()
    {
        global $conn;
        $query = "SELECT u.id, u.name 
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN roles r ON ur.role_id = r.id
                  WHERE r.name IN ('Manager', 'Admin')
                  GROUP BY u.id";
        $result = $conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function find_by_id($id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT b.*, u.name as manager_name 
                                FROM branches b 
                                LEFT JOIN users u ON b.manager_id = u.id 
                                WHERE b.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public static function update($id, $data)
    {
        global $conn;
        $name = $data['name'];
        $address = $data['address'];
        $phone = $data['phone'];
        $status = $data['status'];
        $manager_id = ($data['manager_id'] === "" || $data['manager_id'] === "0") ? null : intval($data['manager_id']);
        $image_url = $data['image_url'] ?? null;

        if ($manager_id) {
            $conn->query("UPDATE branches SET manager_id = NULL WHERE manager_id = $manager_id");
        }

        if ($image_url) {
            $stmt = $conn->prepare("UPDATE branches SET name = ?, address = ?, phone = ?, status = ?, image_url = ?, manager_id = ? WHERE id = ?");
            $stmt->bind_param("sssssii", $name, $address, $phone, $status, $image_url, $manager_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE branches SET name = ?, address = ?, phone = ?, status = ?, manager_id = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $name, $address, $phone, $status, $manager_id, $id);
        }
        return $stmt->execute();
    }

    public static function get_potential_staff()
    {
        global $conn;
        $result = $conn->query("SELECT u.id, u.name, u.branch_id, b.name as current_branch_name, GROUP_CONCAT(r.name) as role_name 
                                FROM users u 
                                JOIN user_roles ur ON u.id = ur.user_id 
                                JOIN roles r ON ur.role_id = r.id 
                                LEFT JOIN branches b ON u.branch_id = b.id
                                WHERE u.deleted_at IS NULL AND r.name <> 'Customer' AND NOT EXISTS (SELECT 1 FROM user_roles xr JOIN roles x ON x.id = xr.role_id WHERE xr.user_id = u.id AND LOWER(REPLACE(REPLACE(REPLACE(x.name, ' ', ''), '_', ''), '-', '')) IN ('admin', 'superadmin'))
                                GROUP BY u.id");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function get_assigned_users($branch_id)
    {
        global $conn;
        $branch_id = intval($branch_id);
        $result = $conn->query("SELECT u.id, u.name, r.name as role_name 
                                FROM users u 
                                JOIN user_roles ur ON u.id = ur.user_id 
                                JOIN roles r ON ur.role_id = r.id 
                                WHERE u.branch_id = $branch_id");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function create($data)
    {
        global $conn;
        $name = $data['name'];
        $address = $data['address'];
        $phone = $data['phone'];
        $status = $data['status'];
        $manager_id = ($data['manager_id'] === "" || $data['manager_id'] === "0") ? null : intval($data['manager_id']);
        $image_url = $data['image_url'] ?? 'assets/img/branches/default.jpg';

        if ($manager_id) {
            $conn->query("UPDATE branches SET manager_id = NULL WHERE manager_id = $manager_id");
        }

        $stmt = $conn->prepare("INSERT INTO branches (name, address, phone, status, image_url, manager_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $address, $phone, $status, $image_url, $manager_id);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        return false;
    }

    public static function delete($id)
    {
        global $conn;
        $id = intval($id);
        
        // Before deleting, check if it's in use by sales or other entities if necessary
        // For now, we'll just allow deletion if no users are assigned (or we nullify them)
        $conn->query("UPDATE users SET branch_id = NULL WHERE branch_id = $id");
        
        $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function assign_users($branch_id, $user_ids)
    {
        global $conn;
        $branch_id = intval($branch_id);
        
        // 1. Clear all currently assigned users for this branch
        $conn->query("UPDATE users SET branch_id = NULL WHERE branch_id = $branch_id");

        // 2. Clear previous assignments for the selected users anywhere else
        if (!empty($user_ids)) {
            $ids = implode(',', array_map('intval', $user_ids));
            $conn->query("UPDATE users SET branch_id = NULL WHERE id IN ($ids)");
            
            // 3. Deploy to the new branch
            $conn->query("UPDATE users SET branch_id = $branch_id WHERE id IN ($ids)");
        }
        return true;
    }

    public static function get_stats()
    {
        global $conn;

        $stats = [
            'total' => 0,
            'active' => 0,
            'offline' => 0,
            'regions' => 0,
            'top_performing' => 'N/A'
        ];

        // Total and Status Counts
        $res = $conn->query("SELECT status, COUNT(*) as count FROM branches GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $stats['total'] += intval($row['count']);
                if ($row['status'] === 'Active') $stats['active'] = intval($row['count']);
                else $stats['offline'] += intval($row['count']);
            }
        }

        // Distinct Regions (Using distinct address starting words as a proxy for region/city)
        $reg_res = $conn->query("SELECT COUNT(DISTINCT(SUBSTRING_INDEX(address, ' ', 1))) as region_count FROM branches");
        if ($reg_res) {
            $row = $reg_res->fetch_assoc();
            $stats['regions'] = intval($row['region_count']);
        }

        // Top Performing Branch (Based on most staff as a proxy for activity level)
        $top_query = "SELECT b.name FROM branches b 
                      LEFT JOIN users u ON b.id = u.branch_id 
                      GROUP BY b.id 
                      ORDER BY COUNT(u.id) DESC 
                      LIMIT 1";
        $top_res = $conn->query($top_query);
        if ($top_res && $row = $top_res->fetch_assoc()) {
            $stats['top_performing'] = $row['name'];
        }

        return $stats;
    }
}
