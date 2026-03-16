<?php
// Simple routing for a single-page event ticket system.
// All the UI lives in app/view/main.php.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

// Start session early to support flash messages and login state.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$authController = new AuthController($conn);
$adminController = new AdminController($conn);
$action = $_GET['action'] ?? 'home';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $authController->register();
    }
    if ($action === 'login') {
        $authController->login();
    }
    if ($action === 'create_user') {
        $adminController->createUser();
    }
}

// Handle GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'logout') {
        $authController->logout();
    }

    if ($action === 'admin') {
        require_once __DIR__ . '/../app/view/admin/dashboard.php';
        return;
    }

    if ($action === 'staff') {
        require_once __DIR__ . '/../app/view/staff/dashboard.php';
        return;
    }

    if ($action === 'profile') {
        require_once __DIR__ . '/../app/view/profile.php';
        return;
    }

    if ($action === 'login') {
        require_once __DIR__ . '/../app/view/Auth/dang_nhap.php';
        return;
    }

    if ($action === 'register') {
        require_once __DIR__ . '/../app/view/Auth/dang_ky.php';
        return;
    }
}

// Default: render the main view (home)
require_once __DIR__ . '/../app/view/main.php';
