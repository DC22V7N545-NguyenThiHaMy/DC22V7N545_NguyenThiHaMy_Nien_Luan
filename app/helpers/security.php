<?php

/**
 * Các hàm bảo mật dùng chung cho toàn hệ thống.
 * Include file này ở đầu routes/web.php để sử dụng ở mọi nơi.
 */



/**
 * Escape dữ liệu trước khi hiển thị ra HTML.
 * Ngăn chặn XSS bằng cách chuyển đổi các ký tự đặc biệt HTML.
 *
 * Cách dùng trong View:
 *   <p><?= e($tenSuKien) ?></p>
 *   <input value="<?= e($moTa) ?>">
 *
 * @param  string|null $value Giá trị cần escape
 * @return string Giá trị đã escape, an toàn để hiển thị trong HTML
 */
function e(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}



/**
 * Tạo hoặc lấy CSRF token từ session.
 * Token được tạo 1 lần cho mỗi session và lưu trong $_SESSION.
 *
 * @return string CSRF token
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Tạo token mới nếu chưa có
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Tạo hidden input chứa CSRF token, dùng trong form HTML.
 *
 * Cách dùng trong View:
 *   <form method="POST">
 *       <?= csrf_field() ?>
 *       ...
 *   </form>
 *
 * @return string HTML hidden input
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Xác thực CSRF token từ request POST.
 * So sánh token trong form với token trong session.
 *
 * @return bool true nếu token hợp lệ, false nếu không
 */
function csrf_verify(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? '';

    // Token phải tồn tại và khớp nhau (dùng hash_equals để chống timing attack)
    if ($sessionToken === '' || $requestToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $requestToken);
}

/**
 * Kiểm tra CSRF token và redirect nếu không hợp lệ.
 * Dùng ở đầu mỗi POST action trong router/controller.
 *
 * @param string $redirectUrl URL redirect khi token không hợp lệ
 * @return void
 */
function csrf_protect(string $redirectUrl = 'index.php'): void
{
    if (!csrf_verify()) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Phiên làm việc không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.',
        ];
        header('Location: ' . $redirectUrl);
        exit;
    }
}
