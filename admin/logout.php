<?php
// Admin Logout
require_once __DIR__ . '/../config/app.php';
session_destroy();
redirect(BASE_URL . '/admin/login.php');
