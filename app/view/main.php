<?php
// Main single-page view for the event ticket system.
// This page shows the home section and the login/register forms.

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nền tảng bán vé sự kiện</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body class="bg-landing">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="text-warning">Ticket</span>Hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="index.php?action=profile">Tài khoản</a></li>
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Đăng xuất</a></li>
        <?php else: ?>
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm me-2" href="index.php?action=login">Đăng nhập</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm" href="index.php?action=register">Đăng ký</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <section id="home" class="py-4 th-reveal">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <span class="badge th-hero-badge mb-2 th-floating-badge">Nền tảng bán vé sự kiện cho giới trẻ</span>
        <h1 class="display-5 fw-bold th-hero-title">Đặt vé cực nhanh, trải nghiệm cực chill</h1>
        <p class="lead text-muted mb-4">
          Quản lý sự kiện, loại vé, đơn hàng và check-in người tham dự trên một hệ thống duy nhất.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($user): ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=profile">Vào bảng điều khiển</a>
          <?php else: ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=register">Bắt đầu bán vé</a>
            <a class="btn btn-outline-light btn-lg btn-th-outline px-4" href="index.php?action=login">Tôi đã có tài khoản</a>
          <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-4 text-muted small">
          <div class="th-chip"><strong>+10</strong> loại vé khác nhau</div>
          <div class="th-chip"><strong>Thống kê</strong> theo thời gian thực</div>
          <div class="th-chip"><strong>QR check-in</strong> tại cổng</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 th-card-glass th-reveal">
          <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold">Sự kiện nổi bật</span>
              <span class="badge bg-warning text-dark">Đang mở bán</span>
            </div>
          </div>
          <div class="card-body">
            <div class="d-flex flex-column flex-md-row gap-3">
              <img src="https://images.unsplash.com/photo-1515165562835-c4c9e0737eaa?auto=format&fit=crop&w=900&q=80"
                   class="img-fluid rounded object-fit-cover"
                   alt="Sự kiện âm nhạc">
              <div>
                <h5 class="card-title mb-1">Live Concert 2026</h5>
                <p class="mb-2 text-muted small">20:00 • 30/04/2026 • Nhà hát lớn</p>
                <span class="badge bg-success mb-2">Còn vé</span>
                <p class="card-text small">
                  Vé thường, VIP, Backstage với mã QR được gửi ngay sau khi thanh toán.
                </p>
                <a href="#" class="btn btn-outline-dark btn-sm disabled">Xem chi tiết (demo)</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="features" class="py-5 th-reveal">
    <div class="text-center mb-4 th-reveal">
      <h2 class="fw-bold">Tính năng cho web bán vé</h2>
      <p class="text-muted">Thiết kế để phục vụ cả nhà tổ chức sự kiện và người mua vé.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Dành cho nhà tổ chức</span>
            <h5 class="card-title">Quản lý sự kiện & loại vé</h5>
            <p class="card-text">
              Tạo sự kiện, cấu hình số lượng vé, giá theo từng loại (Early Bird, VIP, Standard, v.v.)
              và theo dõi số vé đã bán.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Dành cho người mua</span>
            <h5 class="card-title">Đặt vé nhanh & bảo mật</h5>
            <p class="card-text">
              Giao diện thanh toán đơn giản, lưu thông tin đơn hàng và gửi mã vé điện tử ngay sau khi hoàn tất.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Vận hành</span>
            <h5 class="card-title">Check-in bằng QR code</h5>
            <p class="card-text">
              Mỗi vé tương ứng một mã QR duy nhất, giúp kiểm soát lượt vào cổng và tránh vé giả.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Nếu cần, có thể hiển thị một đoạn giới thiệu ngắn khi người dùng đã đăng nhập -->
</main>

<footer class="border-top py-3 mt-4 bg-white">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
    <span>© <?= date('Y') ?> TicketHub – Hệ thống bán vé sự kiện.</span>
    <span>Đồ án môn Niên Luận.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="/public/js/app.js"></script>
</body>
</html>
