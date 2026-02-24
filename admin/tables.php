<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$orderModel = new OrderModel();
$error = '';
$success = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_table'])) {
        $tableNum = sanitize($_POST['table_number']);
        $capacity = (int) $_POST['capacity'];

        if ($tableNum && $capacity > 0) {
            if ($orderModel->createTable(['table_number' => $tableNum, 'capacity' => $capacity])) {
                $success = "Table $tableNum added successfully.";
            } else {
                $error = "Failed to add table. It might already exist.";
            }
        }
    }

    if (isset($_POST['delete_table'])) {
        $id = (int) $_POST['table_id'];
        if ($orderModel->deleteTable($id)) {
            $success = "Table deleted successfully.";
        } else {
            $error = "Failed to delete table.";
        }
    }
}

$tables = $orderModel->getAllTables();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tables & QR Codes -
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
                <div class="topbar-title">Tables & QR Code Management</div>
            </div>
            <button class="btn-primary-custom" onclick="document.getElementById('addTableModal').style.display='flex'">
                <i class="fas fa-plus me-1"></i> Add New Table
            </button>
        </header>

        <div class="admin-content">
            <?php if ($success): ?>
                <div
                    style="background: rgba(39, 174, 96, 0.1); border: 1px solid rgba(39, 174, 96, 0.3); color: #27ae60; padding: 15px; border-radius: 12px; margin-bottom: 25px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div
                    style="background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.3); color: #e74c3c; padding: 15px; border-radius: 12px; margin-bottom: 25px;">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php foreach ($tables as $table):
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode(BASE_URL . "/?table=" . $table['table_number']);
                    ?>
                    <div class="glass-card" style="padding: 25px; text-align: center;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <span class="table-badge" style="font-size: 1rem; padding: 6px 15px;">Table
                                <?= $table['table_number'] ?>
                            </span>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-users me-1"></i>
                                Cap:
                                <?= $table['capacity'] ?>
                            </span>
                        </div>

                        <div
                            style="background: #fff; padding: 15px; border-radius: 15px; display: inline-block; margin-bottom: 15px;">
                            <img src="<?= $qrUrl ?>" alt="QR Code" style="width: 150px; height: 150px; display: block;">
                        </div>

                        <div
                            style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 20px; word-break: break-all;">
                            <?= BASE_URL ?>/?table=
                            <?= $table['table_number'] ?>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <a href="<?= $qrUrl ?>&format=png" download="Table_<?= $table['table_number'] ?>_QR.png"
                                class="btn-primary-custom"
                                style="flex: 1; text-decoration: none; font-size: 0.8rem; padding: 8px;">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this table?')"
                                style="flex: 0 0 auto;">
                                <input type="hidden" name="table_id" value="<?= $table['id'] ?>">
                                <button type="submit" name="delete_table" class="status-btn"
                                    style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; border-color: rgba(231, 76, 60, 0.3); height: 36px; width: 36px; padding: 0;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Simple Add Table Modal Overlay -->
    <div id="addTableModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="glass-card" style="width: 100%; max-width: 400px; padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h4 style="margin: 0;">Add New Table</h4>
                <button onclick="document.getElementById('addTableModal').style.display='none'"
                    style="background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Table Number / Label</label>
                    <input type="text" name="table_number" class="search-wrapper"
                        style="width: 100%; padding: 12px 15px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                        placeholder="e.g. T1, VIP-1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity (Persons)</label>
                    <input type="number" name="capacity" class="search-wrapper"
                        style="width: 100%; padding: 12px 15px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                        value="4" min="1" required>
                </div>
                <button type="submit" name="add_table" class="btn-primary-custom w-100"
                    style="width: 100%; padding: 12px; margin-top: 10px;">
                    Save Table
                </button>
            </form>
        </div>
    </div>

</body>

</html>