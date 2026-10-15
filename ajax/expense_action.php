<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/ActivityLog.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('expense_action', $action);

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'branch_id' => $_POST['branch_id'],
        'category' => $_POST['category'],
        'amount' => $_POST['amount'],
        'expense_date' => $_POST['expense_date'],
        'description' => $_POST['description']
    ];

    $expense_id = Expense::create($data);
    if ($expense_id) {
        ActivityLog::log('Expense Created', 'Expense #' . $expense_id . ': ' . $data['category'] . ' - ' . $data['amount'] . ' (Branch ID ' . $data['branch_id'] . ')');
        echo json_encode(['success' => true, 'message' => 'Expense logged successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to log expense']);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
    $expense = Expense::get_by_id($id);
    if (Expense::delete($id)) {
        $detail = $expense ? $expense['category'] . ' - ' . $expense['amount'] . ' (Branch ID ' . $expense['branch_id'] . ')' : 'ID ' . $id;
        ActivityLog::log('Expense Deleted', 'Expense #' . $id . ': ' . $detail);
        echo json_encode(['success' => true, 'message' => 'Expense deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete expense']);
    }
    exit;
}

if ($action === 'single') {
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
    $expense = Expense::get_by_id($id);
    if ($expense) {
        echo json_encode(['success' => true, 'expense' => $expense]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Expense not found']);
    }
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'branch_id' => $_POST['branch_id'],
        'category' => $_POST['category'],
        'amount' => $_POST['amount'],
        'expense_date' => $_POST['expense_date'],
        'description' => $_POST['description']
    ];

    if (Expense::update($id, $data)) {
        ActivityLog::log('Expense Updated', 'Expense #' . $id . ': ' . $data['category'] . ' - ' . $data['amount'] . ' (Branch ID ' . $data['branch_id'] . ')');
        echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
