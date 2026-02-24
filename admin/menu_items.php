<?php
require_once __DIR__ . '/../config/app.php';
requireAdmin();

$menuModel = new MenuModel();
$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item']) || isset($_POST['update_item'])) {
        $id = (int) ($_POST['item_id'] ?? 0);
        $data = [
            'category_id' => (int) $_POST['category_id'],
            'name' => sanitize($_POST['name']),
            'description' => sanitize($_POST['description']),
            'price' => (float) $_POST['price'],
            'is_veg' => isset($_POST['is_veg']) ? 1 : 0,
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order' => (int) $_POST['sort_order'],
        ];

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('menu_') . '.' . $ext;
            $uploadPath = __DIR__ . '/../uploads/menu/' . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $data['image'] = $fileName;
            }
        }

        if (isset($_POST['add_item'])) {
            if ($menuModel->createItem($data)) {
                $success = "Item added successfully.";
            } else {
                $error = "Failed to add item.";
            }
        } else {
            if ($menuModel->updateItem($id, $data)) {
                if (isset($data['image'])) {
                    $menuModel->updateItemImage($id, $data['image']);
                }
                $success = "Item updated successfully.";
            } else {
                $error = "Update failed.";
            }
        }
    }

    if (isset($_POST['delete_item'])) {
        $id = (int) $_POST['item_id'];
        if ($menuModel->deleteItem($id)) {
            $success = "Item deleted.";
        } else {
            $error = "Delete failed.";
        }
    }
}

$items = $menuModel->getAllItemsAdmin();
$categories = $menuModel->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items -
        <?= SITE_NAME ?> Admin
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        .thumb-preview {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            background: var(--border);
        }

        .modal-scroll {
            max-height: 80vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        .modal-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .modal-scroll::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 2px;
        }
    </style>
</head>

<body class="admin-body">

    <?php include __DIR__ . '/../app/Views/admin/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-title">Menu Management</div>
            </div>
            <button class="btn-primary-custom" onclick="openModal('add')">
                <i class="fas fa-plus me-1"></i> Add dish
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
                                <th>Item</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= BASE_URL ?>/uploads/menu/<?= $item['image'] ?>"
                                                    class="thumb-preview">
                                            <?php else: ?>
                                                <div class="thumb-preview"
                                                    style="display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                                    🍽️</div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600;">
                                                    <?= $item['name'] ?>
                                                </div>
                                                <?php if ($item['is_featured']): ?>
                                                    <span
                                                        style="font-size: 0.65rem; color: var(--accent); background: rgba(245,166,35,0.1); padding: 1px 6px; border-radius: 4px;">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= $item['category_name'] ?>
                                    </td>
                                    <td>
                                        <div class="veg-badge <?= $item['is_veg'] ? 'veg' : 'non-veg' ?>"
                                            style="position: static; display: inline-flex;"></div>
                                    </td>
                                    <td style="font-weight: 600; color: var(--accent);">
                                        <?= formatPrice($item['price']) ?>
                                    </td>
                                    <td>
                                        <?php if ($item['is_available']): ?>
                                            <span class="status-badge status-Completed" style="font-size: 0.7rem;">In
                                                Stock</span>
                                        <?php else: ?>
                                            <span class="status-badge status-Cancelled" style="font-size: 0.7rem;">Out of
                                                Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="status-btn preparing"
                                            onclick="openModal('edit', <?= htmlspecialchars(json_encode($item)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline-block;"
                                            onsubmit="return confirm('Delete this dish?')">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <button type="submit" name="delete_item" class="status-btn"
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

    <!-- Menu Item Modal -->
    <div id="itemModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="glass-card" style="width: 100%; max-width: 600px; padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h4 id="modalTitle">Add New Dish</h4>
                <button onclick="document.getElementById('itemModal').style.display='none'"
                    style="background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>

            <form method="POST" enctype="multipart/form-data" class="modal-scroll">
                <input type="hidden" name="item_id" id="itemId">

                <div class="form-group">
                    <label class="form-label">Dish Name</label>
                    <input type="text" name="name" id="itemName" class="search-wrapper"
                        style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                        required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="itemCat" class="search-wrapper"
                            style="width: 100%; padding: 12px; border-radius: 10px; background: var(--bg-card); color: #fff; border: 1px solid var(--border);"
                            required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (
                            <?= CURRENCY ?>)
                        </label>
                        <input type="number" step="0.01" name="price" id="itemPrice" class="search-wrapper"
                            style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="itemDesc" class="search-wrapper"
                        style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); height: 80px; resize: none;"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Dish Image</label>
                    <input type="file" name="image" class="search-wrapper"
                        style="width: 100%; padding: 10px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                        accept="image/*">
                    <small style="color: var(--text-muted); font-size: 0.7rem; display: block; margin-top: 5px;">Leave
                        empty to keep current image</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="itemOrder" class="search-wrapper"
                            style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border);"
                            value="0">
                    </div>
                    <div style="display: flex; flex-direction: column; justify-content: center; gap: 10px;">
                        <label
                            style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem;">
                            <input type="checkbox" name="is_veg" id="itemVeg"
                                style="accent-color: var(--success); width: 16px; height: 16px;"> Vegetarian
                        </label>
                        <label
                            style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem;">
                            <input type="checkbox" name="is_featured" id="itemFeatured"
                                style="accent-color: var(--accent); width: 16px; height: 16px;"> Featured Dish
                        </label>
                        <label
                            style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.85rem;">
                            <input type="checkbox" name="is_available" id="itemAvailable"
                                style="accent-color: var(--info); width: 16px; height: 16px;"> In Stock
                        </label>
                    </div>
                </div>

                <button type="submit" name="add_item" id="submitBtn" class="btn-primary-custom w-100"
                    style="width: 100%; padding: 12px; margin-top: 15px;">
                    Save Menu Item
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(type, data = null) {
            const modal = document.getElementById('itemModal');
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('submitBtn');

            modal.style.display = 'flex';

            if (type === 'add') {
                title.innerText = 'Add New Dish';
                btn.name = 'add_item';
                document.getElementById('itemId').value = '';
                document.getElementById('itemName').value = '';
                document.getElementById('itemPrice').value = '';
                document.getElementById('itemDesc').value = '';
                document.getElementById('itemOrder').value = '0';
                document.getElementById('itemVeg').checked = true;
                document.getElementById('itemFeatured').checked = false;
                document.getElementById('itemAvailable').checked = true;
            } else {
                title.innerText = 'Edit Dish';
                btn.name = 'update_item';
                document.getElementById('itemId').value = data.id;
                document.getElementById('itemName').value = data.name;
                document.getElementById('itemPrice').value = data.price;
                document.getElementById('itemDesc').value = data.description;
                document.getElementById('itemOrder').value = data.sort_order;
                document.getElementById('itemCat').value = data.category_id;
                document.getElementById('itemVeg').checked = data.is_veg == 1;
                document.getElementById('itemFeatured').checked = data.is_featured == 1;
                document.getElementById('itemAvailable').checked = data.is_available == 1;
            }
        }
    </script>
</body>

</html>