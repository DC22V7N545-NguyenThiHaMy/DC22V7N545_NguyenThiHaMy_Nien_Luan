<?php

/**
 * Model quản lý vé điện tử (bảng ve).
 * Xử lý việc phát hành vé QR Code và tra cứu thông tin vé.
 */
class Ticket
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Phát hành vé điện tử cho một đơn hàng (dùng trong transaction).
     * Mỗi chi tiết đơn hàng sẽ sinh ra N vé tương ứng với số lượng.
     *
     * Lưu ý: Hàm này KHÔNG tự quản lý transaction.
     * Caller phải đảm bảo gọi trong begin_transaction/commit/rollback.
     *
     * @param int   $orderId Mã đơn hàng
     * @param array $details Danh sách chi tiết [['ma_chi_tiet' => int, 'so_luong' => int], ...]
     * @return void
     * @throws RuntimeException Nếu insert vé thất bại
     */
    public function generateForOrder(int $orderId, array $details): void
    {
        $insertStmt = $this->conn->prepare(
            "INSERT INTO ve (ma_chi_tiet, ma_qr, qr_image_url, trang_thai)
             VALUES (?, ?, ?, 'chua_su_dung')"
        );

        foreach ($details as $detail) {
            $detailId = (int)$detail['ma_chi_tiet'];
            $qty = (int)$detail['so_luong'];

            // Sinh mỗi vé 1 mã QR duy nhất bằng chuỗi ngẫu nhiên
            for ($i = 1; $i <= $qty; $i++) {
                $qrCode = 'TICKET-' . $orderId . '-' . $detailId . '-' . $i
                         . '-' . bin2hex(random_bytes(4));
                $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='
                            . rawurlencode($qrCode);

                $insertStmt->bind_param("iss", $detailId, $qrCode, $qrImageUrl);
                if (!$insertStmt->execute()) {
                    throw new RuntimeException('Không thể tạo vé: ' . $insertStmt->error);
                }
            }
        }
    }

    /**
     * Lấy danh sách vé đã phát hành theo mã đơn hàng.
     *
     * @param int $orderId Mã đơn hàng
     * @return array Danh sách vé [['ma_ve', 'ma_qr', 'qr_image_url', 'trang_thai', ...], ...]
     */
    public function getByOrderId(int $orderId): array
    {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare(
            "SELECT v.ma_ve, v.ma_qr, v.qr_image_url, v.trang_thai,
                    lv.ten_loai_ve, dh.ngay_tao
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

    /**
     * Soát vé / Check-in mã QR (Cho nhân viên).
     *
     * @param string $qrCode Mã QR quét được
     * @param int $staffId Mã nhân viên thực hiện check-in
     * @return array ['success' => bool, 'message' => string, 'ticket' => array|null]
     */
    public function checkInTicket(string $qrCode, int $staffId): array
    {
        $qrCode = trim($qrCode);
        if (empty($qrCode)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mã vé.'];
        }

        try {
            $this->conn->begin_transaction();

            // Lấy thông tin vé (FOR UPDATE)
            $stmt = $this->conn->prepare(
                "SELECT v.ma_ve, v.trang_thai, v.ma_qr,
                        lv.ten_loai_ve, sk.ten_su_kien,
                        nd.ho_ten as ten_khach_hang
                 FROM ve v
                 JOIN chi_tiet_don_hang ctdh ON ctdh.ma_chi_tiet = v.ma_chi_tiet
                 JOIN loai_ve lv ON lv.ma_loai_ve = ctdh.ma_loai_ve
                 JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
                 JOIN don_hang dh ON dh.ma_don_hang = ctdh.ma_don_hang
                 JOIN nguoi_dung nd ON nd.ma_nguoi_dung = dh.ma_khach_hang
                 WHERE v.ma_qr = ? FOR UPDATE"
            );
            $stmt->bind_param("s", $qrCode);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();

            if (!$ticket) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Mã vé không tồn tại trong hệ thống.'];
            }

            if ((string)$ticket['trang_thai'] === 'da_su_dung') {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Lỗi: Vé TRÙNG! Mã này đã được check-in trước đó.'];
            }

            if ((string)$ticket['trang_thai'] === 'da_huy') {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Lỗi: Vé đã bị hủy.'];
            }

            // Đánh dấu vé đã sử dụng
            $maVe = (int)$ticket['ma_ve'];
            $updateStmt = $this->conn->prepare("UPDATE ve SET trang_thai = 'da_su_dung' WHERE ma_ve = ?");
            $updateStmt->bind_param("i", $maVe);
            $updateStmt->execute();

            // Lưu lịch sử check-in vào bảng xac_nhan_ve
            $historyStmt = $this->conn->prepare(
                "INSERT INTO xac_nhan_ve (ma_ve, ma_nhan_vien) VALUES (?, ?)"
            );
            $historyStmt->bind_param("ii", $maVe, $staffId);
            $historyStmt->execute();

            $this->conn->commit();

            return [
                'success' => true, 
                'message' => 'Check-in thành công!',
                'ticket' => $ticket
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
}
