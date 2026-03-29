<?php

class News
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function createNews(array $payload): array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO tin_tuc (ma_nguoi_tao, tieu_de, noi_dung, hinh_anh, trang_thai)
             VALUES (?, ?, ?, ?, 'da_duyet')"
        );
        $stmt->bind_param(
            "isss",
            $payload['ma_nguoi_tao'],
            $payload['tieu_de'],
            $payload['noi_dung'],
            $payload['hinh_anh']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Tạo tin tức thành công!'];
        }
        return ['success' => false, 'message' => 'Không thể tạo tin tức: ' . $stmt->error];
    }

    public function updateNews(array $payload): array
    {
        if (($payload['ma_tin_tuc'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Tin tức không hợp lệ.'];
        }

        $hasImage = isset($payload['hinh_anh']) && $payload['hinh_anh'] !== null && $payload['hinh_anh'] !== '';
        if ($hasImage) {
            $stmt = $this->conn->prepare(
                "UPDATE tin_tuc
                 SET tieu_de = ?, noi_dung = ?, hinh_anh = ?
                 WHERE ma_tin_tuc = ?"
            );
            $stmt->bind_param(
                "sssi",
                $payload['tieu_de'],
                $payload['noi_dung'],
                $payload['hinh_anh'],
                $payload['ma_tin_tuc']
            );
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE tin_tuc
                 SET tieu_de = ?, noi_dung = ?
                 WHERE ma_tin_tuc = ?"
            );
            $stmt->bind_param(
                "ssi",
                $payload['tieu_de'],
                $payload['noi_dung'],
                $payload['ma_tin_tuc']
            );
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật tin tức thành công!'];
        }

        return ['success' => false, 'message' => 'Không thể cập nhật tin tức: ' . $stmt->error];
    }

    public function deleteNews(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Tin tức không hợp lệ.'];
        }

        $stmt = $this->conn->prepare("UPDATE tin_tuc SET trang_thai = 'da_xoa' WHERE ma_tin_tuc = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa tin tức thành công!'];
        }

        return ['success' => false, 'message' => 'Không thể xóa tin tức: ' . $stmt->error];
    }

    public function getNews(): array
    {
        $result = $this->conn->query(
            "SELECT tt.*, nd.ho_ten as ten_nguoi_tao
             FROM tin_tuc tt
             LEFT JOIN nguoi_dung nd ON tt.ma_nguoi_tao = nd.ma_nguoi_dung
             WHERE tt.trang_thai != 'da_xoa'
             ORDER BY tt.ngay_tao DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getNewsForHome(int $limit = 6): array
    {
        $stmt = $this->conn->prepare(
            "SELECT tt.*, nd.ho_ten as ten_nguoi_tao
             FROM tin_tuc tt
             LEFT JOIN nguoi_dung nd ON tt.ma_nguoi_tao = nd.ma_nguoi_dung
             WHERE tt.trang_thai = 'da_duyet'
             ORDER BY tt.ngay_tao DESC
             LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getNewsDetail(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT tt.*, nd.ho_ten as ten_nguoi_tao
             FROM tin_tuc tt
             LEFT JOIN nguoi_dung nd ON tt.ma_nguoi_tao = nd.ma_nguoi_dung
             WHERE tt.ma_tin_tuc = ? AND tt.trang_thai = 'da_duyet'"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }
}