<?php

require_once __DIR__ . '/Ticket.php';

/**
 * Model quản lý đơn hàng (bảng don_hang, chi_tiet_don_hang, thanh_toan).
 * Xử lý nghiệp vụ đặt vé, thanh toán, xác nhận đơn hàng và thống kê.
 *
 * Sử dụng Transaction + SELECT ... FOR UPDATE (Pessimistic Locking)
 * để chống bán vượt số lượng vé (Overbooking).
 */
class Order
{
    private mysqli $conn;
    private Ticket $ticketModel;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->ticketModel = new Ticket($conn);
    }


    /**
     * Tạo đơn hàng mua vé trực tiếp (1 loại vé, thanh toán tại quầy).
     *
     * @param int    $ticketTypeId  Mã loại vé
     * @param string $customerEmail Email khách hàng
     * @param int    $quantity      Số lượng vé
     * @return array ['success' => bool, 'message' => string, 'order_id' => int|null]
     */
    public function createForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        if ($ticketTypeId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Dữ liệu đặt vé không hợp lệ.'];
        }

        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            // Khóa dòng vé để chống race condition (Pessimistic Locking)
            $ticketRow = $this->lockTicketType($ticketTypeId);
            if (!$ticketRow) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Loại vé không tồn tại.'];
            }

            $remaining = (int)$ticketRow['so_luong_con'];
            $price = (float)$ticketRow['gia_ve'];

            if ($quantity > $remaining) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Số lượng vé còn lại không đủ.'];
            }

            $total = $price * $quantity;

            $orderId = $this->insertOrder($customerId, $total, 'tai_quay');

            $this->insertOrderDetail($orderId, $ticketTypeId, $quantity, $price, $total);

            $this->decreaseStock($ticketTypeId, $quantity);

            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Đặt vé thành công! Mã đơn hàng của bạn là #' . $orderId,
                'order_id' => $orderId,
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể đặt vé: ' . $e->getMessage()];
        }
    }

    /**
     * Tạo đơn hàng chờ thanh toán (1 loại vé, thanh toán online).
     *
     * @param int    $ticketTypeId  Mã loại vé
     * @param string $customerEmail Email khách hàng
     * @param int    $quantity      Số lượng vé
     * @return array ['success' => bool, 'message' => string, 'order_id' => int|null]
     */
    public function createPendingForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        if ($ticketTypeId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Dữ liệu đặt vé không hợp lệ.'];
        }

        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            $ticketRow = $this->lockTicketType($ticketTypeId);
            if (!$ticketRow) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Loại vé không tồn tại.'];
            }

            $remaining = (int)$ticketRow['so_luong_con'];
            $price = (float)$ticketRow['gia_ve'];
            if ($quantity > $remaining) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Số lượng vé còn lại không đủ.'];
            }

            $total = $price * $quantity;

            $orderId = $this->insertOrder($customerId, $total, 'momo');
            $this->insertOrderDetail($orderId, $ticketTypeId, $quantity, $price, $total);
            $this->decreaseStock($ticketTypeId, $quantity);
            $this->insertPayment($orderId, $total, 'momo');

            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Đã tạo đơn hàng, vui lòng quét QR MoMo để thanh toán.',
                'order_id' => $orderId,
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $e->getMessage()];
        }
    }

    /**
     * Tạo đơn hàng từ giỏ hàng (nhiều loại vé cùng lúc).
     * Sử dụng Transaction với Pessimistic Locking cho tất cả loại vé.
     *
     * @param array  $cart          Giỏ hàng [ma_loai_ve => ['ten_loai_ve', 'gia_ve', 'so_luong'], ...]
     * @param string $customerEmail Email khách hàng
     * @return array ['success' => bool, 'order_id' => int|null, 'tong_tien' => float|null]
     */
    public function createFromCart(array $cart, string $customerEmail): array
    {
        if (empty($cart)) {
            return ['success' => false, 'message' => 'Giỏ hàng trống.'];
        }

        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            // Khóa tất cả loại vé cùng lúc (Pessimistic Locking)
            $ticketIds = array_keys($cart);
            $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
            $lockStmt = $this->conn->prepare(
                "SELECT ma_loai_ve, gia_ve, so_luong_con FROM loai_ve
                 WHERE ma_loai_ve IN ($placeholders) FOR UPDATE"
            );
            $types = str_repeat('i', count($ticketIds));
            $lockStmt->bind_param($types, ...$ticketIds);
            $lockStmt->execute();
            $lockRes = $lockStmt->get_result();

            $ticketRows = [];
            while ($row = $lockRes->fetch_assoc()) {
                $ticketRows[(int)$row['ma_loai_ve']] = $row;
            }

            $grandTotal = 0.0;
            foreach ($cart as $maLoaiVe => $item) {
                $maLoaiVe = (int)$maLoaiVe;
                $qty = (int)$item['so_luong'];

                if (!isset($ticketRows[$maLoaiVe])) {
                    $this->conn->rollback();
                    return ['success' => false, 'message' => 'Loại vé #' . $maLoaiVe . ' không tồn tại.'];
                }
                if ($qty > (int)$ticketRows[$maLoaiVe]['so_luong_con']) {
                    $this->conn->rollback();
                    return ['success' => false, 'message' => 'Vé "' . htmlspecialchars((string)$item['ten_loai_ve']) . '" không đủ số lượng.'];
                }
                $grandTotal += (float)$ticketRows[$maLoaiVe]['gia_ve'] * $qty;
            }

            $orderId = $this->insertOrder($customerId, $grandTotal, 'momo');

            $detailStmt = $this->conn->prepare(
                "INSERT INTO chi_tiet_don_hang (ma_don_hang, ma_loai_ve, so_luong, don_gia, thanh_tien)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $updateStmt = $this->conn->prepare(
                "UPDATE loai_ve SET so_luong_con = so_luong_con - ? WHERE ma_loai_ve = ?"
            );

            foreach ($cart as $maLoaiVe => $item) {
                $maLoaiVe = (int)$maLoaiVe;
                $qty = (int)$item['so_luong'];
                $price = (float)$ticketRows[$maLoaiVe]['gia_ve'];
                $subtotal = $price * $qty;

                $detailStmt->bind_param("iiidd", $orderId, $maLoaiVe, $qty, $price, $subtotal);
                if (!$detailStmt->execute()) {
                    throw new RuntimeException($detailStmt->error);
                }

                $updateStmt->bind_param("ii", $qty, $maLoaiVe);
                if (!$updateStmt->execute()) {
                    throw new RuntimeException($updateStmt->error);
                }
            }

            $this->insertPayment($orderId, $grandTotal, 'momo');

            $this->conn->commit();
            return ['success' => true, 'order_id' => $orderId, 'tong_tien' => $grandTotal];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $e->getMessage()];
        }
    }


    /**
     * Xác nhận thanh toán đơn hàng theo mã đơn + email khách.
     * Khi thanh toán thành công → phát hành vé QR Code.
     *
     * @param int    $orderId       Mã đơn hàng
     * @param string $customerEmail Email khách hàng
     * @return array ['success' => bool, 'message' => string]
     */
    public function confirmPayment(int $orderId, string $customerEmail): array
    {
        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        return $this->doConfirmPayment($orderId, $customerId);
    }

    /**
     * Xác nhận thanh toán theo mã đơn hàng (admin duyệt, không cần email khách).
     *
     * @param int $orderId Mã đơn hàng
     * @return array ['success' => bool, 'message' => string]
     */
    public function confirmPaymentByOrderId(int $orderId): array
    {
        return $this->doConfirmPayment($orderId, null);
    }


    /**
     * Lấy thông tin thanh toán của đơn hàng (dùng cho trang checkout).
     *
     * @param int    $orderId       Mã đơn hàng
     * @param string $customerEmail Email khách hàng
     * @return array|null Thông tin đơn hàng hoặc null
     */
    public function getPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        if ($orderId <= 0) {
            return null;
        }

        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan, dh.trang_thai_don_hang,
                    lv.ten_loai_ve, sk.ten_su_kien, ctdh.so_luong
             FROM don_hang dh
             JOIN chi_tiet_don_hang ctdh ON ctdh.ma_don_hang = dh.ma_don_hang
             JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
             JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
             WHERE dh.ma_don_hang = ? AND dh.ma_khach_hang = ?
             LIMIT 1"
        );
        $stmt->bind_param("ii", $orderId, $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result ? $result->fetch_assoc() : null;
        if (!$order) {
            return null;
        }

        // Xây dựng thông tin QR thanh toán MoMo
        $order = $this->buildMomoPaymentData($order, $orderId);
        $order['tickets'] = $this->ticketModel->getByOrderId($orderId);

        return $order;
    }

    /**
     * Lấy thông tin thanh toán đơn hàng từ giỏ hàng (nhiều loại vé).
     *
     * @param int    $orderId       Mã đơn hàng
     * @param string $customerEmail Email khách hàng
     * @return array|null Thông tin đơn hàng kèm danh sách items
     */
    public function getCartPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        if ($orderId <= 0) {
            return null;
        }

        $customerId = $this->resolveCustomerId($customerEmail);
        if (!$customerId) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan
             FROM don_hang dh
             WHERE dh.ma_don_hang = ? AND dh.ma_khach_hang = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $orderId, $customerId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            return null;
        }

        // Lấy chi tiết các loại vé trong đơn hàng
        $detailStmt = $this->conn->prepare(
            "SELECT lv.ten_loai_ve, sk.ten_su_kien, ctdh.so_luong, ctdh.don_gia, ctdh.thanh_tien
             FROM chi_tiet_don_hang ctdh
             JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
             JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
             WHERE ctdh.ma_don_hang = ?"
        );
        $detailStmt->bind_param("i", $orderId);
        $detailStmt->execute();
        $order['items'] = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Gọi helper chung để build MoMo QR
        $order = $this->buildMomoPaymentData($order, $orderId);
        $order['transfer_content'] = 'DH' . $orderId;
        $order['transfer_amount'] = (int)round((float)$order['tong_tien']);
        $order['tickets'] = $this->ticketModel->getByOrderId($orderId);

        return $order;
    }

    /**
     * Lấy tổng tiền của đơn hàng.
     *
     * @param int $orderId Mã đơn hàng
     * @return float Tổng tiền (0.0 nếu không tìm thấy)
     */
    public function getTotalAmount(int $orderId): float
    {
        if ($orderId <= 0) {
            return 0.0;
        }
        $stmt = $this->conn->prepare("SELECT tong_tien FROM don_hang WHERE ma_don_hang = ? LIMIT 1");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? (float)$row['tong_tien'] : 0.0;
    }

    /**
     * Lấy danh sách đơn hàng chờ thanh toán.
     *
     * @return array Danh sách đơn hàng kèm thông tin khách hàng
     */
    public function getPending(): array
    {
        $sql = "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan,
                       nd.ho_ten, nd.email, dh.ngay_tao
                FROM don_hang dh
                JOIN nguoi_dung nd ON nd.ma_nguoi_dung = dh.ma_khach_hang
                WHERE dh.trang_thai_thanh_toan = 'cho_thanh_toan'
                ORDER BY dh.ngay_tao DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lấy lịch sử đơn hàng theo email khách hàng.
     *
     * @param string $email Email khách hàng
     * @return array Danh sách đơn hàng
     */
    public function getByCustomerEmail(string $email): array
    {
        $stmt = $this->conn->prepare(
            "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan, dh.ngay_tao
             FROM don_hang dh
             WHERE dh.ma_khach_hang = (SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ?)
             ORDER BY dh.ngay_tao DESC"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Xóa đơn hàng và khôi phục số lượng vé.
     * Xóa theo thứ tự: xác nhận vé → vé → chi tiết → thanh toán → đơn hàng.
     *
     * @param int $orderId Mã đơn hàng
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteById(int $orderId): array
    {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Đơn hàng không hợp lệ.'];
        }

        try {
            $this->conn->begin_transaction();

            // Bước 1: Khôi phục số lượng vé TRƯỚC KHI xóa chi tiết
            $restoreStmt = $this->conn->prepare(
                "UPDATE loai_ve lv
                 JOIN chi_tiet_don_hang ctdh ON ctdh.ma_loai_ve = lv.ma_loai_ve
                 SET lv.so_luong_con = lv.so_luong_con + ctdh.so_luong
                 WHERE ctdh.ma_don_hang = ?"
            );
            $restoreStmt->bind_param("i", $orderId);
            $restoreStmt->execute();

            // Bước 2: Xóa xác nhận check-in (bảng xac_nhan_ve có FK tới ve)
            $xnStmt = $this->conn->prepare(
                "DELETE FROM xac_nhan_ve WHERE ma_ve IN (
                    SELECT ma_ve FROM ve WHERE ma_chi_tiet IN (
                        SELECT ma_chi_tiet FROM chi_tiet_don_hang WHERE ma_don_hang = ?
                    )
                )"
            );
            $xnStmt->bind_param("i", $orderId);
            $xnStmt->execute();

            // Bước 3: Xóa vé điện tử liên quan
            $veStmt = $this->conn->prepare(
                "DELETE FROM ve WHERE ma_chi_tiet IN (
                    SELECT ma_chi_tiet FROM chi_tiet_don_hang WHERE ma_don_hang = ?
                )"
            );
            $veStmt->bind_param("i", $orderId);
            $veStmt->execute();

            // Bước 4: Xóa chi tiết đơn hàng
            $ctStmt = $this->conn->prepare("DELETE FROM chi_tiet_don_hang WHERE ma_don_hang = ?");
            $ctStmt->bind_param("i", $orderId);
            $ctStmt->execute();

            // Bước 5: Xóa bản ghi thanh toán
            $ttStmt = $this->conn->prepare("DELETE FROM thanh_toan WHERE ma_don_hang = ?");
            $ttStmt->bind_param("i", $orderId);
            $ttStmt->execute();

            // Bước 6: Xóa đơn hàng
            $dhStmt = $this->conn->prepare("DELETE FROM don_hang WHERE ma_don_hang = ?");
            $dhStmt->bind_param("i", $orderId);
            if (!$dhStmt->execute()) {
                throw new RuntimeException($dhStmt->error);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa đơn hàng thành công.'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể xóa đơn hàng: ' . $e->getMessage()];
        }
    }

    /**
     * Xóa tất cả đơn hàng chờ thanh toán.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteAllPending(): array
    {
        try {
            $getStmt = $this->conn->prepare(
                "SELECT ma_don_hang FROM don_hang WHERE trang_thai_thanh_toan = 'cho_thanh_toan'"
            );
            $getStmt->execute();
            $result = $getStmt->get_result();
            $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

            // Xóa từng đơn hàng (mỗi đơn có transaction riêng trong deleteById)
            $deleted = 0;
            foreach ($orders as $order) {
                $res = $this->deleteById((int)$order['ma_don_hang']);
                if ($res['success']) {
                    $deleted++;
                }
            }

            return ['success' => true, 'message' => 'Xóa tất cả đơn chờ xác nhận thành công (' . $deleted . ' đơn).'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Không thể xóa: ' . $e->getMessage()];
        }
    }


    /**
     * Thống kê doanh thu từ các đơn đã thanh toán thành công.
     *
     * @param string $type  Loại thống kê: 'day' | 'month' | 'year'
     * @param string $value Giá trị lọc: '2026-03-22' | '2026-03' | '2026'
     * @return array ['summary' => [...], 'orders' => [...], 'by_event' => [...]]
     */
    public function getStatistics(string $type, string $value): array
    {
        // Xây dựng điệu kiện WHERE theo loại thống kê
        $where = match ($type) {
            'day'   => "DATE(dh.ngay_tao) = ?",
            'month' => "DATE_FORMAT(dh.ngay_tao, '%Y-%m') = ?",
            'year'  => "YEAR(dh.ngay_tao) = ?",
            default => "DATE(dh.ngay_tao) = ?",
        };

        // Tổng quan: tổng đơn, doanh thu, số vé
        $summaryStmt = $this->conn->prepare(
            "SELECT COUNT(*) as tong_don, SUM(dh.tong_tien) as tong_doanh_thu,
                    SUM(ctdh.so_luong) as tong_ve
             FROM don_hang dh
             JOIN chi_tiet_don_hang ctdh ON ctdh.ma_don_hang = dh.ma_don_hang
             WHERE dh.trang_thai_thanh_toan = 'da_thanh_toan' AND $where"
        );
        $summaryStmt->bind_param("s", $value);
        $summaryStmt->execute();
        $summary = $summaryStmt->get_result()->fetch_assoc();

        // Chi tiết từng đơn hàng
        $ordersStmt = $this->conn->prepare(
            "SELECT dh.ma_don_hang, dh.tong_tien, dh.ngay_tao,
                    nd.ho_ten, nd.email,
                    GROUP_CONCAT(CONCAT(lv.ten_loai_ve, ' x', ctdh.so_luong) SEPARATOR ', ') as chi_tiet_ve,
                    SUM(ctdh.so_luong) as tong_ve
             FROM don_hang dh
             JOIN nguoi_dung nd ON nd.ma_nguoi_dung = dh.ma_khach_hang
             JOIN chi_tiet_don_hang ctdh ON ctdh.ma_don_hang = dh.ma_don_hang
             JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
             WHERE dh.trang_thai_thanh_toan = 'da_thanh_toan' AND $where
             GROUP BY dh.ma_don_hang
             ORDER BY dh.ngay_tao DESC"
        );
        $ordersStmt->bind_param("s", $value);
        $ordersStmt->execute();
        $orders = $ordersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Doanh thu theo sự kiện
        $byEventStmt = $this->conn->prepare(
            "SELECT sk.ten_su_kien, SUM(ctdh.thanh_tien) as doanh_thu, SUM(ctdh.so_luong) as so_ve
             FROM don_hang dh
             JOIN chi_tiet_don_hang ctdh ON ctdh.ma_don_hang = dh.ma_don_hang
             JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
             JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
             WHERE dh.trang_thai_thanh_toan = 'da_thanh_toan' AND $where
             GROUP BY sk.ma_su_kien
             ORDER BY doanh_thu DESC"
        );
        $byEventStmt->bind_param("s", $value);
        $byEventStmt->execute();
        $byEvent = $byEventStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'summary'  => $summary,
            'orders'   => $orders,
            'by_event' => $byEvent,
        ];
    }


    /**
     * Xác nhận thanh toán nội bộ (logic chung cho 2 phương thức public).
     * Khi xác nhận thành công → phát hành vé QR Code.
     *
     * @param int      $orderId    Mã đơn hàng
     * @param int|null $customerId Mã khách hàng (null = admin duyệt, không kiểm tra)
     * @return array ['success' => bool, 'message' => string]
     */
    private function doConfirmPayment(int $orderId, ?int $customerId): array
    {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Đơn hàng không hợp lệ.'];
        }

        try {
            $this->conn->begin_transaction();

            // Khóa đơn hàng để xử lý tuần tự (Pessimistic Locking)
            $whereClause = $customerId
                ? "ma_don_hang = ? AND ma_khach_hang = ?"
                : "ma_don_hang = ?";

            $orderStmt = $this->conn->prepare(
                "SELECT ma_don_hang, trang_thai_thanh_toan FROM don_hang WHERE $whereClause FOR UPDATE"
            );
            if ($customerId) {
                $orderStmt->bind_param("ii", $orderId, $customerId);
            } else {
                $orderStmt->bind_param("i", $orderId);
            }
            $orderStmt->execute();
            $order = $orderStmt->get_result()->fetch_assoc();

            if (!$order) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Không tìm thấy đơn hàng.'];
            }

            // Nếu đã thanh toán → bỏ qua
            if ((string)$order['trang_thai_thanh_toan'] === 'da_thanh_toan') {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Đơn hàng đã được thanh toán trước đó.'];
            }

            // Lấy danh sách chi tiết đơn hàng
            $detailsStmt = $this->conn->prepare(
                "SELECT ma_chi_tiet, so_luong FROM chi_tiet_don_hang WHERE ma_don_hang = ?"
            );
            $detailsStmt->bind_param("i", $orderId);
            $detailsStmt->execute();
            $details = $detailsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Phát hành vé QR Code (delegate cho Ticket model)
            $this->ticketModel->generateForOrder($orderId, $details);

            // Cập nhật trạng thái đơn hàng → đã thanh toán + đã xác nhận
            $updateOrderStmt = $this->conn->prepare(
                "UPDATE don_hang
                 SET trang_thai_thanh_toan = 'da_thanh_toan', trang_thai_don_hang = 'da_xac_nhan'
                 WHERE ma_don_hang = ?"
            );
            $updateOrderStmt->bind_param("i", $orderId);
            if (!$updateOrderStmt->execute()) {
                throw new RuntimeException($updateOrderStmt->error);
            }

            // Cập nhật trạng thái thanh toán
            $updatePaymentStmt = $this->conn->prepare(
                "UPDATE thanh_toan
                 SET trang_thai = 'thanh_cong', thoi_gian = CURRENT_TIMESTAMP
                 WHERE ma_don_hang = ?"
            );
            $updatePaymentStmt->bind_param("i", $orderId);
            if (!$updatePaymentStmt->execute()) {
                throw new RuntimeException($updatePaymentStmt->error);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Thanh toán thành công! Vé đã được phát hành với mã QR riêng.'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Xác nhận thanh toán thất bại: ' . $e->getMessage()];
        }
    }

    /**
     * Khóa một loại vé để đọc (SELECT ... FOR UPDATE).
     * Dùng trong Transaction để chống race condition.
     *
     * @param int $ticketTypeId Mã loại vé
     * @return array|null Thông tin loại vé hoặc null
     */
    private function lockTicketType(int $ticketTypeId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT ma_loai_ve, gia_ve, so_luong_con FROM loai_ve WHERE ma_loai_ve = ? FOR UPDATE"
        );
        $stmt->bind_param("i", $ticketTypeId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Insert đơn hàng mới vào bảng don_hang.
     *
     * @param int    $customerId    Mã khách hàng
     * @param float  $total         Tổng tiền
     * @param string $paymentMethod Phương thức thanh toán
     * @return int Mã đơn hàng vừa tạo
     * @throws RuntimeException Nếu insert thất bại
     */
    private function insertOrder(int $customerId, float $total, string $paymentMethod): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO don_hang (ma_khach_hang, tong_tien, phuong_thuc_thanh_toan,
                                   trang_thai_thanh_toan, trang_thai_don_hang)
             VALUES (?, ?, ?, 'cho_thanh_toan', 'cho_xac_nhan')"
        );
        $stmt->bind_param("ids", $customerId, $total, $paymentMethod);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
        return (int)$this->conn->insert_id;
    }

    /**
     * Insert chi tiết đơn hàng vào bảng chi_tiet_don_hang.
     *
     * @throws RuntimeException Nếu insert thất bại
     */
    private function insertOrderDetail(int $orderId, int $ticketTypeId, int $qty, float $price, float $total): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO chi_tiet_don_hang (ma_don_hang, ma_loai_ve, so_luong, don_gia, thanh_tien)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiidd", $orderId, $ticketTypeId, $qty, $price, $total);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
    }

    /**
     * Trừ số lượng vé trong kho (bảng loai_ve).
     *
     * @throws RuntimeException Nếu update thất bại
     */
    private function decreaseStock(int $ticketTypeId, int $quantity): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE loai_ve SET so_luong_con = so_luong_con - ? WHERE ma_loai_ve = ?"
        );
        $stmt->bind_param("ii", $quantity, $ticketTypeId);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
    }

    /**
     * Tạo bản ghi thanh toán trong bảng thanh_toan.
     *
     * @throws RuntimeException Nếu insert thất bại
     */
    private function insertPayment(int $orderId, float $total, string $method): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO thanh_toan (ma_don_hang, phuong_thuc, so_tien, trang_thai)
             VALUES (?, ?, ?, 'cho_xu_ly')"
        );
        $stmt->bind_param("isd", $orderId, $method, $total);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
    }

    /**
     * Xây dựng dữ liệu QR thanh toán MoMo cho đơn hàng.
     *
     * @param array $order   Thông tin đơn hàng
     * @param int   $orderId Mã đơn hàng
     * @return array Đơn hàng kèm thông tin MoMo
     */
    private function buildMomoPaymentData(array $order, int $orderId): array
    {
        $order['momo_qr_text'] = 'MOMO|ORDER:' . $orderId . '|AMOUNT:' . (float)$order['tong_tien'];
        $amountText = (string)((int)round((float)$order['tong_tien']));
        $noteText = 'Thanh toan don #' . $orderId;

        $baseMomoUrl = defined('MOMO_PAYMENT_BASE_URL') ? trim((string)MOMO_PAYMENT_BASE_URL) : '';
        if ($baseMomoUrl !== '') {
            $glue = str_contains($baseMomoUrl, '?') ? '&' : '?';
            $order['momo_pay_url'] = $baseMomoUrl . $glue . http_build_query([
                'amount' => $amountText,
                'comment' => $noteText,
            ]);
            $order['momo_mode'] = 'live_link';
            $order['momo_qr_text'] = $order['momo_pay_url'];
        } else {
            $staticQrImage = defined('MOMO_STATIC_QR_IMAGE_URL') ? trim((string)MOMO_STATIC_QR_IMAGE_URL) : '';
            if ($staticQrImage !== '') {
                $order['momo_pay_url'] = '';
                $order['momo_mode'] = 'static_qr';
                $order['momo_qr_text'] = 'MOMO-STATIC-QR';
                $order['momo_qr_image'] = $staticQrImage;
                $order['momo_account_name'] = defined('MOMO_ACCOUNT_NAME') ? (string)MOMO_ACCOUNT_NAME : '';
                $order['momo_account_mask'] = defined('MOMO_ACCOUNT_MASK') ? (string)MOMO_ACCOUNT_MASK : '';
                return $order;
            }

            $order['momo_pay_url'] = '';
            $order['momo_mode'] = 'demo';
            $order['momo_qr_text'] = 'MOMO-DEMO|' . $noteText . '|AMOUNT:' . $amountText;
        }

        $order['momo_qr_image'] = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='
                                . rawurlencode((string)$order['momo_qr_text']);
        return $order;
    }

    /**
     * Tìm mã người dùng (ma_nguoi_dung) theo email.
     *
     * @param string $email Email cần tra cứu
     * @return int|null Mã người dùng hoặc null nếu không tìm thấy
     */
    private function resolveCustomerId(string $email): ?int
    {
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return ($row && isset($row['ma_nguoi_dung'])) ? (int)$row['ma_nguoi_dung'] : null;
    }
}
