<?php

require_once __DIR__ . '/../model/EventTicket.php';

class TicketController
{
    private EventTicket $eventTicketModel;

    public function __construct($conn)
    {
        $this->eventTicketModel = new EventTicket($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function buyTicket(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để mua vé.'];
            header('Location: index.php?action=login');
            exit;
        }

        $role = (string)($user['role'] ?? 'khach_hang');
        if ($role !== 'khach_hang') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Chỉ tài khoản khách hàng mới có thể mua vé.'];
            header('Location: index.php');
            exit;
        }

        $ticketTypeId = (int)($_POST['ma_loai_ve'] ?? 0);
        $quantity = (int)($_POST['so_luong'] ?? 1);
        $result = $this->eventTicketModel->createPendingOrderForTicket($ticketTypeId, (string)$user['email'], $quantity);

        if (!($result['success'] ?? false)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message'] ?? 'Không thể mua vé.'];
            header('Location: index.php?action=ticket_detail&id=' . $ticketTypeId);
            exit;
        }

        if (!isset($result['order_id'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tạo được đơn thanh toán.'];
            header('Location: index.php?action=ticket_detail&id=' . $ticketTypeId);
            exit;
        }

        header('Location: index.php?action=pay&order_id=' . (int)$result['order_id']);
        exit;
    }

    public function pay(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để thanh toán.'];
            header('Location: index.php?action=login');
            exit;
        }

        $orderId = (int)($_GET['order_id'] ?? 0);
        $paymentInfo = $this->eventTicketModel->getOrderPaymentInfo($orderId, (string)$user['email']);
        if (!$paymentInfo) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Không tìm thấy đơn hàng.'];
            header('Location: index.php?action=profile');
            exit;
        }

        if (($paymentInfo['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan') {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đơn hàng đã được thanh toán trước đó.'];
            header('Location: index.php?action=profile');
            exit;
        }

        // Chờ admin xác nhận thanh toán
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đơn hàng đã tạo. Vui lòng chờ admin xác nhận thanh toán.'];
        header('Location: index.php?action=profile');
        exit;
    }

    public function sepayCheckout(): void
    {
        // Lưu để tương thích, chuyển sang pay nội bộ
        header('Location: index.php?action=pay&order_id=' . (int)($_GET['order_id'] ?? 0));
        exit;
    }

    public function sepayReturn(): void
    {
        $status = (string)($_GET['status'] ?? '');
        if ($status === 'success') {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Đang chờ SePay xác nhận thanh toán...'];
        } elseif ($status === 'cancel') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Bạn đã hủy thanh toán.'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Thanh toán lỗi, vui lòng thử lại.'];
        }
        header('Location: index.php?action=profile');
        exit;
    }

    public function sepayIpn(): void
    {
        $secretFromHeader = (string)($_SERVER['HTTP_X_SECRET_KEY'] ?? '');
        if ((string)SEPAY_SECRET_KEY === '' || !hash_equals((string)SEPAY_SECRET_KEY, $secretFromHeader)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '', true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payload']);
            return;
        }

        if (($payload['notification_type'] ?? '') !== 'ORDER_PAID') {
            echo json_encode(['success' => true, 'message' => 'Ignored']);
            return;
        }

        $invoice = (string)($payload['order']['order_invoice_number'] ?? '');
        $amount = (float)($payload['transaction']['transaction_amount'] ?? 0);
        $orderId = 0;
        if (str_starts_with($invoice, 'DH')) {
            $orderId = (int)substr($invoice, 2);
        }
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid invoice']);
            return;
        }

        $expected = $this->eventTicketModel->getOrderTotalAmount($orderId);
        if ($expected <= 0 || abs($expected - $amount) > 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Amount mismatch']);
            return;
        }

        $result = $this->eventTicketModel->confirmOrderPaymentByOrderId($orderId);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => (bool)($result['success'] ?? false), 'message' => $result['message'] ?? '']);
    }

    public function sepayIpnTest(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để test thanh toán.'];
            header('Location: index.php?action=login');
            exit;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $paymentInfo = $this->eventTicketModel->getOrderPaymentInfo($orderId, (string)$user['email']);
        if (!$paymentInfo) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy đơn hàng để test.'];
            header('Location: index.php?action=profile');
            exit;
        }

        $result = $this->eventTicketModel->confirmOrderPaymentByOrderId($orderId);
        $_SESSION['flash'] = ['type' => ($result['success'] ?? false) ? 'success' : 'danger', 'message' => $result['message'] ?? ''];
        header('Location: index.php?action=profile');
        exit;
    }

}
