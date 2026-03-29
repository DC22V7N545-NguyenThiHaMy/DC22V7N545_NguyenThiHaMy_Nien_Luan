<?php
/**
 * Header Layout Component
 * 
 * Vars:
 * @var string $pageTitle
 * @var string $bodyClass
 * @var string $activeAction
 */

$user = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'TicketHub – Nền tảng bán vé sự kiện';
$bodyClass = $bodyClass ?? 'bg-landing';
$activeAction = $activeAction ?? ($_GET['action'] ?? 'home');
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-pIV2dM46Eb1RkaNqC8SJPnPIqlqp7l1vQAkse1E57RpjuqzRC3BVR6u5f5ADDpT5LLnRw+E0M0U6qsks7eJ7AA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="/public/css/style.css" rel="stylesheet">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><span class="text-warning">Ticket</span>Hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $activeAction === 'home' ? 'active' : '' ?>" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeAction === 'events' ? 'active' : '' ?>" href="index.php?action=events">Sự kiện</a></li>
        <li class="nav-item"><a class="nav-link <?= $activeAction === 'news' ? 'active' : '' ?>" href="index.php?action=news">Tin tức</a></li>
        <?php if ($activeAction === 'home'): ?>
          <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
        <?php endif; ?>
      </ul>

      <!-- Search Bar visibility based on context -->
      <?php if (!in_array($activeAction, ['login', 'register', 'admin', 'profile'])): ?>
      <form method="GET" action="index.php" class="d-flex gap-2 me-3 ms-auto ms-lg-0" style="flex: 1; max-width: 250px;">
        <input type="hidden" name="action" value="events">
        <input type="text" name="search" class="form-control form-control-sm th-input-dark" placeholder="Tìm vé..." style="min-width: 150px; font-size: 0.9rem;" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn btn-warning btn-sm px-3" style="white-space: nowrap; font-weight: 600;">
          Tìm
        </button>
      </form>
      <?php endif; ?>

      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="index.php?action=cart" style="position: relative;">
            🛒 Giỏ hàng
            <?php if (!empty($_SESSION['cart'])): ?>
              <span class="badge bg-danger" style="position: absolute; top: -5px; right: 0; font-size: 10px;"><?= count($_SESSION['cart']) ?></span>
            <?php endif; ?>
          </a></li>
          <li class="nav-item"><a class="nav-link <?= $activeAction === 'profile' ? 'active' : '' ?>" href="index.php?action=profile">Tài khoản</a></li>
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Đăng xuất</a></li>
        <?php else: ?>
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm me-2 <?= $activeAction === 'login' ? 'active' : '' ?>" href="index.php?action=login">Đăng nhập</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm <?= $activeAction === 'register' ? 'active' : '' ?>" href="index.php?action=register">Đăng ký</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
