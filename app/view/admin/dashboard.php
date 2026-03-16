<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../model/User.php';

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

$userModel = isset($conn) ? new User($conn) : null;
$users = $userModel ? $userModel->getAllUsers() : [];
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

<main class="container py-4 th-admin-shell">
  <?php require __DIR__ . '/../partials/toast.php'; ?>

  <div class="row g-4">
    <div class="col-lg-3">
      <div class="th-admin-sidebar p-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <div class="fw-semibold">Admin Panel</div>
            <div class="text-muted small"><?= htmlspecialchars($user['name']) ?></div>
          </div>
          <span class="badge th-badge-soft">Admin</span>
        </div>

        <div class="nav flex-column gap-2">
          <a class="nav-link th-admin-link active" href="#" data-admin-target="overview">Tổng quan</a>
          <a class="nav-link th-admin-link" href="#" data-admin-target="users">Người dùng</a>
          <a class="nav-link th-admin-link" href="#" data-admin-target="events">Sự kiện</a>
          <a class="nav-link th-admin-link" href="#" data-admin-target="reports">Thống kê</a>
          <a class="nav-link th-admin-link" href="#" data-admin-target="settings">Cài đặt</a>
        </div>

        <div class="border-top border-light border-opacity-10 mt-3 pt-3 small text-muted">
          Email: <strong><?= htmlspecialchars($user['email']) ?></strong>
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <!-- OVERVIEW -->
      <section data-admin-pane="overview">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h1 class="h3 mb-1">Tổng quan hệ thống</h1>
                <div class="text-muted small">Biểu đồ doanh thu & đơn hàng (demo).</div>
              </div>
              <a class="btn btn-outline-light btn-sm rounded-pill" href="index.php?action=logout">Đăng xuất</a>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Doanh thu tháng</div>
                  <div class="h4 mb-0">35 triệu</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Đơn hàng</div>
                  <div class="h4 mb-0">102</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Sự kiện đang bán</div>
                  <div class="h4 mb-0">8</div>
                </div>
              </div>
            </div>

            <div class="p-3 rounded-4 border border-light border-opacity-10" style="height: 320px;">
              <canvas id="adminRevenueChart"></canvas>
            </div>
          </div>
        </div>
      </section>

      <!-- USERS -->
      <section class="d-none" data-admin-pane="users">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h2 class="h4 mb-1">Người dùng</h2>
                <div class="text-muted small">Danh sách tài khoản đã đăng ký + tạo mới khách hàng/nhân viên.</div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge th-badge-soft">Users</span>
                <button class="btn btn-th-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">
                  Tạo tài khoản
                </button>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Họ tên</th>
                    <th scope="col">Email</th>
                    <th scope="col">SĐT</th>
                    <th scope="col">Địa chỉ</th>
                    <th scope="col">Vai trò</th>
                    <th scope="col">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$users): ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">Chưa có người dùng nào trong hệ thống.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($users as $u): ?>
                      <tr>
                        <td><?= htmlspecialchars((string)$u['ma_nguoi_dung']) ?></td>
                        <td><?= htmlspecialchars((string)$u['ho_ten']) ?></td>
                        <td><?= htmlspecialchars((string)$u['email']) ?></td>
                        <td><?= htmlspecialchars((string)($u['so_dien_thoai'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($u['dia_chi'] ?? '')) ?></td>
                        <td>
                          <?php
                            $r = $u['vai_tro'] ?? '';
                            $label = $r === 'quan_tri_vien' ? 'Admin' : ($r === 'nhan_vien' ? 'Nhân viên' : 'Khách hàng');
                            $badge = $r === 'quan_tri_vien' ? 'danger' : ($r === 'nhan_vien' ? 'info' : 'secondary');
                          ?>
                          <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($label) ?></span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars((string)($u['ngay_tao'] ?? '')) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- Create User Modal -->
      <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark text-light border border-light border-opacity-10 rounded-4">
            <div class="modal-header border-0">
              <h5 class="modal-title">Tạo tài khoản</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="index.php?action=create_user" method="POST" autocomplete="off" class="row g-3">
                <div class="col-12">
                  <label class="form-label th-form-label" for="createFullName">Họ và tên</label>
                  <input class="form-control th-input-dark" type="text" id="createFullName" name="full_name" required placeholder="Nguyễn Văn B">
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="createEmail">Email</label>
                  <input class="form-control th-input-dark" type="email" id="createEmail" name="email" required placeholder="user@example.com">
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="createRole">Quyền</label>
                  <select class="form-select th-input-dark" id="createRole" name="role" required>
                    <option value="khach_hang" selected>Khách hàng</option>
                    <option value="nhan_vien">Nhân viên</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="createPassword">Mật khẩu</label>
                  <input class="form-control th-input-dark" type="password" id="createPassword" name="password" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" title="Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt" placeholder="VD: Abc@1234">
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="createConfirmPassword">Xác nhận mật khẩu</label>
                  <input class="form-control th-input-dark" type="password" id="createConfirmPassword" name="confirm_password" required minlength="8" placeholder="Nhập lại mật khẩu">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                  <button type="button" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                  <button class="btn btn-th-primary rounded-pill px-4" type="submit">Tạo</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- EVENTS -->
      <section class="d-none" data-admin-pane="events">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h2 class="h4 mb-1">Sự kiện</h2>
                <div class="text-muted small">Khu vực duyệt sự kiện (demo UI).</div>
              </div>
              <span class="badge th-badge-soft">Events</span>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="fw-semibold">Live Concert 2026</div>
                  <div class="text-muted small mb-2">Trạng thái: chờ duyệt</div>
                  <button class="btn btn-outline-light btn-sm rounded-pill me-2" type="button" disabled>Duyệt</button>
                  <button class="btn btn-outline-danger btn-sm rounded-pill" type="button" disabled>Từ chối</button>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="fw-semibold">Tech Conference</div>
                  <div class="text-muted small mb-2">Trạng thái: đã duyệt</div>
                  <button class="btn btn-outline-light btn-sm rounded-pill" type="button" disabled>Xem</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- REPORTS -->
      <section class="d-none" data-admin-pane="reports">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h2 class="h4 mb-1">Thống kê</h2>
                <div class="text-muted small">Báo cáo doanh thu / vé / đơn hàng (demo UI).</div>
              </div>
              <span class="badge th-badge-soft">Reports</span>
            </div>

            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Top sự kiện</div>
                  <div class="fw-semibold">Live Concert 2026</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Tỷ lệ hoàn vé</div>
                  <div class="fw-semibold">2.1%</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 rounded-4 border border-light border-opacity-10">
                  <div class="text-muted small">Tổng vé bán</div>
                  <div class="fw-semibold">1,248</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SETTINGS -->
      <section class="d-none" data-admin-pane="settings">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h2 class="h4 mb-1">Cài đặt</h2>
                <div class="text-muted small">Cấu hình hệ thống (demo UI).</div>
              </div>
              <span class="badge th-badge-soft">Settings</span>
            </div>

            <div class="p-3 rounded-4 border border-light border-opacity-10">
              <div class="fw-semibold">Chưa triển khai</div>
              <div class="text-muted small">Bạn có thể thêm cấu hình danh mục, chính sách, v.v.</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="/public/js/admin.js"></script>
</body>
</html>

