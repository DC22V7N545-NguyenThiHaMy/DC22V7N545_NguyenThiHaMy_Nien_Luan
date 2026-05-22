<?php
/**
 * Footer Layout Component
 */
?>
<footer class="bg-dark border-top border-light border-opacity-10 pt-5 pb-4 mt-auto">
  <div class="container">
    <div class="row gy-4">
      <!-- Cột 1: Thương hiệu -->
      <div class="col-lg-4 col-md-6">
        <h3 class="text-light fw-bold mb-3"><span class="text-warning">Ticket</span>Hub</h3>
        <p class="text-white-50 small mb-3">
          Nền tảng đặt vé sự kiện trực tuyến hàng đầu, mang đến những trải nghiệm giải trí tuyệt vời nhất với một cú click chuột. Nhanh chóng, an toàn, tiện lợi.
        </p>
        <div class="d-flex gap-3">
          <a href="#" class="text-white-50 text-decoration-none fs-5 hover-warning transition-colors"><i class="fab fa-facebook"></i></a>
          <a href="#" class="text-white-50 text-decoration-none fs-5 hover-warning transition-colors"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-white-50 text-decoration-none fs-5 hover-warning transition-colors"><i class="fab fa-youtube"></i></a>
          <a href="#" class="text-white-50 text-decoration-none fs-5 hover-warning transition-colors"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>

      <!-- Cột 2: Liên kết nhanh -->
      <div class="col-lg-4 col-md-6">
        <h6 class="text-light fw-bold text-uppercase mb-3">Khám phá</h6>
        <ul class="list-unstyled mb-0">
          <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none small hover-warning">Trang chủ</a></li>
          <li class="mb-2"><a href="index.php?action=events" class="text-white-50 text-decoration-none small hover-warning">Sự kiện nổi bật</a></li>
          <li class="mb-2"><a href="index.php?action=news" class="text-white-50 text-decoration-none small hover-warning">Tin tức & Khuyến mãi</a></li>
          <li class="mb-2"><a href="index.php?action=about" class="text-white-50 text-decoration-none small hover-warning">Về TicketHub</a></li>
        </ul>
      </div>

      <!-- Cột 3: Liên hệ -->
      <div class="col-lg-4 col-md-12">
        <h6 class="text-light fw-bold text-uppercase mb-3">Liên hệ hỗ trợ</h6>
        <ul class="list-unstyled mb-0 text-white-50 small">
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="fas fa-map-marker-alt text-warning"></i>
            123 Đường Công Nghệ, Q.Ninh Kiều, Cần Thơ
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="fas fa-phone-alt text-warning"></i>
            1900 1234 (8:00 - 22:00)
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="fas fa-envelope text-warning"></i>
            hotro@tickethub.vn
          </li>
        </ul>
      </div>
    </div>

    <hr class="border-light border-opacity-10 my-4">

    <!-- Copyright -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
      <span class="mb-2 mb-md-0">© <?= date('Y') ?> TicketHub. Bảo lưu mọi quyền.</span>
      <span>Đồ án môn Niên Luận.</span>
    </div>
  </div>
</footer>

<style>
.hover-warning:hover { color: #fbbf24 !important; }
.transition-colors { transition: color 0.3s ease; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="/public/js/app.js"></script>
</body>
</html>
