<?php
// =========================================================================
// ROUTER CHÍNH (Front Controller Pattern)
// Luồng xử lý: Client → index.php → routes/web.php → Controller → Model → View
// =========================================================================

// Helpers & Config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/security.php';

// Controllers
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/TicketController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';

// Models
require_once __DIR__ . '/../app/model/EventTicket.php';

// Khởi tạo session sớm (hỗ trợ flash messages và trạng thái đăng nhập)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Khởi tạo các controller
$authController   = new AuthController($conn);
$adminController  = new AdminController($conn);
$ticketController = new TicketController($conn);
$cartController   = new CartController($conn);
$eventTicketModel = new EventTicket($conn);

// Xác định action từ request
$action = $_GET['action'] ?? $_POST['action'] ?? 'home';

// =========================================================================
// XỬ LÝ POST ACTIONS
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Xác thực CSRF cho tất cả POST request
    // Ngoại trừ SePay IPN (webhook callback từ bên ngoài, dùng secret key riêng)
    $csrfExemptActions = ['sepay_ipn'];
    if (!in_array($action, $csrfExemptActions, true)) {
        csrf_protect('index.php');
    }

    // Xác thực (Auth)
    if ($action === 'register') {
        $authController->register();
    }
    if ($action === 'login') {
        $authController->login();
    }
    if ($action === 'change_password') {
        $authController->changePassword();
    }

    // Quản lý người dùng (Admin only)
    if ($action === 'create_user') {
        $adminController->createUser();
    }
    if ($action === 'update_user') {
        $adminController->updateUser();
    }
    if ($action === 'delete_user') {
        $adminController->deleteUser();
    }

    // Quản lý danh mục
    if ($action === 'create_category') {
        $adminController->createCategory();
    }
    if ($action === 'update_category') {
        $adminController->updateCategory();
    }
    if ($action === 'delete_category') {
        $adminController->deleteCategory();
    }

    // Quản lý sự kiện
    if ($action === 'create_event') {
        $adminController->createEvent();
    }
    if ($action === 'update_event') {
        $adminController->updateEvent();
    }
    if ($action === 'delete_event') {
        $adminController->deleteEvent();
    }
    if ($action === 'approve_event') {
        $adminController->approveEvent();
    }

    // Quản lý loại vé
    if ($action === 'create_ticket_type') {
        $adminController->createTicketType();
    }
    if ($action === 'update_ticket_type') {
        $adminController->updateTicketType();
    }
    if ($action === 'delete_ticket_type') {
        $adminController->deleteTicketType();
    }

    // Quản lý đơn hàng
    if ($action === 'confirm_order_payment') {
        $adminController->confirmOrderPayment();
    }
    if ($action === 'delete_order') {
        $adminController->deleteOrder();
    }
    if ($action === 'delete_all_orders') {
        $adminController->deleteAllOrders();
    }

    // Soát vé (Check-in)
    if ($action === 'process_checkin') {
        $adminController->processCheckin();
    }

    // Quản lý tin tức
    if ($action === 'create_news') {
        $adminController->createNews();
    }
    if ($action === 'update_news') {
        $adminController->updateNews();
    }
    if ($action === 'delete_news') {
        $adminController->deleteNews();
    }

    // Mua vé
    if ($action === 'buy_ticket') {
        $ticketController->buyTicket();
    }

    // Giỏ hàng (delegate → CartController)
    if ($action === 'add_to_cart') {
        $cartController->addToCart();
    }
    if ($action === 'update_cart') {
        $cartController->updateCart();
    }
    if ($action === 'checkout_cart') {
        $cartController->checkoutCart();
    }

    // SePay Webhook (IPN)
    if ($action === 'sepay_ipn') {
        $ticketController->sepayIpn();
    }
    if ($action === 'sepay_ipn_test') {
        $ticketController->sepayIpnTest();
    }
}

// =========================================================================
// XỬ LÝ GET ACTIONS
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Đăng xuất
    if ($action === 'logout') {
        $authController->logout();
    }

    // Xuất báo cáo Excel/CSV
    if ($action === 'export_statistics') {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            header('Location: index.php');
            exit;
        }
        $type  = $_GET['type']  ?? 'month';
        $value = $_GET['value'] ?? date('Y-m');
        $stats = $eventTicketModel->getStatistics($type, $value);

        $filename = 'thongke_' . $type . '_' . $value . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        $out = fopen('php://output', 'w');
        // BOM UTF-8 để Excel đọc đúng tiếng Việt
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Mã đơn', 'Khách hàng', 'Email', 'Chi tiết vé', 'Tổng vé', 'Tổng tiền (đ)', 'Ngày tạo']);
        foreach ($stats['orders'] as $o) {
            fputcsv($out, [
                $o['ma_don_hang'],
                $o['ho_ten'],
                $o['email'],
                $o['chi_tiet_ve'],
                $o['tong_ve'],
                number_format((float)$o['tong_tien'], 0, '.', ''),
                $o['ngay_tao'],
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, [
            'TỔNG CỘNG', '', '', '',
            $stats['summary']['tong_ve'] ?? 0,
            number_format((float)($stats['summary']['tong_doanh_thu'] ?? 0), 0, '.', ''),
            '',
        ]);
        fclose($out);
        exit;
    }

    // Trang Admin
    if ($action === 'admin') {
        require_once __DIR__ . '/../app/view/admin/dashboard.php';
        return;
    }
    if ($action === 'admin_categories') {
        require_once __DIR__ . '/../app/view/admin/categories.php';
        return;
    }
    if ($action === 'admin_events') {
        require_once __DIR__ . '/../app/view/admin/events.php';
        return;
    }
    if ($action === 'admin_tickets') {
        require_once __DIR__ . '/../app/view/admin/tickets.php';
        return;
    }
    if ($action === 'admin_news') {
        require_once __DIR__ . '/../app/view/admin/news.php';
        return;
    }
    if ($action === 'admin_checkin') {
        $adminController->showCheckin();
        return;
    }

    // Nhân viên redirect → admin
    if ($action === 'staff') {
        header('Location: index.php?action=admin');
        exit;
    }

    // Trang công khai
    if ($action === 'about') {
        require_once __DIR__ . '/../app/view/about.php';
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
    if ($action === 'ticket_detail') {
        require_once __DIR__ . '/../app/view/ticket_detail.php';
        return;
    }
    if ($action === 'events') {
        require_once __DIR__ . '/../app/view/events.php';
        return;
    }
    if ($action === 'news') {
        require_once __DIR__ . '/../app/view/news.php';
        return;
    }
    if ($action === 'news_detail') {
        require_once __DIR__ . '/../app/view/news_detail.php';
        return;
    }

    // Giỏ hàng
    if ($action === 'cart') {
        require_once __DIR__ . '/../app/view/cart.php';
        return;
    }
    if ($action === 'remove_from_cart') {
        $cartController->removeFromCart();
    }

    // Thanh toán
    if ($action === 'payment') {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login');
            exit;
        }
        $orderId = (int)($_GET['order_id'] ?? 0);
        $paymentInfo = $eventTicketModel->getCartOrderPaymentInfo($orderId, (string)$user['email']);
        if (!$paymentInfo) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy đơn hàng.'];
            header('Location: index.php?action=profile');
            exit;
        }
        require_once __DIR__ . '/../app/view/checkout.php';
        return;
    }

    if ($action === 'payment_status') {
        header('Content-Type: application/json; charset=utf-8');
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false]);
            exit;
        }
        $orderId = (int)($_GET['order_id'] ?? 0);
        $info = $eventTicketModel->getCartOrderPaymentInfo($orderId, (string)$user['email']);
        if (!$info) {
            echo json_encode(['success' => false]);
            exit;
        }
        $paid = ($info['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan';
        echo json_encode(['success' => true, 'paid' => $paid, 'tickets' => $info['tickets'] ?? []]);
        exit;
    }

    if ($action === 'pay') {
        $ticketController->pay();
        return;
    }
    if ($action === 'sepay_checkout') {
        $ticketController->pay();
        return;
    }
    if ($action === 'sepay_return') {
        $ticketController->sepayReturn();
        return;
    }
}

// =========================================================================
// MẶC ĐỊNH: Trang chủ
// =========================================================================
require_once __DIR__ . '/../app/view/main.php';
