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

    public function createUser(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền thực hiện thao tác này.'];
            header('Location: index.php');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'khach_hang');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!in_array($role, ['khach_hang', 'nhan_vien'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Quyền không hợp lệ.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if ($fullName === '' || $email === '' || $password === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin tài khoản.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không đúng định dạng.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $pwOk = (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password);
        if (!$pwOk) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $result = $this->userModel->register($fullName, $email, $password, null, null, $role);

        if ($result['success']) {
            $roleLabel = $role === 'nhan_vien' ? 'nhân viên' : 'khách hàng';
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tạo tài khoản ' . $roleLabel . ' thành công!'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message'] ?? 'Tạo tài khoản thất bại.'];
        header('Location: index.php?action=admin&tab=users');
        exit;
    }
}

