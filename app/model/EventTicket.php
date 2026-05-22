<?php

require_once __DIR__ . '/Category.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/TicketType.php';
require_once __DIR__ . '/Order.php';
require_once __DIR__ . '/Ticket.php';

/**
 * Facade (mặt tiền) ủy quyền tới các model riêng biệt.
 *
 * File này giữ lại tất cả method signatures cũ để đảm bảo tương thích ngược
 * (backward compatibility) với Controller và Router hiện tại.
 * Mỗi method sẽ delegate (ủy quyền) tới model tương ứng:
 *
 *   - Category  → quản lý danh mục (bảng danh_muc)
 *   - Event     → quản lý sự kiện (bảng su_kien)
 *   - TicketType→ quản lý loại vé (bảng loai_ve)
 *   - Order     → đơn hàng + thanh toán + thống kê
 *   - Ticket    → vé điện tử (bảng ve)
 */
class EventTicket
{
    private mysqli $conn;

    // Các model con (sub-models)
    private Category   $categoryModel;
    private Event      $eventModel;
    private TicketType $ticketTypeModel;
    private Order      $orderModel;
    private Ticket     $ticketModel;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;

        // Khởi tạo các model con
        $this->categoryModel   = new Category($conn);
        $this->eventModel      = new Event($conn);
        $this->ticketTypeModel = new TicketType($conn);
        $this->orderModel      = new Order($conn);
        $this->ticketModel     = new Ticket($conn);
    }


    public function getCategories(): array
    {
        return $this->categoryModel->getAll();
    }

    public function createCategory(string $name, ?string $description): array
    {
        return $this->categoryModel->create($name, $description);
    }

    public function updateCategory(int $id, string $name, ?string $description): array
    {
        return $this->categoryModel->update($id, $name, $description);
    }

    public function deleteCategory(int $id): array
    {
        return $this->categoryModel->delete($id);
    }


    public function getEvents(): array
    {
        return $this->eventModel->getAll();
    }

    public function createEvent(array $payload): array
    {
        return $this->eventModel->create($payload);
    }

    public function updateEvent(array $payload): array
    {
        return $this->eventModel->update($payload);
    }

    public function updateEventStatus(int $eventId, string $status): array
    {
        return $this->eventModel->updateStatus($eventId, $status);
    }

    public function deleteEvent(int $id): array
    {
        return $this->eventModel->delete($id);
    }


    public function createTicketType(array $payload): array
    {
        return $this->ticketTypeModel->create($payload);
    }

    public function updateTicketType(array $payload): array
    {
        return $this->ticketTypeModel->update($payload);
    }

    public function deleteTicketType(int $id): array
    {
        return $this->ticketTypeModel->delete($id);
    }

    public function getTicketTypesForAdmin(): array
    {
        return $this->ticketTypeModel->getAllForAdmin();
    }

    public function getTicketsForHome(int $limit = 12): array
    {
        return $this->ticketTypeModel->getForHome($limit);
    }

    public function getTicketDetail(int $ticketTypeId): ?array
    {
        return $this->ticketTypeModel->getDetail($ticketTypeId);
    }


    public function createOrderForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        return $this->orderModel->createForTicket($ticketTypeId, $customerEmail, $quantity);
    }

    public function createPendingOrderForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        return $this->orderModel->createPendingForTicket($ticketTypeId, $customerEmail, $quantity);
    }

    public function createOrderFromCart(array $cart, string $customerEmail): array
    {
        return $this->orderModel->createFromCart($cart, $customerEmail);
    }

    public function confirmOrderPayment(int $orderId, string $customerEmail): array
    {
        return $this->orderModel->confirmPayment($orderId, $customerEmail);
    }

    public function confirmOrderPaymentByOrderId(int $orderId): array
    {
        return $this->orderModel->confirmPaymentByOrderId($orderId);
    }

    public function getOrderPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        return $this->orderModel->getPaymentInfo($orderId, $customerEmail);
    }

    public function getCartOrderPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        return $this->orderModel->getCartPaymentInfo($orderId, $customerEmail);
    }

    public function getOrderTotalAmount(int $orderId): float
    {
        return $this->orderModel->getTotalAmount($orderId);
    }

    public function getPendingOrders(): array
    {
        return $this->orderModel->getPending();
    }

    public function getOrdersByCustomerEmail(string $email): array
    {
        return $this->orderModel->getByCustomerEmail($email);
    }

    public function deleteOrderById(int $orderId): array
    {
        return $this->orderModel->deleteById($orderId);
    }

    public function deleteAllPendingOrders(): array
    {
        return $this->orderModel->deleteAllPending();
    }

    public function getStatistics(string $type, string $value): array
    {
        return $this->orderModel->getStatistics($type, $value);
    }


    public function getGeneratedTicketsByOrder(int $orderId): array
    {
        return $this->ticketModel->getByOrderId($orderId);
    }

    public function checkInTicket(string $qrCode, int $staffId): array
    {
        return $this->ticketModel->checkInTicket($qrCode, $staffId);
    }


    /**
     * Tìm mã người dùng theo email.
     * Không còn fallback sang user đầu tiên (đã sửa lỗi bảo mật).
     *
     * @param string $email Email cần tra cứu
     * @return int|null Mã người dùng hoặc null
     */
    public function resolveCreatorIdByEmail(string $email): ?int
    {
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return ($row && isset($row['ma_nguoi_dung'])) ? (int)$row['ma_nguoi_dung'] : null;
    }


    public function getBanners(): array
    {
        $sql = "SELECT * FROM banner WHERE hieu_luc = 1 ORDER BY ma_banner ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Sự kiện đặc biệt
    public function getSpecialEvents(int $limit = 8): array
    {
        $sql = "SELECT sk.*, dm.ten_danh_muc 
                FROM su_kien sk
                LEFT JOIN danh_muc dm ON sk.ma_danh_muc = dm.ma_danh_muc
                WHERE sk.trang_thai = 'da_duyet' AND sk.noi_bat = 1
                ORDER BY sk.ma_su_kien DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Sự kiện xu hướng: sự kiện mới nhất đã duyệt (top bán chạy)
    public function getTrendingEvents(): array
    {
        $sql = "SELECT sk.*, dm.ten_danh_muc,
                       COALESCE(SUM(lv.so_luong - lv.so_luong_con), 0) AS tong_da_ban
                FROM su_kien sk
                LEFT JOIN danh_muc dm ON sk.ma_danh_muc = dm.ma_danh_muc
                LEFT JOIN loai_ve lv ON lv.ma_su_kien = sk.ma_su_kien
                WHERE sk.trang_thai = 'da_duyet'
                GROUP BY sk.ma_su_kien
                ORDER BY tong_da_ban DESC, sk.ma_su_kien DESC LIMIT 10";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecommendedEvents(): array
    {
        $sql = "SELECT sk.*, dm.ten_danh_muc 
                FROM su_kien sk
                LEFT JOIN danh_muc dm ON sk.ma_danh_muc = dm.ma_danh_muc
                WHERE sk.trang_thai = 'da_duyet'
                ORDER BY RAND() LIMIT 4";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

