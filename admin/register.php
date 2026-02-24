<?php
require_once __DIR__ . '/../config/app.php';

if (isAdminLoggedIn()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name && $username && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $adminModel = new AdminModel();

            // Check if username exists
            if ($adminModel->findByUsername($username)) {
                $error = 'Username already taken.';
            } else {
                if ($adminModel->createAdmin($username, $password, $name)) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
            }
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
    <title>Admin Registration -
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
            <div class="login-logo">👨‍🍳</div>
            <h2 style="font-family:'Playfair Display',serif;text-align:center;margin-bottom:5px;">Admin Sign Up</h2>
            <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;margin-bottom:30px;">Create your
                administrator account</p>

            <?php if ($error): ?>
                <div
                    style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#e74c3c;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div
                    style="background:rgba(39,174,96,0.1);border:1px solid rgba(39,174,96,0.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:0.85rem;color:#27ae60;">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <div style="margin-top: 10px;">
                        <a href="login.php" style="color:#27ae60; font-weight:700;">Click here to Login</a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div style="position:relative;">
                        <i class="fas fa-id-card"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                        <input type="text" name="name" class="form-control-dark" style="padding-left:40px;"
                            placeholder="Full Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div style="position:relative;">
                        <i class="fas fa-user"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                        <input type="text" name="username" class="form-control-dark" style="padding-left:40px;"
                            placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position:relative;">
                        <i class="fas fa-lock"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                        <input type="password" name="password" id="password" class="form-control-dark"
                            style="padding-left:40px;" placeholder="Password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div style="position:relative;">
                        <i class="fas fa-shield-alt"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                        <input type="password" name="confirm_password" class="form-control-dark"
                            style="padding-left:40px;" placeholder="Confirm Password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary-custom w-100"
                    style="border-radius:12px;padding:14px;font-size:0.95rem;margin-top:8px;">
                    <i class="fas fa-user-plus me-2"></i>Sign Up
                </button>
            </form>

            <div style="text-align:center;margin-top:25px;font-size:0.85rem;color:var(--text-muted);">
                Already have an account? <a href="login.php"
                    style="color:var(--primary);text-decoration:none;font-weight:600;">Login Here</a>
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