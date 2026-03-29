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
$events = $eventTicketModel ? $eventTicketModel->getEvents() : [];
$ticketTypes = $eventTicketModel ? $eventTicketModel->getTicketTypesForAdmin() : [];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý sự kiện - TicketHub</title>
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
      <!-- CREATE EVENT CARD -->
      <div class="card border-0 th-auth-card mb-4">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 mb-0">Tạo sự kiện</h2>
            <button class="btn btn-th-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#createEventBox">Thêm</button>
          </div>
          <div class="collapse" id="createEventBox">
            <form action="index.php?action=create_event" method="POST" enctype="multipart/form-data" class="row g-2" onsubmit="return confirm('Xác nhận tạo sự kiện mới?');">
              <div class="col-12">
                <label class="form-label th-form-label mb-1">Danh mục</label>
                <select class="form-select th-input-dark" name="ma_danh_muc" required>
                  <option value="">Chọn danh mục</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['ma_danh_muc'] ?>"><?= htmlspecialchars((string)$c['ten_danh_muc']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label th-form-label mb-1">Hình ảnh sự kiện</label>
                <input type="file" class="form-control th-input-dark" id="eventImageInput" name="hinh_anh" accept="image/*" onchange="previewEventImage(event)">
                <small class="text-muted d-block mt-1">Định dạng: JPG, PNG. Kích thước ≤ 5MB</small>
                <div id="imagePreview" style="margin-top: 10px; display:none;">
                  <img id="previewImage" src="" alt="Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px;">
                </div>
              </div>
              <div class="col-12">
                <label class="form-label th-form-label mb-1">Tên sự kiện</label>
                <input type="text" class="form-control th-input-dark" name="ten_su_kien" required placeholder="VD: Live Concert 2026">
              </div>
              <div class="col-6">
                <label class="form-label th-form-label mb-1">Ngày tổ chức</label>
                <input type="date" class="form-control th-input-dark" name="ngay_to_chuc" required>
              </div>
              <div class="col-6">
                <label class="form-label th-form-label mb-1">Giờ tổ chức</label>
                <input type="time" class="form-control th-input-dark" name="gio_to_chuc" required>
              </div>
              <div class="col-12">
                <label class="form-label th-form-label mb-1">Địa điểm</label>
                <input type="text" class="form-control th-input-dark" name="dia_diem" required placeholder="VD: Nhà hát lớn">
              </div>
              <div class="col-12">
                <label class="form-label th-form-label mb-1">Mô tả</label>
                <textarea class="form-control th-input-dark" name="mo_ta" rows="2" placeholder="Mô tả ngắn sự kiện"></textarea>
              </div>
              <div class="col-12"><button class="btn btn-th-primary btn-sm rounded-pill px-3" type="submit">Lưu</button></div>
            </form>
          </div>
        </div>
      </div>

      <!-- LIST EVENTS CARD -->
      <div class="card border-0 th-auth-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h2 class="h5 mb-0">Danh sách sự kiện</h2>
            <span class="badge th-badge-soft"><?= count($events) ?> sự kiện</span>
          </div>

          <!-- Filter by Category -->
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label small">Lọc theo danh mục</label>
              <select class="form-select form-select-sm th-input-dark" id="filterCategory" onchange="filterEvents()">
                <option value="">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= (int)$c['ma_danh_muc'] ?>"><?= htmlspecialchars((string)$c['ten_danh_muc']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Tìm kiếm tên sự kiện</label>
              <input type="text" id="searchEvent" class="form-control form-control-sm th-input-dark" placeholder="Nhập tên sự kiện..." onkeyup="filterEvents()">
            </div>
            <div class="col-md-4">
              <label class="form-label small">&nbsp;</label>
              <button onclick="resetFilter()" class="btn btn-sm btn-outline-light w-100">Đặt lại</button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" id="eventsTable">
              <thead>
                <tr>
                  <th style="width: 8%;">Hình ảnh</th>
                  <th>Sự kiện</th>
                  <th>Danh mục</th>
                  <th>Ngày giờ</th>
                  <th>Địa điểm</th>
                  <th style="width: 12%;">Vé</th>
                  <th class="text-end" style="width: 12%;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$events): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">Chưa có sự kiện nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($events as $ev): ?>
                    <?php $eventId = (int)$ev['ma_su_kien']; ?>
                    <?php
                      $ticketsSold = 0;
                      $ticketsRemaining = 0;
                      foreach ($ticketTypes as $t) {
                        if ((int)$t['ma_su_kien'] === $eventId) {
                          $ticketsSold += ((int)$t['so_luong'] - (int)$t['so_luong_con']);
                          $ticketsRemaining += (int)$t['so_luong_con'];
                        }
                      }
                    ?>
                    <tr class="event-row" data-category="<?= (int)$ev['ma_danh_muc'] ?>" data-name="<?= htmlspecialchars((string)$ev['ten_su_kien']) ?>">
                      <td>
                        <?php if (!empty($ev['hinh_anh'])): ?>
                          <img src="<?= htmlspecialchars((string)$ev['hinh_anh']) ?>" alt="Event" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <?php else: ?>
                          <div style="width: 50px; height: 50px; background: #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">No image</div>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars((string)$ev['ten_su_kien']) ?></td>
                      <td><?= htmlspecialchars((string)$ev['ten_danh_muc']) ?></td>
                      <td>
                        <small class="text-muted"><?= htmlspecialchars((string)$ev['ngay_to_chuc']) ?></small><br>
                        <small class="text-muted"><?= htmlspecialchars((string)$ev['gio_to_chuc']) ?></small>
                      </td>
                      <td><?= htmlspecialchars((string)$ev['dia_diem']) ?></td>
                      <td>
                        <small class="badge bg-success"><?= $ticketsSold ?> bán</small><br>
                        <small class="badge bg-info"><?= $ticketsRemaining ?> còn</small>
                      </td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning me-1" type="button" data-bs-toggle="collapse" data-bs-target="#editEvent<?= $eventId ?>" aria-expanded="false">Sửa</button>
                        <form action="index.php?action=delete_event" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa sự kiện này?');">
                          <input type="hidden" name="ma_su_kien" value="<?= $eventId ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                        </form>
                      </td>
                    </tr>
                    <tr class="collapse" id="editEvent<?= $eventId ?>">
                      <td colspan="7" class="bg-dark">
                        <form action="index.php?action=update_event" method="POST" enctype="multipart/form-data" class="row g-2 p-2">
                          <input type="hidden" name="ma_su_kien" value="<?= $eventId ?>">
                          <input type="hidden" name="current_hinh_anh" value="<?= htmlspecialchars((string)($ev['hinh_anh'] ?? '')) ?>">
                          <div class="col-md-3">
                            <label class="form-label th-form-label mb-1">Danh mục</label>
                            <select class="form-select form-select-sm th-input-dark" name="ma_danh_muc" required>
                              <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['ma_danh_muc'] ?>" <?= ((int)$c['ma_danh_muc'] === (int)$ev['ma_danh_muc']) ? 'selected' : '' ?>>
                                  <?= htmlspecialchars((string)$c['ten_danh_muc']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label th-form-label mb-1">Tên sự kiện</label>
                            <input type="text" class="form-control form-control-sm th-input-dark" name="ten_su_kien" value="<?= htmlspecialchars((string)$ev['ten_su_kien']) ?>" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label th-form-label mb-1">Ngày</label>
                            <input type="date" class="form-control form-control-sm th-input-dark" name="ngay_to_chuc" value="<?= htmlspecialchars((string)$ev['ngay_to_chuc']) ?>" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label th-form-label mb-1">Giờ</label>
                            <input type="time" class="form-control form-control-sm th-input-dark" name="gio_to_chuc" value="<?= htmlspecialchars((string)$ev['gio_to_chuc']) ?>" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label th-form-label mb-1">Địa điểm</label>
                            <input type="text" class="form-control form-control-sm th-input-dark" name="dia_diem" value="<?= htmlspecialchars((string)$ev['dia_diem']) ?>" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label th-form-label mb-1">Ảnh mới (không bắt buộc)</label>
                            <input type="file" class="form-control form-control-sm th-input-dark" name="hinh_anh" accept="image/*" onchange="previewUpdateImage(event)">
                            <div id="imagePreviewUpdate<?= $eventId ?>" style="margin-top: 8px; display:none;">
                              <img id="previewImageUpdate<?= $eventId ?>" src="" alt="Preview" style="max-width: 100%; max-height: 100px; border-radius: 4px;">
                            </div>
                            <?php if (!empty($ev['hinh_anh'])): ?>
                              <small class="text-muted d-block mt-2">Ảnh hiện tại:</small>
                              <img src="<?= htmlspecialchars((string)$ev['hinh_anh']) ?>" alt="Current image" style="width: 72px; height: 72px; object-fit: cover; border-radius: 6px; margin-top: 6px;">
                            <?php endif; ?>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label th-form-label mb-1">Mô tả</label>
                            <textarea class="form-control form-control-sm th-input-dark" name="mo_ta" rows="2"><?= htmlspecialchars((string)($ev['mo_ta'] ?? '')) ?></textarea>
                          </div>
                          <div class="col-12 text-end">
                            <button class="btn btn-sm btn-warning me-1" type="submit" onclick="return confirm('Xác nhận cập nhật sự kiện này?');">Lưu</button>
                            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#editEvent<?= $eventId ?>">Đóng</button>
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
<script>
function previewEventImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

function previewUpdateImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Tìm element preview dựa vào focus input
            const input = event.target;
            const row = input.closest('tr');
            const previewDiv = row.querySelector('[id^="imagePreviewUpdate"]');
            const previewImg = row.querySelector('[id^="previewImageUpdate"]');
            
            if (previewImg) {
                previewImg.src = e.target.result;
            }
            if (previewDiv) {
                previewDiv.style.display = 'block';
            }
        }
        reader.readAsDataURL(file);
    }
}

function filterEvents() {
    const categoryId = document.getElementById('filterCategory').value;
    const searchText = document.getElementById('searchEvent').value.toLowerCase();
    const rows = document.querySelectorAll('.event-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const category = row.getAttribute('data-category');
        const name = row.getAttribute('data-name').toLowerCase();
        
        const matchCategory = !categoryId || category === categoryId;
        const matchSearch = !searchText || name.includes(searchText);
        
        if (matchCategory && matchSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    const tbody = document.querySelector('#eventsTable tbody');
    if (visibleCount === 0) {
        let existingMsg = tbody.querySelector('.no-results');
        if (!existingMsg) {
            const tr = document.createElement('tr');
            tr.className = 'no-results';
            tr.innerHTML = '<td colspan="7" class="text-center text-muted py-4">Không tìm thấy sự kiện nào phù hợp.</td>';
            tbody.appendChild(tr);
        }
    } else {
        const existingMsg = tbody.querySelector('.no-results');
        if (existingMsg) existingMsg.remove();
    }
}

function resetFilter() {
    document.getElementById('filterCategory').value = '';
    document.getElementById('searchEvent').value = '';
    filterEvents();
}
</script>
</body>
</html>
