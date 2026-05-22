<?php

/**
 * Model quản lý sự kiện (bảng su_kien).
 * Cung cấp các thao tác CRUD và truy vấn thông tin sự kiện.
 */
class Event
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả sự kiện kèm tên danh mục (dùng cho trang admin).
     *
     * @return array Danh sách sự kiện
     */
    public function getAll(): array
    {
        $sql = "SELECT sk.ma_su_kien, sk.ma_danh_muc, sk.ten_su_kien, sk.mo_ta, sk.hinh_anh,
                       sk.ngay_to_chuc, sk.gio_to_chuc, sk.dia_diem, sk.trang_thai, dm.ten_danh_muc
                FROM su_kien sk
                JOIN danh_muc dm ON dm.ma_danh_muc = sk.ma_danh_muc
                ORDER BY sk.ma_su_kien DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Tạo sự kiện mới. Trạng thái có thể là 'cho_duyet' (nhân viên) hoặc 'da_duyet' (admin).
     */
    public function create(array $payload): array
    {
        $trangThai = $payload['trang_thai'] ?? 'cho_duyet';
        $stmt = $this->conn->prepare(
            "INSERT INTO su_kien (ma_danh_muc, ma_nguoi_tao, ten_su_kien, mo_ta, hinh_anh,
                                  ngay_to_chuc, gio_to_chuc, dia_diem, trang_thai)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iisssssss",
            $payload['ma_danh_muc'],
            $payload['ma_nguoi_tao'],
            $payload['ten_su_kien'],
            $payload['mo_ta'],
            $payload['hinh_anh'],
            $payload['ngay_to_chuc'],
            $payload['gio_to_chuc'],
            $payload['dia_diem'],
            $trangThai
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Tạo sự kiện thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể tạo sự kiện: ' . $stmt->error];
    }

    /**
     * Cập nhật thông tin sự kiện.
     *
     * @param array $payload Mảng chứa ma_su_kien (bắt buộc) và các trường cần cập nhật
     * @return array ['success' => bool, 'message' => string]
     */
    public function update(array $payload): array
    {
        if (($payload['ma_su_kien'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Sự kiện không hợp lệ.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE su_kien
             SET ma_danh_muc = ?, ten_su_kien = ?, mo_ta = ?, hinh_anh = ?,
                 ngay_to_chuc = ?, gio_to_chuc = ?, dia_diem = ?
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

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật sự kiện thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể cập nhật sự kiện: ' . $stmt->error];
    }

    /**
     * Cập nhật trạng thái sự kiện (Ví dụ: duyệt sự kiện).
     *
     * @param int $id Mã sự kiện
     * @param string $status Trạng thái ('cho_duyet', 'da_duyet', 'da_huy', ...)
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateStatus(int $id, string $status): array
    {
        $stmt = $this->conn->prepare("UPDATE su_kien SET trang_thai = ? WHERE ma_su_kien = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật trạng thái sự kiện thành công!'];
        }
        return ['success' => false, 'message' => 'Lỗi cập nhật trạng thái: ' . $stmt->error];
    }

    /**
     * Xóa sự kiện. Thất bại nếu sự kiện đang có loại vé (FK constraint).
     *
     * @param int $id Mã sự kiện
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete(int $id): array
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
}
