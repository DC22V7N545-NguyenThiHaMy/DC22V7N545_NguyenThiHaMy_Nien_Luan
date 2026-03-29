<?php
// Xác định trang hiện tại để highlight đúng mục
$currentAction = $_GET['action'] ?? 'admin';
$currentTab    = $_GET['tab']    ?? '';

$menu = [
    ['label' => 'Tổng quan',    'href' => 'index.php?action=admin&tab=overview',    'match_action' => 'admin',            'match_tab' => 'overview'],
    ['label' => 'Thanh toán',   'href' => 'index.php?action=admin&tab=orders',      'match_action' => 'admin',            'match_tab' => 'orders'],
    ['label' => 'Người dùng',   'href' => 'index.php?action=admin&tab=users',       'match_action' => 'admin',            'match_tab' => 'users'],
    ['label' => 'Danh mục',     'href' => 'index.php?action=admin_categories',      'match_action' => 'admin_categories', 'match_tab' => ''],
    ['label' => 'Sự kiện',      'href' => 'index.php?action=admin_events',          'match_action' => 'admin_events',     'match_tab' => ''],
    ['label' => 'Loại vé',      'href' => 'index.php?action=admin_tickets',         'match_action' => 'admin_tickets',    'match_tab' => ''],
    ['label' => 'Tin tức',      'href' => 'index.php?action=admin_news',            'match_action' => 'admin_news',       'match_tab' => ''],
];
?>
<div class="th-admin-sidebar p-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <div class="fw-semibold">Admin Panel</div>
      <div class="text-muted small"><?= htmlspecialchars((string)($user['name'] ?? '')) ?></div>
    </div>
    <span class="badge th-badge-soft">Admin</span>
  </div>

  <div class="nav flex-column gap-2">
    <?php foreach ($menu as $item):
      $isActive = ($currentAction === $item['match_action'])
                  && ($item['match_tab'] === '' || $currentTab === $item['match_tab']
                      || ($item['match_tab'] === 'overview' && $currentTab === ''));
    ?>
      <a class="nav-link th-admin-link <?= $isActive ? 'active' : '' ?>"
         href="<?= $item['href'] ?>">
        <?= $item['label'] ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="border-top border-light border-opacity-10 mt-3 pt-3 small text-muted">
    Email: <strong><?= htmlspecialchars((string)($user['email'] ?? '')) ?></strong>
  </div>
</div>
