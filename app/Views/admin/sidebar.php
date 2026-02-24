<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Toast Container for Notifications -->
<div id="toastContainer" class="toast-container"></div>

<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand-icon">🍽️</div>
    <div class="sidebar-brand-text">
      <h4><?= SITE_NAME ?></h4>
      <span>Admin Panel</span>
    </div>
    <!-- Close button for mobile -->
    <button class="sidebar-close" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Overview</div>
    <a href="<?= BASE_URL ?>/admin/dashboard.php"
      class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>/admin/orders.php"
      class="sidebar-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>">
      <i class="fas fa-receipt"></i> Live Orders
      <span id="pendingBadge"
        style="margin-left:auto;background:var(--danger);color:#fff;border-radius:50%;width:20px;height:20px;font-size:0.7rem;display:none;align-items:center;justify-content:center;"></span>
    </a>
    <a href="<?= BASE_URL ?>/admin/waiter_requests.php"
      class="sidebar-link <?= $currentPage === 'waiter_requests.php' ? 'active' : '' ?>">
      <i class="fas fa-bell"></i> Waiter Assistance
      <span id="waiterBadge"
        style="margin-left:auto;background:var(--accent);color:#fff;border-radius:50%;width:20px;height:20px;font-size:0.7rem;display:none;align-items:center;justify-content:center;"></span>
    </a>

    <div class="nav-section-title" style="margin-top:20px;">Menu Management</div>
    <a href="<?= BASE_URL ?>/admin/menu_items.php"
      class="sidebar-link <?= $currentPage === 'menu_items.php' ? 'active' : '' ?>">
      <i class="fas fa-utensils"></i> Menu Items
    </a>
    <a href="<?= BASE_URL ?>/admin/categories.php"
      class="sidebar-link <?= $currentPage === 'categories.php' ? 'active' : '' ?>">
      <i class="fas fa-tags"></i> Categories
    </a>

    <div class="nav-section-title" style="margin-top:20px;">Tables & QR</div>
    <a href="<?= BASE_URL ?>/admin/tables.php"
      class="sidebar-link <?= $currentPage === 'tables.php' ? 'active' : '' ?>">
      <i class="fas fa-chair"></i> Tables & QR Codes
    </a>

    <div class="nav-section-title" style="margin-top:20px;">Reports</div>
    <a href="<?= BASE_URL ?>/admin/sales.php" class="sidebar-link <?= $currentPage === 'sales.php' ? 'active' : '' ?>">
      <i class="fas fa-chart-bar"></i> Sales Report
    </a>
    <a href="<?= BASE_URL ?>/admin/feedback.php"
      class="sidebar-link <?= $currentPage === 'feedback.php' ? 'active' : '' ?>">
      <i class="fas fa-comment-dots"></i> Customer Feedback
    </a>

    <div class="nav-section-title" style="margin-top:20px;">Settings</div>
    <div class="sidebar-link"
      style="cursor: default; display: flex; justify-content: space-between; align-items: center;">
      <span><i class="fas fa-volume-up"></i> Sound Alerts</span>
      <label class="switch" style="position: relative; display: inline-block; width: 34px; height: 20px;">
        <input type="checkbox" id="soundToggle" checked onchange="toggleSoundPref(this.checked)"
          style="opacity: 0; width: 0; height: 0;">
        <span class="slider"
          style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 20px;"></span>
      </label>
    </div>

    <div class="nav-section-title" style="margin-top:20px;">System</div>
    <a href="<?= BASE_URL ?>/" target="_blank" class="sidebar-link">
      <i class="fas fa-external-link-alt"></i> View Menu
    </a>
    <a href="<?= BASE_URL ?>/admin/logout.php" class="sidebar-link logout-btn">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </nav>
</aside>

<style>
  .switch input:checked+.slider {
    background-color: var(--primary);
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
  }

  .switch input:checked+.slider:before {
    transform: translateX(14px);
  }
</style>

<script>
  let lastWaiterCount = -1;
  let lastOrderCount = -1;
  let lastPaidOrderCount = -1; // Track payments specifically
  let isInitialLoad = true;

  function toggleSoundPref(enabled) {
    localStorage.setItem('soundAlerts', enabled ? 'on' : 'off');
    if (enabled) {
      // Play a short beep to confirm
      new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3').play().catch(e => { });
    }
  }

  // Load Initial Sound Pref
  document.addEventListener('DOMContentLoaded', () => {
    const pref = localStorage.getItem('soundAlerts') || 'on';
    const toggle = document.getElementById('soundToggle');
    if (toggle) toggle.checked = (pref === 'on');
  });

  function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
  }

  async function updateCounts() {
    try {
      // Update Order Count (Pending)
      const resOrder = await fetch('<?= BASE_URL ?>/api/get_orders.php?status=Pending');
      const dataOrder = await resOrder.json();
      const orderBadge = document.getElementById('pendingBadge');

      let newOrderDetected = false;
      if (orderBadge) {
        if (dataOrder.count > 0) {
          orderBadge.textContent = dataOrder.count;
          orderBadge.style.display = 'flex';
          if (!isInitialLoad && dataOrder.count > lastOrderCount) {
            newOrderDetected = true;
          }
        } else {
          orderBadge.style.display = 'none';
        }
      }

      // Check for Paid but not completed orders (new payments)
      // This is crucial for the "Real-time payment update" demo
      const resPaid = await fetch('<?= BASE_URL ?>/api/get_orders.php');
      const dataAll = await resPaid.json();
      const currentPaidCount = (dataAll.orders || []).filter(o => o.payment_status === 'Paid' && o.status !== 'Completed').length;

      let newPaymentDetected = false;
      if (!isInitialLoad && currentPaidCount > lastPaidOrderCount) {
        newPaymentDetected = true;
      }

      // Update Waiter Request Count
      const resWaiter = await fetch('<?= BASE_URL ?>/api/get_waiter_requests.php');
      const dataWaiter = await resWaiter.json();
      const waiterBadge = document.getElementById('waiterBadge');

      let newWaiterRequestDetected = false;
      if (waiterBadge) {
        if (dataWaiter.count > 0) {
          waiterBadge.textContent = dataWaiter.count;
          waiterBadge.style.display = 'flex';
          if (!isInitialLoad && dataWaiter.count > lastWaiterCount) {
            newWaiterRequestDetected = true;
          }
        } else {
          waiterBadge.style.display = 'none';
        }
      }

      // If new data detected, play sound and refresh relevant pages
      if (newOrderDetected || newWaiterRequestDetected || newPaymentDetected) {
        if (newOrderDetected) {
          playNotificationSound('order');
          showToast('New customer order received! 📦', 'success');
        }
        if (newPaymentDetected) {
          playNotificationSound('order'); // Use same sound for payment success
          showToast('Payment received for an order! 💰', 'success');
        }
        if (newWaiterRequestDetected) {
          playNotificationSound('waiter');
          showToast('Waiter requested at a table! 🔔', 'waiter');
        }

        // Pages that should automatically refresh to show new data
        const livePages = ['dashboard.php', 'orders.php', 'waiter_requests.php', 'sales.php'];
        const currentPage = '<?= $currentPage ?>';

        if (livePages.includes(currentPage)) {
          setTimeout(() => location.reload(), 1500); // Refresh faster for demo
        }
      }

      lastOrderCount = dataOrder.count;
      lastWaiterCount = dataWaiter.count;
      lastPaidOrderCount = currentPaidCount;
      isInitialLoad = false;
    } catch (e) {
      console.error('Polling error:', e);
    }
  }

  function playNotificationSound(type) {
    if (localStorage.getItem('soundAlerts') === 'off') return;

    // Order: Modern "Ping", Waiter: "Bell chime"
    const orderSound = 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3';
    const waiterSound = 'https://assets.mixkit.co/active_storage/sfx/951/951-preview.mp3';

    const audio = new Audio(type === 'order' ? orderSound : waiterSound);
    audio.play().catch(e => console.log('Audio play failed (waiting for interaction):', e));
  }

  function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast-msg ${type}`;
    toast.style.marginBottom = '10px';
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'waiter' ? 'fa-bell' : 'fa-exclamation-circle')}"></i>
        <span>${msg}</span>
    `;

    // Add custom style for waiter toast if not in CSS
    if (type === 'waiter') {
      toast.style.borderLeftColor = 'var(--accent)';
    }

    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.4s ease';
      setTimeout(() => toast.remove(), 400);
    }, 5000);
  }

  // Initialize
  updateCounts();
  setInterval(updateCounts, 3000); // 3 seconds polling for true real-time demo
</script>