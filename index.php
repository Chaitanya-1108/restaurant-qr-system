<?php
require_once __DIR__ . '/config/app.php';

$menuModel = new MenuModel();
$orderModel = new OrderModel();

// Get table info
$tableNumber = sanitize($_GET['table'] ?? '');
$table = null;
if ($tableNumber) {
    $table = $orderModel->getTableByNumber($tableNumber);
}

// Get categories and items
$categories = $menuModel->getAllCategories();
$selectedCategoryId = (int) ($_GET['category'] ?? 0);
$search = sanitize($_GET['search'] ?? '');
$items = $menuModel->getAllItems($selectedCategoryId ?: null, $search ?: null);
$featuredItems = $menuModel->getFeaturedItems();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Digital Menu</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>

    <!-- Hero Header -->
    <header class="menu-hero">
        <div class="menu-hero-content container">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div class="restaurant-logo">🍽️</div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php if ($table): ?>
                        <div class="table-badge"><i class="fas fa-couch"></i> Table <?= $table['table_number'] ?></div>
                    <?php else: ?>
                        <div class="table-badge" style="background: rgba(255,255,255,0.1); box-shadow: none;"><i
                                class="fas fa-info-circle"></i> No Table Selected</div>
                    <?php endif; ?>
                    <button class="share-btn" onclick="shareMenu()" title="Share Menu">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
            <h1 class="gradient-text" style="font-size: 2.2rem; margin-bottom: 5px;"><?= SITE_NAME ?></h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Delicious food delivered to your table in minutes.
            </p>

            <!-- Search Bar -->
            <form action="<?= BASE_URL ?>/" method="GET" class="search-wrapper">
                <?php if ($tableNumber): ?><input type="hidden" name="table" value="<?= $tableNumber ?>"><?php endif; ?>
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" placeholder="Search for dishes..." value="<?= $search ?>">
            </form>
        </div>
    </header>

    <!-- Category Pills -->
    <div class="category-scroll">
        <a href="<?= BASE_URL ?>/<?= $tableNumber ? '?table=' . $tableNumber : '' ?>"
            class="cat-pill <?= !$selectedCategoryId ? 'active' : '' ?>">
            All
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>/?<?= $tableNumber ? "table=$tableNumber&" : "" ?>category=<?= $cat['id'] ?>"
                class="cat-pill <?= $selectedCategoryId == $cat['id'] ? 'active' : '' ?>">
                <span><?= $cat['icon'] ?></span> <?= $cat['name'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Main Menu -->
    <main class="menu-section container">

        <?php if ($featuredItems && !$selectedCategoryId && !$search): ?>
            <h3 class="section-title"><i class="fas fa-star" style="color: var(--accent);"></i> Chef's Specials</h3>
            <div class="menu-grid">
                <?php foreach ($featuredItems as $item): ?>
                    <div class="menu-card" id="item-<?= $item['id'] ?>"
                        onclick="openItemDetails(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>, '<?= $item['image'] ?: '' ?>', '<?= addslashes($item['description']) ?>')">
                        <div class="featured-badge">Featured</div>
                        <div class="veg-badge <?= $item['is_veg'] ? 'veg' : 'non-veg' ?>"></div>
                        <div class="menu-card-img">
                            <?php if ($item['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/menu/<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-utensils"></i>
                            <?php endif; ?>
                        </div>
                        <div class="menu-card-body">
                            <div class="menu-card-name"><?= $item['name'] ?></div>
                            <div class="menu-card-desc"><?= $item['description'] ?></div>
                            <div class="menu-card-price"><?= formatPrice($item['price']) ?></div>
                            <button class="add-btn"><i class="fas fa-plus me-1"></i> Add</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h3 class="section-title">
            <i class="fas fa-utensils" style="color: var(--primary);"></i>
            <?= $selectedCategoryId ? $categories[array_search($selectedCategoryId, array_column($categories, 'id'))]['name'] : ($search ? 'Search Results' : 'Explore Menu') ?>
        </h3>

        <div class="menu-grid">
            <?php if (empty($items)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-search" style="font-size: 3rem; opacity: 0.2; margin-bottom: 15px;"></i>
                    <p>No dishes found. Try a different search or category.</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="menu-card" id="item-<?= $item['id'] ?>"
                        onclick="openItemDetails(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>, '<?= $item['image'] ?: '' ?>', '<?= addslashes($item['description']) ?>')">
                        <div class="veg-badge <?= $item['is_veg'] ? 'veg' : 'non-veg' ?>"></div>
                        <div class="menu-card-img">
                            <?php if ($item['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/menu/<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-utensils"></i>
                            <?php endif; ?>
                        </div>
                        <div class="menu-card-body">
                            <div class="menu-card-name"><?= $item['name'] ?></div>
                            <div class="menu-card-desc"><?= $item['description'] ?></div>
                            <div class="menu-card-price"><?= formatPrice($item['price']) ?></div>
                            <button class="add-btn"><i class="fas fa-plus me-1"></i> Add</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Item Details Modal -->
        <div class="item-modal-overlay" id="itemModal" onclick="toggleItemModal()">
            <div class="item-modal" onclick="event.stopPropagation()">
                <button class="item-modal-close" onclick="toggleItemModal()"><i class="fas fa-times"></i></button>
                <div class="item-modal-img" id="modalItemImg">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="item-modal-body">
                    <div class="item-modal-header">
                        <div class="item-modal-title" id="modalItemName">Item Name</div>
                        <div class="item-modal-price" id="modalItemPrice">₹0.00</div>
                    </div>
                    <div class="item-modal-desc" id="modalItemDesc">
                        Item description goes here. Delicious and freshly prepared for you.
                    </div>

                    <div class="item-modal-instruction">
                        <label>Special Instructions</label>
                        <textarea id="modalItemNotes"
                            placeholder="E.g. Extra spicy, no onions, gluten free..."></textarea>
                    </div>

                    <div class="item-modal-footer">
                        <div class="qty-input">
                            <button onclick="updateModalQty(-1)"><i class="fas fa-minus"></i></button>
                            <span id="modalItemQty">1</span>
                            <button onclick="updateModalQty(1)"><i class="fas fa-plus"></i></button>
                        </div>
                        <button class="btn-primary-custom" style="flex: 1; border-radius: 12px; padding: 12px;"
                            onclick="addModalItemToCart()">
                            Add to Basket
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Cart Button -->
    <button class="cart-fab" id="cartFab" style="display: none;" onclick="toggleCart()">
        <i class="fas fa-shopping-basket"></i>
        <span>View Basket</span>
        <div class="cart-count">0</div>
    </button>

    <!-- Call Waiter Button -->
    <?php if ($table): ?>
        <button class="waiter-fab" onclick="toggleWaiterModal()">
            <i class="fas fa-bell"></i>
            <span>Service</span>
        </button>
    <?php endif; ?>

    <!-- Waiter Modal -->
    <div class="waiter-modal-overlay" id="waiterModal" onclick="toggleWaiterModal()">
        <div class="waiter-modal" onclick="event.stopPropagation()">
            <h3 style="margin-bottom: 20px; font-family: 'Poppins', sans-serif;">How can we help?</h3>

            <div class="waiter-option" onclick="sendWaiterRequest(event, 'Waiter')">
                <i class="fas fa-user-friends"></i>
                <div class="waiter-option-text">
                    <h4>Call Waiter</h4>
                    <p>Request assistance at your table</p>
                </div>
            </div>

            <div class="waiter-option" onclick="sendWaiterRequest(event, 'Water')">
                <i class="fas fa-tint"></i>
                <div class="waiter-option-text">
                    <h4>Request Water</h4>
                    <p>Need some refreshments?</p>
                </div>
            </div>

            <div class="waiter-option" onclick="sendWaiterRequest(event, 'Bill')">
                <i class="fas fa-file-invoice-dollar"></i>
                <div class="waiter-option-text">
                    <h4>Request Bill</h4>
                    <p>Ready to checkout?</p>
                </div>
            </div>

            <button class="btn-primary-custom"
                style="width: 100%; margin-top: 10px; background: var(--glass); color: #fff; border: 1px solid var(--border);"
                onclick="toggleWaiterModal()">
                Dismiss
            </button>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-basket me-2"></i> Your Basket</h3>
            <button class="cart-close" onclick="toggleCart()"><i class="fas fa-times"></i></button>
        </div>

        <div class="cart-items" id="cartItemsList">
            <!-- Items injected by JS -->
        </div>

        <div class="cart-footer">
            <div class="cart-total-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">₹0.00</span>
            </div>
            <div class="cart-total-row">
                <span>Tax (<?= TAX_PERCENT ?>%)</span>
                <span id="cartTax">₹0.00</span>
            </div>
            <div class="cart-total-row grand">
                <span>Grand Total</span>
                <span id="cartGrandTotal">₹0.00</span>
            </div>

            <div class="form-group mt-3" style="margin-top: 15px;">
                <label class="form-label"><i class="fas fa-user-circle me-1"></i> Your Name (Optional but
                    recommended)</label>
                <input type="text" id="customerName" class="search-wrapper"
                    style="border-radius: 12px; padding: 12px 15px; width: 100%; border: 1px solid var(--border); background: rgba(255,255,255,0.05); color: #fff;"
                    placeholder="Example: Rahul / Suman">
            </div>
            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label"><i class="fas fa-credit-card me-1"></i> Payment Method</label>
                <div class="payment-methods-grid">
                    <label class="payment-method-card">
                        <input type="radio" name="payment_method" value="Cash" checked>
                        <div class="payment-card-content">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </div>
                    </label>
                    <label class="payment-method-card">
                        <input type="radio" name="payment_method" value="UPI">
                        <div class="payment-card-content">
                            <i class="fas fa-mobile-alt"></i>
                            <span>UPI / Online</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px; margin-bottom: 25px;">
                <label class="form-label">Notes (Optional)</label>
                <textarea id="orderNotes" class="search-wrapper"
                    style="border-radius: 12px; padding: 10px 15px; width: 100%; border: 1px solid var(--border); background: rgba(255,255,255,0.05); color: #fff; height: 50px; resize: none;"
                    placeholder="Any special instructions?"></textarea>
            </div>

            <?php if ($table): ?>
                <button class="btn-primary-custom w-100" id="placeOrderBtn"
                    style="width: 100%; padding: 15px; font-size: 1rem; border-radius: 12px;" onclick="placeOrder()">
                    <i class="fas fa-paper-plane me-2"></i> Place Order
                </button>
            <?php else: ?>
                <div
                    style="background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.3); padding: 15px; border-radius: 12px; text-align: center; color: var(--danger); font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i> Please scan a table QR code to place an order.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        let cart = [];
        const tableId = <?= $table ? $table['id'] : 0 ?>;
        const tableNumber = '<?= $table ? $table['table_number'] : '' ?>';
        const taxPercent = <?= TAX_PERCENT ?>;
        const currency = '<?= CURRENCY ?>';
        const baseUrl = '<?= BASE_URL ?>';

        let currentItem = null;

        function openItemDetails(id, name, price, image, description) {
            currentItem = { id, name, price, image };
            document.getElementById('modalItemName').innerText = name;
            document.getElementById('modalItemPrice').innerText = currency + price.toFixed(2);
            document.getElementById('modalItemDesc').innerText = description || 'Freshly prepared with the finest ingredients.';
            document.getElementById('modalItemQty').innerText = '1';
            document.getElementById('modalItemNotes').value = '';

            const imgContainer = document.getElementById('modalItemImg');
            if (image) {
                imgContainer.innerHTML = `<img src="${baseUrl}/uploads/menu/${image}" alt="${name}">`;
            } else {
                imgContainer.innerHTML = '<i class="fas fa-utensils"></i>';
            }

            toggleItemModal();
        }

        function toggleItemModal() {
            document.getElementById('itemModal').classList.toggle('open');
        }

        function updateModalQty(delta) {
            const qtyEl = document.getElementById('modalItemQty');
            let qty = parseInt(qtyEl.innerText) + delta;
            if (qty < 1) qty = 1;
            qtyEl.innerText = qty;
        }

        function addModalItemToCart() {
            const qty = parseInt(document.getElementById('modalItemQty').innerText);
            const notes = document.getElementById('modalItemNotes').value.trim();

            for (let i = 0; i < qty; i++) {
                addToCart(currentItem.id, currentItem.name, currentItem.price, currentItem.image, notes);
            }

            toggleItemModal();
        }

        function addToCart(id, name, price, image, notes = '') {
            // If it's the same item AND same notes, we can combine quantity
            const existing = cart.find(item => item.id === id && item.notes === notes);
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ id, name, price, image, quantity: 1, notes: notes });
            }
            updateCartUI();
            showToast(`Added ${name} to basket!`, 'success');

            const card = document.getElementById(`item-${id}`);
            if (card) {
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.style.transform = '', 100);
            }
        }

        function updateCartUI() {
            const list = document.getElementById('cartItemsList');
            const fab = document.getElementById('cartFab');
            const count = document.querySelector('.cart-count');

            if (cart.length === 0) {
                list.innerHTML = `
                    <div class="cart-empty">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Your basket is empty.<br>Select something delicious!</p>
                    </div>
                `;
                fab.style.display = 'none';
            } else {
                fab.style.display = 'flex';
                count.innerText = cart.reduce((acc, item) => acc + item.quantity, 0);

                list.innerHTML = cart.map((item, index) => `
                    <div class="cart-item" style="flex-direction: column; align-items: stretch;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${currency}${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                        ${item.notes ? `<div style="font-size: 0.7rem; color: var(--accent); margin-top: 4px; border-left: 2px solid var(--accent); padding-left: 8px;">"${item.notes}"</div>` : ''}
                        <div class="qty-control" style="margin-top: 10px; width: 100px;">
                            <button class="qty-btn" onclick="event.stopPropagation(); changeQty(${index}, -1)">-</button>
                            <span class="qty-num">${item.quantity}</span>
                            <button class="qty-btn" onclick="event.stopPropagation(); changeQty(${index}, 1)">+</button>
                        </div>
                    </div>
                `).join('');
            }

            calculateTotals();
        }

        function changeQty(index, delta) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            updateCartUI();
        }

        function calculateTotals() {
            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
            const tax = subtotal * (taxPercent / 100);
            const grandTotal = subtotal + tax;

            document.getElementById('cartSubtotal').innerText = currency + subtotal.toFixed(2);
            document.getElementById('cartTax').innerText = currency + tax.toFixed(2);
            document.getElementById('cartGrandTotal').innerText = currency + grandTotal.toFixed(2);
        }

        function toggleCart() {
            document.getElementById('cartSidebar').classList.toggle('open');
            document.getElementById('cartOverlay').classList.toggle('open');
        }

        function toggleWaiterModal() {
            document.getElementById('waiterModal').classList.toggle('open');
        }

        async function sendWaiterRequest(event, type) {
            if (!tableId) {
                showToast('Please scan a table QR code first', 'error');
                return;
            }

            const activeOption = event.currentTarget;
            const originalContent = activeOption.innerHTML;
            activeOption.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            activeOption.style.pointerEvents = 'none';

            try {
                const response = await fetch(`${baseUrl}/api/call_waiter.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        table_id: tableId,
                        table_number: tableNumber,
                        request_type: type
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message);
                    toggleWaiterModal();
                } else {
                    showToast(result.message || 'Failed to send request', 'error');
                }
            } catch (error) {
                console.error('Waiter Request Error:', error);
                showToast('Connection error. Please try again.', 'error');
            } finally {
                activeOption.innerHTML = originalContent;
                activeOption.style.pointerEvents = 'all';
            }
        }

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-msg ${type}`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${msg}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'toastOut 0.4s ease forwards';
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        async function placeOrder() {
            if (cart.length === 0) return;

            const btn = document.getElementById('placeOrderBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'Cash';

            const orderData = {
                table_id: tableId,
                table_number: tableNumber,
                customer_name: document.getElementById('customerName').value || 'Guest',
                payment_method: paymentMethod,
                notes: document.getElementById('orderNotes').value,
                items: cart.map(item => ({
                    menu_item_id: item.id,
                    item_name: item.name,
                    item_price: item.price,
                    quantity: item.quantity,
                    notes: item.notes || ''
                }))
            };

            try {
                const response = await fetch(`${baseUrl}/api/place_order.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = `${baseUrl}/order_confirmation.php?order_number=${result.order_number}`;
                } else {
                    showToast(result.message || 'Something went wrong', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Place Order';
                }
            } catch (error) {
                showToast('Connection error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Place Order';
            }
        }

        async function shareMenu() {
            const shareData = {
                title: '<?= SITE_NAME ?> Menu',
                text: 'Check out our digital menu and place your order directly!',
                url: window.location.href
            };

            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else {
                    // Fallback to copy link
                    await navigator.clipboard.writeText(window.location.href);
                    showToast('Menu link copied to clipboard!', 'success');
                }
            } catch (err) {
                console.log('Share failed:', err);
            }
        }

        // Check for auto-call waiter from confirmation page
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('call_waiter')) {
                setTimeout(toggleWaiterModal, 500);
                // Clean up URL without refreshing
                const newUrl = window.location.pathname + window.location.search.replace(/[&?]call_waiter=1/, '');
                window.history.replaceState({}, '', newUrl);
            }
        });
    </script>
</body>

</html>