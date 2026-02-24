<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$orderModel = new OrderModel();
$statusFilter = $_GET['status'] ?? '';
$orders = $orderModel->getAllOrders($statusFilter);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Orders - <?= SITE_NAME ?> Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        .order-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 20px;
            overflow: hidden;
            transition: var(--transition);
        }

        .order-card:hover {
            border-color: var(--primary);
        }

        .order-card-header {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-card-body {
            padding: 20px;
        }

        .order-item-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 0.9rem;
        }

        .order-item-row:last-child {
            border-bottom: none;
        }

        .order-card-footer {
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-btn-group {
            display: flex;
            gap: 5px;
        }

        .status-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            font-weight: 500;
            transition: var(--transition);
        }

        .status-btn.preparing {
            background: rgba(41, 128, 185, 0.1);
            color: #2980b9;
            border-color: rgba(41, 128, 185, 0.3);
        }

        .status-btn.served {
            background: rgba(142, 68, 173, 0.1);
            color: #9b59b6;
            border-color: rgba(142, 68, 173, 0.3);
        }

        .status-btn.completed {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border-color: rgba(39, 174, 96, 0.3);
        }

        .status-btn.preparing:hover {
            background: #2980b9;
            color: #fff;
        }

        .status-btn.served:hover {
            background: #9b59b6;
            color: #fff;
        }

        .status-btn.completed:hover {
            background: #27ae60;
            color: #fff;
        }
    </style>
</head>

<body class="admin-body">

    <?php include __DIR__ . '/../app/Views/admin/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-title">Live Orders</div>
            </div>
            <div style="display: flex; gap: 10px;">
                <select id="statusFilter" class="search-wrapper"
                    style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem; margin: 0; background: var(--bg-card); color: #fff; border: 1px solid var(--border);"
                    onchange="location.href='?status='+this.value">
                    <option value="">All Status</option>
                    <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Preparing" <?= $statusFilter == 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                    <option value="Served" <?= $statusFilter == 'Served' ? 'selected' : '' ?>>Served</option>
                    <option value="Completed" <?= $statusFilter == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $statusFilter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button class="btn-primary-custom" onclick="location.reload()"
                    style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem;">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </header>

        <div class="admin-content">
            <?php if (empty($orders)): ?>
                <div class="glass-card" style="text-align: center; padding: 60px;">
                    <i class="fas fa-receipt" style="font-size: 4rem; opacity: 0.1; margin-bottom: 20px;"></i>
                    <h4>No orders found</h4>
                    <p style="color: var(--text-muted);">When customers place orders, they will appear here in real-time.
                    </p>
                </div>
            <?php else: ?>
                <div id="ordersContainer">
                    <?php
                    // Session-aware Add-on Detection
                    // 1. First, count how many active (Pending/Preparing/Served) orders exist for each table
                    $tableActiveCount = [];
                    foreach ($orders as $o) {
                        if (!in_array($o['status'], ['Completed', 'Cancelled'])) {
                            $tid = $o['table_id'];
                            $tableActiveCount[$tid] = ($tableActiveCount[$tid] ?? 0) + 1;
                        }
                    }

                    // 2. Loop through orders (assumed DESC order: newest first)
                    foreach ($orders as $order):
                        $isAddon = false;

                        // Only active orders can be considered add-ons
                        if (!in_array($order['status'], ['Completed', 'Cancelled'])) {
                            $tid = $order['table_id'];
                            // Decrement count for current order
                            $tableActiveCount[$tid]--;

                            // If there are still active orders for this table in the remaining (older) list,
                            // then this current order is an "Add-on" to those older orders.
                            if ($tableActiveCount[$tid] > 0) {
                                $isAddon = true;
                            }
                        }
                        ?>
                        <div class="order-card <?= $isAddon ? 'addon-order' : '' ?>" id="order-<?= $order['id'] ?>">
                            <div class="order-card-header">
                                <div>
                                    <span
                                        style="font-weight: 700; font-size: 1.1rem; color: var(--primary-light);">#<?= $order['order_number'] ?></span>
                                    <span class="table-badge" style="margin-left: 10px; padding: 4px 12px;">TABLE
                                        <?= $order['table_number'] ?></span>

                                    <!-- Payment Badge -->
                                    <span class="badge"
                                        style="margin-left: 10px; font-size: 0.7rem; background: <?= $order['payment_method'] === 'UPI' ? 'rgba(155, 89, 182, 0.2)' : 'rgba(46, 204, 113, 0.1)' ?>; color: <?= $order['payment_method'] === 'UPI' ? '#9b59b6' : '#2ecc71' ?>; border: 1px solid <?= $order['payment_method'] === 'UPI' ? 'rgba(155, 89, 182, 0.3)' : 'rgba(46, 204, 113, 0.2)' ?>; padding: 4px 10px; border-radius: 50px;">
                                        <i
                                            class="fas <?= $order['payment_method'] === 'UPI' ? 'fa-mobile-alt' : 'fa-money-bill-wave' ?> me-1"></i>
                                        <?= strtoupper($order['payment_method']) ?>
                                    </span>

                                    <!-- Payment Status Badge -->
                                    <?php if ($order['payment_status'] === 'Paid'): ?>
                                        <span class="badge"
                                            style="margin-left: 5px; font-size: 0.65rem; background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 2px 8px; border-radius: 4px;">
                                            <i class="fas fa-check-double me-1"></i> PAID
                                        </span>
                                    <?php elseif ($order['payment_status'] === 'Cancelled'): ?>
                                                    <span class="badge"
                                                        style="margin-left: 5px; font-size: 0.65rem; background: rgba(149, 165, 166, 0.2); color: #95a5a6; border: 1px solid rgba(149, 165, 166, 0.3); padding: 2px 8px; border-radius: 4px;">
                                                        <i class="fas fa-times-circle me-1"></i> CANCELLED
                                                    </span>
                                            <?php else: ?>
                                        <span class="badge pulse-border"
                                            style="margin-left: 5px; font-size: 0.65rem; background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 2px 8px; border-radius: 4px;">
                                            <i class="fas fa-clock me-1"></i> UNPAID
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($isAddon): ?>
                                        <span class="badge bg-info"
                                            style="margin-left: 10px; font-size: 0.7rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-plus-circle me-1"></i> ADD-ON ORDER
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?= getStatusBadge($order['status']) ?>
                                    <span style="margin-left: 15px; font-size: 0.8rem; color: var(--text-muted);"><i
                                            class="far fa-clock me-1"></i>
                                        <?= date('h:i A', strtotime($order['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="order-card-body">
                                <div style="margin-bottom: 15px; display: flex; justify-content: space-between;">
                                    <div>
                                        <div
                                            style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                                            Customer</div>
                                        <div style="font-weight: 600;"><?= $order['customer_name'] ?></div>
                                    </div>
                                    <?php if ($order['notes']): ?>
                                        <div style="max-width: 60%;">
                                            <div
                                                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                                                Special Notes</div>
                                            <div style="font-size: 0.85rem; color: var(--accent);"><i
                                                    class="fas fa-info-circle me-1"></i> <?= $order['notes'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="order-items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="order-item-row" style="flex-direction: column; align-items: flex-start;">
                                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                                <span>
                                                    <strong style="color: var(--primary-light);"><?= $item['quantity'] ?>x</strong>
                                                    <?= $item['item_name'] ?>
                                                </span>
                                                <span style="color: var(--text-muted);"><?= formatPrice($item['subtotal']) ?></span>
                                            </div>
                                            <?php if ($item['notes']): ?>
                                                <div
                                                    style="font-size: 0.75rem; color: var(--accent); margin-top: 2px; border-left: 2px solid var(--accent); padding-left: 8px;">
                                                    <i class="fas fa-comment-dots me-1"></i> <?= $item['notes'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <div style="font-size: 1.1rem; font-weight: 700;">
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">Total
                                        Amount:</span>
                                    <?= formatPrice($order['total_amount']) ?>
                                </div>
                                <div class="status-btn-group">
                                    <?php if ($order['status'] == 'Pending'): ?>
                                        <button class="status-btn preparing"
                                            onclick="updateStatus(<?= $order['id'] ?>, 'Preparing')">Start Preparing</button>
                                    <?php elseif ($order['status'] == 'Preparing'): ?>
                                        <button class="status-btn served" onclick="updateStatus(<?= $order['id'] ?>, 'Served')">Mark
                                            as Served</button>
                                    <?php elseif ($order['status'] == 'Served'): ?>
                                        <button class="status-btn completed"
                                            onclick="updateStatus(<?= $order['id'] ?>, 'Completed')">Mark as Paid & Free
                                            Table</button>
                                    <?php endif; ?>

                                    <?php if ($order['payment_status'] === 'Pending'): ?>
                                        <button class="status-btn"
                                            style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; border-color: rgba(46, 204, 113, 0.3);"
                                            onclick="confirmPayment(<?= $order['id'] ?>)">
                                            <i class="fas fa-check-circle"></i> Confirm Payment
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!in_array($order['status'], ['Completed', 'Cancelled'])): ?>
                                        <button class="status-btn"
                                            style="background: rgba(252, 186, 3, 0.1); color: #fcba03; border-color: rgba(252, 186, 3, 0.3);"
                                            onclick="printKOT(<?= htmlspecialchars(json_encode($order)) ?>)">
                                            <i class="fas fa-print"></i> KOT
                                        </button>
                                        <button class="status-btn"
                                            style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; border-color: rgba(231, 76, 60, 0.3);"
                                            onclick="updateStatus(<?= $order['id'] ?>, 'Cancelled')">Cancel</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- KOT Print Template (Hidden) -->
    <div id="kotPrintTemplate" style="display: none;">
        <div style="font-family: monospace; width: 300px; padding: 20px; border: 1px solid #000;">
            <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                <h3 style="margin: 0;">KITCHEN ORDER TICKET</h3>
                <h4 style="margin: 5px 0;">#<span id="p-orderNum"></span></h4>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Table:</strong> <span id="p-table"></span><br>
                <strong>Customer:</strong> <span id="p-customer"></span><br>
                <strong>Time:</strong> <span id="p-time"></span>
            </div>
            <div style="border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 10px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left;">
                            <th>Item</th>
                            <th style="text-align: right;">Qty</th>
                        </tr>
                    </thead>
                    <tbody id="p-items"></tbody>
                </table>
            </div>
            <div id="p-notes-wrap" style="margin-top: 10px; border: 1px dashed #000; padding: 5px; display: none;">
                <strong>Notes:</strong> <span id="p-notes"></span>
            </div>
            <div style="text-align: center; margin-top: 20px; font-size: 0.8rem;">
                Spice Garden Restaurant
            </div>
        </div>
    </div>

    <script>
        function printKOT(order) {
            document.getElementById('p-orderNum').textContent = order.order_number;
            document.getElementById('p-table').textContent = 'TABLE ' + order.table_number;
            document.getElementById('p-customer').textContent = order.customer_name;
            document.getElementById('p-time').textContent = new Date().toLocaleTimeString();

            const itemsList = document.getElementById('p-items');
            itemsList.innerHTML = '';
            order.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>
                    ${item.item_name}
                    ${item.notes ? `<div style="font-size: 0.7rem; font-style: italic;">* ${item.notes}</div>` : ''}
                </td><td style="text-align:right; border-bottom: 1px dotted #ccc;">${item.quantity}</td>`;
                itemsList.appendChild(tr);
            });

            const notesWrap = document.getElementById('p-notes-wrap');
            if (order.notes) {
                notesWrap.style.display = 'block';
                document.getElementById('p-notes').textContent = order.notes;
            } else {
                notesWrap.style.display = 'none';
            }

            const printContent = document.getElementById('kotPrintTemplate').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=400');
            printWindow.document.write('<html><head><title>Print KOT</title>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }

        async function confirmPayment(orderId) {
            try {
                const response = await fetch('<?= BASE_URL ?>/api/update_payment_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId, status: 'Paid' })
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Failed to update payment status');
                }
            } catch (e) {
                alert('Connection error');
            }
        }

        async function updateStatus(orderId, newStatus) {
            try {
                const response = await fetch('<?= BASE_URL ?>/api/update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId, status: newStatus })
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Failed to update status');
                }
            } catch (e) {
                alert('Connection error');
            }
        }

        // Auto-refresh handled globally by sidebar.php when new orders arrive
    </script>
</body>

</html>