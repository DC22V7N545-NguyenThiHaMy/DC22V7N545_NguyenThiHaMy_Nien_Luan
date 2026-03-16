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
        // Tài khoản quản trị mặc định (phục vụ demo/đồ án)
        if ($email === 'admin@gmail.com' && $password === 'admin') {
            return [
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'user' => [
                    'ma_nguoi_dung' => 0,
                    'ho_ten' => 'Admin',
                    'email' => $email,
                    'vai_tro' => 'quan_tri_vien',
                ],
            ];
        }

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
}
