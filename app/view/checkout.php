<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
$isPaid = ($paymentInfo['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan';

$pageTitle = 'Thanh toán đơn hàng #' . (int)$paymentInfo['ma_don_hang'] . ' - TicketHub';
$bodyClass = '';
require_once __DIR__ . '/layouts/header.php';
?>

<style>
  body { background: linear-gradient(135deg,#1e3c72 0%,#2a5298 100%); min-height: 100vh; }
  .card-dark { background: #16213e; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; color: #eee; }
  .item-row { display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.08); font-size: .9rem; }
  .item-row:last-child { border-bottom: none; }
  .copy-btn { font-size: .75rem; padding: 2px 10px; }
  .qr-img { border-radius: 12px; border: 2px solid rgba(255,255,255,.15); max-width: 240px; }
  .ticket-card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px; padding: 14px; margin-bottom: 12px; }
  .badge-status { font-size: .75rem; padding: 4px 10px; border-radius: 20px; }
</style>


<main class="container py-4">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <div class="row g-4 justify-content-center">

    <!-- QR thanh toán -->
    <div class="col-lg-4">
      <div class="card-dark p-4 text-center h-100">
        <span class="badge bg-warning text-dark mb-3 d-inline-block">QR THANH TOÁN MOMO</span>
        <div>
          <img src="<?= htmlspecialchars((string)$paymentInfo['momo_qr_image']) ?>"
               alt="QR MoMo" class="qr-img img-fluid mb-3">
        </div>
        <p class="text-warning fw-semibold small mb-1">Quét mã để thanh toán qua MoMo</p>
        <p class="text-muted small">Sau khi chuyển khoản, hệ thống tự xác nhận và phát vé.</p>

        <div class="mt-3 text-start p-3 rounded-3" style="background:rgba(0,0,0,.3)">
          <div class="small text-muted mb-1">Số tiền</div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong id="amt"><?= number_format($paymentInfo['transfer_amount'], 0, ',', '.') ?> đ</strong>
            <button class="btn btn-outline-light copy-btn" onclick="doCopy('<?= $paymentInfo['transfer_amount'] ?>')">Copy</button>
          </div>
          <div class="small text-muted mb-1">Nội dung chuyển khoản</div>
          <div class="d-flex justify-content-between align-items-center">
            <strong id="content"><?= htmlspecialchars((string)$paymentInfo['transfer_content']) ?></strong>
            <button class="btn btn-outline-light copy-btn" onclick="doCopy('<?= htmlspecialchars((string)$paymentInfo['transfer_content'], ENT_QUOTES) ?>')">Copy</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Chi tiết đơn hàng + vé -->
    <div class="col-lg-8">
      <div class="card-dark p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Đơn hàng <span class="text-warning">#<?= (int)$paymentInfo['ma_don_hang'] ?></span></h5>
          <span id="status-badge" class="badge-status <?= $isPaid ? 'bg-success' : 'bg-warning text-dark' ?>">
            <?= $isPaid ? '✓ Đã thanh toán' : '⏳ Chờ thanh toán' ?>
          </span>
        </div>

        <!-- Danh sách vé trong đơn -->
        <?php foreach ($paymentInfo['items'] as $item): ?>
          <div class="item-row">
            <div>
              <div class="fw-semibold"><?= htmlspecialchars((string)$item['ten_su_kien']) ?></div>
              <div class="text-muted small">🎟 <?= htmlspecialchars((string)$item['ten_loai_ve']) ?> × <?= (int)$item['so_luong'] ?></div>
            </div>
            <div class="text-warning fw-bold"><?= number_format((float)$item['thanh_tien'], 0, ',', '.') ?> đ</div>
          </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.15)">
          <span class="fw-semibold">Tổng cộng</span>
          <span class="text-warning fw-bold fs-5"><?= number_format((float)$paymentInfo['tong_tien'], 0, ',', '.') ?> đ</span>
        </div>

        <!-- Trạng thái thanh toán -->
        <div id="payment-status-box" class="alert <?= $isPaid ? 'alert-success' : 'alert-info' ?> mt-3 mb-0">
          <?= $isPaid ? '✅ Thanh toán thành công. Vé đã được phát hành.' : '⏳ Đang chờ xác nhận từ MoMo...' ?>
        </div>
      </div>

      <!-- Vé điện tử -->
      <div class="card-dark p-4">
        <h6 class="mb-3">🎫 Vé điện tử</h6>
        <div id="tickets-box">
          <?php if (empty($paymentInfo['tickets'])): ?>
            <p class="text-muted small mb-0">Vé QR sẽ xuất hiện tại đây sau khi thanh toán được xác nhận.</p>
          <?php else: ?>
            <?php foreach ($paymentInfo['tickets'] as $t): ?>
              <div class="ticket-card">
                <div class="small text-muted mb-1"><?= htmlspecialchars((string)$t['ten_loai_ve']) ?></div>
                <div class="fw-semibold mb-2">Mã vé #<?= (int)$t['ma_ve'] ?></div>
                <img src="<?= htmlspecialchars((string)$t['qr_image_url']) ?>" alt="QR vé" class="qr-img img-fluid mb-2" style="max-width:180px">
                <div class="small text-warning">QR check-in — không dùng để thanh toán</div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
const doCopy = async (txt) => { try { await navigator.clipboard.writeText(String(txt)); } catch(e){} };

const orderId = <?= (int)$paymentInfo['ma_don_hang'] ?>;
const statusBox = document.getElementById('payment-status-box');
const statusBadge = document.getElementById('status-badge');
const ticketsBox = document.getElementById('tickets-box');

const renderTickets = (tickets) => {
  if (!tickets || !tickets.length) return;
  ticketsBox.innerHTML = tickets.map(t => `
    <div class="ticket-card">
      <div class="small text-muted mb-1">${t.ten_loai_ve ?? ''}</div>
      <div class="fw-semibold mb-2">Mã vé #${t.ma_ve}</div>
      <img src="${t.qr_image_url}" alt="QR vé" class="qr-img img-fluid mb-2" style="max-width:180px">
      <div class="small text-warning">QR check-in — không dùng để thanh toán</div>
    </div>
  `).join('');
};

const poll = async () => {
  try {
    const res = await fetch(`index.php?action=payment_status&order_id=${orderId}`, { cache: 'no-store' });
    if (!res.ok) { setTimeout(poll, 5000); return; }
    const data = await res.json();
    if (!data.success) { setTimeout(poll, 5000); return; }
    if (data.paid) {
      statusBox.className = 'alert alert-success mt-3 mb-0';
      statusBox.textContent = '✅ Thanh toán thành công. Vé đã được phát hành.';
      statusBadge.className = 'badge-status bg-success';
      statusBadge.textContent = '✓ Đã thanh toán';
      renderTickets(data.tickets || []);
      return;
    }
    setTimeout(poll, 5000);
  } catch(e) { setTimeout(poll, 5000); }
};

<?php if (!$isPaid): ?>
setTimeout(poll, 5000);
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

