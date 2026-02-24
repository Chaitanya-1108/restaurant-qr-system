<?php
require_once __DIR__ . '/../config/app.php';

if (isAdminLoggedIn()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');

    if ($username) {
        $adminModel = new AdminModel();
        $admin = $adminModel->findByUsername($username);

        if ($admin) {
            // Store user ID in session temporarily for reset
            $_SESSION['reset_admin_id'] = $admin['id'];
            $_SESSION['reset_admin_username'] = $admin['username'];
            redirect(BASE_URL . '/admin/reset_password.php');
        } else {
            // Standard practice: don't reveal if email exists, but for admin panel it's fine
            $error = "No account found with the username: <strong>" . htmlspecialchars($username) . "</strong>";
        }
    } else {
        $error = "Please enter your username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password -
        <?= SITE_NAME ?>
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body class="admin-body">
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo"><i class="fas fa-key"></i></div>
            <h2 style="font-family:'Playfair Display',serif;text-align:center;margin-bottom:5px;">Forgot Password</h2>
            <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;margin-bottom:30px;">Enter your
                username to receive a reset link</p>

            <?php if ($error): ?>
                <div
                    style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#e74c3c;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div
                    style="background:rgba(39,174,96,0.1);border:1px solid rgba(39,174,96,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#27ae60;">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div style="position:relative;">
                        <i class="fas fa-user"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                        <input type="text" name="username" class="form-control-dark" style="padding-left:40px;"
                            placeholder="Enter your username" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary-custom w-100"
                    style="border-radius:12px;padding:14px;font-size:0.95rem;margin-top:8px;">
                    <i class="fas fa-paper-plane me-2"></i>Generate Reset Link
                </button>
            </form>

            <div style="text-align:center;margin-top:25px;">
                <a href="login.php"
                    style="font-size:0.85rem;color:var(--text-muted);text-decoration:none;font-weight:600;">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>

            <div style="text-align:center;margin-top:15px;">
                <a href="<?= BASE_URL ?>/" style="font-size:0.8rem;color:var(--text-muted);text-decoration:none;">
                    <i class="fas fa-arrow-left me-1"></i>Back to Menu
                </a>
            </div>
        </div>
    </div>
</body>

</html>