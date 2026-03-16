<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng ký - TicketHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body class="th-page-gradient">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="text-warning">Ticket</span>Hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=login">Đăng nhập</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <?php require __DIR__ . '/../partials/toast.php'; ?>

      <div class="card border-0 th-auth-card th-reveal">
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <span class="badge bg-warning text-dark mb-2">Tạo tài khoản nhà tổ chức</span>
            <h2 class="h4 mb-0">Bắt đầu bán vé</h2>
            <p class="text-muted small mb-0">Quản lý sự kiện và đơn hàng trên TicketHub.</p>
          </div>

          <form action="index.php?action=register" method="POST" autocomplete="off">
            <div class="mb-3">
              <label class="form-label th-form-label" for="fullName">Họ và tên</label>
              <input class="form-control th-input-dark" type="text" id="fullName" name="full_name" required placeholder="Nguyễn Văn A">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="registerEmail">Email</label>
              <input class="form-control th-input-dark" type="email" id="registerEmail" name="email" required placeholder="you@example.com">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="phone">Số điện thoại</label>
              <input class="form-control th-input-dark" type="tel" id="phone" name="phone" placeholder="VD: 0901234567" pattern="^0\d{9}$" title="SĐT phải có 10 số và bắt đầu bằng 0 (VD: 0901234567)">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="address">Địa chỉ</label>
              <input class="form-control th-input-dark" type="text" id="address" name="address" placeholder="VD: 123 Lê Lợi, Q1, TP.HCM">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="registerPassword">Mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="registerPassword" name="password" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" title="Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt" placeholder="VD: Abc@1234">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="confirmPassword">Xác nhận mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="confirmPassword" name="confirm_password" required minlength="8" placeholder="Nhập lại mật khẩu">
            </div>
            <button class="btn btn-th-primary w-100 rounded-pill" type="submit">Đăng ký</button>
          </form>

          <p class="text-center text-muted small mt-3 mb-0">
            Đã có tài khoản?
            <a href="index.php?action=login">Đăng nhập</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
