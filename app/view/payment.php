<?php
$user = $_SESSION['user'] ?? null;

$pageTitle = 'Thanh toán MoMo - TicketHub';
$bodyClass = 'th-page-gradient';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>
  <?php
  $transferAmount = (int)round((float)$paymentInfo['tong_tien']);
  $transferContent = 'DH' . (int)$paymentInfo['ma_don_hang'];
  ?>

  <div class="row g-4 justify-content-center">
    <div class="col-lg-4">
      <div class="card border-0 th-auth-card h-100">
        <div class="card-body text-center">
          <div class="badge bg-warning text-dark mb-3">QR THANH TOÁN MOMO</div>
          <img src="<?= htmlspecialchars((string)$paymentInfo['momo_qr_image']) ?>" alt="QR MoMo" class="img-fluid rounded-4 border border-light border-opacity-25" style="max-width: 300px;">
          <div class="small mt-3 text-warning fw-semibold">Quét mã này để thanh toán</div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card border-0 th-auth-card h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Thông tin thanh toán</h2>
          <div class="mb-2">Đơn hàng: <strong>#<?= (int)$paymentInfo['ma_don_hang'] ?></strong></div>
          <div class="mb-2">Số tiền: <strong class="text-warning"><?= number_format((float)$paymentInfo['tong_tien'], 0, ',', '.') ?> đ</strong></div>
          <div class="mb-3">Loại vé: <strong><?= htmlspecialchars((string)$paymentInfo['ten_loai_ve']) ?></strong> - SL: <strong><?= (int)$paymentInfo['so_luong'] ?></strong></div>
          <div class="p-3 rounded-3 bg-dark bg-opacity-50 border border-light border-opacity-10 mb-3">
            <div class="small text-muted mb-2">Thông tin chuyển khoản cần nhập trong MoMo</div>
            <div class="mb-2">
              <span class="text-muted small d-block">Số tiền</span>
              <div class="d-flex align-items-center justify-content-between gap-2">
                <strong id="transfer-amount"><?= number_format($transferAmount, 0, ',', '.') ?> đ</strong>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="copyText('<?= $transferAmount ?>')">Copy tiền</button>
              </div>
            </div>
            <div>
              <span class="text-muted small d-block">Nội dung chuyển khoản</span>
              <div class="d-flex align-items-center justify-content-between gap-2">
                <strong id="transfer-content"><?= htmlspecialchars($transferContent) ?></strong>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="copyText('<?= htmlspecialchars($transferContent, ENT_QUOTES) ?>')">Copy mã</button>
              </div>
            </div>
          </div>
          <div id="payment-status-box" class="alert <?= (($paymentInfo['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan') ? 'alert-success' : 'alert-info' ?> mb-3">
            <?= (($paymentInfo['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan') ? 'Thanh toán thành công. Vé đã phát hành tự động.' : 'Đang chờ xác nhận tự động từ MoMo...' ?>
          </div>

          <h3 class="h6 mb-3">Vé điện tử</h3>
          <div id="tickets-box">
            <?php if (empty($paymentInfo['tickets'])): ?>
              <div class="alert alert-dark border border-light border-opacity-10 mb-0">
                Sau khi MoMo báo thành công, hệ thống tự tạo vé QR riêng cho từng vé.
              </div>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach ($paymentInfo['tickets'] as $ticketQr): ?>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-dark bg-opacity-50 border border-light border-opacity-10">
                      <div class="small text-muted mb-1"><?= htmlspecialchars((string)$ticketQr['ten_loai_ve']) ?></div>
                      <div class="fw-semibold mb-2">Mã vé #<?= (int)$ticketQr['ma_ve'] ?></div>
                      <img src="<?= htmlspecialchars((string)$ticketQr['qr_image_url']) ?>" alt="QR vé" class="img-fluid rounded-3 border border-light border-opacity-25 mb-2">
                      <div class="small text-warning mb-2">QR vé check-in, không dùng để thanh toán</div>
                      <div class="small text-break">QR code: <?= htmlspecialchars((string)$ticketQr['ma_qr']) ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
(() => {
  window.copyText = async (txt) => {
    try {
      await navigator.clipboard.writeText(txt);
    } catch (e) {}
  };

  const orderId = <?= (int)$paymentInfo['ma_don_hang'] ?>;
  const statusBox = document.getElementById('payment-status-box');
  const ticketsBox = document.getElementById('tickets-box');
  const renderTickets = (tickets) => {
    if (!tickets || !tickets.length) return;
    const html = tickets.map((t) => `
      <div class="col-md-6">
        <div class="p-3 rounded-3 bg-dark bg-opacity-50 border border-light border-opacity-10">
          <div class="small text-muted mb-1">${t.ten_loai_ve ?? ''}</div>
          <div class="fw-semibold mb-2">Mã vé #${t.ma_ve}</div>
          <img src="${t.qr_image_url}" alt="QR vé" class="img-fluid rounded-3 border border-light border-opacity-25 mb-2">
          <div class="small text-break">QR code: ${t.ma_qr}</div>
        </div>
      </div>
    `).join('');
    ticketsBox.innerHTML = `<div class="row g-3">${html}</div>`;
  };

  const poll = async () => {
    try {
      const res = await fetch(`index.php?action=payment_status&order_id=${orderId}`, { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      if (!data.success) return;
      if (data.paid) {
        statusBox.className = 'alert alert-success mb-3';
        statusBox.textContent = 'Thanh toán thành công. Vé đã phát hành tự động.';
        renderTickets(data.tickets || []);
        return;
      }
      setTimeout(poll, 5000);
    } catch (e) {
      setTimeout(poll, 5000);
    }
  };

  if (!<?= (($paymentInfo['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan') ? 'true' : 'false' ?>) {
    setTimeout(poll, 5000);
  }
})();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

