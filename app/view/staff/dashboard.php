<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.php?action=login');
    exit;
}
if (($user['role'] ?? null) !== 'nhan_vien') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập trang nhân viên.'];
    header('Location: index.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nhân viên - TicketHub</title>
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
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php?action=staff">Nhân viên</a></li>
        <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-5">
  <?php require __DIR__ . '/../partials/toast.php'; ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <h1 class="h3 mb-1">Khu vực nhân viên</h1>
              <div class="text-muted small">Xin chào <strong><?= htmlspecialchars($user['name']) ?></strong>.</div>
            </div>
            <span class="badge th-badge-soft">Staff</span>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Tạo sự kiện</div>
                <div class="text-muted small">Nhập thông tin, lịch tổ chức, địa điểm.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Quản lý loại vé</div>
                <div class="text-muted small">Giá vé, số lượng, trạng thái mở bán.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Đơn hàng</div>
                <div class="text-muted small">Theo dõi đơn, xác nhận thanh toán (nếu có).</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Check-in</div>
                <div class="text-muted small">Quét QR, kiểm tra trạng thái vé.</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <div class="fw-semibold mb-2">Tài khoản</div>
          <div class="small text-muted mb-1">Email: <strong><?= htmlspecialchars($user['email']) ?></strong></div>
          <div class="small text-muted">Vai trò: <strong>nhân viên</strong></div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

