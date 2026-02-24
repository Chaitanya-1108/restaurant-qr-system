<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$orderModel = new OrderModel();
$summary = $orderModel->getDailySummary();
$weeklySales = $orderModel->getWeeklySales();
$topItems = $orderModel->getTopItems();
$recentOrders = array_slice($orderModel->getAllOrders(), 0, 5);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?> Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="admin-body">

    <?php include __DIR__ . '/../app/Views/admin/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-title">Dashboard Overview</div>
            </div>
            <div class="topbar-user">
                <span style="color: var(--text-muted); font-size: 0.85rem; margin-right: 10px;">Welcome,
                    <?= $_SESSION['admin_name'] ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name']) ?>&background=e8521a&color=fff"
                    alt="User" style="width: 35px; height: 35px; border-radius: 50%;">
            </div>
        </header>

        <div class="admin-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card orange">
                    <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-value"><?= $summary['total_orders'] ?? 0 ?></div>
                    <div class="stat-label">Today's Orders</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon green"><i class="fas fa-indian-rupee-sign"></i></div>
                    <div class="stat-value"><?= formatPrice($summary['total_revenue'] ?? 0) ?></div>
                    <div class="stat-label">Today's Revenue</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon purple" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;"><i
                            class="fas fa-mobile-alt"></i></div>
                    <div class="stat-value"><?= formatPrice($summary['sum_upi'] ?? 0) ?></div>
                    <div class="stat-label">UPI Collection (<?= $summary['count_upi'] ?? 0 ?>)</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon orange" style="background: rgba(230, 126, 34, 0.1); color: #e67e22;"><i
                            class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-value"><?= formatPrice($summary['sum_cash'] ?? 0) ?></div>
                    <div class="stat-label">Cash Collection (<?= $summary['count_cash'] ?? 0 ?>)</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon blue"><i class="fas fa-clock"></i></div>
                    <div class="stat-value"><?= $summary['pending_orders'] ?? 0 ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-icon yellow"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?= $summary['completed_orders'] ?? 0 ?></div>
                    <div class="stat-label">Orders Completed</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px;">
                <!-- Sales Chart -->
                <div class="admin-table-wrap" style="padding: 20px;">
                    <h5 style="margin-bottom: 20px;">7-Day Sales Trend</h5>
                    <div style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Top Items -->
                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <h5>Top Selling Items</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align: right;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topItems)): ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; color: var(--text-muted);">No sales data
                                            yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($topItems as $item): ?>
                                        <tr>
                                            <td style="font-weight: 500;"><?= $item['item_name'] ?></td>
                                            <td style="text-align: right; color: var(--accent); font-weight: 600;">
                                                <?= $item['total_qty'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="admin-table-wrap">
                <div class="admin-table-header">
                    <h5>Recent Orders</h5>
                    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn-primary-custom"
                        style="padding: 6px 15px; font-size: 0.75rem; border-radius: 8px; text-decoration: none;">View
                        All</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Table</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">No
                                        orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--primary-light);"><?= $order['order_number'] ?>
                                        </td>
                                        <td><span class="table-badge"
                                                style="padding: 3px 10px; font-size: 0.7rem;">T<?= $order['table_number'] ?></span>
                                        </td>
                                        <td><?= $order['customer_name'] ?></td>
                                        <td style="font-weight: 600;"><?= formatPrice($order['total_amount']) ?></td>
                                        <td><?= getStatusBadge($order['status']) ?></td>
                                        <td style="color: var(--text-muted); font-size: 0.75rem;">
                                            <?= date('h:i A', strtotime($order['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart.js Configuration
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?= json_encode($weeklySales) ?>;

        const labels = salesData.map(d => d.date);
        const revenue = salesData.map(d => d.revenue);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenue,
                    borderColor: '#e8521a',
                    backgroundColor: 'rgba(232, 82, 26, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#e8521a',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9a9ab0', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9a9ab0', font: { size: 11 } }
                    }
                }
            }
        });
    </script>
</body>

</html>