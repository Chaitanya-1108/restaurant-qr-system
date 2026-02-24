<?php
// API: Get live orders (Admin polling or Customer status check)
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

$orderModel = new OrderModel();
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

// Individual order check (Used by customers on confirmation page)
if ($orderId > 0) {
    $order = $orderModel->getOrderById($orderId);
    if ($order) {
        $tableInfo = $orderModel->getTableByNumber($order['table_number']);
        $order['table_status'] = $tableInfo['status'] ?? 'unknown';
        jsonResponse(['success' => true, 'order' => $order]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }
}

// Bulk order fetch (Admin only)
if (!isAdminLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$status = sanitize($_GET['status'] ?? '');
$orders = $orderModel->getAllOrders($status);

jsonResponse(['success' => true, 'orders' => $orders, 'count' => count($orders)]);
