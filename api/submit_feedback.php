<?php
require_once __DIR__ . '/../config/app.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['rating'])) {
    jsonResponse(['success' => false, 'message' => 'Rating is required'], 400);
}

$feedbackModel = new FeedbackModel();
$success = $feedbackModel->createFeedback([
    'order_id' => $data['order_id'] ?? null,
    'customer_name' => $data['customer_name'] ?? 'Guest',
    'rating' => (int) $data['rating'],
    'comment' => $data['comment'] ?? ''
]);

if ($success) {
    jsonResponse(['success' => true, 'message' => 'Thank you for your feedback!']);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to save feedback'], 500);
}
