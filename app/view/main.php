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

$banners = $eventTicketModel ? $eventTicketModel->getBanners() : [];
$specialEvents = $eventTicketModel ? $eventTicketModel->getSpecialEvents(8) : [];
$trendingEvents = $eventTicketModel ? $eventTicketModel->getTrendingEvents() : [];
$recommendedEvents = $eventTicketModel ? $eventTicketModel->getRecommendedEvents() : [];

$pageTitle = 'TicketHub _ Nền tảng bán vé sự kiện';
$bodyClass = 'bg-landing';
require_once __DIR__ . '/layouts/header.php';
?>

<hr class="border-secondary opacity-25 m-0">

<!-- BANNER WITH MARGIN -->
<?php if (!empty($banners)): ?>
  <div class="mx-auto mt-2 mb-2" style="max-width: 1440px; padding: 0 15px;">
  <div id="heroCarousel" class="carousel slide shadow-sm overflow-hidden" data-bs-ride="carousel">
    
    <!-- Indicators -->
    <div class="carousel-indicators" style="bottom: 15px;">
      <?php foreach ($banners as $index => $banner): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>" style="width: 10px; height: 10px; border-radius: 50%; opacity: 0.8; margin: 0 4px; border: none;"></button>
      <?php endforeach; ?>
    </div>

    <div class="carousel-inner">
      <?php foreach ($banners as $index => $banner): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
          <a href="<?= htmlspecialchars((string)$banner['duong_dan'] ?? '#') ?>" class="d-block w-100 position-relative" style="height: 540px; overflow: hidden;">
            <!-- Lớp nền: ảnh phóng to + blur lấp đầy khoảng trống 2 bên -->
            <img src="<?= htmlspecialchars((string)$banner['hinh_anh']) ?>" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; filter: blur(20px) brightness(0.5); transform: scale(1.1);" alt="">
            <!-- Lớp chính: ảnh gốc hiển thị đầy đủ chi tiết -->
            <img src="<?= htmlspecialchars((string)$banner['hinh_anh']) ?>" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: contain; object-position: center;" alt="<?= htmlspecialchars((string)$banner['tieu_de']) ?>">
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 50px; left: 5px; opacity: 1;">
      <span class="d-flex align-items-center justify-content-center" style="width: 36px; height: 60px; background: rgba(0,50,30,0.55); border-radius: 8px; backdrop-filter: blur(4px);" aria-hidden="true">
        <span style="font-size: 22px; color: #fff; font-weight: bold; line-height: 1;">&#10094;</span>
      </span>
      <span class="visually-hidden">Trước</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 50px; right: 5px; opacity: 1;">
      <span class="d-flex align-items-center justify-content-center" style="width: 36px; height: 60px; background: rgba(0,50,30,0.55); border-radius: 8px; backdrop-filter: blur(4px);" aria-hidden="true">
        <span style="font-size: 22px; color: #fff; font-weight: bold; line-height: 1;">&#10095;</span>
      </span>
      <span class="visually-hidden">Sau</span>
    </button>
  </div>
</div>
<?php endif; ?>

<hr class="border-secondary opacity-25 m-0">

<!-- SỰ KIỆN XU HƯỚNG - đặt dưới banner, trên nội dung chính -->
<?php if (!empty($trendingEvents)): ?>
<div class="mx-auto mt-2 mb-0" style="max-width: 1440px; padding: 0 15px;">
  <section class="py-3">
    <div class="d-flex align-items-center mb-3">
        <h2 class="fw-bold mb-0">🔥 Sự kiện xu hướng</h2>
    </div>
    <div class="position-relative">
      <button class="btn position-absolute top-50 start-0 translate-middle-y shadow d-none d-md-flex align-items-center justify-content-center" onclick="document.getElementById('trendScroll').scrollBy({left:-320,behavior:'smooth'})" style="width:36px;height:60px;z-index:5;left:5px;background:rgba(0,50,30,0.55);border-radius:8px;backdrop-filter:blur(4px);border:none;">
        <span style="font-size:22px;color:#fff;font-weight:bold;line-height:1;">&#10094;</span>
      </button>
      <button class="btn position-absolute top-50 end-0 translate-middle-y shadow d-none d-md-flex align-items-center justify-content-center" onclick="document.getElementById('trendScroll').scrollBy({left:320,behavior:'smooth'})" style="width:36px;height:60px;z-index:5;right:5px;background:rgba(0,50,30,0.55);border-radius:8px;backdrop-filter:blur(4px);border:none;">
        <span style="font-size:22px;color:#fff;font-weight:bold;line-height:1;">&#10095;</span>
      </button>
      <div id="trendScroll" class="d-flex gap-3 overflow-auto pb-2" style="scroll-snap-type:x mandatory; -ms-overflow-style:none; scrollbar-width:none;">
        <?php foreach ($trendingEvents as $index => $ev): ?>
          <?php $eventId = (int)$ev['ma_su_kien']; ?>
          <a href="index.php?action=ticket_detail&id=<?= $eventId ?>" class="text-decoration-none flex-shrink-0" style="width:260px; scroll-snap-align:start;">
            <div class="rounded-4 overflow-hidden position-relative" style="height:200px;">
              <img src="<?= htmlspecialchars((string)$ev['hinh_anh']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="<?= htmlspecialchars((string)$ev['ten_su_kien']) ?>">
              <!-- Số thứ tự overlay lớn -->
              <div class="position-absolute bottom-0 start-0" style="font-size:100px; font-weight:900; line-height:0.75; color:rgba(255,255,255,0.35); pointer-events:none; padding:0 0 0 8px; font-family:'Inter','Arial Black',sans-serif; text-shadow:0 2px 15px rgba(0,0,0,0.5); z-index:1;">
                <?= $index + 1 ?>
              </div>
              <!-- Gradient overlay -->
              <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background:linear-gradient(transparent, rgba(0,0,0,0.8));">
                <div class="text-white fw-bold small" style="line-height:1.3;"><?= htmlspecialchars(mb_strimwidth((string)$ev['ten_su_kien'], 0, 45, '...')) ?></div>
                <div class="text-white-50" style="font-size:11px;"><i class="far fa-calendar-alt me-1"></i><?= htmlspecialchars((string)$ev['ngay_to_chuc']) ?></div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <style>#trendScroll::-webkit-scrollbar{display:none}</style>
  </section>
</div>
<?php endif; ?>

<hr class="border-secondary opacity-25 m-0">

<main class="container pt-4 pb-3">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <section id="home" class="pb-1 th-reveal">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <span class="badge th-hero-badge mb-2 th-floating-badge">🎪 Sân chơi vé sự kiện #1 cho Gen Z</span>
        <h1 class="display-5 fw-bold th-hero-title">Một chạm — có vé.<br>Một quét — vào cổng.</h1>
        <p class="lead text-muted mb-4">
          Không xếp hàng. Không giấy tờ. Chỉ cần điện thoại — bạn đã sẵn sàng bùng nổ cùng hàng ngàn sự kiện đỉnh nhất.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($user): ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=profile">Vào bảng điều khiển</a>
          <?php else: ?>
            <a class="btn btn-th-primary btn-lg rounded-pill px-4" href="index.php?action=register">🚀 Tạo tài khoản miễn phí</a>
            <a class="btn btn-outline-light btn-lg btn-th-outline px-4" href="index.php?action=login">Đăng nhập ngay</a>
          <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-4 text-muted small">
          <div class="th-chip">⚡ Đặt vé trong <strong>30 giây</strong></div>
          <div class="th-chip">📊 Dashboard <strong>real-time</strong></div>
          <div class="th-chip">📱 Quét QR — <strong>vào cổng tức thì</strong></div>
        </div>
      </div>
      <div class="col-lg-6">
        <?php
          // Normalize: ưu tiên specialEvents, fallback homeTickets
          $ft = !empty($specialEvents) ? $specialEvents[0] : ($homeTickets[0] ?? null);
          $ftLink = $ft ? ('index.php?action=ticket_detail&id=' . (int)($ft['ma_su_kien'] ?? $ft['ma_loai_ve'] ?? 0)) : '#';
          $ftDesc = $ft['mo_ta'] ?? $ft['mo_ta_su_kien'] ?? '';
        ?>
        <div class="card border-0 th-card-glass th-reveal overflow-hidden" style="border-radius: 16px;">
          <div class="card-header bg-dark text-white py-3" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold" style="font-size: 15px;">⭐ Sự kiện đặc biệt</span>
              <span class="badge bg-warning text-dark"><?= $ft ? 'Đang mở bán' : 'Chưa có sự kiện' ?></span>
            </div>
          </div>
          <div class="card-body p-0">
            <?php if ($ft): ?>
              <a href="<?= $ftLink ?>" class="text-decoration-none text-reset d-block">
                <?php if (!empty($ft['hinh_anh'])): ?>
                  <div style="height: 220px; overflow: hidden; position: relative;">
                    <img src="<?= htmlspecialchars((string)$ft['hinh_anh']) ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars((string)$ft['ten_su_kien']) ?>">
                    <span class="badge position-absolute bottom-0 start-0 m-3" style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);padding:6px 14px;font-size:12px;">
                      <?= htmlspecialchars((string)$ft['ten_danh_muc']) ?>
                    </span>
                  </div>
                <?php endif; ?>
                <div class="p-4">
                  <h5 class="fw-bold text-white mb-2" style="line-height: 1.4;"><?= htmlspecialchars((string)$ft['ten_su_kien']) ?></h5>
                  <div class="text-muted small mb-3">
                    <div class="mb-1">📅 <?= htmlspecialchars((string)$ft['ngay_to_chuc']) ?> · <?= htmlspecialchars((string)$ft['gio_to_chuc']) ?></div>
                    <div class="text-truncate">📍 <?= htmlspecialchars((string)$ft['dia_diem']) ?></div>
                  </div>
                  <?php if ($ftDesc): ?>
                    <p class="text-muted small mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.6;">
                      <?= htmlspecialchars(mb_strimwidth(strip_tags((string)$ftDesc), 0, 150, '...')) ?>
                    </p>
                  <?php endif; ?>
                </div>
              </a>
            <?php else: ?>
              <div class="text-center text-muted py-5">Chưa có sự kiện đặc biệt nào.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="features" class="py-5 th-reveal">
    <div class="text-center mb-4 th-reveal">
      <h2 class="fw-bold">Tất cả trong một. Không thiếu thứ gì.</h2>
      <p class="text-muted">Từ ý tưởng sự kiện đến lúc khán giả quét vé vào cổng — TicketHub lo hết.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">🎯 Nhà tổ chức</span>
            <h5 class="card-title">Dựng sự kiện trong 5 phút</h5>
            <p class="card-text">
              Tạo sự kiện, tuỳ chỉnh vé VIP · Early Bird · Standard, set giá linh hoạt — theo dõi doanh thu real-time trên một dashboard duy nhất.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">🎫 Người mua vé</span>
            <h5 class="card-title">Chọn — Thanh toán — Nhận vé. Done!</h5>
            <p class="card-text">
              3 bước duy nhất. Thanh toán an toàn, vé điện tử gửi về tức thì — không sợ mất, không sợ giả.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 th-reveal">
        <div class="card h-100 border-0 th-card-feature">
          <div class="card-body">
            <span class="badge bg-light text-dark mb-2">⚙️ Vận hành</span>
            <h5 class="card-title">Quét QR — 1 giây — Xong!</h5>
            <p class="card-text">
              Mỗi vé = 1 mã QR duy nhất. Kiểm soát cổng chính xác, loại bỏ vé giả, dữ liệu check-in cập nhật live.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SỰ KIỆN ĐẶC BIỆT (scroll ngang) -->
  <?php if (!empty($specialEvents)): ?>
  <section class="mb-5 mt-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="fw-bold mb-0">Sự kiện đặc biệt</h2>
        <a href="index.php?action=events" class="btn btn-outline-light btn-sm">Xem tất cả →</a>
    </div>
    <div class="position-relative">
      <!-- Nút scroll -->
      <button class="btn position-absolute top-50 start-0 translate-middle-y shadow d-none d-md-flex align-items-center justify-content-center" onclick="document.getElementById('specialScroll').scrollBy({left:-320,behavior:'smooth'})" style="width:36px;height:60px;z-index:5;left:5px;background:rgba(0,50,30,0.55);border-radius:8px;backdrop-filter:blur(4px);border:none;">
        <span style="font-size:22px;color:#fff;font-weight:bold;line-height:1;">&#10094;</span>
      </button>
      <button class="btn position-absolute top-50 end-0 translate-middle-y shadow d-none d-md-flex align-items-center justify-content-center" onclick="document.getElementById('specialScroll').scrollBy({left:320,behavior:'smooth'})" style="width:36px;height:60px;z-index:5;right:5px;background:rgba(0,50,30,0.55);border-radius:8px;backdrop-filter:blur(4px);border:none;">
        <span style="font-size:22px;color:#fff;font-weight:bold;line-height:1;">&#10095;</span>
      </button>
      <!-- Container scroll -->
      <div id="specialScroll" class="d-flex gap-3 overflow-auto pb-2" style="scroll-snap-type:x mandatory; -ms-overflow-style:none; scrollbar-width:none;">
        <?php foreach ($specialEvents as $ev): ?>
          <?php $eventId = (int)$ev['ma_su_kien']; ?>
          <a href="index.php?action=ticket_detail&id=<?= $eventId ?>" class="text-decoration-none flex-shrink-0" style="width:220px; scroll-snap-align:start;">
            <div class="rounded-4 overflow-hidden position-relative" style="height:300px;">
              <img src="<?= htmlspecialchars((string)$ev['hinh_anh']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="<?= htmlspecialchars((string)$ev['ten_su_kien']) ?>">
              <!-- Gradient overlay phía dưới -->
              <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background:linear-gradient(transparent, rgba(0,0,0,0.85));">
                <div class="text-white fw-bold small" style="line-height:1.3;"><?= htmlspecialchars(mb_strimwidth((string)$ev['ten_su_kien'], 0, 50, '...')) ?></div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <style>#specialScroll::-webkit-scrollbar{display:none}</style>
  </section>
  <?php endif; ?>



  <!-- DÀNH CHO BẠN -->
  <?php if (!empty($recommendedEvents)): ?>
  <section class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="fw-bold mb-0">Dành cho bạn</h2>
      <a href="index.php?action=events" class="btn btn-outline-light btn-sm">Xem tất cả →</a>
    </div>
    <div class="row g-4">
      <?php foreach ($recommendedEvents as $ev): ?>
        <?php $eventId = (int)$ev['ma_su_kien']; ?>
        <div class="col-6 col-md-3">
          <a href="index.php?action=ticket_detail&id=<?= $eventId ?>" class="text-decoration-none">
            <div class="rounded-4 overflow-hidden position-relative th-card-hover" style="height:200px;">
              <img src="<?= htmlspecialchars((string)$ev['hinh_anh']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="<?= htmlspecialchars((string)$ev['ten_su_kien']) ?>">
              <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background:linear-gradient(transparent, rgba(0,0,0,0.85));">
                <div class="text-white fw-bold small" style="line-height:1.3;"><?= htmlspecialchars(mb_strimwidth((string)$ev['ten_su_kien'], 0, 40, '...')) ?></div>
                <div class="text-white-50" style="font-size:11px;"><i class="far fa-calendar-alt me-1"></i><?= htmlspecialchars((string)$ev['ngay_to_chuc']) ?></div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <hr class="border-secondary opacity-25 mb-5">
  <?php endif; ?>

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
          <?php
            $soLuong = (int)($ticket['so_luong'] ?? 0);
            $soLuongCon = (int)($ticket['so_luong_con'] ?? 0);
            $daBan = $soLuong - $soLuongCon;
            $phanTram = $soLuong > 0 ? round(($daBan / $soLuong) * 100) : 0;
          ?>
          <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 th-card-feature" style="overflow: hidden; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.12); transition: all 0.3s ease;">
              <!-- Ảnh sự kiện -->
              <div style="position: relative; height: 200px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <?php if (!empty($ticket['hinh_anh'])): ?>
                  <img src="<?= htmlspecialchars((string)$ticket['hinh_anh']) ?>" alt="Event" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); font-size: 48px;">🎫</div>
                <?php endif; ?>
                <!-- Số vé còn lại -->
                <div style="position: absolute; top: 10px; right: 10px;">
                  <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 8px 12px; font-weight: 600;"><?= $soLuongCon ?> vé</span>
                </div>
                <!-- Danh mục overlay góc dưới trái -->
                <div style="position: absolute; bottom: 10px; left: 10px;">
                  <span class="badge" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); padding: 6px 12px; font-weight: 600; font-size: 11px;"><?= htmlspecialchars((string)$ticket['ten_danh_muc']) ?></span>
                </div>
              </div>

              <!-- Nội dung chi tiết -->
              <div class="card-body d-flex flex-column" style="padding: 16px;">
                <!-- Tên sự kiện -->
                <h6 class="fw-bold mb-2" style="font-size: 16px; color: #fff; line-height: 1.4;"><?= htmlspecialchars((string)$ticket['ten_su_kien']) ?></h6>

                <!-- Mô tả ngắn -->
                <?php if (!empty($ticket['mo_ta_su_kien'])): ?>
                  <p class="text-muted small mb-3" style="line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars(mb_strimwidth(strip_tags((string)$ticket['mo_ta_su_kien']), 0, 100, '...')) ?>
                  </p>
                <?php endif; ?>

                <!-- Ngày giờ + Địa điểm -->
                <div class="small text-muted mb-3" style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                  <div class="mb-1">📅 <?= htmlspecialchars((string)$ticket['ngay_to_chuc']) ?> <?= htmlspecialchars((string)$ticket['gio_to_chuc']) ?></div>
                  <div class="text-truncate">📍 <?= htmlspecialchars((string)$ticket['dia_diem']) ?></div>
                </div>

                <!-- Vé đã bán -->
                <div class="small mb-2" style="color: #a78bfa;">
                  <?= number_format($daBan) ?>/<?= number_format($soLuong) ?> vé đã bán
                </div>
                <!-- Thanh tiến trình -->
                <div class="progress mb-3" style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px;">
                  <div class="progress-bar" style="width: <?= $phanTram ?>%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 2px;"></div>
                </div>

                <!-- Giá + Nút mua -->
                <div class="mt-auto">
                  <div class="small text-muted mb-1">Từ</div>
                  <div class="fw-bold mb-3" style="font-size: 20px; color: #ffd700;"><?= number_format((float)$ticket['gia_ve'], 0, ',', '.') ?> đ</div>
                  <a class="btn w-100 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; padding: 10px; font-size: 14px; transition: all 0.3s ease;" href="index.php?action=ticket_detail&id=<?= (int)$ticket['ma_loai_ve'] ?>">
                    Mua vé ngay
                  </a>
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


</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

