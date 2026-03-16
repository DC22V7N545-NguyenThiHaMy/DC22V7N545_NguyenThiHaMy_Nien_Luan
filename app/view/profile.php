<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header('Location: index.php?action=login');
    exit;
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tài khoản - TicketHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body class="th-page-gradient">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="text-warning">Ticket</span>Hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#features">Tính năng</a></li>
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php?action=profile">Tài khoản</a></li>
        <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-5">
  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 th-auth-card th-reveal">
        <div class="card-body">
          <h1 class="h3 mb-3">Thông tin tài khoản</h1>
          <p class="mb-1">Họ tên: <strong><?= htmlspecialchars($user['name']) ?></strong></p>
          <p class="mb-1">Email: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
          <p class="mb-3">
            Vai trò:
            <span class="badge bg-secondary">
              <?= htmlspecialchars($user['role'] ?? 'khách') ?>
            </span>
          </p>
          <p class="text-muted small mb-0">
            Đây là trang dashboard tài khoản. Bạn có thể mở rộng thêm các chức năng bên dưới cho đồ án:
          </p>
          <ul class="text-muted small mb-0">
            <li>Quản lý danh sách sự kiện bạn tạo.</li>
            <li>Xem các đơn đặt vé và trạng thái thanh toán.</li>
            <li>Xuất báo cáo doanh thu, số lượng vé đã bán.</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 th-auth-card mb-3 th-reveal">
        <div class="card-body">
          <h6 class="text-uppercase text-muted small mb-3">Trạng thái tài khoản</h6>
          <p class="mb-1"><span class="badge bg-success">Đã đăng nhập</span></p>
          <p class="small text-muted mb-0">
            Bạn có thể thêm mục “Đổi mật khẩu”, “Cập nhật thông tin” tại đây.
          </p>
        </div>
      </div>

      <div class="card border-0 th-auth-card th-reveal">
        <div class="card-body">
          <h6 class="text-uppercase text-muted small mb-3">Menu quản lý (gợi ý)</h6>
          <ul class="list-unstyled small mb-0">
            <li class="mb-1">• Sự kiện của tôi</li>
            <li class="mb-1">• Đơn hàng / vé đã bán</li>
            <li class="mb-1">• Cấu hình loại vé</li>
            <li class="mb-1">• Cài đặt hệ thống</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="border-top py-3 mt-4 bg-white">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
    <span>© <?= date('Y') ?> TicketHub – Hệ thống bán vé sự kiện.</span>
    <span>Đồ án môn Niên luận.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

