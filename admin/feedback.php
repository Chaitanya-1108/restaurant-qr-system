<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$feedbackModel = new FeedbackModel();
$allFeedback = $feedbackModel->getAllFeedback();
$stats = $feedbackModel->getAverageRating();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback -
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
                <div class="topbar-title">Customer Feedback</div>
            </div>
        </header>

        <div class="admin-content">
            <!-- Stats Bar -->
            <div class="stats-grid"
                style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 25px;">
                <div class="stat-card orange">
                    <div class="stat-icon orange"><i class="fas fa-star"></i></div>
                    <div class="stat-value">
                        <?= number_format($stats['avg_rating'] ?? 0, 1) ?> / 5.0
                    </div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
                    <div class="stat-value">
                        <?= $stats['total_count'] ?? 0 ?>
                    </div>
                    <div class="stat-label">Total Reviews</div>
                </div>
            </div>

            <div class="admin-table-wrap">
                <div class="admin-table-header">
                    <h5>Recent Reviews</h5>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Order #</th>
                                <th>Rating</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allFeedback)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">No
                                        feedback received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allFeedback as $f): ?>
                                    <tr>
                                        <td style="font-size: 0.8rem; color: var(--text-muted);">
                                            <?= date('M d, h:i A', strtotime($f['created_at'])) ?>
                                        </td>
                                        <td style="font-weight: 500;">
                                            <?= $f['customer_name'] ?>
                                        </td>
                                        <td>
                                            <?php if ($f['order_number']): ?>
                                                <span style="color: var(--primary-light);">#
                                                    <?= $f['order_number'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="color: #e8521a;">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= $i <= $f['rating'] ? 'fas' : 'far' ?> fa-star"
                                                        style="font-size: 0.75rem;"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td style="max-width: 300px; white-space: normal; line-height: 1.4;">
                                            <?= htmlspecialchars($f['comment']) ?>
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

</body>

</html>