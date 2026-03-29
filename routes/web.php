<?php
// Simple routing for a single-page event ticket system.
// All the UI lives in app/view/main.php.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/TicketController.php';
require_once __DIR__ . '/../app/model/EventTicket.php';

// Start session early to support flash messages and login state.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$authController = new AuthController($conn);
$adminController = new AdminController($conn);
$ticketController = new TicketController($conn);
$eventTicketModel = new EventTicket($conn);
$action = $_GET['action'] ?? $_POST['action'] ?? 'home';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $authController->register();
    }
    if ($action === 'login') {
        $authController->login();
    }
    if ($action === 'change_password') {
        $authController->changePassword();
    }
    if ($action === 'create_user') {
        $adminController->createUser();
    }
    if ($action === 'create_category') {
        $adminController->createCategory();
    }
    if ($action === 'update_category') {
        $adminController->updateCategory();
    }
    if ($action === 'delete_category') {
        $adminController->deleteCategory();
    }
    if ($action === 'create_event') {
        $adminController->createEvent();
    }
    if ($action === 'update_event') {
        $adminController->updateEvent();
    }
    if ($action === 'delete_event') {
        $adminController->deleteEvent();
    }
    if ($action === 'create_ticket_type') {
        $adminController->createTicketType();
    }
    if ($action === 'update_ticket_type') {
        $adminController->updateTicketType();
    }
    if ($action === 'delete_ticket_type') {
        $adminController->deleteTicketType();
    }
    if ($action === 'confirm_order_payment') {
        $adminController->confirmOrderPayment();
    }
    if ($action === 'delete_order') {
        $adminController->deleteOrder();
    }
    if ($action === 'delete_all_orders') {
        $adminController->deleteAllOrders();
    }
    if ($action === 'create_news') {
        $adminController->createNews();
    }
    if ($action === 'update_news') {
        $adminController->updateNews();
    }
    if ($action === 'delete_news') {
        $adminController->deleteNews();
    }
    if ($action === 'buy_ticket') {
        $ticketController->buyTicket();
    }
    if ($action === 'add_to_cart') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? 'khach_hang') !== 'khach_hang') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để sử dụng giỏ hàng.'];
            header('Location: index.php?action=login');
            exit;
        }
        
        $ma_loai_ve = (int)($_POST['ma_loai_ve'] ?? 0);
        $so_luong = (int)($_POST['so_luong'] ?? 1);
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$ma_loai_ve])) {
            $_SESSION['cart'][$ma_loai_ve]['so_luong'] += $so_luong;
        } else {
            // Get ticket details
            $eventTicketModel = new EventTicket($conn);
            $ticket = $eventTicketModel->getTicketDetail($ma_loai_ve);
            if ($ticket) {
                $_SESSION['cart'][$ma_loai_ve] = [
                    'ten_su_kien' => $ticket['ten_su_kien'],
                    'ten_loai_ve' => $ticket['ten_loai_ve'],
                    'gia_ve' => $ticket['gia_ve'],
                    'so_luong' => $so_luong
                ];
            }
        }
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã thêm vé vào giỏ hàng.'];
        header('Location: index.php?action=cart');
        exit;
    }
    if ($action === 'update_cart') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $ma_loai_ve = (int)($_POST['id'] ?? 0);
        $so_luong = (int)($_POST['so_luong'] ?? 1);
        
        if (isset($_SESSION['cart'][$ma_loai_ve])) {
            if ($so_luong <= 0) {
                unset($_SESSION['cart'][$ma_loai_ve]);
            } else {
                $_SESSION['cart'][$ma_loai_ve]['so_luong'] = $so_luong;
            }
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đã cập nhật giỏ hàng.'];
        }
        header('Location: index.php?action=cart');
        exit;
    }
    if ($action === 'checkout_cart') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? 'khach_hang') !== 'khach_hang') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để thanh toán.'];
            header('Location: index.php?action=login');
            exit;
        }

        $cartData = $_SESSION['cart'] ?? [];
        if (empty($cartData)) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Giỏ hàng trống.'];
            header('Location: index.php?action=cart');
            exit;
        }

        $result = $eventTicketModel->createOrderFromCart($cartData, (string)$user['email']);
        if (!$result['success']) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message']];
            header('Location: index.php?action=cart');
            exit;
        }

        unset($_SESSION['cart']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đặt vé thành công! Đơn hàng #' . $result['order_id'] . ' đang chờ admin xác nhận. Vé QR sẽ hiện sau khi thanh toán được duyệt.'];
        header('Location: index.php?action=profile');
        exit;
    }
    if ($action === 'sepay_ipn') {
        $ticketController->sepayIpn();
    }
    if ($action === 'sepay_ipn_test') {
        $ticketController->sepayIpnTest();
    }
}

// Handle GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'logout') {
        $authController->logout();
    }

    if ($action === 'export_statistics') {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            header('Location: index.php'); exit;
        }
        $type  = $_GET['type']  ?? 'month';
        $value = $_GET['value'] ?? date('Y-m');
        $stats = $eventTicketModel->getStatistics($type, $value);

        $filename = 'thongke_' . $type . '_' . $value . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        $out = fopen('php://output', 'w');
        // BOM UTF-8 cho Excel
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
        fputcsv($out, ['TỔNG CỘNG', '', '', '', $stats['summary']['tong_ve'] ?? 0, number_format((float)($stats['summary']['tong_doanh_thu'] ?? 0), 0, '.', ''), '']);
        fclose($out);
        exit;
    }

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
    if ($action === 'cart') {
        require_once __DIR__ . '/../app/view/cart.php';
        return;
    }
    if ($action === 'remove_from_cart') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $ma_loai_ve = (int)($_GET['id'] ?? 0);
        if (isset($_SESSION['cart'][$ma_loai_ve])) {
            unset($_SESSION['cart'][$ma_loai_ve]);
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đã xóa khỏi giỏ hàng.'];
        }
        header('Location: index.php?action=cart');
        exit;
    }
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
        if (!$user) { echo json_encode(['success' => false]); exit; }
        $orderId = (int)($_GET['order_id'] ?? 0);
        $info = $eventTicketModel->getCartOrderPaymentInfo($orderId, (string)$user['email']);
        if (!$info) { echo json_encode(['success' => false]); exit; }
        $paid = ($info['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan';
        echo json_encode(['success' => true, 'paid' => $paid, 'tickets' => $info['tickets'] ?? []]);
        exit;
    }

    if ($action === 'pay') {
        $ticketController->pay();
        return;
    }
    if ($action === 'sepay_checkout') {
        // Giữ compat, chuyển vào pay nội bộ
        $ticketController->pay();
        return;
    }
    if ($action === 'sepay_return') {
        $ticketController->sepayReturn();
        return;
    }
}

// Default: render the main view (home)
require_once __DIR__ . '/../app/view/main.php';
