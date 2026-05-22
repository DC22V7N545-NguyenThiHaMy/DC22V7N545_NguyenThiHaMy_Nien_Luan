<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../model/EventTicket.php';
require_once __DIR__ . '/../../model/News.php';

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

$newsModel = isset($conn) ? new News($conn) : null;
$news = $newsModel ? $newsModel->getNews() : [];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý tin tức - TicketHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-pIV2dM46Eb1RkaNqC8SJPnPIqlqp7l1vQAkse1E57RpjuqzRC3BVR6u5f5ADDpT5LLnRw+E0M0U6qsks7eJ7AA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
      <div class="card th-card-dark mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Quản lý tin tức</h4>
          <button class="btn btn-th-primary btn-sm px-3" type="button" data-bs-toggle="collapse" data-bs-target="#createBox" aria-expanded="false">
            <i class="fas fa-plus me-1"></i>Thêm tin tức
          </button>
        </div>

        <div class="collapse" id="createBox">
          <div class="card-body">
            <form action="index.php?action=create_news" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Tiêu đề</label>
                  <input type="text" name="tieu_de" class="form-control th-input-dark" placeholder="Nhập tiêu đề tin tức" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Nội dung</label>
                  <textarea name="noi_dung" class="form-control th-input-dark" rows="6" placeholder="Nhập nội dung tin tức" required></textarea>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Hình ảnh</label>
                  <input type="file" name="hinh_anh" class="form-control th-input-dark" accept="image/*" onchange="previewNewsImage(event)">
                  <div class="mt-2">
                    <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; display: none;">
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-success">Tạo tin tức</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="card th-card-dark">
        <div class="card-header">
          <h5 class="mb-0">Danh sách tin tức</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-dark table-striped">
              <thead>
                <tr>
                  <th>Hình ảnh</th>
                  <th>Tiêu đề</th>
                  <th>Người tạo</th>
                  <th>Ngày tạo</th>
                  <th>Trạng thái</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$news): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">Chưa có tin tức nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($news as $n): ?>
                    <?php $id = (int)$n['ma_tin_tuc']; ?>
                    <tr>
                      <td>
                        <?php if ($n['hinh_anh']): ?>
                          <img src="<?= htmlspecialchars((string)$n['hinh_anh']) ?>" alt="Hình ảnh" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                        <?php else: ?>
                          <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 5px;">
                            <i class="fas fa-image text-white"></i>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <strong><?= htmlspecialchars((string)$n['tieu_de']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars(substr((string)$n['noi_dung'], 0, 100)) ?>...</small>
                      </td>
                      <td><?= htmlspecialchars((string)$n['ten_nguoi_tao']) ?></td>
                      <td><?= date('d/m/Y H:i', strtotime($n['ngay_tao'])) ?></td>
                      <td>
                        <span class="badge bg-<?= $n['trang_thai'] === 'da_duyet' ? 'success' : 'warning' ?>">
                          <?= $n['trang_thai'] === 'da_duyet' ? 'Đã duyệt' : 'Nháp' ?>
                        </span>
                      </td>
                      <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editBox<?= e($id) ?>" aria-expanded="false">
                          <i class="fas fa-edit"></i> Sửa
                        </button>
                        <form method="POST" action="index.php?action=delete_news" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa tin tức này?')">
                <?= csrf_field() ?>
                          <input type="hidden" name="ma_tin_tuc" value="<?= e($id) ?>">
                          <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i> Xóa
                          </button>
                        </form>
                      </td>
                    </tr>
                    <tr class="collapse" id="editBox<?= e($id) ?>">
                      <td colspan="6">
                        <div class="p-3 bg-dark rounded">
                          <form action="index.php?action=update_news" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                            <input type="hidden" name="ma_tin_tuc" value="<?= e($id) ?>">
                            <input type="hidden" name="current_hinh_anh" value="<?= htmlspecialchars((string)$n['hinh_anh']) ?>">
                            <div class="row g-3">
                              <div class="col-md-12">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="tieu_de" value="<?= htmlspecialchars((string)$n['tieu_de']) ?>" class="form-control th-input-dark" required>
                              </div>
                              <div class="col-md-12">
                                <label class="form-label">Nội dung</label>
                                <textarea name="noi_dung" class="form-control th-input-dark" rows="6" required><?= htmlspecialchars((string)$n['noi_dung']) ?></textarea>
                              </div>
                              <div class="col-md-12">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" name="hinh_anh" class="form-control th-input-dark" accept="image/*" onchange="previewNewsImage(event)">
                                <?php if ($n['hinh_anh']): ?>
                                  <div class="mt-2">
                                    <small class="text-muted">Hình hiện tại:</small><br>
                                    <img src="<?= htmlspecialchars((string)$n['hinh_anh']) ?>" alt="Current" class="img-thumbnail mt-1" style="max-width: 200px;">
                                  </div>
                                <?php endif; ?>
                                <div class="mt-2">
                                  <img id="editImagePreview<?= e($id) ?>" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; display: none;">
                                </div>
                              </div>
                              <div class="col-12">
                                <button type="submit" class="btn btn-primary">Cập nhật</button>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#editBox<?= e($id) ?>">Hủy</button>
                              </div>
                            </div>
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
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewNewsImage(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('imagePreview');
      if (preview) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      }
    };
    reader.readAsDataURL(file);
  }
}
</script>
</body>
</html>