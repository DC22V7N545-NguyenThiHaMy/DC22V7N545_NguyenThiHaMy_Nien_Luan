<?php
$pageTitle = 'Soát vé (Check-in) - Admin';
require_once __DIR__ . '/../layouts/header.php';

$user = $_SESSION['user'] ?? null;
if (!$user || !in_array($user['role'], ['quan_tri_vien', 'nhan_vien'], true)) {
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="flex-grow-1 bg-light">
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg border-bottom bg-white px-4 py-3 shadow-sm">
      <div class="container-fluid">
        <span class="navbar-brand mb-0 h1 fw-bold">Soát vé (Check-in)</span>
        <div class="d-flex align-items-center gap-3">
          <div class="text-end">
            <div class="fw-bold"><?= e($user['ho_ten'] ?? 'Admin') ?></div>
            <div class="text-muted small"><?= e($user['role'] === 'quan_tri_vien' ? 'Quản trị viên' : 'Nhân viên') ?></div>
          </div>
          <img src="https://ui-avatars.com/api/?name=<?= urlencode((string)($user['ho_ten'] ?? 'A')) ?>&background=random" 
               class="rounded-circle" width="40" height="40" alt="Avatar">
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="p-4">
      <?php require __DIR__ . '/../partials/toast.php'; ?>

      <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 th-card-feature overflow-hidden p-4 text-center">
            
            <div class="mb-4">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-qrcode text-primary fa-3x"></i>
              </div>
              <h3 class="fw-bold">Quét mã QR</h3>
              <p class="text-muted small">Sử dụng máy quét mã vạch hoặc nhập tay mã QR trên vé để check-in.</p>
            </div>

            <form action="index.php?action=process_checkin" method="POST" autocomplete="off" id="checkinForm">
              <?= csrf_field() ?>
              <div class="mb-4">
                <input type="text" name="qr_code" id="qr_code" class="form-control form-control-lg text-center" placeholder="Vd: TICKET-12-34-1-abcde..." required autofocus style="letter-spacing: 1px;">
              </div>
              
              <button type="submit" class="btn btn-th-primary btn-lg w-100 rounded-pill shadow-sm">
                <i class="fas fa-check-circle me-1"></i> Check-in ngay
              </button>
            </form>

            <div class="mt-4 pt-3 border-top text-start">
              <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-info me-1"></i>Hướng dẫn:</h6>
              <ul class="text-muted small mb-0 ps-3">
                <li>Kết nối máy quét mã vạch qua cổng USB/Bluetooth.</li>
                <li>Đảm bảo con trỏ chuột đang nhấp nháy ở ô nhập mã.</li>
                <li>Máy quét sẽ tự động nhập mã và gửi yêu cầu soát vé.</li>
              </ul>
            </div>
            
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
// Tự động focus lại ô nhập sau khi submit hoặc tải lại trang
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        document.getElementById('qr_code').focus();
    }, 100);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
