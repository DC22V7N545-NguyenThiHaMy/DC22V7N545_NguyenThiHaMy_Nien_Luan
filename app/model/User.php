<?php

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Đăng ký người dùng mới.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function register($fullName, $email, $password, $phone = null, $address = null, $role = 'khach_hang') {
        // Kiểm tra xem email đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã tồn tại.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, vai_tro) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $fullName, $email, $hashedPassword, $phone, $address, $role);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đăng ký thành công!'];
        }

        return ['success' => false, 'message' => 'Lỗi: ' . $stmt->error];
    }

    /**
     * Đăng nhập người dùng.
     *
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login($email, $password) {
        // Xác thực qua DB — mật khẩu được kiểm tra bằng password_verify (Bcrypt)
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung, ho_ten, mat_khau, vai_tro FROM nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email không tồn tại.', 'user' => null];
        }

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['mat_khau'])) {
            return ['success' => true, 'message' => 'Đăng nhập thành công!', 'user' => $user];
        }

        return ['success' => false, 'message' => 'Mật khẩu không đúng.', 'user' => null];
    }

    /**
     * Lấy danh sách người dùng (phục vụ admin).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers(): array
    {
        $sql = "SELECT ma_nguoi_dung, ho_ten, email, so_dien_thoai, dia_chi, vai_tro, ngay_tao
                FROM nguoi_dung
                ORDER BY ma_nguoi_dung DESC";
        $result = $this->conn->query($sql);

        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Đổi mật khẩu người dùng.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword($userId, $oldPassword, $newPassword, $confirmPassword) {
        // Kiểm tra mật khẩu mới và xác nhận có khớp không
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp.'];
        }

        // Kiểm tra mật khẩu mới không được trống
        if (empty($newPassword) || strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'];
        }

        // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
        $stmt = $this->conn->prepare("SELECT mat_khau FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại.'];
        }

        $user = $result->fetch_assoc();

        // Xác minh mật khẩu cũ
        if (!password_verify($oldPassword, $user['mat_khau'])) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không đúng.'];
        }

        // Kiểm tra mật khẩu mới khác mật khẩu cũ
        if (password_verify($newPassword, $user['mat_khau'])) {
            return ['success' => false, 'message' => 'Mật khẩu mới không được giống mật khẩu cũ.'];
        }

        // Mã hóa mật khẩu mới
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu trong cơ sở dữ liệu
        $stmt = $this->conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare: ' . $this->conn->error];
        }

        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Lỗi execute: ' . $stmt->error];
        }

        $stmt->close();
        return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
    }

    /**
     * Cập nhật thông tin người dùng.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUser(int $userId, string $fullName, string $email, string $phone, string $address, string $role, string $password = '') {
        // Kiểm tra xem email cập nhật có bị trùng với user khác không
        $stmt = $this->conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? AND ma_nguoi_dung != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email đã tồn tại!'];
        }
        $stmt->close();

        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare(
                "UPDATE nguoi_dung SET ho_ten = ?, email = ?, so_dien_thoai = ?, dia_chi = ?, vai_tro = ?, mat_khau = ? WHERE ma_nguoi_dung = ?"
            );
            $stmt->bind_param("ssssssi", $fullName, $email, $phone, $address, $role, $hashedPassword, $userId);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE nguoi_dung SET ho_ten = ?, email = ?, so_dien_thoai = ?, dia_chi = ?, vai_tro = ? WHERE ma_nguoi_dung = ?"
            );
            $stmt->bind_param("sssssi", $fullName, $email, $phone, $address, $role, $userId);
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật thành công.'];
        }
        
        return ['success' => false, 'message' => 'Lỗi: ' . $stmt->error];
    }

    /**
     * Xóa người dùng.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser(int $id) {
        $stmt = $this->conn->prepare("DELETE FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $stmt->bind_param("i", $id);
        
        try {
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Đã xóa người dùng.'];
            }
            return ['success' => false, 'message' => 'Lỗi khi xóa người dùng.'];
        } catch (mysqli_sql_exception $e) {
            // Lỗi khóa ngoại (code 1451)
            if ($e->getCode() == 1451) {
                return ['success' => false, 'message' => 'Không thể xóa người dùng này vì họ đã có dữ liệu giao dịch hoặc sự kiện.'];
            }
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
        }
    }
}
