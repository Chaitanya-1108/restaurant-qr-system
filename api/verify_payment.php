<?php
require_once __DIR__ . '/../config/app.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['razorpay_order_id']) || !isset($data['razorpay_payment_id']) || !isset($data['razorpay_signature'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid payment data'], 400);
}

// Verify Signature
$isValid = RazorpayUtil::verifySignature(
    $data['razorpay_order_id'],
    $data['razorpay_payment_id'],
    $data['razorpay_signature']
);

if ($isValid) {
    $orderModel = new OrderModel();
    $order = $orderModel->getOrderByNumber($data['order_number']);

    if ($order) {
        // Update payment status to Paid
        $orderModel->updatePaymentStatus($order['id'], 'Paid');

        // Optionally update order status to Preparing automatically if paid
        if ($order['status'] === 'Pending') {
            $orderModel->updateOrderStatus($order['id'], 'Preparing');
        }

        jsonResponse(['success' => true, 'message' => 'Payment verified and order updated!']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Order not found after payment'], 404);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid signature! Potential fraud detected.'], 403);
}
