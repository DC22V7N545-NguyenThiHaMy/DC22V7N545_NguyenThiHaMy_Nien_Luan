<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/EventTicket.php';

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header('Location: index.php?action=login');
    exit;
}

$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;
$orders = [];
$allTickets = [];

if ($eventTicketModel) {
    $orders = $eventTicketModel->getOrdersByCustomerEmail($user['email']);
    
    // Lấy vé cho mỗi order đã thanh toán
    foreach ($orders as $order) {
        if (($order['trang_thai_thanh_toan'] ?? '') === 'da_thanh_toan') {
            $tickets = $eventTicketModel->getGeneratedTicketsByOrder((int)$order['ma_don_hang']);
            if ($tickets) {
                $allTickets = array_merge($allTickets, $tickets);
            }
        }
    }
}

$pageTitle = 'Tài khoản - TicketHub';
$bodyClass = 'th-page-gradient';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container-fluid py-5" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: calc(100vh - 120px);">
  <div class="container">
    <!-- Header Profile -->
    <div class="row g-4 mb-5">
      <div class="col-lg-12">
        <div class="card border-0 shadow-lg rounded-4" style="background: rgba(255,255,255,0.95);">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-auto">
                <div class="rounded-circle p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
              </div>
              <div class="col">
                <h1 class="display-6 mb-1 text-dark"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="text-muted mb-2">📧 <?= htmlspecialchars($user['email']) ?></p>
                <div>
                  <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;">
                    ✓ <?= htmlspecialchars($user['role'] ?? 'khách') ?>
                  </span>
                </div>
              </div>
              <div class="col-auto">
                <div class="text-end">
                  <a href="index.php?action=logout" class="btn btn-outline-danger btn-lg rounded-3">
                    Đăng xuất
                  </a>
                  <button class="btn btn-outline-primary btn-lg rounded-3 ms-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    Đổi mật khẩu
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets Section -->
    <?php if ($allTickets): ?>
    <div class="row g-4 mb-5">
      <div class="col-12">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="card-header p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h2 class="h4 mb-1 text-white" style="font-weight: 700;">🎫 Vé của bạn</h2>
                <p class="text-white-50 mb-0">Tổng: <strong><?= count($allTickets) ?> vé</strong></p>
              </div>
            </div>
          </div>
          <div class="card-body p-4">
            <!-- Filter Controls -->
            <div class="row g-3 mb-4 p-3 rounded-3" style="background: #f8f9fa;">
              <div class="col-md-5">
                <label class="form-label fw-semibold small text-dark">📅 Lọc theo ngày</label>
                <input type="text" id="filterDate" class="form-control form-control-lg rounded-2" placeholder="dd/mm/yyyy" style="border: 2px solid #e0e0e0;">
              </div>
              <div class="col-md-5">
                <label class="form-label fw-semibold small text-dark">🕐 Lọc theo giờ</label>
                <input type="number" id="filterHour" min="0" max="23" class="form-control form-control-lg rounded-2" placeholder="0-23 (tùy chọn)" style="border: 2px solid #e0e0e0;">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button onclick="filterTickets()" class="btn btn-primary btn-lg w-100 rounded-2" style="font-weight: 600;">
                  Lọc
                </button>
              </div>
            </div>

            <!-- Tickets Grid -->
            <div class="row g-4" id="ticketsContainer">
              <?php foreach ($allTickets as $ticket): 
                $ticketDate = isset($ticket['ngay_tao']) ? substr($ticket['ngay_tao'], 0, 10) : '';
                $ticketTime = isset($ticket['ngay_tao']) ? substr($ticket['ngay_tao'], 11, 8) : '';
              ?>
              <div class="col-lg-4 col-md-6 ticket-card" data-date="<?= htmlspecialchars($ticketDate) ?>" data-time="<?= htmlspecialchars($ticketTime) ?>">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="transition: all 0.3s ease; cursor: pointer;">
                  <div class="card-header p-3 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <span class="badge bg-light text-dark fw-bold px-3 py-2">🎫 <?= htmlspecialchars((string)$ticket['ten_loai_ve']) ?></span>
                  </div>
                  <div class="card-body p-4 text-center">
                    <div class="mb-3">
                      <p class="small mb-1 fw-semibold text-dark">Mã vé</p>
                      <p class="h5 fw-bold text-dark" style="font-family: monospace; letter-spacing: 2px;"><?= htmlspecialchars((string)$ticket['ma_qr']) ?></p>
                    </div>
                    <div class="mb-4">
                      <p class="small mb-2 fw-semibold text-dark">QR Code</p>
                      <?php if (!empty($ticket['qr_image_url'])): ?>
                        <img src="<?= htmlspecialchars($ticket['qr_image_url']) ?>" alt="QR Code" class="rounded-3" style="width: 100%; max-width: 180px; height: auto; border: 3px solid #e0e0e0;">
                      <?php else: ?>
                        <div class="alert alert-warning small mb-0">⚠️ QR code chưa có</div>
                      <?php endif; ?>
                    </div>
                    <div class="mb-3">
                      <span class="badge bg-success px-3 py-2 fw-semibold">
                        ✓ <?= htmlspecialchars((string)($ticket['trang_thai'] ?? 'chưa sử dụng')) ?>
                      </span>
                    </div>
                    <div class="border-top pt-3">
                      <p class="fw-semibold text-dark small mb-0">
                        📅 <?= htmlspecialchars($ticketDate) ?> | 🕐 <?= htmlspecialchars($ticketTime) ?>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Orders History Section -->
    <?php if ($orders): ?>
    <div class="row g-4">
      <div class="col-12">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="card-header p-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h2 class="h4 mb-1 text-white" style="font-weight: 700;">📋 Lịch sử đơn hàng</h2>
                <p class="text-white-50 mb-0">Tổng: <strong><?= count($orders) ?> đơn</strong></p>
              </div>
            </div>
          </div>
          <div class="card-body p-4">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8f9fa;">
                  <tr>
                    <th scope="col" class="fw-bold text-dark">Số đơn</th>
                    <th scope="col" class="fw-bold text-dark">Số tiền</th>
                    <th scope="col" class="fw-bold text-dark">Trạng thái</th>
                    <th scope="col" class="fw-bold text-dark">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order): ?>
                  <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td class="fw-bold text-primary">#<?= htmlspecialchars((string)$order['ma_don_hang']) ?></td>
                    <td class="fw-semibold text-dark"><?= number_format((float)$order['tong_tien']) ?> <span style="color: #ffc107;">₫</span></td>
                    <td>
                      <?php
                        $status = (string)($order['trang_thai_thanh_toan'] ?? '');
                        if ($status === 'da_thanh_toan') {
                          echo '<span class="badge bg-success px-3 py-2">✓ Đã thanh toán</span>';
                        } else {
                          echo '<span class="badge bg-warning px-3 py-2">⏱ Chờ xác nhận</span>';
                        }
                      ?>
                    </td>
                    <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$order['ngay_tao']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <div class="col-12">
        <div class="card border-0 shadow-lg rounded-4">
          <div class="card-body p-5 text-center">
            <div style="font-size: 64px; color: #ccc; margin-bottom: 20px;">📦</div>
            <p class="text-muted h5">Bạn chưa có đơn hàng nào</p>
            <p class="text-muted small">Hãy <a href="index.php" class="text-primary fw-bold">khám phá các sự kiện</a> để đặt vé ngay!</p>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<style>
  .card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-5px);
    transition: all 0.3s ease;
  }
  
  .table-hover tbody tr:hover {
    background-color: #f8f9fa !important;
  }
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>


