<?php

require_once __DIR__ . '/../model/EventTicket.php';

/**
 * CartController — xử lý nghiệp vụ giỏ hàng.
 * Di chuyển logic từ routes/web.php vào đây theo đúng kiến trúc MVC.
 */
class CartController
{
    private EventTicket $eventTicketModel;

    public function __construct($conn)
    {
        $this->eventTicketModel = new EventTicket($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Thêm vé vào giỏ hàng (Session-based cart).
     * Kiểm tra: chỉ khách hàng mới được sử dụng giỏ hàng.
     */
    public function addToCart(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? 'khach_hang') !== 'khach_hang') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để sử dụng giỏ hàng.'];
            header('Location: index.php?action=login');
            exit;
        }

        $ma_loai_ve = (int)($_POST['ma_loai_ve'] ?? 0);
        $so_luong = (int)($_POST['so_luong'] ?? 1);

        if ($ma_loai_ve <= 0 || $so_luong <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Dữ liệu vé không hợp lệ.'];
            header('Location: index.php');
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$ma_loai_ve])) {
            $_SESSION['cart'][$ma_loai_ve]['so_luong'] += $so_luong;
        } else {
            $ticket = $this->eventTicketModel->getTicketDetail($ma_loai_ve);
            if ($ticket) {
                $_SESSION['cart'][$ma_loai_ve] = [
                    'ten_su_kien' => $ticket['ten_su_kien'],
                    'ten_loai_ve' => $ticket['ten_loai_ve'],
                    'gia_ve' => $ticket['gia_ve'],
                    'so_luong' => $so_luong,
                ];
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã thêm vé vào giỏ hàng.'];
        header('Location: index.php?action=cart');
        exit;
    }

    /**
     * Cập nhật số lượng vé trong giỏ hàng.
     * Nếu số lượng <= 0 → xóa vé khỏi giỏ.
     */
    public function updateCart(): void
    {
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

    /**
     * Xóa một loại vé khỏi giỏ hàng (GET request).
     */
    public function removeFromCart(): void
    {
        $ma_loai_ve = (int)($_GET['id'] ?? 0);
        if (isset($_SESSION['cart'][$ma_loai_ve])) {
            unset($_SESSION['cart'][$ma_loai_ve]);
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đã xóa khỏi giỏ hàng.'];
        }

        header('Location: index.php?action=cart');
        exit;
    }

    /**
     * Xử lý checkout giỏ hàng: tạo đơn hàng từ giỏ.
     * Kiểm tra: user đã đăng nhập, giỏ không trống.
     */
    public function checkoutCart(): void
    {
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

        $result = $this->eventTicketModel->createOrderFromCart($cartData, (string)$user['email']);
        if (!$result['success']) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message']];
            header('Location: index.php?action=cart');
            exit;
        }

        unset($_SESSION['cart']);
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Đặt vé thành công! Đơn hàng #' . $result['order_id']
                       . ' đang chờ admin xác nhận. Vé QR sẽ hiện sau khi thanh toán được duyệt.',
        ];
        header('Location: index.php?action=profile');
        exit;
    }
}
