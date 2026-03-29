<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Chuyển đến SePay - TicketHub';
$bodyClass = 'th-page-gradient';
require_once __DIR__ . '/layouts/header.php';
?>


<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card border-0 th-auth-card">
        <div class="card-body text-center">
          <?php if (!empty($sepayError)): ?>
            <div class="alert alert-danger" role="alert">
              <strong>Lỗi SePay:</strong> <?= htmlspecialchars((string)$sepayError) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($checkoutBase) && !empty($fields)): ?>
            <h1 class="h4 mb-3">Đang chuyển đến cổng thanh toán SePay...</h1>
            <p class="text-muted">Nếu không tự chuyển, bấm nút bên dưới.</p>
            <form id="sepayForm" method="POST" action="<?= htmlspecialchars($checkoutBase) ?>">
              <?php foreach ($fields as $k => $v): ?>
                <input type="hidden" name="<?= htmlspecialchars((string)$k) ?>" value="<?= htmlspecialchars((string)$v) ?>">
              <?php endforeach; ?>
              <button class="btn btn-th-primary rounded-pill px-4" type="submit">Tiếp tục thanh toán</button>
            </form>
          <?php else: ?>
            <h1 class="h4 mb-3">Chưa cấu hình SePay</h1>
            <p class="text-muted mb-4">Đơn đang chờ thanh toán. Bạn có thể test nhanh bằng nút bên dưới.</p>
            <form method="POST" action="index.php?action=sepay_ipn_test">
              <input type="hidden" name="order_id" value="<?= (int)($_GET['order_id'] ?? 0) ?>">
              <button class="btn btn-th-primary rounded-pill px-4" type="submit">Test thanh toán thành công</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
<?php if (!empty($checkoutBase) && !empty($fields)): ?>
<script>document.getElementById('sepayForm').submit();</script>
<?php endif; ?>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>

