<?php

/**
 * Model quản lý danh mục sự kiện (bảng danh_muc).
 * Cung cấp các thao tác CRUD cơ bản cho danh mục.
 */
class Category
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả danh mục, sắp xếp theo ID giảm dần.
     *
     * @return array Danh sách danh mục [['ma_danh_muc', 'ten_danh_muc', 'mo_ta'], ...]
     */
    public function getAll(): array
    {
        $result = $this->conn->query(
            "SELECT ma_danh_muc, ten_danh_muc, mo_ta FROM danh_muc ORDER BY ma_danh_muc DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Tạo danh mục mới.
     *
     * @param string      $name        Tên danh mục (bắt buộc)
     * @param string|null $description Mô tả (tùy chọn)
     * @return array ['success' => bool, 'message' => string]
     */
    public function create(string $name, ?string $description): array
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

    /**
     * Cập nhật thông tin danh mục.
     *
     * @param int         $id          Mã danh mục cần cập nhật
     * @param string      $name        Tên mới
     * @param string|null $description Mô tả mới
     * @return array ['success' => bool, 'message' => string]
     */
    public function update(int $id, string $name, ?string $description): array
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

    /**
     * Xóa danh mục. Sẽ thất bại nếu danh mục đang được sự kiện tham chiếu (FK constraint).
     *
     * @param int $id Mã danh mục cần xóa
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete(int $id): array
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
}
