<?php
// API: Place Order
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Invalid request data'], 400);
}

// Validate
$tableId = (int) ($input['table_id'] ?? 0);
$tableNumber = sanitize($input['table_number'] ?? '');
$items = $input['items'] ?? [];

if (!$tableId || !$tableNumber || empty($items)) {
    jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
}

// Validate items count
if (count($items) > 50) {
    jsonResponse(['success' => false, 'message' => 'Too many items'], 400);
}

$orderModel = new OrderModel();
$menuModel = new MenuModel();

// Verify table exists
$table = $orderModel->getTableByNumber($tableNumber);
if (!$table || $table['id'] !== $tableId) {
    jsonResponse(['success' => false, 'message' => 'Invalid table'], 400);
}

// Validate & price items from DB (prevent price tampering)
$validatedItems = [];
$totalAmount = 0;

foreach ($items as $item) {
    $menuItem = $menuModel->getItemById((int) ($item['menu_item_id'] ?? 0));
    if (!$menuItem || !$menuItem['is_available']) {
        jsonResponse(['success' => false, 'message' => "Item '{$item['item_name']}' is not available"], 400);
    }
    $qty = max(1, min(20, (int) ($item['quantity'] ?? 1)));
    $subtotal = round($menuItem['price'] * $qty, 2);
    $totalAmount += $subtotal;
    $validatedItems[] = [
        'menu_item_id' => $menuItem['id'],
        'item_name' => $menuItem['name'],
        'item_price' => $menuItem['price'],
        'quantity' => $qty,
        'notes' => sanitize($item['notes'] ?? ''),
        'subtotal' => $subtotal,
    ];
}

// Add tax
$tax = round($totalAmount * TAX_PERCENT / 100, 2);
$totalAmount = round($totalAmount + $tax, 2);

$orderNumber = generateOrderNumber();

$orderData = [
    'order_number' => $orderNumber,
    'table_id' => $tableId,
    'table_number' => $tableNumber,
    'customer_name' => sanitize($input['customer_name'] ?? 'Guest'),
    'total_amount' => $totalAmount,
    'payment_method' => sanitize($input['payment_method'] ?? 'Cash'),
    'payment_status' => (isset($input['payment_method']) && $input['payment_method'] === 'UPI') ? 'Pending' : 'Pending', // For now, all are Pending until verified by staff
    'notes' => sanitize($input['notes'] ?? ''),
];

$orderId = $orderModel->createOrder($orderData, $validatedItems);

if ($orderId) {
    jsonResponse([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'total' => $totalAmount,
        'message' => 'Order placed successfully!'
    ]);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to place order. Please try again.'], 500);
}
