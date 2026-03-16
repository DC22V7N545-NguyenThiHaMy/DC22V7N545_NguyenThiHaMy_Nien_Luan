<?php

require_once 'app/model/User.php';

class AuthController {
    private $userModel;

    public function __construct($conn) {
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
            $_SESSION['user'] = ['email' => $email, 'name' => $result['user']['ho_ten'], 'role' => $result['user']['vai_tro']];
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
}
