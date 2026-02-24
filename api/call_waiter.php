<?php
require_once __DIR__ . '/../config/app.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['table_id']) || !isset($data['request_type'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
}

$waiterModel = new WaiterModel();
$success = $waiterModel->createRequest(
    $data['table_id'],
    $data['table_number'] ?? 'Unknown',
    $data['request_type']
);

if ($success) {
    jsonResponse(['success' => true, 'message' => 'Request sent! A waiter will assist you shortly.']);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to send request'], 500);
}
