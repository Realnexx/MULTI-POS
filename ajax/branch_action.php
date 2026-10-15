<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/ActivityLog.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('branch_action', $action);

if ($action === 'list') {
    $branches = Branch::get_all();
    $stats = Branch::get_stats();
    foreach ($branches as &$b) {
        $b['full_image_url'] = get_branch_image($b['image_url'], $b['id']);
        $b['manager_name'] = $b['manager_name'] ?? 'Unassigned';
    }
    echo json_encode([
        'success' => true, 
        'data' => $branches,
        'stats' => $stats
    ]);
    exit;
}

if ($action === 'get') {
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
    $branch = Branch::find_by_id($id);
    if ($branch) {
        $branch['full_image_url'] = get_branch_image($branch['image_url'], $branch['id']);
    }
    echo json_encode(['success' => (bool)$branch, 'data' => $branch]);
    exit;
}

if ($action === 'get_managers') {
    $managers = Branch::get_potential_managers();
    echo json_encode(['success' => true, 'data' => $managers]);
    exit;
}

if ($action === 'get_staff') {
    $branch_id = intval($_GET['branch_id'] ?? 0);
    $potential = Branch::get_potential_staff();
    $assigned = Branch::get_assigned_users($branch_id);
    echo json_encode(['success' => true, 'potential' => $potential, 'assigned' => $assigned]);
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Active');
    $manager_id = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
    $staff_ids = isset($_POST['staff_ids']) ? $_POST['staff_ids'] : [];
    
    $data = [
        'name' => $name,
        'address' => $address,
        'phone' => $phone,
        'status' => $status,
        'manager_id' => $manager_id
    ];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../assets/img/branches/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0755, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename)) {
            $data['image_url'] = 'assets/img/branches/' . $filename;
        }
    }

    $new_id = Branch::create($data);
    if ($new_id) {
        if (!empty($staff_ids)) {
            Branch::assign_users($new_id, $staff_ids);
        }
        ActivityLog::log('Branch Created', "Created branch '{$name}' (ID: {$new_id})");
        echo json_encode(['success' => true, 'message' => 'New branch established and personnel transitioned successfully.', 'branch_id' => $new_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to establish new branch']);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
    $branch = Branch::find_by_id($id);
    $branch_name = $branch ? $branch['name'] : "ID {$id}";
    if (Branch::delete($id)) {
        ActivityLog::log('Branch Deleted', "Deleted branch '{$branch_name}' (ID: {$id})");
        echo json_encode(['success' => true, 'message' => 'Branch decommissioned successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to decommission branch']);
    }
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['branch_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Active');
    $manager_id = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
    $staff_ids = isset($_POST['staff_ids']) ? $_POST['staff_ids'] : [];
    
    $data = [
        'name' => $name,
        'address' => $address,
        'phone' => $phone,
        'status' => $status,
        'manager_id' => $manager_id
    ];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../assets/img/branches/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0755, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename)) {
            $data['image_url'] = 'assets/img/branches/' . $filename;
        }
    }

    if (Branch::update($id, $data)) {
        Branch::assign_users($id, $staff_ids);
        ActivityLog::log('Branch Updated', "Updated branch '{$name}' (ID: {$id})");
        echo json_encode(['success' => true, 'message' => 'Operational changes saved and staff redeployed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update branch']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
