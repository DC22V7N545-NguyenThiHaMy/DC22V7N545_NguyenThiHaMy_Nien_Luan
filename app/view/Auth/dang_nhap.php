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
  <title>Đăng nhập - TicketHub</title>
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
        <li class="nav-item"><a class="nav-link" href="index.php?action=register">Đăng ký</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <?php require __DIR__ . '/../partials/toast.php'; ?>

      <div class="card border-0 th-auth-card th-reveal">
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <span class="badge bg-warning text-dark mb-2">Đăng nhập tài khoản</span>
            <h2 class="h4 mb-0">Chào mừng trở lại</h2>
            <p class="text-muted small mb-0">Truy cập bảng điều khiển bán vé của bạn.</p>
          </div>

          <form action="index.php?action=login" method="POST" autocomplete="off">
            <div class="mb-3">
              <label class="form-label th-form-label" for="loginEmail">Email</label>
              <input class="form-control th-input-dark" type="email" id="loginEmail" name="email" required placeholder="you@example.com">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="loginPassword">Mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="loginPassword" name="password" required placeholder="••••••••">
            </div>
            <button class="btn btn-th-primary w-100 rounded-pill" type="submit">Đăng nhập</button>
          </form>

          <p class="text-center text-muted small mt-3 mb-0">
            Chưa có tài khoản?
            <a href="index.php?action=register">Đăng ký ngay</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
