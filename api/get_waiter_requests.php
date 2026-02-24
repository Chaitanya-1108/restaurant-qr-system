<?php
require_once __DIR__ . '/../config/app.php';

$waiterModel = new WaiterModel();
$pending = $waiterModel->getPendingRequests();

jsonResponse([
    'success' => true,
    'count' => count($pending),
    'requests' => $pending
]);
