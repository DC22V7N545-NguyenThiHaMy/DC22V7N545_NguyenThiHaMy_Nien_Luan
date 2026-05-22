<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../model/EventTicket.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.php?action=login');
    exit;
}
if (!in_array($user['role'] ?? null, ['quan_tri_vien', 'nhan_vien'], true)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền truy cập trang quản trị.'];
    header('Location: index.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$userModel        = isset($conn) ? new User($conn) : null;
$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;
$users            = $userModel        ? $userModel->getAllUsers()    : [];
$pendingOrders    = $eventTicketModel ? $eventTicketModel->getPendingOrders() : [];

// Thống kê cho overview
$statType  = $_GET['stat_type']  ?? 'month';
$statValue = $_GET['stat_value'] ?? date('Y-m');
$stats     = $eventTicketModel ? $eventTicketModel->getStatistics($statType, $statValue) : ['summary'=>[],'orders'=>[],'by_event'=>[]];
$summary   = $stats['summary']  ?? [];
$byEvent   = $stats['by_event'] ?? [];
$statOrders = $stats['orders']  ?? [];

$statTypeLabel = match($statType) {
    'day'   => 'Ngày ' . date('d/m/Y', strtotime($statValue)),
    'month' => 'Tháng ' . date('m/Y', strtotime($statValue . '-01')),
    'year'  => 'Năm ' . $statValue,
    default => $statValue,
};
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
      <?php require __DIR__ . '/partials/sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
      <!-- OVERVIEW -->
      <section data-admin-pane="overview">
        <div class="card border-0 th-auth-card">
          <div class="card-body">

            <!-- Bộ lọc thống kê -->
            <form method="GET" action="index.php" class="d-flex flex-wrap gap-2 align-items-end mb-4">
              <input type="hidden" name="action" value="admin">
              <input type="hidden" name="tab" value="overview">
              <div>
                <label class="form-label small mb-1">Loại</label>
                <select name="stat_type" class="form-select form-select-sm th-input-dark" onchange="syncValuePlaceholder(this)">
                  <option value="day"   <?= $statType==='day'   ? 'selected':'' ?>>Theo ngày</option>
                  <option value="month" <?= $statType==='month' ? 'selected':'' ?>>Theo tháng</option>
                  <option value="year"  <?= $statType==='year'  ? 'selected':'' ?>>Theo năm</option>
                </select>
              </div>
              <div>
                <label class="form-label small mb-1">Giá trị</label>
                <input type="text" name="stat_value" value="<?= htmlspecialchars($statValue) ?>"
                       class="form-control form-control-sm th-input-dark" style="width:150px"
                       placeholder="<?= $statType==='day' ? 'vd: 2026-03-22' : ($statType==='year' ? 'vd: 2026' : 'vd: 2026-03') ?>">
              </div>
              <button type="submit" class="btn btn-warning btn-sm px-3">Xem</button>
              <a href="index.php?action=export_statistics&type=<?= urlencode($statType) ?>&value=<?= urlencode($statValue) ?>"
                 class="btn btn-success btn-sm px-3">⬇ Xuất Excel</a>
            </form>

            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h1 class="h4 mb-0">Tổng quan — <?= htmlspecialchars($statTypeLabel) ?></h1>
                <div class="text-muted small">Chỉ tính đơn đã xác nhận thanh toán</div>
              </div>
            </div>

            <!-- 4 card số liệu -->
            <div class="row g-3 mb-4">
              <div class="col-md-3 col-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10 text-center">
                  <div class="h4 mb-0 text-warning"><?= number_format((int)($summary['tong_don'] ?? 0)) ?></div>
                  <div class="text-muted small mt-1">Đơn đã thanh toán</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10 text-center">
                  <div class="h4 mb-0 text-warning"><?= number_format((int)($summary['tong_ve'] ?? 0)) ?></div>
                  <div class="text-muted small mt-1">Vé đã bán</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10 text-center">
                  <div class="h4 mb-0 text-warning"><?= count($pendingOrders) ?></div>
                  <div class="text-muted small mt-1">Đơn chờ xác nhận</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="p-3 rounded-4 border border-light border-opacity-10 text-center">
                  <div class="h5 mb-0 text-warning"><?= number_format((float)($summary['tong_doanh_thu'] ?? 0), 0, ',', '.') ?> đ</div>
                  <div class="text-muted small mt-1">Doanh thu</div>
                </div>
              </div>
            </div>

            <!-- Doanh thu theo sự kiện -->
            <?php if (!empty($byEvent)): ?>
            <div class="mb-4">
              <div class="fw-semibold small mb-2">Doanh thu theo sự kiện</div>
              <?php
                $maxRev = max(array_column($byEvent, 'doanh_thu')) ?: 1;
                foreach ($byEvent as $ev):
                  $pct = round((float)$ev['doanh_thu'] / $maxRev * 100);
              ?>
              <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="text-truncate" style="max-width:60%"><?= htmlspecialchars((string)$ev['ten_su_kien']) ?></span>
                  <span class="text-warning"><?= number_format((float)$ev['doanh_thu'], 0, ',', '.') ?> đ · <?= (int)$ev['so_ve'] ?> vé</span>
                </div>
                <div class="rounded-pill" style="background:rgba(255,255,255,.08);height:8px">
                  <div class="rounded-pill" style="width:<?= e($pct) ?>%;height:8px;background:linear-gradient(90deg,#667eea,#f0c040)"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Bảng đơn hàng -->
            <div class="fw-semibold small mb-2">Chi tiết đơn hàng</div>
            <?php if (empty($statOrders)): ?>
              <div class="text-muted small">Không có đơn hàng nào trong kỳ này.</div>
            <?php else: ?>
            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle mb-0" style="font-size:.83rem">
                <thead>
                  <tr>
                    <th>#</th><th>Khách hàng</th><th>Chi tiết vé</th>
                    <th class="text-end">Số tiền</th><th>Ngày</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($statOrders as $o): ?>
                  <tr>
                    <td class="text-warning">#<?= (int)$o['ma_don_hang'] ?></td>
                    <td>
                      <div><?= htmlspecialchars((string)$o['ho_ten']) ?></div>
                      <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars((string)$o['email']) ?></div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars((string)$o['chi_tiet_ve']) ?></td>
                    <td class="text-end fw-semibold text-warning"><?= number_format((float)$o['tong_tien'], 0, ',', '.') ?> đ</td>
                    <td class="text-muted"><?= htmlspecialchars(substr((string)$o['ngay_tao'], 0, 16)) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr style="border-top:1px solid rgba(255,255,255,.15)">
                    <td colspan="3" class="fw-bold">Tổng cộng</td>
                    <td class="text-end fw-bold text-warning"><?= number_format((float)($summary['tong_doanh_thu'] ?? 0), 0, ',', '.') ?> đ</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </section>

      <!-- ORDERS (PENDING) -->
      <section class="d-none" data-admin-pane="orders">
        <div class="card border-0 th-auth-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h2 class="h4 mb-1">Thanh toán</h2>
                <div class="text-muted small">Danh sách đơn chờ thanh toán.</div>
              </div>
              <span class="badge th-badge-soft"><?= count($pendingOrders) ?> đơn</span>
            </div>

            <!-- Filter by Date -->
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small">Lọc theo ngày</label>
                <input type="text" id="filterDate" class="form-control form-control-sm th-input-dark" placeholder="dd/mm/yyyy">
              </div>
              <div class="col-md-6">
                <label class="form-label small">Lọc theo giờ</label>
                <input type="number" id="filterHour" min="0" max="23" class="form-control form-control-sm th-input-dark" placeholder="0-23 (không bắt buộc)">
              </div>
            </div>
            <div class="mb-3">
              <button onclick="filterOrders()" class="btn btn-sm btn-outline-light">Lọc đơn hàng</button>
            </div>

            <!-- Delete All Button -->
            <div class="mb-3">
              <form action="index.php?action=delete_all_orders" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa tất cả đơn chờ thanh toán không?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger rounded-2">Xóa tất cả</button>
              </form>
            </div>

            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle mb-0" id="ordersTable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Khách hàng</th>
                    <th scope="col">Email</th>
                    <th scope="col">Số tiền</th>
                    <th scope="col">Ngày tạo</th>
                    <th scope="col">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$pendingOrders): ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted py-4">Không có đơn hàng nào chờ thanh toán.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($pendingOrders as $order): ?>
                      <tr class="order-row" data-date="<?= htmlspecialchars((string)$order['ngay_tao']) ?>">
                        <td><?= htmlspecialchars((string)$order['ma_don_hang']) ?></td>
                        <td><?= htmlspecialchars((string)$order['ho_ten']) ?></td>
                        <td><?= htmlspecialchars((string)$order['email']) ?></td>
                        <td class="fw-semibold"><?= number_format((float)$order['tong_tien']) ?> ₫</td>
                        <td class="text-muted small"><?= htmlspecialchars((string)$order['ngay_tao']) ?></td>
                        <td>
                          <div class="d-flex gap-2">
                            <form action="index.php?action=confirm_order_payment" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                              <input type="hidden" name="order_id" value="<?= (int)$order['ma_don_hang'] ?>">
                              <button type="submit" class="btn btn-sm btn-success rounded-2" title="Thanh toán">
                                <span class="small">✓</span>
                              </button>
                            </form>
                            <form action="index.php?action=delete_order" method="POST" style="display:inline;" onsubmit="return confirm('Xóa đơn này không?');">
                <?= csrf_field() ?>
                              <input type="hidden" name="order_id" value="<?= (int)$order['ma_don_hang'] ?>">
                              <button type="submit" class="btn btn-sm btn-danger rounded-2" title="Xóa đơn">
                                <span class="small">×</span>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- USERS (Chỉ Admin) -->
      <?php if (($user['role'] ?? null) === 'quan_tri_vien'): ?>
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
                    <th scope="col" class="text-end">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$users): ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted py-4">Chưa có người dùng nào trong hệ thống.</td>
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
                          <span class="badge bg-<?= e($badge) ?>"><?= htmlspecialchars($label) ?></span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars((string)($u['ngay_tao'] ?? '')) ?></td>
                        <td class="text-end">
                          <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-sm btn-outline-info rounded-2" title="Sửa" onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u)) ?>)">
                              <span class="small">✎</span>
                            </button>
                            <form action="index.php?action=delete_user" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này không?');">
                <?= csrf_field() ?>
                              <input type="hidden" name="user_id" value="<?= (int)$u['ma_nguoi_dung'] ?>">
                              <button type="submit" class="btn btn-sm btn-danger rounded-2" title="Xóa">
                                <span class="small">×</span>
                              </button>
                            </form>
                          </div>
                        </td>
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
                <?= csrf_field() ?>
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

      <!-- Edit User Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark text-light border border-light border-opacity-10 rounded-4">
            <div class="modal-header border-0">
              <h5 class="modal-title">Cập nhật người dùng</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="index.php?action=update_user" method="POST" autocomplete="off" class="row g-3">
                <?= csrf_field() ?>
                <input type="hidden" id="editUserId" name="user_id" value="">
                <div class="col-12">
                  <label class="form-label th-form-label" for="editFullName">Họ và tên</label>
                  <input class="form-control th-input-dark" type="text" id="editFullName" name="full_name" required>
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="editEmail">Email</label>
                  <input class="form-control th-input-dark" type="email" id="editEmail" name="email" required>
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="editPhone">Số điện thoại</label>
                  <input class="form-control th-input-dark" type="text" id="editPhone" name="phone">
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="editAddress">Địa chỉ</label>
                  <input class="form-control th-input-dark" type="text" id="editAddress" name="address">
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="editRole">Quyền</label>
                  <select class="form-select th-input-dark" id="editRole" name="role" required>
                    <option value="khach_hang">Khách hàng</option>
                    <option value="nhan_vien">Nhân viên</option>
                    <option value="quan_tri_vien">Admin</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label th-form-label" for="editPassword">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                  <input class="form-control th-input-dark" type="password" id="editPassword" name="password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" title="Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt" placeholder="Nhập để đổi mật khẩu">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                  <button type="button" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                  <button class="btn btn-th-primary rounded-pill px-4" type="submit">Lưu</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

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

<script>
// Filter orders by date
function filterOrders() {
  let dateInput = document.getElementById('filterDate')?.value || '';
  const hourInput = document.getElementById('filterHour')?.value || '';

  // Convert dd/mm/yyyy to yyyy-mm-dd if needed
  if (dateInput && dateInput.includes('/')) {
    const [dd, mm, yyyy] = dateInput.split('/');
    if (dd && mm && yyyy) {
      dateInput = `${yyyy}-${mm.padStart(2, '0')}-${dd.padStart(2, '0')}`;
    }
  }

  const rows = document.querySelectorAll('#ordersTable tbody .order-row');
  
  rows.forEach(row => {
    const dateTimeString = row.getAttribute('data-date') || '';
    const dateString = dateTimeString.split(' ')[0];
    const timeString = dateTimeString.split(' ')[1] || '';
    const hour = timeString.split(':')[0];

    let isVisible = true;

    if (dateInput && dateString !== dateInput) {
      isVisible = false;
    }
    
    if (hourInput && hour !== hourInput.padStart(2, '0')) {
      isVisible = false;
    }

    row.style.display = isVisible ? '' : 'none';
  });
}
</script>
<script>
function syncValuePlaceholder(sel) {
  const inp = sel.closest('form').querySelector('[name="stat_value"]');
  const now = new Date();
  const pad = n => String(n).padStart(2,'0');
  if (sel.value === 'day')   { inp.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`; inp.placeholder = 'vd: 2026-03-22'; }
  if (sel.value === 'month') { inp.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}`; inp.placeholder = 'vd: 2026-03'; }
  if (sel.value === 'year')  { inp.value = `${now.getFullYear()}`; inp.placeholder = 'vd: 2026'; }
}

function openEditUserModal(user) {
  document.getElementById('editUserId').value = user.ma_nguoi_dung;
  document.getElementById('editFullName').value = user.ho_ten;
  document.getElementById('editEmail').value = user.email;
  document.getElementById('editPhone').value = user.so_dien_thoai || '';
  document.getElementById('editAddress').value = user.dia_chi || '';
  document.getElementById('editRole').value = user.vai_tro;
  document.getElementById('editPassword').value = '';
  
  const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
  modal.show();
}
</script>
</body>
</html>

