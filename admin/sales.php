<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$orderModel = new OrderModel();
$summary = $orderModel->getDailySummary();
$weeklySales = $orderModel->getWeeklySales();
$topItems = $orderModel->getTopItems();

// Calculate some extra stats
$totalOrders = 0;
$totalRevenue = 0;
foreach ($weeklySales as $day) {
    $totalOrders += $day['orders'];
    $totalRevenue += $day['revenue'];
}
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report -
        <?= SITE_NAME ?> Admin
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body class="admin-body">

    <?php include __DIR__ . '/../app/Views/admin/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-title">Sales & Performance Report</div>
            </div>
            <button class="btn-primary-custom" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Export PDF
            </button>
        </header>

        <div class="admin-content">
            <!-- Summary Row -->
            <div class="stats-grid">
                <div class="stat-card orange">
                    <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-value">
                        <?= formatPrice($totalRevenue) ?>
                    </div>
                    <div class="stat-label">Last 7 Days Revenue</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
                    <div class="stat-value">
                        <?= $totalOrders ?>
                    </div>
                    <div class="stat-label">Last 7 Days Orders</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon green"><i class="fas fa-calculator"></i></div>
                    <div class="stat-value">
                        <?= formatPrice($avgOrderValue) ?>
                    </div>
                    <div class="stat-label">Avg. Order Value</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-icon yellow"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-value">
                        <?= formatPrice($summary['total_revenue'] ?? 0) ?>
                    </div>
                    <div class="stat-label">Today's Revenue</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Sales Breakdown Table -->
                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <h5>Daily Sales Breakdown</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Orders</th>
                                    <th style="text-align: right;">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($weeklySales) as $day): ?>
                                    <tr>
                                        <td style="font-weight: 500;">
                                            <?= date('M d, Y', strtotime($day['date'])) ?>
                                        </td>
                                        <td>
                                            <?= $day['orders'] ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: var(--accent);">
                                            <?= formatPrice($day['revenue']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Sellers Detail -->
                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <h5>Menu Item Performance (Top 5)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Menu Item</th>
                                    <th>Quantity Sold</th>
                                    <th style="text-align: right;">Gross Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topItems as $item): ?>
                                    <tr>
                                        <td style="font-weight: 500; font-size: 0.9rem;">
                                            <?= $item['item_name'] ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="font-weight: 600;">
                                                    <?= $item['total_qty'] ?>
                                                </span>
                                                <div
                                                    style="flex: 1; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; min-width: 60px;">
                                                    <?php
                                                    $maxQty = $topItems[0]['total_qty'];
                                                    $percent = ($item['total_qty'] / $maxQty) * 100;
                                                    ?>
                                                    <div
                                                        style="width: <?= $percent ?>%; height: 100%; background: var(--primary); border-radius: 3px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: var(--success);">
                                            <?= formatPrice($item['total_revenue']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 25px;">
                <div class="glass-card" style="padding: 25px;">
                    <h5 style="margin-bottom: 20px;">Order Status Breakdown (Today)</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div
                            style="background: rgba(243, 156, 18, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(243, 156, 18, 0.1);">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Pending
                            </div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #f39c12;">
                                <?= $summary['pending_orders'] ?? 0 ?>
                            </div>
                        </div>
                        <div
                            style="background: rgba(41, 128, 185, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(41, 128, 185, 0.1);">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                Preparing</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #2980b9;">
                                <?= $summary['preparing_orders'] ?? 0 ?>
                            </div>
                        </div>
                        <div
                            style="background: rgba(39, 174, 96, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(39, 174, 96, 0.1);">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                Completed</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #27ae60;">
                                <?= $summary['completed_orders'] ?? 0 ?>
                            </div>
                        </div>
                        <div
                            style="background: rgba(231, 76, 60, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(231, 76, 60, 0.1);">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                Cancelled</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #e74c3c;">
                                <?= $summary['cancelled_orders'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card" style="padding: 25px;">
                    <h5 style="margin-bottom: 20px;">Payment Collection (Today)</h5>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(155, 89, 182, 0.05); border-radius: 12px; border: 1px solid rgba(155, 89, 182, 0.1);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(155, 89, 182, 0.1); display: flex; align-items: center; justify-content: center; color: #9b59b6;">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div>
                                    <div style="font-size: 0.85rem; font-weight: 600;">UPI Payments
                                        (<?= $summary['count_upi'] ?? 0 ?>)</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">Digital collection</div>
                                </div>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: #9b59b6;">
                                <?= formatPrice($summary['sum_upi'] ?? 0) ?>
                            </div>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(230, 126, 34, 0.05); border-radius: 12px; border: 1px solid rgba(230, 126, 34, 0.1);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(230, 126, 34, 0.1); display: flex; align-items: center; justify-content: center; color: #e67e22;">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <div style="font-size: 0.85rem; font-weight: 600;">Cash Payments
                                        (<?= $summary['count_cash'] ?? 0 ?>)</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">Counter collection</div>
                                </div>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: #e67e22;">
                                <?= formatPrice($summary['sum_cash'] ?? 0) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        @media print {

            .admin-sidebar,
            .admin-topbar button {
                display: none;
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-body {
                background: #fff;
                color: #000;
            }

            .glass-card,
            .admin-table-wrap {
                border: 1px solid #ddd;
                box-shadow: none;
                background: #fff;
            }

            .stat-value,
            .stat-label {
                color: #000;
            }
        }
    </style>
</body>

</html>