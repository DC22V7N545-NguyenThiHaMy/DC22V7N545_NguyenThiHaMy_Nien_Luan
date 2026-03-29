<?php

require_once 'app/model/User.php';

class AuthController {
    private $userModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new User($conn);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Phương thức không hợp lệ.'];
            header('Location: index.php');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc.'];
            header('Location: index.php?action=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không đúng định dạng.'];
            header('Location: index.php?action=register');
            exit;
        }

        // SĐT: cho phép bỏ trống, nhưng nếu nhập thì phải đúng định dạng VN (0 + 9 số)
        if ($phone !== '' && !preg_match('/^0\d{9}$/', $phone)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Số điện thoại không đúng định dạng (VD: 0901234567).'];
            header('Location: index.php?action=register');
            exit;
        }

        // Mật khẩu: >= 8 ký tự, có hoa + thường + số + ký tự đặc biệt
        $pwOk = (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password);
        if (!$pwOk) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt.'];
            header('Location: index.php?action=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'];
            header('Location: index.php?action=register');
            exit;
        }

        $result = $this->userModel->register($fullName, $email, $password, $phone ?: null, $address ?: null);

        if ($result['success']) {
            $_SESSION['user'] = ['email' => $email, 'name' => $fullName];
            $_SESSION['flash'] = ['type' => 'success', 'message' => $result['message']];
            header('Location: index.php');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message']];
        header('Location: index.php?action=register');
        exit;
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Phương thức không hợp lệ.'];
            header('Location: index.php');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = $this->userModel->login($email, $password);

        if ($result['success']) {
            $_SESSION['user'] = [
                'ma_nguoi_dung' => $result['user']['ma_nguoi_dung'],
                'email' => $email,
                'name' => $result['user']['ho_ten'],
                'role' => $result['user']['vai_tro']
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => $result['message']];
            $role = $_SESSION['user']['role'] ?? 'khach_hang';
            if ($role === 'quan_tri_vien') {
                header('Location: index.php?action=admin');
                exit;
            }
            if ($role === 'nhan_vien') {
                header('Location: index.php?action=staff');
                exit;
            }
            header('Location: index.php?action=profile');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message']];
        header('Location: index.php?action=login');
        exit;
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Phương thức không hợp lệ.'];
            header('Location: index.php?action=profile');
            exit;
        }

        // Kiểm tra người dùng đã đăng nhập
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['ma_nguoi_dung'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn cần đăng nhập để đổi mật khẩu.'];
            header('Location: index.php?action=login');
            exit;
        }

        $userId = $_SESSION['user']['ma_nguoi_dung'];
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đủ thông tin.'];
            header('Location: index.php?action=profile');
            exit;
        }

        $changeResult = $this->userModel->changePassword($userId, $oldPassword, $newPassword, $confirmPassword);

        $_SESSION['flash'] = ['type' => $changeResult['success'] ? 'success' : 'danger', 'message' => $changeResult['message']];
        header('Location: index.php?action=profile');
        exit;
    }
}
