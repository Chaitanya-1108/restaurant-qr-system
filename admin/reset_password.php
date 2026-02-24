<?php
require_once __DIR__ . '/../config/app.php';

if (isAdminLoggedIn()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Check if we came from the forgot_password page via session
if (!isset($_SESSION['reset_admin_id'])) {
    redirect(BASE_URL . '/admin/forgot_password.php');
}

$adminId = $_SESSION['reset_admin_id'];
$username = $_SESSION['reset_admin_username'];
$error = '';
$success = '';

$adminModel = new AdminModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            if ($adminModel->updatePassword($adminId, $password)) {
                $success = "Password updated successfully for <strong>$username</strong>!";
                // Clear the reset session
                unset($_SESSION['reset_admin_id']);
                unset($_SESSION['reset_admin_username']);
            } else {
                $error = "Failed to update password.";
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - <?= SITE_NAME ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body class="admin-body">
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo"><i class="fas fa-lock-open"></i></div>
            <h2 style="font-family:'Playfair Display',serif;text-align:center;margin-bottom:5px;">Set New Password</h2>
            <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;margin-bottom:30px;">Resetting
                password for: <strong><?= htmlspecialchars($username) ?></strong></p>

            <?php if ($error): ?>
                <div
                    style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#e74c3c;">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div
                    style="background:rgba(39,174,96,0.1);border:1px solid rgba(39,174,96,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#27ae60;">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                    <div style="margin-top:15px;"><a href="login.php" class="btn-primary-custom"
                            style="display:inline-block; text-decoration:none; padding:8px 20px; font-size:0.8rem;">Login
                            Now</a></div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="position:relative;">
                            <i class="fas fa-lock"
                                style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                            <input type="password" name="password" class="form-control-dark" style="padding-left:40px;"
                                placeholder="Min 6 characters" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div style="position:relative;">
                            <i class="fas fa-shield-alt"
                                style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                            <input type="password" name="confirm_password" class="form-control-dark"
                                style="padding-left:40px;" placeholder="Repeat password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary-custom w-100"
                        style="border-radius:12px;padding:14px;font-size:0.95rem;margin-top:8px;">
                        <i class="fas fa-save me-2"></i>Update Password
                    </button>
                </form>
            <?php endif; ?>

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