<?php
require_once __DIR__ . '/../config/app.php';
if (isAdminLoggedIn()) {
  redirect(BASE_URL . '/admin/dashboard.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = sanitize($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($username && $password) {
    $adminModel = new AdminModel();
    $admin = $adminModel->findByUsername($username);
    if ($admin && $adminModel->verifyPassword($password, $admin['password'])) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_name'] = $admin['name'];
      $_SESSION['admin_user'] = $admin['username'];
      redirect(BASE_URL . '/admin/dashboard.php');
    } else {
      $error = 'Invalid username or password.';
    }
  } else {
    $error = 'Please fill in all fields.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - <?= SITE_NAME ?></title>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body class="admin-body">
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">🍽️</div>
      <h2 style="font-family:'Playfair Display',serif;text-align:center;margin-bottom:5px;">Admin Panel</h2>
      <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;margin-bottom:30px;"><?= SITE_NAME ?></p>
      <?php if ($error): ?>
        <div
          style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#e74c3c;">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="POST" id="loginForm">
        <div class="form-group">
          <label class="form-label">Username</label>
          <div style="position:relative;">
            <i class="fas fa-user"
              style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
            <input type="text" name="username" class="form-control-dark" style="padding-left:40px;"
              placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required
              autocomplete="username">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div style="position:relative;">
            <i class="fas fa-lock"
              style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
            <input type="password" name="password" id="password" class="form-control-dark"
              style="padding-left:40px;padding-right:44px;" placeholder="Enter password" required
              autocomplete="current-password">
            <button type="button" onclick="togglePwd()"
              style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div style="text-align:right;margin-top:8px;">
            <a href="forgot_password.php" style="font-size:0.75rem;color:var(--text-muted);text-decoration:none;">Forgot Password?</a>
          </div>
        </div>
        <button type="submit" class="btn-primary-custom w-100"
          style="border-radius:12px;padding:14px;font-size:0.95rem;margin-top:8px;">
          <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>
      </form>
      <div style="text-align:center;margin-top:20px;font-size:0.78rem;color:var(--text-muted);">
        Don't have an account? <a href="register.php"
          style="color:var(--primary);text-decoration:none;font-weight:600;">Sign Up</a>
      </div>
      <div style="text-align:center;margin-top:10px;font-size:0.78rem;color:var(--text-muted); opacity: 0.5;">
        Default: <code style="color:var(--accent);">admin</code> / <code style="color:var(--accent);">password</code>
      </div>
      <div style="text-align:center;margin-top:10px;">
        <a href="<?= BASE_URL ?>/" style="font-size:0.8rem;color:var(--text-muted);text-decoration:none;">
          <i class="fas fa-arrow-left me-1"></i>Back to Menu
        </a>
      </div>
    </div>
  </div>
  <script>
    function togglePwd() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('eyeIcon');
      if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'fas fa-eye-slash'; }
      else { pwd.type = 'password'; icon.className = 'fas fa-eye'; }
    }
  </script>
</body>

</html>