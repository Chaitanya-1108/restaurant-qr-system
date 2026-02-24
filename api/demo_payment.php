<?php
require_once __DIR__ . '/../config/app.php';

// Public Demo Endpoint (Simulation Only)
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['order_number'])) {
    jsonResponse(['success' => false, 'message' => 'Order number is required'], 400);
}

$orderModel = new OrderModel();
$order = $orderModel->getOrderByNumber($data['order_number']);

if ($order) {
    // Simulate successful payment processing
    $orderModel->updatePaymentStatus($order['id'], 'Paid');

    // Automatically move to Preparing so it shows up in kitchen alerts
    if ($order['status'] === 'Pending') {
        $orderModel->updateOrderStatus($order['id'], 'Preparing');
    }

    jsonResponse(['success' => true, 'message' => 'Demo Payment Successful!']);
} else {
    jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
}
