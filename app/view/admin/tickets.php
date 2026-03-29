<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../model/EventTicket.php';

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

$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;
$events = $eventTicketModel ? $eventTicketModel->getEvents() : [];
$ticketTypes = $eventTicketModel ? $eventTicketModel->getTicketTypesForAdmin() : [];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý loại vé - TicketHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body class="th-page-gradient">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="text-warning">Ticket</span>Hub</a>
    <a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Đăng xuất</a>
  </div>
</nav>

<main class="container py-4 th-admin-shell">
  <?php require __DIR__ . '/../partials/toast.php'; ?>

  <div class="row g-4">
    <div class="col-lg-3">
      <?php require __DIR__ . '/partials/sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
      <div class="row g-3 mb-4">
        <div class="col-lg-12">
          <div class="card border-0 th-auth-card h-100">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Thêm loại vé</h2>
                <button class="btn btn-th-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#createTicketBox">Thêm</button>
              </div>
              <div class="collapse" id="createTicketBox">
                <form action="index.php?action=create_ticket_type" method="POST" class="row g-2" onsubmit="return confirm('Xác nhận thêm loại vé mới?');">
                  <div class="col-12">
                    <label class="form-label th-form-label mb-1">Sự kiện</label>
                    <select class="form-select th-input-dark" name="ma_su_kien" required>
                      <option value="">Chọn sự kiện</option>
                      <?php foreach ($events as $ev): ?>
                        <option value="<?= (int)$ev['ma_su_kien'] ?>"><?= htmlspecialchars((string)$ev['ten_su_kien']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label th-form-label mb-1">Tên loại vé</label>
                    <input type="text" class="form-control th-input-dark" name="ten_loai_ve" required placeholder="VD: Vé VIP">
                  </div>
                  <div class="col-6">
                    <label class="form-label th-form-label mb-1">Giá vé</label>
                    <input type="number" min="1000" step="1000" class="form-control th-input-dark" name="gia_ve" required placeholder="VD: 500000">
                  </div>
                  <div class="col-6">
                    <label class="form-label th-form-label mb-1">Số lượng</label>
                    <input type="number" min="1" class="form-control th-input-dark" name="so_luong" required placeholder="VD: 100">
                  </div>
                  <div class="col-12"><button class="btn btn-th-primary btn-sm rounded-pill px-3" type="submit">Lưu</button></div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h2 class="h5 mb-0">Danh sách loại vé</h2>
            <span class="badge th-badge-soft"><?= count($ticketTypes) ?> loại vé</span>
          </div>

          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Loại vé</th>
                  <th>Sự kiện</th>
                  <th>Giá</th>
                  <th>Tổng SL</th>
                  <th>Còn lại</th>
                  <th>Đã bán</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$ticketTypes): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">Chưa có loại vé nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($ticketTypes as $t): ?>
                    <?php $id = (int)$t['ma_loai_ve']; ?>
                    <?php $soldCount = (int)$t['so_luong'] - (int)$t['so_luong_con']; ?>
                    <tr>
                      <td><strong><?= htmlspecialchars((string)$t['ten_loai_ve']) ?></strong></td>
                      <td><?= htmlspecialchars((string)$t['ten_su_kien']) ?></td>
                      <td class="fw-semibold"><?= number_format((float)$t['gia_ve'], 0, ',', '.') ?> đ</td>
                      <td><?= (int)$t['so_luong'] ?></td>
                      <td>
                        <span class="badge bg-info"><?= (int)$t['so_luong_con'] ?></span>
                      </td>
                      <td>
                        <span class="badge bg-success"><?= $soldCount ?></span>
                      </td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning me-1" type="button" data-bs-toggle="collapse" data-bs-target="#editTicket<?= $id ?>" aria-expanded="false">Sửa</button>
                        <form action="index.php?action=delete_ticket_type" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa loại vé này?');">
                          <input type="hidden" name="ma_loai_ve" value="<?= $id ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                        </form>
                      </td>
                    </tr>
                    <tr class="collapse" id="editTicket<?= $id ?>">
                      <td colspan="9" class="bg-dark">
                        <form action="index.php?action=update_ticket_type" method="POST" class="row g-2 p-2">
                          <input type="hidden" name="ma_loai_ve" value="<?= $id ?>">
                          <div class="col-md-3">
                            <label class="form-label th-form-label mb-1">Tên loại vé</label>
                            <input type="text" class="form-control form-control-sm th-input-dark" name="ten_loai_ve" value="<?= htmlspecialchars((string)$t['ten_loai_ve']) ?>" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label th-form-label mb-1">Giá vé</label>
                            <input type="number" min="1000" step="1000" class="form-control form-control-sm th-input-dark" name="gia_ve" value="<?= (float)$t['gia_ve'] ?>" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label th-form-label mb-1">Tổng số lượng</label>
                            <input type="number" min="1" class="form-control form-control-sm th-input-dark" name="so_luong" value="<?= (int)$t['so_luong'] ?>" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label th-form-label mb-1">Số lượng còn</label>
                            <input type="number" min="0" class="form-control form-control-sm th-input-dark" name="so_luong_con" value="<?= (int)$t['so_luong_con'] ?>" required>
                          </div>
                          <div class="col-md-3 text-end">
                            <button class="btn btn-sm btn-warning me-1" type="submit" onclick="return confirm('Xác nhận cập nhật loại vé này?');">Lưu</button>
                            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#editTicket<?= $id ?>">Đóng</button>
                          </div>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

