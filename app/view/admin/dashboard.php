<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.php?action=login');
    exit;
}
if (($user['role'] ?? null) !== 'quan_tri_vien') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập trang quản trị.'];
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
  <title>Quản trị - TicketHub</title>
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
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php?action=admin">Quản trị</a></li>
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
              <h1 class="h3 mb-1">Dashboard quản trị</h1>
              <div class="text-muted small">Xin chào <strong><?= htmlspecialchars($user['name']) ?></strong> (quản trị viên).</div>
            </div>
            <span class="badge th-badge-soft">Admin</span>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Quản lý người dùng</div>
                <div class="text-muted small">Thêm/sửa/xóa tài khoản nhân viên, khách hàng.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Duyệt sự kiện</div>
                <div class="text-muted small">Duyệt / từ chối sự kiện do nhân viên tạo.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Thống kê hệ thống</div>
                <div class="text-muted small">Doanh thu, số vé bán, top sự kiện.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-4 border border-light border-opacity-10">
                <div class="fw-semibold">Cấu hình</div>
                <div class="text-muted small">Danh mục, chính sách hoàn vé, cấu hình chung.</div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="card border-0 th-auth-card mt-4">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <h2 class="h5 mb-1">Tạo tài khoản nhân viên</h2>
              <div class="text-muted small">Nhân viên đăng nhập sẽ vào trang “Nhân viên”.</div>
            </div>
            <span class="badge th-badge-soft">Create Staff</span>
          </div>

          <form action="index.php?action=create_staff" method="POST" autocomplete="off" class="row g-3">
            <div class="col-md-6">
              <label class="form-label th-form-label" for="staffFullName">Họ và tên</label>
              <input class="form-control th-input-dark" type="text" id="staffFullName" name="full_name" required placeholder="Nguyễn Văn B">
            </div>
            <div class="col-md-6">
              <label class="form-label th-form-label" for="staffEmail">Email</label>
              <input class="form-control th-input-dark" type="email" id="staffEmail" name="email" required placeholder="nhanvien@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label th-form-label" for="staffPassword">Mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="staffPassword" name="password" required placeholder="Tối thiểu 6 ký tự">
            </div>
            <div class="col-md-6">
              <label class="form-label th-form-label" for="staffConfirmPassword">Xác nhận mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="staffConfirmPassword" name="confirm_password" required placeholder="Nhập lại mật khẩu">
            </div>
            <div class="col-12">
              <button class="btn btn-th-primary rounded-pill px-4" type="submit">Tạo nhân viên</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <div class="fw-semibold mb-2">Tài khoản</div>
          <div class="small text-muted mb-1">Email: <strong><?= htmlspecialchars($user['email']) ?></strong></div>
          <div class="small text-muted">Vai trò: <strong>quản trị viên</strong></div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

