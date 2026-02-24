<?php
// API: Update Payment Status (Admin only)
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int) ($input['id'] ?? 0);
$status = sanitize($input['status'] ?? '');

$allowed = ['Pending', 'Paid'];
if (!$orderId || !in_array($status, $allowed)) {
    jsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
}

$orderModel = new OrderModel();
$result = $orderModel->updatePaymentStatus($orderId, $status);

if ($result) {
    jsonResponse(['success' => true, 'message' => "Payment marked as $status"]);
} else {
    jsonResponse(['success' => false, 'message' => 'Update failed'], 500);
}
