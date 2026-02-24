<?php
// ============================================
// Application Bootstrap / Autoloader
// ============================================

session_start();

require_once __DIR__ . '/../config/database.php';

// Simple autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/Models/' . $class . '.php',
        __DIR__ . '/../app/Controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Helper functions
function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

function generateOrderNumber(): string
{
    return 'ORD-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

function formatPrice(float $price): string
{
    return CURRENCY . number_format($price, 2);
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getStatusBadge(string $status): string
{
    $badges = [
        'Pending' => 'warning',
        'Preparing' => 'info',
        'Served' => 'primary',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ];
    $color = $badges[$status] ?? 'secondary';
    return "<span class='badge bg-{$color}'>{$status}</span>";
}
