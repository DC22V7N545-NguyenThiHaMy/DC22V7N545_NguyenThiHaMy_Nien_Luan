<?php
require_once __DIR__ . '/../model/News.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$user = $_SESSION['user'] ?? null;
$newsModel = isset($conn) ? new News($conn) : null;

$news = $newsModel ? $newsModel->getNewsForHome(50) : []; // Get more news for listing page

$pageTitle = 'Tin tức - TicketHub';
$bodyClass = 'bg-landing';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container py-4">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-white">Tin tức mới nhất</h1>
      </div>

      <?php if (!$news): ?>
        <div class="text-center py-5">
          <div class="text-muted">
            <i class="fas fa-newspaper fa-3x mb-3"></i>
            <h4>Chưa có tin tức nào</h4>
            <p>Hãy quay lại sau để cập nhật những tin tức mới nhất từ TicketHub.</p>
          </div>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($news as $n): ?>
            <div class="col-lg-6 col-xl-4">
              <div class="card h-100" style="background: #ffffff !important; border: 1px solid #e0e0e0;">
                <?php if ($n['hinh_anh']): ?>
                  <img src="<?= htmlspecialchars((string)$n['hinh_anh']) ?>" class="card-img-top" alt="<?= htmlspecialchars((string)$n['tieu_de']) ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                  <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                    <i class="fas fa-newspaper fa-3x text-white"></i>
                  </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title fw-bold mb-2" style="color: #000 !important;">
                    <?= htmlspecialchars((string)$n['tieu_de']) ?>
                  </h5>
                  <p class="card-text small mb-2" style="color: #333 !important;">
                    <i class="fas fa-user me-1"></i><?= htmlspecialchars((string)$n['ten_nguoi_tao']) ?> •
                    <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($n['ngay_tao'])) ?>
                  </p>
                  <p class="card-text mb-3" style="color: #222 !important; font-size: 0.9rem;">
                    <?= htmlspecialchars(substr(strip_tags((string)$n['noi_dung']), 0, 150)) ?>...
                  </p>
                  <div class="mt-auto">
                    <a href="index.php?action=news_detail&id=<?= (int)$n['ma_tin_tuc'] ?>" class="btn btn-warning btn-sm w-100">
                      <i class="fas fa-eye me-1"></i>Đọc thêm
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>