<?php

class EventTicket
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getCategories(): array
    {
        $result = $this->conn->query("SELECT ma_danh_muc, ten_danh_muc, mo_ta FROM danh_muc ORDER BY ma_danh_muc DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createCategory(string $name, ?string $description): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['success' => false, 'message' => 'Tên danh mục không được để trống.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm danh mục thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể thêm danh mục: ' . $stmt->error];
    }

    public function updateCategory(int $id, string $name, ?string $description): array
    {
        $name = trim($name);
        if ($id <= 0 || $name === '') {
            return ['success' => false, 'message' => 'Dữ liệu danh mục không hợp lệ.'];
        }

        $stmt = $this->conn->prepare("UPDATE danh_muc SET ten_danh_muc = ?, mo_ta = ? WHERE ma_danh_muc = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật danh mục thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể cập nhật danh mục: ' . $stmt->error];
    }

    public function deleteCategory(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Danh mục không hợp lệ.'];
        }
        $stmt = $this->conn->prepare("DELETE FROM danh_muc WHERE ma_danh_muc = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa danh mục thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể xóa danh mục (có thể đang được dùng): ' . $stmt->error];
    }

    public function createEvent(array $payload): array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO su_kien (ma_danh_muc, ma_nguoi_tao, ten_su_kien, mo_ta, hinh_anh, ngay_to_chuc, gio_to_chuc, dia_diem, trang_thai)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'da_duyet')"
        );
        $stmt->bind_param(
            "iissssss",
            $payload['ma_danh_muc'],
            $payload['ma_nguoi_tao'],
            $payload['ten_su_kien'],
            $payload['mo_ta'],
            $payload['hinh_anh'],
            $payload['ngay_to_chuc'],
            $payload['gio_to_chuc'],
            $payload['dia_diem']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Tạo sự kiện thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể tạo sự kiện: ' . $stmt->error];
    }

    public function updateEvent(array $payload): array
    {
        if (($payload['ma_su_kien'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Sự kiện không hợp lệ.'];
        }

        $hasImage = isset($payload['hinh_anh']) && $payload['hinh_anh'] !== null && $payload['hinh_anh'] !== '';
        if ($hasImage) {
            $stmt = $this->conn->prepare(
                "UPDATE su_kien
                 SET ma_danh_muc = ?, ten_su_kien = ?, mo_ta = ?, hinh_anh = ?, ngay_to_chuc = ?, gio_to_chuc = ?, dia_diem = ?
                 WHERE ma_su_kien = ?"
            );
            $stmt->bind_param(
                "issssssi",
                $payload['ma_danh_muc'],
                $payload['ten_su_kien'],
                $payload['mo_ta'],
                $payload['hinh_anh'],
                $payload['ngay_to_chuc'],
                $payload['gio_to_chuc'],
                $payload['dia_diem'],
                $payload['ma_su_kien']
            );
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE su_kien
                 SET ma_danh_muc = ?, ten_su_kien = ?, mo_ta = ?, hinh_anh = ?, ngay_to_chuc = ?, gio_to_chuc = ?, dia_diem = ?
                 WHERE ma_su_kien = ?"
            );
            $stmt->bind_param(
                "issssssi",
                $payload['ma_danh_muc'],
                $payload['ten_su_kien'],
                $payload['mo_ta'],
                $payload['hinh_anh'],
                $payload['ngay_to_chuc'],
                $payload['gio_to_chuc'],
                $payload['dia_diem'],
                $payload['ma_su_kien']
            );
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật sự kiện thành công!'];
        }

        return ['success' => false, 'message' => 'Không thể cập nhật sự kiện: ' . $stmt->error];
    }

    public function deleteEvent(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Sự kiện không hợp lệ.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM su_kien WHERE ma_su_kien = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa sự kiện thành công!'];
        }

        return ['success' => false, 'message' => 'Không thể xóa sự kiện (có thể đang có loại vé): ' . $stmt->error];
    }

    public function createTicketType(array $payload): array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO loai_ve (ma_su_kien, ten_loai_ve, gia_ve, so_luong, so_luong_con)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "isdii",
            $payload['ma_su_kien'],
            $payload['ten_loai_ve'],
            $payload['gia_ve'],
            $payload['so_luong'],
            $payload['so_luong_con']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm loại vé thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể thêm loại vé: ' . $stmt->error];
    }

    public function updateTicketType(array $payload): array
    {
        if (($payload['ma_loai_ve'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Loại vé không hợp lệ.'];
        }
        $stmt = $this->conn->prepare(
            "UPDATE loai_ve
             SET ten_loai_ve = ?, gia_ve = ?, so_luong = ?, so_luong_con = ?
             WHERE ma_loai_ve = ?"
        );
        $stmt->bind_param(
            "sdiii",
            $payload['ten_loai_ve'],
            $payload['gia_ve'],
            $payload['so_luong'],
            $payload['so_luong_con'],
            $payload['ma_loai_ve']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật loại vé thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể cập nhật loại vé: ' . $stmt->error];
    }

    public function deleteTicketType(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Loại vé không hợp lệ.'];
        }
        $stmt = $this->conn->prepare("DELETE FROM loai_ve WHERE ma_loai_ve = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa loại vé thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể xóa loại vé (có thể đã phát sinh đơn hàng): ' . $stmt->error];
    }

    public function getEvents(): array
    {
        $sql = "SELECT sk.ma_su_kien, sk.ma_danh_muc, sk.ten_su_kien, sk.mo_ta, sk.hinh_anh, sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
                FROM su_kien sk
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                ORDER BY sk.ma_su_kien DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTicketTypesForAdmin(): array
    {
        $sql = "SELECT lv.ma_loai_ve, lv.ma_su_kien, lv.ten_loai_ve, lv.gia_ve, lv.so_luong, lv.so_luong_con,
                       sk.ten_su_kien, sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
                FROM loai_ve lv
                JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                ORDER BY lv.ma_loai_ve DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTicketsForHome(int $limit = 12): array
    {
        $limit = max(1, $limit);
        $sql = "SELECT lv.ma_loai_ve, lv.ten_loai_ve, lv.gia_ve, lv.so_luong_con,
                       sk.ten_su_kien, sk.mo_ta AS mo_ta_su_kien, sk.hinh_anh, sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
                FROM loai_ve lv
                JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                WHERE lv.so_luong_con > 0
                ORDER BY lv.ma_loai_ve DESC
                LIMIT {$limit}";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTicketDetail(int $ticketTypeId): ?array
    {
        if ($ticketTypeId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT lv.ma_loai_ve, lv.ma_su_kien, lv.ten_loai_ve, lv.gia_ve, lv.so_luong, lv.so_luong_con,
                    sk.ten_su_kien, sk.mo_ta, sk.hinh_anh, sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
             FROM loai_ve lv
             JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
             JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
             WHERE lv.ma_loai_ve = ?
             LIMIT 1"
        );
        $stmt->bind_param("i", $ticketTypeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        return $row ?: null;
    }

    public function createOrderForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        if ($ticketTypeId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Dữ liệu đặt vé không hợp lệ.'];
        }

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            $ticketStmt = $this->conn->prepare(
                "SELECT ma_loai_ve, gia_ve, so_luong_con
                 FROM loai_ve
                 WHERE ma_loai_ve = ?
                 FOR UPDATE"
            );
            $ticketStmt->bind_param("i", $ticketTypeId);
            $ticketStmt->execute();
            $ticketRes = $ticketStmt->get_result();
            $ticketRow = $ticketRes ? $ticketRes->fetch_assoc() : null;

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

            $orderStmt = $this->conn->prepare(
                "INSERT INTO don_hang (ma_khach_hang, tong_tien, phuong_thuc_thanh_toan, trang_thai_thanh_toan, trang_thai_don_hang)
                 VALUES (?, ?, 'tai_quay', 'cho_thanh_toan', 'cho_xac_nhan')"
            );
            $orderStmt->bind_param("id", $customerId, $total);
            if (!$orderStmt->execute()) {
                throw new RuntimeException($orderStmt->error);
            }
            $orderId = (int)$this->conn->insert_id;

            $detailStmt = $this->conn->prepare(
                "INSERT INTO chi_tiet_don_hang (ma_don_hang, ma_loai_ve, so_luong, don_gia, thanh_tien)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $detailStmt->bind_param("iiidd", $orderId, $ticketTypeId, $quantity, $price, $total);
            if (!$detailStmt->execute()) {
                throw new RuntimeException($detailStmt->error);
            }

            $newRemaining = $remaining - $quantity;
            $updateStmt = $this->conn->prepare("UPDATE loai_ve SET so_luong_con = ? WHERE ma_loai_ve = ?");
            $updateStmt->bind_param("ii", $newRemaining, $ticketTypeId);
            if (!$updateStmt->execute()) {
                throw new RuntimeException($updateStmt->error);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Đặt vé thành công! Mã đơn hàng của bạn là #' . $orderId, 'order_id' => $orderId];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể đặt vé: ' . $e->getMessage()];
        }
    }

    public function createPendingOrderForTicket(int $ticketTypeId, string $customerEmail, int $quantity): array
    {
        if ($ticketTypeId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Dữ liệu đặt vé không hợp lệ.'];
        }

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            $ticketStmt = $this->conn->prepare(
                "SELECT ma_loai_ve, gia_ve, so_luong_con
                 FROM loai_ve
                 WHERE ma_loai_ve = ?
                 FOR UPDATE"
            );
            $ticketStmt->bind_param("i", $ticketTypeId);
            $ticketStmt->execute();
            $ticketRes = $ticketStmt->get_result();
            $ticketRow = $ticketRes ? $ticketRes->fetch_assoc() : null;

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

            $orderStmt = $this->conn->prepare(
                "INSERT INTO don_hang (ma_khach_hang, tong_tien, phuong_thuc_thanh_toan, trang_thai_thanh_toan, trang_thai_don_hang)
                 VALUES (?, ?, 'momo', 'cho_thanh_toan', 'cho_xac_nhan')"
            );
            $orderStmt->bind_param("id", $customerId, $total);
            if (!$orderStmt->execute()) {
                throw new RuntimeException($orderStmt->error);
            }
            $orderId = (int)$this->conn->insert_id;

            $detailStmt = $this->conn->prepare(
                "INSERT INTO chi_tiet_don_hang (ma_don_hang, ma_loai_ve, so_luong, don_gia, thanh_tien)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $detailStmt->bind_param("iiidd", $orderId, $ticketTypeId, $quantity, $price, $total);
            if (!$detailStmt->execute()) {
                throw new RuntimeException($detailStmt->error);
            }

            $newRemaining = $remaining - $quantity;
            $updateStmt = $this->conn->prepare("UPDATE loai_ve SET so_luong_con = ? WHERE ma_loai_ve = ?");
            $updateStmt->bind_param("ii", $newRemaining, $ticketTypeId);
            if (!$updateStmt->execute()) {
                throw new RuntimeException($updateStmt->error);
            }

            $paymentStmt = $this->conn->prepare(
                "INSERT INTO thanh_toan (ma_don_hang, phuong_thuc, so_tien, trang_thai)
                 VALUES (?, 'momo', ?, 'cho_xu_ly')"
            );
            $paymentStmt->bind_param("id", $orderId, $total);
            if (!$paymentStmt->execute()) {
                throw new RuntimeException($paymentStmt->error);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã tạo đơn hàng, vui lòng quét QR MoMo để thanh toán.', 'order_id' => $orderId];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $e->getMessage()];
        }
    }

    public function getOrderPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        if ($orderId <= 0) {
            return null;
        }

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
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
                $order['tickets'] = $this->getGeneratedTicketsByOrder($orderId);
                return $order;
            }

            $order['momo_pay_url'] = '';
            $order['momo_mode'] = 'demo';
            $order['momo_qr_text'] = 'MOMO-DEMO|' . $noteText . '|AMOUNT:' . $amountText;
        }
        $order['momo_qr_image'] = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . rawurlencode((string)$order['momo_qr_text']);
        $order['tickets'] = $this->getGeneratedTicketsByOrder($orderId);

        return $order;
    }

    public function confirmOrderPayment(int $orderId, string $customerEmail): array
    {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Đơn hàng không hợp lệ.'];
        }

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            $orderStmt = $this->conn->prepare(
                "SELECT ma_don_hang, trang_thai_thanh_toan
                 FROM don_hang
                 WHERE ma_don_hang = ? AND ma_khach_hang = ?
                 FOR UPDATE"
            );
            $orderStmt->bind_param("ii", $orderId, $customerId);
            $orderStmt->execute();
            $orderRes = $orderStmt->get_result();
            $order = $orderRes ? $orderRes->fetch_assoc() : null;
            if (!$order) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Không tìm thấy đơn hàng.'];
            }

            if ((string)$order['trang_thai_thanh_toan'] === 'da_thanh_toan') {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Đơn hàng đã được thanh toán trước đó.'];
            }

            $detailsStmt = $this->conn->prepare(
                "SELECT ma_chi_tiet, so_luong
                 FROM chi_tiet_don_hang
                 WHERE ma_don_hang = ?"
            );
            $detailsStmt->bind_param("i", $orderId);
            $detailsStmt->execute();
            $detailsRes = $detailsStmt->get_result();
            $details = $detailsRes ? $detailsRes->fetch_all(MYSQLI_ASSOC) : [];

            $insertTicketStmt = $this->conn->prepare(
                "INSERT INTO ve (ma_chi_tiet, ma_qr, qr_image_url, trang_thai)
                 VALUES (?, ?, ?, 'chua_su_dung')"
            );

            foreach ($details as $detail) {
                $detailId = (int)$detail['ma_chi_tiet'];
                $qty = (int)$detail['so_luong'];
                for ($i = 1; $i <= $qty; $i++) {
                    $qrCode = 'TICKET-' . $orderId . '-' . $detailId . '-' . $i . '-' . bin2hex(random_bytes(4));
                    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . rawurlencode($qrCode);
                    $insertTicketStmt->bind_param("iss", $detailId, $qrCode, $qrImageUrl);
                    if (!$insertTicketStmt->execute()) {
                        throw new RuntimeException($insertTicketStmt->error);
                    }
                }
            }

            $updateOrderStmt = $this->conn->prepare(
                "UPDATE don_hang
                 SET trang_thai_thanh_toan = 'da_thanh_toan', trang_thai_don_hang = 'da_xac_nhan'
                 WHERE ma_don_hang = ?"
            );
            $updateOrderStmt->bind_param("i", $orderId);
            if (!$updateOrderStmt->execute()) {
                throw new RuntimeException($updateOrderStmt->error);
            }

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

    public function confirmOrderPaymentByOrderId(int $orderId): array
    {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Đơn hàng không hợp lệ.'];
        }

        try {
            $this->conn->begin_transaction();

            $orderStmt = $this->conn->prepare(
                "SELECT ma_don_hang, trang_thai_thanh_toan
                 FROM don_hang
                 WHERE ma_don_hang = ?
                 FOR UPDATE"
            );
            $orderStmt->bind_param("i", $orderId);
            $orderStmt->execute();
            $orderRes = $orderStmt->get_result();
            $order = $orderRes ? $orderRes->fetch_assoc() : null;
            if (!$order) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Không tìm thấy đơn hàng.'];
            }

            if ((string)$order['trang_thai_thanh_toan'] === 'da_thanh_toan') {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Đơn hàng đã thanh toán.'];
            }

            $detailsStmt = $this->conn->prepare(
                "SELECT ma_chi_tiet, so_luong
                 FROM chi_tiet_don_hang
                 WHERE ma_don_hang = ?"
            );
            $detailsStmt->bind_param("i", $orderId);
            $detailsStmt->execute();
            $detailsRes = $detailsStmt->get_result();
            $details = $detailsRes ? $detailsRes->fetch_all(MYSQLI_ASSOC) : [];

            $insertTicketStmt = $this->conn->prepare(
                "INSERT INTO ve (ma_chi_tiet, ma_qr, qr_image_url, trang_thai)
                 VALUES (?, ?, ?, 'chua_su_dung')"
            );

            foreach ($details as $detail) {
                $detailId = (int)$detail['ma_chi_tiet'];
                $qty = (int)$detail['so_luong'];
                for ($i = 1; $i <= $qty; $i++) {
                    $qrCode = 'TICKET-' . $orderId . '-' . $detailId . '-' . $i . '-' . bin2hex(random_bytes(4));
                    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . rawurlencode($qrCode);
                    $insertTicketStmt->bind_param("iss", $detailId, $qrCode, $qrImageUrl);
                    if (!$insertTicketStmt->execute()) {
                        throw new RuntimeException($insertTicketStmt->error);
                    }
                }
            }

            $updateOrderStmt = $this->conn->prepare(
                "UPDATE don_hang
                 SET trang_thai_thanh_toan = 'da_thanh_toan', trang_thai_don_hang = 'da_xac_nhan'
                 WHERE ma_don_hang = ?"
            );
            $updateOrderStmt->bind_param("i", $orderId);
            if (!$updateOrderStmt->execute()) {
                throw new RuntimeException($updateOrderStmt->error);
            }

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
            return ['success' => true, 'message' => 'Thanh toán thành công.'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi xác nhận thanh toán: ' . $e->getMessage()];
        }
    }

    public function getGeneratedTicketsByOrder(int $orderId): array
    {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare(
            "SELECT v.ma_ve, v.ma_qr, v.qr_image_url, v.trang_thai, lv.ten_loai_ve, dh.ngay_tao
             FROM ve v
             JOIN chi_tiet_don_hang ctdh ON ctdh.ma_chi_tiet = v.ma_chi_tiet
             JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
             JOIN don_hang dh ON dh.ma_don_hang = ctdh.ma_don_hang
             WHERE ctdh.ma_don_hang = ?
             ORDER BY v.ma_ve DESC"
        );
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getOrderTotalAmount(int $orderId): float
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

    public function getPendingOrders(): array
    {
        $sql = "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan, nd.ho_ten, nd.email, dh.ngay_tao
                FROM don_hang dh
                JOIN nguoi_dung nd ON nd.ma_nguoi_dung = dh.ma_khach_hang
                WHERE dh.trang_thai_thanh_toan = 'cho_thanh_toan'
                ORDER BY dh.ngay_tao DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getOrdersByCustomerEmail(string $email): array
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

    public function deleteOrderById(int $orderId): array
    {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Đơn hàng không hợp lệ.'];
        }

        try {
            $this->conn->begin_transaction();

            // Xóa vé liên quan
            $veSql = "DELETE FROM ve WHERE ma_chi_tiet IN (
                SELECT ma_chi_tiet FROM chi_tiet_don_hang WHERE ma_don_hang = ?
            )";
            $veStmt = $this->conn->prepare($veSql);
            $veStmt->bind_param("i", $orderId);
            $veStmt->execute();

            // Xóa chi tiết đơn hàng
            $ctStmt = $this->conn->prepare("DELETE FROM chi_tiet_don_hang WHERE ma_don_hang = ?");
            $ctStmt->bind_param("i", $orderId);
            $ctStmt->execute();

            // Xóa thanh toán
            $ttStmt = $this->conn->prepare("DELETE FROM thanh_toan WHERE ma_don_hang = ?");
            $ttStmt->bind_param("i", $orderId);
            $ttStmt->execute();

            // Khôi phục số lượng vé (nếu cần)
            $loiStmt = $this->conn->prepare(
                "UPDATE loai_ve SET so_luong_con = so_luong_con + (
                  SELECT COALESCE(SUM(so_luong), 0) FROM chi_tiet_don_hang WHERE ma_don_hang = ?
                )"
            );
            $loiStmt->bind_param("i", $orderId);
            $loiStmt->execute();

            // Xóa đơn hàng
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

    public function deleteAllPendingOrders(): array
    {
        try {
            $this->conn->begin_transaction();

            // Lấy danh sách order cho_thanh_toan
            $getStmt = $this->conn->prepare("SELECT ma_don_hang FROM don_hang WHERE trang_thai_thanh_toan = 'cho_thanh_toan'");
            $getStmt->execute();
            $result = $getStmt->get_result();
            $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

            // Xóa từng order
            foreach ($orders as $order) {
                $orderId = (int)$order['ma_don_hang'];

                // Xóa vé
                $veSql = "DELETE FROM ve WHERE ma_chi_tiet IN (
                    SELECT ma_chi_tiet FROM chi_tiet_don_hang WHERE ma_don_hang = ?
                )";
                $veStmt = $this->conn->prepare($veSql);
                $veStmt->bind_param("i", $orderId);
                $veStmt->execute();

                // Xóa chi tiết
                $ctStmt = $this->conn->prepare("DELETE FROM chi_tiet_don_hang WHERE ma_don_hang = ?");
                $ctStmt->bind_param("i", $orderId);
                $ctStmt->execute();

                // Xóa thanh toán
                $ttStmt = $this->conn->prepare("DELETE FROM thanh_toan WHERE ma_don_hang = ?");
                $ttStmt->bind_param("i", $orderId);
                $ttStmt->execute();

                // Xóa order
                $dhStmt = $this->conn->prepare("DELETE FROM don_hang WHERE ma_don_hang = ?");
                $dhStmt->bind_param("i", $orderId);
                $dhStmt->execute();
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa tất cả đơn chờ xác nhận thành công (' . count($orders) . ' đơn).'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể xóa: ' . $e->getMessage()];
        }
    }

    /**
     * Thống kê doanh thu từ đơn đã xác nhận thanh toán.
     * $type: 'day' | 'month' | 'year'
     * $value: '2026-03-22' | '2026-03' | '2026'
     */
    public function getStatistics(string $type, string $value): array
    {
        $where = match($type) {
            'day'   => "DATE(dh.ngay_tao) = ?",
            'month' => "DATE_FORMAT(dh.ngay_tao, '%Y-%m') = ?",
            'year'  => "YEAR(dh.ngay_tao) = ?",
            default => "DATE(dh.ngay_tao) = ?",
        };

        // Tổng quan
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

        // Chi tiết từng đơn
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
            'summary' => $summary,
            'orders'  => $orders,
            'by_event' => $byEvent,
        ];
    }

    /**
     * Tạo đơn hàng từ giỏ hàng (nhiều loại vé).
     * $cart = [ ma_loai_ve => ['ten_su_kien'=>..., 'ten_loai_ve'=>..., 'gia_ve'=>..., 'so_luong'=>...], ... ]
     */
    public function createOrderFromCart(array $cart, string $customerEmail): array
    {
        if (empty($cart)) {
            return ['success' => false, 'message' => 'Giỏ hàng trống.'];
        }

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
        if (!$customerId) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản người mua.'];
        }

        try {
            $this->conn->begin_transaction();

            // Lock & validate tất cả loại vé trước
            $ticketIds = array_keys($cart);
            $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
            $lockStmt = $this->conn->prepare(
                "SELECT ma_loai_ve, gia_ve, so_luong_con FROM loai_ve WHERE ma_loai_ve IN ($placeholders) FOR UPDATE"
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

            // Tạo đơn hàng
            $orderStmt = $this->conn->prepare(
                "INSERT INTO don_hang (ma_khach_hang, tong_tien, phuong_thuc_thanh_toan, trang_thai_thanh_toan, trang_thai_don_hang)
                 VALUES (?, ?, 'momo', 'cho_thanh_toan', 'cho_xac_nhan')"
            );
            $orderStmt->bind_param("id", $customerId, $grandTotal);
            if (!$orderStmt->execute()) {
                throw new RuntimeException($orderStmt->error);
            }
            $orderId = (int)$this->conn->insert_id;

            // Thêm chi tiết & trừ tồn kho
            $detailStmt = $this->conn->prepare(
                "INSERT INTO chi_tiet_don_hang (ma_don_hang, ma_loai_ve, so_luong, don_gia, thanh_tien) VALUES (?, ?, ?, ?, ?)"
            );
            $updateStmt = $this->conn->prepare("UPDATE loai_ve SET so_luong_con = so_luong_con - ? WHERE ma_loai_ve = ?");

            foreach ($cart as $maLoaiVe => $item) {
                $maLoaiVe = (int)$maLoaiVe;
                $qty = (int)$item['so_luong'];
                $price = (float)$ticketRows[$maLoaiVe]['gia_ve'];
                $subtotal = $price * $qty;

                $detailStmt->bind_param("iiidd", $orderId, $maLoaiVe, $qty, $price, $subtotal);
                if (!$detailStmt->execute()) throw new RuntimeException($detailStmt->error);

                $updateStmt->bind_param("ii", $qty, $maLoaiVe);
                if (!$updateStmt->execute()) throw new RuntimeException($updateStmt->error);
            }

            // Tạo bản ghi thanh toán
            $payStmt = $this->conn->prepare(
                "INSERT INTO thanh_toan (ma_don_hang, phuong_thuc, so_tien, trang_thai) VALUES (?, 'momo', ?, 'cho_xu_ly')"
            );
            $payStmt->bind_param("id", $orderId, $grandTotal);
            if (!$payStmt->execute()) throw new RuntimeException($payStmt->error);

            $this->conn->commit();
            return ['success' => true, 'order_id' => $orderId, 'tong_tien' => $grandTotal];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Không thể tạo đơn hàng: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy thông tin đơn hàng từ cart (nhiều loại vé) để hiển thị trang thanh toán.
     */
    public function getCartOrderPaymentInfo(int $orderId, string $customerEmail): ?array
    {
        if ($orderId <= 0) return null;

        $customerId = $this->resolveCreatorIdByEmail($customerEmail);
        if (!$customerId) return null;

        $stmt = $this->conn->prepare(
            "SELECT dh.ma_don_hang, dh.tong_tien, dh.trang_thai_thanh_toan
             FROM don_hang dh
             WHERE dh.ma_don_hang = ? AND dh.ma_khach_hang = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $orderId, $customerId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) return null;

        // Lấy chi tiết các loại vé
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

        // Build QR MoMo
        $amountText = (string)((int)round((float)$order['tong_tien']));
        $noteText = 'DH' . $orderId;
        $baseMomoUrl = defined('MOMO_PAYMENT_BASE_URL') ? trim((string)MOMO_PAYMENT_BASE_URL) : '';
        if ($baseMomoUrl !== '') {
            $glue = str_contains($baseMomoUrl, '?') ? '&' : '?';
            $order['momo_pay_url'] = $baseMomoUrl . $glue . http_build_query(['amount' => $amountText, 'comment' => $noteText]);
            $order['momo_qr_text'] = $order['momo_pay_url'];
        } else {
            $staticQr = defined('MOMO_STATIC_QR_IMAGE_URL') ? trim((string)MOMO_STATIC_QR_IMAGE_URL) : '';
            $order['momo_qr_text'] = 'MOMO-DEMO|' . $noteText . '|AMOUNT:' . $amountText;
            $order['momo_qr_image_static'] = $staticQr;
            $order['momo_account_name'] = defined('MOMO_ACCOUNT_NAME') ? (string)MOMO_ACCOUNT_NAME : '';
            $order['momo_account_mask'] = defined('MOMO_ACCOUNT_MASK') ? (string)MOMO_ACCOUNT_MASK : '';
        }
        $order['momo_qr_image'] = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . rawurlencode((string)$order['momo_qr_text']);
        $order['transfer_content'] = $noteText;
        $order['transfer_amount'] = (int)round((float)$order['tong_tien']);
        $order['tickets'] = $this->getGeneratedTicketsByOrder($orderId);

        return $order;
    }

    public function resolveCreatorIdByEmail(string $email): ?int
    {
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if ($row && isset($row['ma_nguoi_dung'])) {
            return (int)$row['ma_nguoi_dung'];
        }

        $fallback = $this->conn->query("SELECT ma_nguoi_dung FROM nguoi_dung ORDER BY ma_nguoi_dung ASC LIMIT 1");
        $fallbackRow = $fallback ? $fallback->fetch_assoc() : null;
        return $fallbackRow ? (int)$fallbackRow['ma_nguoi_dung'] : null;
    }
}

