<?php

/**
 * Model quản lý loại vé (bảng loai_ve).
 * Cung cấp CRUD và các truy vấn liên quan đến loại vé.
 */
class TicketType
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Tạo loại vé mới cho một sự kiện.
     *
     * @param array $payload Mảng chứa: ma_su_kien, ten_loai_ve, gia_ve, so_luong, so_luong_con
     * @return array ['success' => bool, 'message' => string]
     */
    public function create(array $payload): array
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

    /**
     * Cập nhật thông tin loại vé.
     *
     * @param array $payload Mảng chứa: ma_loai_ve, ten_loai_ve, gia_ve, so_luong, so_luong_con
     * @return array ['success' => bool, 'message' => string]
     */
    public function update(array $payload): array
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

    /**
     * Xóa loại vé. Thất bại nếu loại vé đã phát sinh đơn hàng (FK constraint).
     *
     * @param int $id Mã loại vé
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete(int $id): array
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

    /**
     * Lấy danh sách loại vé kèm thông tin sự kiện (dùng cho trang admin).
     *
     * @return array Danh sách loại vé
     */
    public function getAllForAdmin(): array
    {
        $sql = "SELECT lv.ma_loai_ve, lv.ma_su_kien, lv.ten_loai_ve, lv.gia_ve,
                       lv.so_luong, lv.so_luong_con,
                       sk.ten_su_kien, sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem,
                       dm.ten_danh_muc
                FROM loai_ve lv
                JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                ORDER BY lv.ma_loai_ve DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lấy danh sách loại vé còn hàng cho trang chủ (hiển thị cho khách hàng).
     *
     * @param int $limit Số lượng tối đa trả về
     * @return array Danh sách loại vé còn hàng
     */
    public function getForHome(int $limit = 12): array
    {
        $limit = max(1, $limit);
        $sql = "SELECT lv.ma_loai_ve, lv.ten_loai_ve, lv.gia_ve, lv.so_luong, lv.so_luong_con,
                       sk.ten_su_kien, sk.mo_ta AS mo_ta_su_kien, sk.hinh_anh,
                       sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
                FROM loai_ve lv
                JOIN su_kien sk ON sk.ma_su_kien = lv.ma_su_kien
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                WHERE lv.so_luong_con > 0
                ORDER BY lv.ma_loai_ve DESC
                LIMIT {$limit}";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lấy chi tiết một loại vé kèm thông tin sự kiện và danh mục.
     *
     * @param int $ticketTypeId Mã loại vé
     * @return array|null Thông tin chi tiết hoặc null nếu không tìm thấy
     */
    public function getDetail(int $ticketTypeId): ?array
    {
        if ($ticketTypeId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT lv.ma_loai_ve, lv.ma_su_kien, lv.ten_loai_ve, lv.gia_ve,
                    lv.so_luong, lv.so_luong_con,
                    sk.ten_su_kien, sk.mo_ta, sk.hinh_anh, sk.ngay_to_chuc,
                    sk.gio_to_chuc, sk.dia_diem, dm.ten_danh_muc
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
}
