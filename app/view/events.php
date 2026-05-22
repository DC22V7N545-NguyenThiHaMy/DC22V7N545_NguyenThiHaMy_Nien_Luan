<?php
require_once __DIR__ . '/../model/EventTicket.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
$eventTicketModel = isset($conn) ? new EventTicket($conn) : null;

$categories = $eventTicketModel ? $eventTicketModel->getCategories() : [];
$events = $eventTicketModel ? $eventTicketModel->getEvents() : [];
$ticketTypes = $eventTicketModel ? $eventTicketModel->getTicketTypesForAdmin() : [];

// Filter by category if provided
$selectedCategory = (int)($_GET['category'] ?? 0);
$searchQuery = trim($_GET['search'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$filteredEvents = [];
foreach ($events as $ev) {
    if (($ev['trang_thai'] ?? '') !== 'da_duyet') continue;
    $matchCategory = !$selectedCategory || (int)$ev['ma_danh_muc'] === $selectedCategory;
    
    // Search by event name or ticket type name
    $matchSearch = true;
    if ($searchQuery) {
        $matchEventName = stripos((string)$ev['ten_su_kien'], $searchQuery) !== false;
        
        // Check if any ticket type for this event matches
        $matchTicketType = false;
        foreach ($ticketTypes as $tt) {
            if ((int)$tt['ma_su_kien'] === (int)$ev['ma_su_kien'] && 
                stripos((string)$tt['ten_loai_ve'], $searchQuery) !== false) {
                $matchTicketType = true;
                break;
            }
        }
        $matchSearch = $matchEventName || $matchTicketType;
    }
    
    // Filter by Date
    $matchDate = true;
    if ($fromDate && $ev['ngay_to_chuc'] < $fromDate) {
        $matchDate = false;
    }
    if ($toDate && $ev['ngay_to_chuc'] > $toDate) {
        $matchDate = false;
    }
    
    if ($matchCategory && $matchSearch && $matchDate) {
        $filteredEvents[] = $ev;
    }
}

$pageTitle = 'Danh sách sự kiện - TicketHub';
$bodyClass = 'bg-landing';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <section class="mb-5">
    <div class="row align-items-center g-4 mb-4">
      <div class="col-lg-4">
        <h1 class="display-6 fw-bold mb-2">Khám phá sự kiện</h1>
        <p class="text-muted">Tìm và đặt vé cho các sự kiện hấp dẫn</p>
      </div>
      <div class="col-lg-8">
        <form method="GET" class="d-flex flex-wrap gap-2 justify-content-lg-end">
          <input type="hidden" name="action" value="events">
          
          <!-- Lọc theo ngày -->
          <input type="date" name="from_date" class="form-control" style="width: 140px;" value="<?= htmlspecialchars($fromDate) ?>" title="Từ ngày">
          <span class="d-flex align-items-center mb-0 text-muted">-</span>
          <input type="date" name="to_date" class="form-control" style="width: 140px;" value="<?= htmlspecialchars($toDate) ?>" title="Đến ngày">
          
          <!-- Lọc theo tên -->
          <input type="text" name="search" class="form-control" style="max-width: 220px;" placeholder="Tìm kiếm sự kiện..." value="<?= htmlspecialchars($searchQuery) ?>">
          <button type="submit" class="btn btn-th-primary">
            <i class="fas fa-filter"></i> Lọc
          </button>
        </form>
      </div>
    </div>

    <!-- Filter by Category -->
    <div class="mb-4">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small">Lọc theo danh mục:</span>
        <a href="index.php?action=events<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?><?= $fromDate ? '&from_date='.urlencode($fromDate) : '' ?><?= $toDate ? '&to_date='.urlencode($toDate) : '' ?>" class="btn btn-sm <?= !$selectedCategory ? 'btn-th-primary' : 'btn-outline-light' ?> rounded-pill">Tất cả</a>
        <?php foreach ($categories as $cat): ?>
          <a href="index.php?action=events&category=<?= (int)$cat['ma_danh_muc'] ?>&search=<?= urlencode($searchQuery) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" 
             class="btn btn-sm <?= ($selectedCategory === (int)$cat['ma_danh_muc']) ? 'btn-th-primary' : 'btn-outline-light' ?> rounded-pill">
            <?= htmlspecialchars((string)$cat['ten_danh_muc']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Results Info -->
    <div class="mb-3">
      <p class="text-muted small">
        <?php if ($searchQuery): ?>
          Kết quả tìm kiếm cho "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
          <?php if ($selectedCategory): ?>
            trong danh mục <strong><?= htmlspecialchars((string)(array_values(array_filter($categories, fn($c) => (int)$c['ma_danh_muc'] === $selectedCategory))[0]['ten_danh_muc'] ?? '')) ?></strong>
          <?php endif; ?>
          (<?= count($filteredEvents) ?> kết quả)
        <?php elseif ($selectedCategory): ?>
          <strong><?= htmlspecialchars((string)(array_values(array_filter($categories, fn($c) => (int)$c['ma_danh_muc'] === $selectedCategory))[0]['ten_danh_muc'] ?? '')) ?></strong> (<?= count($filteredEvents) ?> sự kiện)
        <?php else: ?>
          Tất cả sự kiện (<?= count($filteredEvents) ?> sự kiện)
        <?php endif; ?>
      </p>
    </div>

    <!-- Events Grid -->
    <div class="row g-4">
      <?php if (!$filteredEvents): ?>
        <div class="col-12">
          <div class="alert alert-dark border border-light border-opacity-10 text-center py-5" role="alert">
            <div class="mb-3" style="font-size: 48px;">🔍</div>
            <h5 class="mb-2">Không tìm thấy sự kiện nào</h5>
            <p class="text-muted mb-3">Vui lòng thử lại với từ khóa khác hoặc quay lại sau.</p>
            <a href="index.php?action=events" class="btn btn-th-primary btn-sm">Xem tất cả sự kiện</a>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($filteredEvents as $event): ?>
          <?php
            $eventId = (int)$event['ma_su_kien'];
            $eventTickets = array_filter($ticketTypes, fn($t) => (int)$t['ma_su_kien'] === $eventId);
            $hasTickets = count($eventTickets) > 0;
            $availableTickets = array_filter($eventTickets, fn($t) => (int)$t['so_luong_con'] > 0);
            $ticketsSold = 0;
            $ticketsTotal = 0;
            foreach ($eventTickets as $t) {
              $ticketsSold += ((int)$t['so_luong'] - (int)$t['so_luong_con']);
              $ticketsTotal += (int)$t['so_luong'];
            }
          ?>
          <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 th-card-feature" style="overflow: hidden; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.12); transition: all 0.3s ease;">
              <!-- Image Section -->
              <div style="position: relative; height: 200px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <?php if (!empty($event['hinh_anh'])): ?>
                  <img src="<?= htmlspecialchars((string)$event['hinh_anh']) ?>" alt="Event" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); font-size: 48px;">🎫</div>
                <?php endif; ?>
                
                <!-- Status Badge -->
                <div style="position: absolute; top: 10px; right: 10px;">
                  <?php if (!$hasTickets): ?>
                    <span class="badge bg-danger" style="padding: 8px 12px; font-weight: 600;">Hết vé</span>
                  <?php elseif (count($availableTickets) === 0): ?>
                    <span class="badge bg-danger" style="padding: 8px 12px; font-weight: 600;">Hết vé</span>
                  <?php else: ?>
                    <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 8px 12px; font-weight: 600;"><?= array_sum(array_map(fn($t) => (int)$t['so_luong_con'], $availableTickets)) ?> vé</span>
                  <?php endif; ?>
                </div>

                <!-- Category Badge -->
                <div style="position: absolute; bottom: 10px; left: 10px;">
                  <span class="badge" style="background: rgba(0,0,0,0.6); padding: 6px 12px; font-size: 11px;"><?= htmlspecialchars((string)$event['ten_danh_muc']) ?></span>
                </div>
              </div>

              <!-- Content Section -->
              <div class="card-body" style="display: flex; flex-direction: column; height: 100%;">
                <!-- Event Name -->
                <h6 class="fw-bold mb-2" style="font-size: 16px; color: #333; line-height: 1.4;">
                  <?= htmlspecialchars((string)$event['ten_su_kien']) ?>
                </h6>

                <!-- Description -->
                <p class="small" style="color: #666; margin-bottom: 12px; flex-grow: 1; line-height: 1.5;">
                  <?= htmlspecialchars(mb_strimwidth((string)($event['mo_ta'] ?? 'Sự kiện đang mở bán, đặt vé ngay để giữ chỗ đẹp.'), 0, 70, '...')) ?>
                </p>

                <!-- Date & Location -->
                <div style="border-top: 1px solid #eee; padding-top: 12px; margin-bottom: 12px;">
                  <div class="small" style="color: #999; margin-bottom: 4px;">📅 <?= htmlspecialchars((string)$event['ngay_to_chuc']) ?> <?= htmlspecialchars((string)$event['gio_to_chuc']) ?></div>
                  <div class="small" style="color: #999;">📍 <?= htmlspecialchars((string)$event['dia_diem']) ?></div>
                </div>

                <!-- Tickets Sold Progress -->
                <?php if ($ticketsTotal > 0): ?>
                  <div class="mb-3">
                    <div class="progress" style="height: 6px; background: #eee;">
                      <div class="progress-bar" role="progressbar" style="width: <?= round(($ticketsSold / $ticketsTotal) * 100) ?>%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                    </div>
                    <small class="text-muted d-block mt-1"><?= e($ticketsSold) ?>/<?= e($ticketsTotal) ?> vé đã bán</small>
                  </div>
                <?php endif; ?>

                <!-- Min Price -->
                <?php if ($eventTickets): ?>
                  <?php $minPrice = min(array_map(fn($t) => (float)$t['gia_ve'], $eventTickets)); ?>
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                      <small class="text-muted">Từ</small><br>
                      <strong style="font-size: 18px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        <?= number_format($minPrice, 0, ',', '.') ?> đ
                      </strong>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Action Button -->
                <?php if (count($availableTickets) === 0): ?>
                  <button class="btn btn-sm btn-secondary w-100" disabled style="border-radius: 8px;">Hết vé</button>
                <?php else: ?>
                  <a href="index.php?action=ticket_detail&id=<?= (int)array_values($availableTickets)[0]['ma_loai_ve'] ?>" 
                     class="btn btn-sm w-100" 
                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    Mua vé ngay
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

