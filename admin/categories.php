<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$menuModel = new MenuModel();
$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = sanitize($_POST['name']);
        $icon = sanitize($_POST['icon'] ?: '🍽️');
        $order = (int) $_POST['sort_order'];

        if ($menuModel->createCategory(['name' => $name, 'icon' => $icon, 'sort_order' => $order])) {
            $success = "Category '$name' added.";
        } else {
            $error = "Failed to add category.";
        }
    }

    if (isset($_POST['update_category'])) {
        $id = (int) $_POST['category_id'];
        $name = sanitize($_POST['name']);
        $icon = sanitize($_POST['icon']);
        $order = (int) $_POST['sort_order'];
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($menuModel->updateCategory($id, ['name' => $name, 'icon' => $icon, 'sort_order' => $order, 'is_active' => $active])) {
            $success = "Category updated.";
        } else {
            $error = "Update failed.";
        }
    }

    if (isset($_POST['delete_category'])) {
        $id = (int) $_POST['category_id'];
        if ($menuModel->deleteCategory($id)) {
            $success = "Category deleted.";
        } else {
            $error = "Delete failed. Ensure no items are linked to this category.";
        }
    }
}

$categories = $menuModel->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories -
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
                <div class="topbar-title">Manage Categories</div>
            </div>
            <button class="btn-primary-custom" onclick="openModal('add')">
                <i class="fas fa-plus me-1"></i> Add Category
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

            <div class="admin-table-wrap">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Icon</th>
                                <th>Category Name</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td style="font-size: 1.5rem;">
                                        <?= $cat['icon'] ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?= $cat['name'] ?>
                                    </td>
                                    <td>
                                        <?= $cat['sort_order'] ?>
                                    </td>
                                    <td>
                                        <?php if ($cat['is_active']): ?>
                                            <span class="status-badge"
                                                style="background: rgba(39, 174, 96, 0.1); color: #27ae60; border-color: rgba(39, 174, 96, 0.3);">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge"
                                                style="background: rgba(0,0,0,0.2); color: #9a9ab0; border-color: var(--border);">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="status-btn preparing"
                                            onclick="openModal('edit', <?= htmlspecialchars(json_encode($cat)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline-block;"
                                            onsubmit="return confirm('Delete this category?')">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                            <button type="submit" name="delete_category" class="status-btn"
                                                style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; border-color: rgba(231, 76, 60, 0.3);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Category Modal -->
    <div id="categoryModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="glass-card" style="width: 100%; max-width: 450px; padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h4 id="modalTitle">Add Category</h4>
                <button onclick="document.getElementById('categoryModal').style.display='none'"
                    style="background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="category_id" id="catId">
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="catName" class="search-wrapper"
                        style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                        required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji/HTML)</label>
                        <input type="text" name="icon" id="catIcon" class="search-wrapper"
                            style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                            placeholder="🍕">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="catOrder" class="search-wrapper"
                            style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                            value="0">
                    </div>
                </div>
                <div class="form-group" id="statusGroup" style="display: none;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="catActive"
                            style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span>Category is Active</span>
                    </label>
                </div>
                <button type="submit" name="add_category" id="submitBtn" class="btn-primary-custom w-100"
                    style="width: 100%; padding: 12px; margin-top: 15px;">
                    Save Category
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(type, data = null) {
            const modal = document.getElementById('categoryModal');
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('submitBtn');
            const statusGroup = document.getElementById('statusGroup');

            modal.style.display = 'flex';

            if (type === 'add') {
                title.innerText = 'Add New Category';
                btn.name = 'add_category';
                statusGroup.style.display = 'none';
                document.getElementById('catId').value = '';
                document.getElementById('catName').value = '';
                document.getElementById('catIcon').value = '🍽️';
                document.getElementById('catOrder').value = '0';
            } else {
                title.innerText = 'Edit Category';
                btn.name = 'update_category';
                statusGroup.style.display = 'block';
                document.getElementById('catId').value = data.id;
                document.getElementById('catName').value = data.name;
                document.getElementById('catIcon').value = data.icon;
                document.getElementById('catOrder').value = data.sort_order;
                document.getElementById('catActive').checked = data.is_active == 1;
            }
        }
    </script>
</body>

</html>