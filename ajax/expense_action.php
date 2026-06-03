<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Expense.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'branch_id' => $_POST['branch_id'],
        'category' => $_POST['category'],
        'amount' => $_POST['amount'],
        'expense_date' => $_POST['expense_date'],
        'description' => $_POST['description']
    ];

    if (Expense::create($data)) {
        echo json_encode(['success' => true, 'message' => 'Expense logged successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to log expense']);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if (Expense::delete($id)) {
        echo json_encode(['success' => true, 'message' => 'Expense deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete expense']);
    }
    exit;
}

if ($action === 'single') {
    $id = intval($_GET['id'] ?? 0);
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
        echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
