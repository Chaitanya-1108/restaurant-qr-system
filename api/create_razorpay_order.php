<?php
require_once __DIR__ . '/../config/app.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['order_number'])) {
    jsonResponse(['success' => false, 'message' => 'Order number is required'], 400);
}

$orderModel = new OrderModel();
$order = $orderModel->getOrderByNumber($data['order_number']);

if (!$order) {
    jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
}

// Convert amount to paise
$amountInPaise = (int) ($order['total_amount'] * 100);

$razorpayOrder = RazorpayUtil::createOrder($amountInPaise, $order['order_number']);

if (isset($razorpayOrder['id'])) {
    jsonResponse([
        'success' => true,
        'razorpay_order_id' => $razorpayOrder['id'],
        'amount' => $amountInPaise,
        'currency' => 'INR',
        'key_id' => RAZORPAY_KEY_ID,
        'customer' => [
            'name' => $order['customer_name'],
            'email' => 'customer@example.com', // Placeholder
            'contact' => '9999999999' // Placeholder
        ]
    ]);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to create Razorpay order', 'details' => $razorpayOrder], 500);
}
