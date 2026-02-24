<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$waiterModel = new WaiterModel();
$pendingRequests = $waiterModel->getPendingRequests();

// Handle completing request
if (isset($_GET['complete'])) {
    $waiterModel->completeRequest((int) $_GET['complete']);
    redirect('waiter_requests.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Waiter Assistance -
        <?= SITE_NAME ?>
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body class="admin-body">
    <?php include __DIR__ . '/../app/Views/admin/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-title">Waiter Assistance Requests</div>
            </div>
            <button class="btn-primary-custom" onclick="location.reload()"
                style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem;">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </header>

        <div class="admin-content">
            <div class="glass-card" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Active Requests</h3>
                    <span class="badge bg-danger" id="requestCount">
                        <?= count($pendingRequests) ?> Pending
                    </span>
                </div>

                <?php if (empty($pendingRequests)): ?>
                    <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                        <i class="fas fa-check-circle" style="font-size: 4rem; opacity: 0.2; margin-bottom: 15px;"></i>
                        <p>No pending assistance requests.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Request Type</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $req): ?>
                                    <tr>
                                        <td><strong>Table
                                                <?= $req['table_number'] ?>
                                            </strong></td>
                                        <td>
                                            <?php
                                            $icon = 'bell';
                                            $class = 'warning';
                                            if ($req['request_type'] == 'Bill') {
                                                $icon = 'file-invoice-dollar';
                                                $class = 'success';
                                            }
                                            if ($req['request_type'] == 'Water') {
                                                $icon = 'tint';
                                                $class = 'info';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $class ?>">
                                                <i class="fas fa-<?= $icon ?> me-1"></i>
                                                <?= $req['request_type'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('h:i A', strtotime($req['created_at'])) ?> (
                                            <?= floor((time() - strtotime($req['created_at'])) / 60) ?> mins ago)
                                        </td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <a href="?complete=<?= $req['id'] ?>" class="btn-primary-custom"
                                                style="padding: 5px 15px; font-size: 0.8rem;">
                                                <i class="fas fa-check"></i> Complete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Refresh handled globally by sidebar.php when new requests arrive
    </script>
</body>

</html>