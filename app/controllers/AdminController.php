<?php

require_once __DIR__ . '/../model/User.php';

class AdminController
{
    private User $userModel;

    public function __construct($conn)
    {
        $this->userModel = new User($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function createStaff(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền thực hiện thao tác này.'];
            header('Location: index.php');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || $email === '' || $password === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin nhân viên.'];
            header('Location: index.php?action=admin');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'];
            header('Location: index.php?action=admin');
            exit;
        }

        $result = $this->userModel->register($fullName, $email, $password, null, null, 'nhan_vien');

        if ($result['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tạo tài khoản nhân viên thành công!'];
            header('Location: index.php?action=admin');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message'] ?? 'Tạo tài khoản thất bại.'];
        header('Location: index.php?action=admin');
        exit;
    }
}

