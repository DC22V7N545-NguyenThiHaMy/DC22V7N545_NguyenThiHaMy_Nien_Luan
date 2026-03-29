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
$categories = $eventTicketModel ? $eventTicketModel->getCategories() : [];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý danh mục - TicketHub</title>
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
      <div class="card border-0 th-auth-card mb-4">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h4 mb-0">Quản lý danh mục</h1>
            <button class="btn btn-th-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#createCategoryBox">Thêm danh mục</button>
          </div>
          <div class="collapse" id="createCategoryBox">
            <form action="index.php?action=create_category" method="POST" class="row g-3" onsubmit="return confirm('Xác nhận thêm danh mục mới?');">
              <div class="col-md-5">
                <label class="form-label th-form-label">Tên danh mục</label>
                <input type="text" class="form-control th-input-dark" name="name" required placeholder="VD: Âm nhạc">
              </div>
              <div class="col-md-5">
                <label class="form-label th-form-label">Mô tả</label>
                <input type="text" class="form-control th-input-dark" name="description" placeholder="Mô tả ngắn">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-th-primary w-100 rounded-pill" type="submit">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <h2 class="h5 mb-3">Danh sách danh mục</h2>
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tên danh mục</th>
                  <th>Mô tả</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$categories): ?>
                  <tr><td colspan="4" class="text-center text-muted py-4">Chưa có danh mục.</td></tr>
                <?php else: ?>
                  <?php foreach ($categories as $c): ?>
                    <?php $id = (int)$c['ma_danh_muc']; ?>
                    <tr>
                      <td><?= $id ?></td>
                      <td><?= htmlspecialchars((string)$c['ten_danh_muc']) ?></td>
                      <td><?= htmlspecialchars((string)($c['mo_ta'] ?? '')) ?></td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning me-1" type="button" data-bs-toggle="collapse" data-bs-target="#editCategory<?= $id ?>" aria-expanded="false">Sửa</button>
                        <form action="index.php?action=delete_category" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                          <input type="hidden" name="ma_danh_muc" value="<?= $id ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                        </form>
                      </td>
                    </tr>
                    <tr class="collapse" id="editCategory<?= $id ?>">
                      <td colspan="4" class="bg-dark">
                        <form action="index.php?action=update_category" method="POST" class="row g-2 p-2">
                          <input type="hidden" name="ma_danh_muc" value="<?= $id ?>">
                          <div class="col-md-4">
                            <label class="form-label th-form-label mb-1">Tên danh mục</label>
                            <input type="text" class="form-control form-control-sm th-input-dark" name="name" value="<?= htmlspecialchars((string)$c['ten_danh_muc']) ?>" required>
                          </div>
                          <div class="col-md-5">
                            <label class="form-label th-form-label mb-1">Mô tả</label>
                            <input type="text" class="form-control form-control-sm th-input-dark" name="description" value="<?= htmlspecialchars((string)($c['mo_ta'] ?? '')) ?>">
                          </div>
                          <div class="col-md-3 text-end">
                            <button class="btn btn-sm btn-warning me-1" type="submit" onclick="return confirm('Xác nhận cập nhật danh mục này?');">Lưu</button>
                            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#editCategory<?= $id ?>">Đóng</button>
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

