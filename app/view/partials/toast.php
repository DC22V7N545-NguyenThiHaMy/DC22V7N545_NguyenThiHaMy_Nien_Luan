<?php
/**
 * Toast "bong bóng" cho flash message.
 * Yêu cầu biến $flash tồn tại với dạng: ['type' => 'success'|'danger'|'warning'|'info', 'message' => '...']
 */
if (!isset($flash) || !$flash) {
    return;
}

$type = $flash['type'] ?? 'info';
$message = (string)($flash['message'] ?? '');

$tone = match ($type) {
    'success' => 'success',
    'danger' => 'danger',
    'warning' => 'warning',
    default => 'info',
};
?>

<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div class="toast show th-toast th-toast-<?= htmlspecialchars($tone) ?>" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body d-flex align-items-start gap-2">
      <div class="th-toast-dot"></div>
      <div class="flex-grow-1">
        <div class="fw-semibold mb-1">
          <?php if ($tone === 'success'): ?>Thành công<?php endif; ?>
          <?php if ($tone === 'danger'): ?>Có lỗi<?php endif; ?>
          <?php if ($tone === 'warning'): ?>Lưu ý<?php endif; ?>
          <?php if ($tone === 'info'): ?>Thông báo<?php endif; ?>
        </div>
        <div class="small"><?= htmlspecialchars($message) ?></div>
      </div>
      <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

