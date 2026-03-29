<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/News.php';

$user = $_SESSION['user'] ?? null;
$newsModel = isset($conn) ? new News($conn) : null;
$newsId = (int)($_GET['id'] ?? 0);
$news = $newsModel ? $newsModel->getNewsDetail($newsId) : null;

if (!$news) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Không tìm thấy bài viết tin tức.'];
    header('Location: index.php?action=news');
    exit;
}

$pageTitle = ($news['tieu_de'] ?? 'Tin tức') . ' - TicketHub';
$bodyClass = 'bg-landing';
require_once __DIR__ . '/layouts/header.php';
?>



<main class="container py-5">
  <?php require __DIR__ . '/partials/toast.php'; ?>

  <!-- Back Button -->
  <div class="mb-4">
    <a href="index.php?action=news" class="btn btn-outline-light btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Quay lại tin tức
    </a>
  </div>

  <!-- Hero Section -->
  <?php if (!empty($news['hinh_anh'])): ?>
    <div class="mb-4" style="border-radius: 15px; overflow: hidden; max-height: 400px;">
      <img src="<?= htmlspecialchars((string)$news['hinh_anh']) ?>" alt="<?= htmlspecialchars((string)$news['tieu_de']) ?>" class="img-fluid w-100" style="object-fit: cover; max-height: 400px;">
    </div>
  <?php endif; ?>

  <!-- Article Content -->
  <article class="row justify-content-center">
    <div class="col-lg-8">
      <!-- Title & Meta -->
      <div class="mb-4">
        <h1 class="display-5 fw-bold text-white mb-3">
          <?= htmlspecialchars((string)$news['tieu_de']) ?>
        </h1>
        <div class="d-flex flex-wrap gap-3 text-muted small">
          <span><i class="fas fa-user me-1"></i><?= htmlspecialchars((string)$news['ten_nguoi_tao']) ?></span>
          <span><i class="fas fa-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($news['ngay_tao'])) ?></span>
          <span><i class="fas fa-clock me-1"></i>Đã duyệt</span>
        </div>
      </div>

      <!-- Divider -->
      <hr class="border-secondary my-4">

      <!-- Body Content -->
      <div class="card mb-4" style="background: #ffffff !important; border: 1px solid #e0e0e0;">
        <div class="card-body">
          <div style="font-size: 1.05rem; line-height: 1.8; color: #222 !important;">
            <?= nl2br(htmlspecialchars((string)$news['noi_dung'])) ?>
          </div>
        </div>
      </div>

      <!-- Related Section -->
      <div class="card" style="background: #ffffff !important; border: 1px solid #e0e0e0;">
        <div class="card-header" style="background: #f8f9fa !important; border-bottom: 1px solid #e0e0e0;">
          <h5 class="mb-0" style="color: #000 !important;">Tin tức liên quan</h5>
        </div>
        <div class="card-body">
          <p class="mb-0" style="color: #333 !important;">
            <i class="fas fa-circle-info me-2"></i>
            <a href="index.php?action=news" class="text-warning text-decoration-none">Xem tất cả tin tức →</a>
          </p>
        </div>
      </div>
    </div>
  </article>
</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>