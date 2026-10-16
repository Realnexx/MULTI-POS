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
        $query = "SELECT u.id, u.name,
                  (SELECT GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ')
                   FROM branches b WHERE b.manager_id=u.id OR b.id=u.branch_id) assigned_branch_names
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN roles r ON ur.role_id = r.id
                  WHERE r.name IN ('Manager', 'Admin') AND u.deleted_at IS NULL
                  GROUP BY u.id ORDER BY u.name";
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
        $manager_id = !empty($data['manager_id']) ? intval($data['manager_id']) : null;
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
        $manager_id = !empty($data['manager_id']) ? intval($data['manager_id']) : null;
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

    public static function deletion_validation($id, $lock=false)
    {
        require_once __DIR__.'/RecipeInventory.php';
        $id=(int)$id;
        $branch=RecipeInventory::rows('SELECT id,manager_id FROM branches WHERE id=?'.($lock?' FOR UPDATE':''),'i',[$id]);
        if(!$branch)return ['allowed'=>false,'reasons'=>['Branch no longer exists.']];
        $reasons=[];
        if(!empty($branch[0]['manager_id']))$reasons[]='An assigned branch manager';
        // Discover installed modules too, so older and newly installed branch records are protected.
        $columns=RecipeInventory::rows("SELECT c.TABLE_NAME table_name,c.COLUMN_NAME column_name FROM information_schema.COLUMNS c JOIN information_schema.TABLES t ON t.TABLE_SCHEMA=c.TABLE_SCHEMA AND t.TABLE_NAME=c.TABLE_NAME WHERE c.TABLE_SCHEMA=DATABASE() AND t.TABLE_TYPE='BASE TABLE' AND c.TABLE_NAME<>'branches' AND (c.COLUMN_NAME='branch_id' OR RIGHT(c.COLUMN_NAME,10)='_branch_id') ORDER BY c.TABLE_NAME,c.COLUMN_NAME");
        $tables=[];foreach($columns as $column){
            if(!preg_match('/^[a-zA-Z0-9_]+$/D',$column['table_name']) || !preg_match('/^[a-zA-Z0-9_]+$/D',$column['column_name']))throw new RuntimeException('Unsupported branch reference.');
            $tables[$column['table_name']][]=$column['column_name'];
        }
        $labels=['sales'=>'Sales history','products'=>'Assigned products','users'=>'Assigned staff or user records','expenses'=>'Expense records','product_batches'=>'Stock batches','online_orders'=>'Online orders','cash_registers'=>'Cash registers','cash_shifts'=>'Cashier shifts','stock_transfers'=>'Stock transfers','purchase_orders'=>'Purchase orders','purchase_suppliers'=>'Suppliers'];
        foreach($tables as $table=>$fields){
            $where=implode(' OR ',array_map(static fn($field)=>'`'.$field.'`=?',$fields));
            if(RecipeInventory::rows('SELECT 1 FROM `'.$table.'` WHERE '.$where.' LIMIT 1'.($lock?' FOR UPDATE':''),str_repeat('i',count($fields)),array_fill(0,count($fields),$id)))$reasons[]=$labels[$table] ?? ucwords(str_replace('_',' ',$table));
        }
        return ['allowed'=>!$reasons,'reasons'=>$reasons];
    }

    public static function delete($id)
    {
        require_once __DIR__.'/RecipeInventory.php';
        return RecipeInventory::atomic(function()use($id){
            global $conn;
            if(!self::deletion_validation($id,true)['allowed'])return false;
            $stmt=$conn->prepare('DELETE FROM branches WHERE id=?');$id=(int)$id;$stmt->bind_param('i',$id);$stmt->execute();
            return $stmt->affected_rows===1;
        });
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
