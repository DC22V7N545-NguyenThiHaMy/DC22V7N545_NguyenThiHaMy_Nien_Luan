<?php
// Main single-page view for the event ticket system.
// This page shows the home section and the login/register forms.
require_once __DIR__ . '/../model/EventTicket.php';
require_once __DIR__ . '/../model/News.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;
$newsModel = isset($conn) ? new News($conn) : null;
$homeTickets = $eventTicketModel ? $eventTicketModel->getTicketsForHome(8) : [];
$homeNews = $newsModel ? $newsModel->getNewsForHome(6) : [];

$pageTitle = 'Nền tảng bán vé sự kiện';
$bodyClass = 'bg-landing';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <section id="home" class="py-4 th-reveal">
    <?php $featured = $homeTickets[0] ?? null; ?>
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <span class="badge th-hero-badge mb-2 th-floating-badge">Nền tảng bán vé sự kiện cho giới trẻ</span>
        <h1 class="display-5 fw-bold th-hero-title">Đặt vé cực nhanh, trải nghiệm cực chill</h1>
        <p class="lead text-muted mb-4">
          Quản lý sự kiện, loại vé, đơn hàng và check-in người tham dự trên một hệ thống duy nhất.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($user): ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=profile">Vào bảng điều khiển</a>
          <?php else: ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=register">Bắt đầu bán vé</a>
            <a class="btn btn-outline-light btn-lg btn-th-outline px-4" href="index.php?action=login">Tôi đã có tài khoản</a>
          <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-4 text-muted small">
          <div class="th-chip"><strong>+10</strong> loại vé khác nhau</div>
          <div class="th-chip"><strong>Thống kê</strong> theo thời gian thực</div>
          <div class="th-chip"><strong>QR check-in</strong> tại cổng</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 th-card-glass th-reveal">
          <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold">Sự kiện nổi bật</span>
              <span class="badge bg-warning text-dark"><?= $featured ? 'Đang mở bán' : 'Chưa có sự kiện' ?></span>
            </div>
          </div>
          <div class="card-body">
            <?php if ($featured): ?>
              <a href="index.php?action=ticket_detail&id=<?= (int)$featured['ma_loai_ve'] ?>" class="text-decoration-none text-reset">
                <div class="d-flex flex-column flex-md-row gap-3">
                  <?php if (!empty($featured['hinh_anh'])): ?>
                    <img src="<?= htmlspecialchars((string)$featured['hinh_anh']) ?>"
                         class="img-fluid rounded object-fit-cover"
                         alt="<?= htmlspecialchars((string)$featured['ten_su_kien']) ?>"
                         style="max-width: 180px; height: 160px; flex-shrink: 0;">
                  <?php else: ?>
                    <div class="bg-secondary rounded" style="width: 180px; height: 160px; display:flex; align-items:center; justify-content:center; color:#f8fafc; flex-shrink: 0;">
                      <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                  <?php endif; ?>
                  <div>
                    <h5 class="card-title mb-1 text-white"><?= htmlspecialchars((string)$featured['ten_su_kien']) ?></h5>
                    <p class="mb-2 text-muted small"><?= htmlspecialchars((string)$featured['gio_to_chuc']) ?> • <?= htmlspecialchars((string)$featured['ngay_to_chuc']) ?> • <?= htmlspecialchars((string)$featured['dia_diem']) ?></p>
                    <span class="badge bg-success mb-2">Còn vé</span>
                    <p class="card-text small text-light"><?= htmlspecialchars(mb_strimwidth((string)$featured['mo_ta_su_kien'], 0, 120, '...')) ?></p>
                  </div>
                </div>
              </a>
            <?php else: ?>
              <div class="text-center text-muted py-4">Chưa có vé sự kiện đang mở bán.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="features" class="py-5 th-reveal">
    <div class="text-center mb-4 th-reveal">
      <h2 class="fw-bold">Tính năng cho web bán vé</h2>
      <p class="text-muted">Thiết kế để phục vụ cả nhà tổ chức sự kiện và người mua vé.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Dành cho nhà tổ chức</span>
            <h5 class="card-title">Quản lý sự kiện & loại vé</h5>
            <p class="card-text">
              Tạo sự kiện, cấu hình số lượng vé, giá theo từng loại (Early Bird, VIP, Standard, v.v.)
              và theo dõi số vé đã bán.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Dành cho người mua</span>
            <h5 class="card-title">Đặt vé nhanh & bảo mật</h5>
            <p class="card-text">
              Giao diện thanh toán đơn giản, lưu thông tin đơn hàng và gửi mã vé điện tử ngay sau khi hoàn tất.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">Vận hành</span>
            <h5 class="card-title">Check-in bằng QR code</h5>
            <p class="card-text">
              Mỗi vé tương ứng một mã QR duy nhất, giúp kiểm soát lượt vào cổng và tránh vé giả.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="tickets" class="py-4 th-reveal">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-0">Vé đang mở bán</h2>
      <a href="index.php?action=events" class="btn btn-outline-light btn-sm">Xem tất cả →</a>
    </div>
    <div class="row g-4">
      <?php if (!$homeTickets): ?>
        <div class="col-12">
          <div class="alert alert-dark border border-light border-opacity-10 mb-0">
            Chưa có vé nào. Admin cần thêm danh mục, sự kiện và loại vé ở trang quản trị.
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($homeTickets as $ticket): ?>
          <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 th-card-feature" style="overflow: hidden; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.12); transition: all 0.3s ease; cursor: pointer;">
              <!-- Image Section -->
              <div style="position: relative; height: 200px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <?php if (!empty($ticket['hinh_anh'])): ?>
                  <img src="<?= htmlspecialchars((string)$ticket['hinh_anh']) ?>" alt="Event" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); font-size: 48px;">🎫</div>
                <?php endif; ?>
                <!-- Badge Overlay -->
                <div style="position: absolute; top: 10px; right: 10px;">
                  <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 8px 12px; font-weight: 600;"><?= (int)$ticket['so_luong_con'] ?> vé</span>
                </div>
              </div>

              <!-- Content Section -->
              <div class="card-body" style="display: flex; flex-direction: column; height: 100%;">
                <!-- Category Badge -->
                <span class="badge" style="width: fit-content; background: rgba(102, 126, 234, 0.1); color: #667eea; padding: 6px 12px; margin-bottom: 12px; border-radius: 20px; font-weight: 600; font-size: 11px; text-transform: uppercase;"><?= htmlspecialchars((string)$ticket['ten_danh_muc']) ?></span>

                <!-- Event Name -->
                <h6 class="fw-bold mb-1" style="font-size: 18px; color: #fff; line-height: 1.4; flex-grow: 1;"><?= htmlspecialchars((string)$ticket['ten_su_kien']) ?></h6>

                <!-- Ticket Type -->
                <div class="small" style="color: #ffd700; font-weight: 600; margin-bottom: 12px;">🎟️ <?= htmlspecialchars((string)$ticket['ten_loai_ve']) ?></div>

                <!-- Price & Button -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div class="fw-bold" style="font-size: 18px; color: #ffd700;"><?= number_format((float)$ticket['gia_ve'], 0, ',', '.') ?> đ</div>
                  <a class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 6px 12px; font-weight: 600; font-size: 12px; transition: all 0.3s ease;" href="index.php?action=ticket_detail&id=<?= (int)$ticket['ma_loai_ve'] ?>">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <section id="news" class="py-4 th-reveal">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-0">Tin tức mới nhất</h2>
      <a href="index.php?action=news" class="btn btn-outline-light btn-sm">Xem tất cả →</a>
    </div>
    <div class="row g-4">
      <?php if (!$homeNews): ?>
        <div class="col-12">
          <div class="alert alert-dark border border-light border-opacity-10 mb-0">
            Chưa có tin tức nào. Admin sẽ cập nhật tin tức mới nhất.
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($homeNews as $news): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 th-card-feature" style="overflow: hidden; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.12); transition: all 0.3s ease; background: #ffffff !important;">
              <!-- Image Section -->
              <div style="position: relative; height: 200px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <?php if (!empty($news['hinh_anh'])): ?>
                  <img src="<?= htmlspecialchars((string)$news['hinh_anh']) ?>" alt="News" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); font-size: 48px;">📰</div>
                <?php endif; ?>
              </div>

              <!-- Content Section -->
              <div class="card-body" style="display: flex; flex-direction: column; height: 100%; background: #ffffff !important;">
                <!-- Title -->
                <h6 class="fw-bold mb-2" style="font-size: 16px; color: #000 !important; line-height: 1.4;">
                  <?= htmlspecialchars((string)$news['tieu_de']) ?>
                </h6>

                <!-- Meta Info -->
                <div class="small" style="color: #333 !important; font-weight: 600; margin-bottom: 8px;">
                  👤 <?= htmlspecialchars((string)$news['ten_nguoi_tao']) ?> • 📅 <?= date('d/m/Y', strtotime($news['ngay_tao'])) ?>
                </div>

                <!-- Excerpt -->
                <p class="small" style="color: #222 !important; margin-bottom: 12px; flex-grow: 1; line-height: 1.5;">
                  <?= htmlspecialchars(substr(strip_tags((string)$news['noi_dung']), 0, 120)) ?>...
                </p>

                <!-- Read More Button -->
                <div>
                  <a class="btn btn-sm w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; font-size: 12px; transition: all 0.3s ease;" href="index.php?action=news_detail&id=<?= (int)$news['ma_tin_tuc'] ?>">
                    <i class="fas fa-newspaper me-1"></i>Đọc thêm
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Nếu cần, có thể hiển thị một đoạn giới thiệu ngắn khi người dùng đã đăng nhập -->
</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

