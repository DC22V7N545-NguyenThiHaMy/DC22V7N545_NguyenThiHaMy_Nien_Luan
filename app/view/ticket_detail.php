<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/EventTicket.php';

$user = $_SESSION['user'] ?? null;
$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;
$ticketId = (int)($_GET['id'] ?? 0);
$ticket = $eventTicketModel ? $eventTicketModel->getTicketDetail($ticketId) : null;

if (!$ticket) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Không tìm thấy thông tin vé.'];
    header('Location: index.php');
    exit;
}

$pageTitle = 'Chi tiết vé - TicketHub';
$bodyClass = '';
require_once __DIR__ . '/layouts/header.php';
?>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  /* Hero Section */
  .ticket-hero {
    position: relative;
    height: 450px;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    margin-bottom: 40px;
  }

  .ticket-hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .ticket-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 40px;
    color: white;
  }

  .ticket-hero-title {
    font-size: 48px;
    font-weight: bold;
    margin-bottom: 15px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
  }

  .ticket-hero-type {
    font-size: 18px;
    color: rgba(255,255,255,0.9);
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
  }

  /* Main Content */
  .ticket-container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
    margin-bottom: 40px;
  }

  .ticket-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
  }

  .detail-card {
    padding: 25px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .detail-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }

  .detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
  }

  .detail-value {
    font-size: 20px;
    font-weight: bold;
    color: #333;
  }

  .detail-icon {
    font-size: 28px;
    margin-bottom: 10px;
  }

  /* Description */
  .description-section {
    margin-bottom: 40px;
    padding: 25px;
    background: rgba(102, 126, 234, 0.08);
    border-left: 4px solid #667eea;
    border-radius: 10px;
  }

  .description-section h3 {
    color: #333;
    margin-bottom: 15px;
    font-weight: bold;
  }

  .description-section p {
    color: #666;
    line-height: 1.8;
    margin: 0;
  }

  /* Right Sidebar */
  .ticket-purchase {
    position: sticky;
    top: 20px;
  }

  .purchase-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border: 2px solid #f0f0f0;
  }

  .price-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
  }

  .price-label {
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .price-value {
    font-size: 40px;
    font-weight: bold;
    color: #667eea;
    line-height: 1;
  }

  .price-unit {
    font-size: 16px;
    color: #666;
    margin-left: 5px;
  }

  .tickets-remain {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
  }

  .tickets-remain strong {
    font-size: 20px;
    display: block;
    margin-top: 5px;
  }

  .quantity-input {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 12px;
    font-size: 16px;
    margin-bottom: 20px;
  }

  .quantity-input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .btn-purchase {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-bottom: 12px;
  }

  .btn-purchase:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-purchase:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .btn-login {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    text-decoration: none;
  }

  .btn-login:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(245, 87, 108, 0.4);
  }

  .badge-category {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
  }

  .badge-available {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
  }

  .badge-sold {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  }

  @media (max-width: 768px) {
    .ticket-hero-title {
      font-size: 32px;
    }

    .ticket-hero-overlay {
      padding: 25px;
    }

    .ticket-container {
      padding: 20px;
    }

    .ticket-details-grid {
      grid-template-columns: 1fr;
    }

    .ticket-purchase {
      position: static;
      margin-top: 30px;
    }
  }
</style>


<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <!-- Hero Section with Image -->
  <div class="ticket-hero">
    <?php if (!empty($ticket['hinh_anh'])): ?>
      <img src="<?= htmlspecialchars((string)$ticket['hinh_anh']) ?>" alt="Event" class="ticket-hero-image">
    <?php else: ?>
      <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
    <?php endif; ?>
    <div class="ticket-hero-overlay">
      <h1 class="ticket-hero-title"><?= htmlspecialchars((string)$ticket['ten_su_kien']) ?></h1>
      <div class="ticket-hero-type">🎫 <?= htmlspecialchars((string)$ticket['ten_loai_ve']) ?></div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
      <div class="ticket-container">
        <!-- Badges -->
        <div class="d-flex gap-2 mb-30">
          <span class="badge-category"><?= htmlspecialchars((string)$ticket['ten_danh_muc']) ?></span>
          <span class="badge <?= (int)$ticket['so_luong_con'] > 0 ? 'badge-available' : 'badge-sold' ?>">
            <?= (int)$ticket['so_luong_con'] > 0 ? '✓ Còn vé' : '✗ Hết vé' ?>
          </span>
        </div>

        <!-- Details Grid -->
        <div class="ticket-details-grid">
          <div class="detail-card">
            <div class="detail-icon">📅</div>
            <div class="detail-label">Ngày tổ chức</div>
            <div class="detail-value"><?= htmlspecialchars((string)$ticket['ngay_to_chuc']) ?></div>
          </div>
          <div class="detail-card">
            <div class="detail-icon">⏰</div>
            <div class="detail-label">Thời gian</div>
            <div class="detail-value"><?= htmlspecialchars((string)$ticket['gio_to_chuc']) ?></div>
          </div>
          <div class="detail-card">
            <div class="detail-icon">📍</div>
            <div class="detail-label">Địa điểm</div>
            <div class="detail-value"><?= htmlspecialchars((string)$ticket['dia_diem']) ?></div>
          </div>
          <div class="detail-card">
            <div class="detail-icon">🎟️</div>
            <div class="detail-label">Số vé còn lại</div>
            <div class="detail-value"><?= (int)$ticket['so_luong_con'] ?></div>
          </div>
        </div>

        <!-- Description -->
        <?php if (!empty($ticket['mo_ta'])): ?>
          <div class="description-section">
            <h3>📝 Thông tin chi tiết</h3>
            <p><?= nl2br(htmlspecialchars((string)$ticket['mo_ta'])) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Purchase Sidebar -->
    <div class="col-lg-4">
      <div class="ticket-purchase">
        <div class="purchase-card">
          <div class="price-section">
            <div class="price-label">Giá vé</div>
            <div style="display: flex; align-items: baseline;">
              <span class="price-value"><?= number_format((float)$ticket['gia_ve'], 0, ',', '.') ?></span>
              <span class="price-unit">đ</span>
            </div>
          </div>

          <div class="tickets-remain">
            Còn lại
            <strong><?= (int)$ticket['so_luong_con'] ?> vé</strong>
          </div>

          <?php if ((int)$ticket['so_luong_con'] <= 0): ?>
            <button class="btn-purchase" disabled>✗ Đã hết vé</button>
            <a href="index.php" class="btn btn-outline-secondary w-100">Quay lại</a>
          <?php elseif (!$user): ?>
            <a class="btn-purchase btn-login" href="index.php?action=login" style="display: block; text-align: center; text-decoration: none; padding-top: 15px; padding-bottom: 15px;">
              🔑 Đăng nhập để mua vé
            </a>
          <?php elseif (($user['role'] ?? 'khach_hang') !== 'khach_hang'): ?>
            <div class="alert alert-message alert-warning mb-0">
              ⚠️ Chỉ tài khoản khách hàng mới có thể mua vé.
            </div>
          <?php else: ?>
            <div class="mb-3">
              <label class="form-label" style="font-weight: 600;">Số lượng</label>
              <input
                type="number"
                id="ticket_quantity"
                class="quantity-input"
                min="1"
                max="<?= (int)$ticket['so_luong_con'] ?>"
                value="1"
              >
            </div>

            <form method="POST" id="buy_form">
              <input type="hidden" name="action" value="buy_ticket">
              <input type="hidden" name="ma_loai_ve" value="<?= (int)$ticket['ma_loai_ve'] ?>">
              <input type="hidden" name="so_luong" id="buy_quantity">
              <button class="btn-purchase" type="button" onclick="submitBuy()">💳 Mua ngay</button>
            </form>

            <form method="POST" action="index.php" style="margin-top: 10px;" id="cart_form">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="ma_loai_ve" value="<?= (int)$ticket['ma_loai_ve'] ?>">
              <input type="hidden" name="so_luong" id="cart_quantity">
              <button class="btn-purchase" type="button" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); width: 100%;" onclick="submitToCart()">🛒 Thêm vào giỏ</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
function submitBuy() {
  const quantity = document.getElementById('ticket_quantity').value;
  if (quantity < 1) {
    alert('Vui lòng chọn số lượng hợp lệ');
    return;
  }
  if (confirm('Xác nhận mua vé này?')) {
    document.getElementById('buy_quantity').value = quantity;
    document.getElementById('buy_form').submit();
  }
}

function submitToCart() {
  const quantity = document.getElementById('ticket_quantity').value;
  if (quantity < 1) {
    alert('Vui lòng chọn số lượng hợp lệ');
    return;
  }
  document.getElementById('cart_quantity').value = quantity;
  document.getElementById('cart_form').submit();
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

