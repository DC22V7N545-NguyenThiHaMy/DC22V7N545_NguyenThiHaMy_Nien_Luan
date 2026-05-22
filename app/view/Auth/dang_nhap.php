<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;

$pageTitle = 'Đăng nhập - TicketHub';
$bodyClass = 'th-page-gradient';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <?php require __DIR__ . '/../partials/toast.php'; ?>

      <div class="card border-0 th-auth-card th-reveal">
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <span class="badge bg-warning text-dark mb-2">Đăng nhập tài khoản</span>
            <h2 class="h4 mb-0">Chào mừng trở lại</h2>
            <p class="text-muted small mb-0">Truy cập bảng điều khiển bán vé của bạn.</p>
          </div>

          <form action="index.php?action=login" method="POST" autocomplete="off">
                <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label th-form-label" for="loginEmail">Email</label>
              <input class="form-control th-input-dark" type="email" id="loginEmail" name="email" required placeholder="you@example.com">
            </div>
            <div class="mb-3">
              <label class="form-label th-form-label" for="loginPassword">Mật khẩu</label>
              <input class="form-control th-input-dark" type="password" id="loginPassword" name="password" required placeholder="••••••••">
            </div>
            <button class="btn btn-th-primary w-100 rounded-pill" type="submit">Đăng nhập</button>
          </form>

          <p class="text-center text-muted small mt-3 mb-0">
            Chưa có tài khoản?
            <a href="index.php?action=register">Đăng ký ngay</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

