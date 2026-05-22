<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$activeAction = 'about';
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Hero Section -->
<section class="text-center border-bottom border-light border-opacity-10" style="padding: 120px 0; background: linear-gradient(135deg, rgba(8,15,31,0.95), rgba(8,15,31,0.85)), url('https://images.unsplash.com/photo-1540039155732-d674140af339?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;">
    <div class="container">
        <h1 class="display-3 fw-bolder text-warning mb-3">Về TicketHub</h1>
        <p class="lead text-light mb-4 mx-auto" style="max-width: 700px;">
            Nền tảng đặt vé sự kiện trực tuyến hàng đầu, kết nối hàng triệu khán giả với những trải nghiệm giải trí đỉnh cao một cách nhanh chóng, an toàn và tiện lợi.
        </p>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5 my-4">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Concert Crowd" class="img-fluid rounded-4 shadow-lg border border-light border-opacity-10">
            </div>
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Sứ mệnh của chúng tôi</h2>
                <p class="text-muted fs-5 mb-4">
                    Chúng tôi tin rằng mọi khoảnh khắc giải trí đều đáng được trân trọng. Sứ mệnh của TicketHub là phá bỏ mọi rào cản giữa khán giả và nghệ sĩ, nghệ thuật, âm nhạc, thông qua việc áp dụng công nghệ bán vé thông minh.
                </p>
                <div class="d-flex gap-3 mb-3">
                    <div class="text-warning fs-4">✓</div>
                    <div>
                        <h5 class="text-light mb-1">Minh bạch & Rõ ràng</h5>
                        <p class="text-muted small">Không có phí ẩn. Mọi quy trình chốt vé và thanh toán đều hiển thị chi tiết minh bạch.</p>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <div class="text-warning fs-4">✓</div>
                    <div>
                        <h5 class="text-light mb-1">Công nghệ tiên phong</h5>
                        <p class="text-muted small">Sử dụng QR Code để Check-in siêu tốc, giải quyết triệt để tình trạng vé giả, vé chợ đen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 border-top border-bottom border-warning border-opacity-10 bg-warning bg-opacity-10" style="--bs-bg-opacity: .03;">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-md-3 col-6 p-4">
                <div class="display-4 fw-bolder text-warning mb-2">+10K</div>
                <div class="text-uppercase small text-light text-opacity-75" style="letter-spacing: 2px;">Sự kiện</div>
            </div>
            <div class="col-md-3 col-6 p-4">
                <div class="display-4 fw-bolder text-warning mb-2">2M</div>
                <div class="text-uppercase small text-light text-opacity-75" style="letter-spacing: 2px;">Người dùng</div>
            </div>
            <div class="col-md-3 col-6 p-4">
                <div class="display-4 fw-bolder text-warning mb-2">500+</div>
                <div class="text-uppercase small text-light text-opacity-75" style="letter-spacing: 2px;">Đối tác</div>
            </div>
            <div class="col-md-3 col-6 p-4">
                <div class="display-4 fw-bolder text-warning mb-2">99%</div>
                <div class="text-uppercase small text-light text-opacity-75" style="letter-spacing: 2px;">Hài lòng</div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 my-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Giá trị cốt lõi</h2>
            <p class="text-muted">Những nguyên tắc định hình cách chúng tôi phục vụ bạn mỗi ngày.</p>
        </div>
        
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 rounded-4 border border-light border-opacity-10 h-100" style="background: rgba(255,255,255,0.02)">
                    <div class="display-5 mb-4">🚀</div>
                    <h4 class="text-light mb-3">Tốc độ & Tiện lợi</h4>
                    <p class="text-muted small mb-0">Hệ thống tối ưu giúp bạn hoàn tất việc đặt vé chỉ trong vòng chưa đầy 1 phút. Dễ dàng truy cập trên mọi thiết bị di động.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 border border-light border-opacity-10 h-100" style="background: rgba(255,255,255,0.02)">
                    <div class="display-5 mb-4">🛡️</div>
                    <h4 class="text-light mb-3">An toàn tuyệt đối</h4>
                    <p class="text-muted small mb-0">Hợp tác với các cổng thanh toán hàng đầu (SePay, MoMo, VNPay) đảm bảo dữ liệu thanh toán của bạn luôn được mã hóa bảo mật.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 border border-light border-opacity-10 h-100" style="background: rgba(255,255,255,0.02)">
                    <div class="display-5 mb-4">🎧</div>
                    <h4 class="text-light mb-3">Hỗ trợ 24/7</h4>
                    <p class="text-muted small mb-0">Đội ngũ chăm sóc khách hàng luôn sẵn sàng túc trực để hỗ trợ mọi sự cố, đổi trả vé hay thắc mắc về sự kiện.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 my-5 border-top border-light border-opacity-10">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Cách TicketHub hoạt động</h2>
            <p class="text-muted">Chỉ 4 bước — từ ý tưởng sự kiện đến lúc khán giả quét vé vào cổng.</p>
        </div>

        <div class="row g-4">
            <!-- Bước 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="text-center p-4 rounded-4 h-100 position-relative" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24, #d97706); font-weight: 800; font-size: 24px; color: #111;">1</div>
                    <h5 class="text-light mb-2">Tạo sự kiện</h5>
                    <p class="text-muted small mb-0">Nhà tổ chức đăng ký tài khoản, điền thông tin sự kiện: tên, ngày giờ, địa điểm, mô tả và hình ảnh banner.</p>
                </div>
            </div>
            <!-- Bước 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="text-center p-4 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24, #d97706); font-weight: 800; font-size: 24px; color: #111;">2</div>
                    <h5 class="text-light mb-2">Cấu hình vé</h5>
                    <p class="text-muted small mb-0">Thiết lập các loại vé (VIP, Standard, Early Bird...), giá tiền, số lượng giới hạn và thời gian mở bán cho từng hạng.</p>
                </div>
            </div>
            <!-- Bước 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="text-center p-4 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24, #d97706); font-weight: 800; font-size: 24px; color: #111;">3</div>
                    <h5 class="text-light mb-2">Khách mua vé</h5>
                    <p class="text-muted small mb-0">Người dùng duyệt sự kiện, chọn loại vé, thanh toán qua chuyển khoản ngân hàng — nhận vé điện tử kèm mã QR tức thì.</p>
                </div>
            </div>
            <!-- Bước 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="text-center p-4 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24, #d97706); font-weight: 800; font-size: 24px; color: #111;">4</div>
                    <h5 class="text-light mb-2">Check-in tại cổng</h5>
                    <p class="text-muted small mb-0">Ngày diễn ra sự kiện, nhân viên quét mã QR trên vé — hệ thống xác thực tức thì, đánh dấu đã sử dụng, chống vé giả 100%.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 my-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Đội ngũ phát triển</h2>
            <p class="text-muted">Dự án niên luận ngành Công nghệ thông tin — Trường Đại học Cần Thơ.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Thành viên 1 -->
            <div class="col-md-4">
                <div class="text-center p-4 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 90px; height: 90px; background: linear-gradient(135deg, #667eea, #764ba2); font-size: 36px;">👩‍💻</div>
                    <h5 class="text-light mb-1">Nguyễn Thị Hà My</h5>
                    <span class="badge bg-warning text-dark mb-3">Full-stack Developer</span>
                    <p class="text-muted small mb-0">MSSV: DC22V7N545 — Phụ trách thiết kế giao diện, xây dựng backend API, tích hợp thanh toán và triển khai hệ thống.</p>
                </div>
            </div>
            <!-- Giảng viên hướng dẫn -->
            <div class="col-md-4">
                <div class="text-center p-4 rounded-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 90px; height: 90px; background: linear-gradient(135deg, #34d399, #059669); font-size: 36px;">👨‍🏫</div>
                    <h5 class="text-light mb-1">Giảng viên hướng dẫn</h5>
                    <span class="badge bg-success mb-3">Mentor</span>
                    <p class="text-muted small mb-0">Hướng dẫn về kiến trúc hệ thống, quy trình phát triển phần mềm và đánh giá chất lượng sản phẩm cuối kỳ.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tech Stack Section -->
<section class="py-5 border-top border-bottom border-light border-opacity-10" style="background: rgba(255,255,255,0.01);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Công nghệ sử dụng</h2>
            <p class="text-muted">Xây dựng trên nền tảng công nghệ ổn định, bảo mật và dễ mở rộng.</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">🐘</div>
                    <h6 class="text-light mb-1">PHP</h6>
                    <small class="text-muted">Backend</small>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">🐬</div>
                    <h6 class="text-light mb-1">MySQL</h6>
                    <small class="text-muted">Database</small>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">🎨</div>
                    <h6 class="text-light mb-1">Bootstrap 5</h6>
                    <small class="text-muted">UI Framework</small>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">⚡</div>
                    <h6 class="text-light mb-1">JavaScript</h6>
                    <small class="text-muted">Frontend Logic</small>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">📱</div>
                    <h6 class="text-light mb-1">QR Code</h6>
                    <small class="text-muted">Check-in</small>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-3 rounded-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="fs-1 mb-2">🏦</div>
                    <h6 class="text-light mb-1">SePay</h6>
                    <small class="text-muted">Thanh toán</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 my-5">
    <div class="container" style="max-width: 800px;">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Câu hỏi thường gặp</h2>
            <p class="text-muted">Giải đáp nhanh những thắc mắc phổ biến nhất.</p>
        </div>

        <div class="accordion" id="faqAccordion">
            <!-- FAQ 1 -->
            <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="background: transparent; color: #f8fafc; box-shadow: none;">
                        Làm sao để mua vé trên TicketHub?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small" style="background: transparent;">
                        Rất đơn giản! Bạn chỉ cần: <strong>(1)</strong> Duyệt sự kiện trên trang chủ hoặc tìm kiếm, <strong>(2)</strong> Chọn loại vé và số lượng, <strong>(3)</strong> Thanh toán qua chuyển khoản ngân hàng. Vé điện tử kèm mã QR sẽ được gửi về tài khoản của bạn ngay lập tức.
                    </div>
                </div>
            </div>
            <!-- FAQ 2 -->
            <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="background: transparent; color: #f8fafc; box-shadow: none;">
                        Vé điện tử hoạt động như thế nào?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small" style="background: transparent;">
                        Mỗi vé được gắn một mã QR duy nhất. Khi đến sự kiện, bạn chỉ cần đưa mã QR (trên điện thoại hoặc in ra) cho nhân viên quét. Hệ thống sẽ xác thực vé trong tích tắc và đánh dấu đã sử dụng — hoàn toàn không thể làm giả.
                    </div>
                </div>
            </div>
            <!-- FAQ 3 -->
            <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="background: transparent; color: #f8fafc; box-shadow: none;">
                        Tôi có thể hoàn vé hoặc đổi vé không?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small" style="background: transparent;">
                        Chính sách hoàn/đổi vé phụ thuộc vào quy định của từng nhà tổ chức sự kiện. Thông thường, vé có thể được hoàn trước thời điểm sự kiện diễn ra một khoảng thời gian nhất định. Vui lòng kiểm tra chi tiết tại trang thông tin từng sự kiện.
                    </div>
                </div>
            </div>
            <!-- FAQ 4 -->
            <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="background: transparent; color: #f8fafc; box-shadow: none;">
                        Tôi muốn tổ chức sự kiện và bán vé trên TicketHub thì làm sao?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small" style="background: transparent;">
                        Bạn chỉ cần đăng ký tài khoản, sau đó liên hệ admin để được cấp quyền <strong>Nhà tổ chức</strong>. Khi được duyệt, bạn sẽ có quyền tạo sự kiện, cấu hình loại vé, theo dõi doanh thu và quản lý check-in — tất cả trong một dashboard duy nhất.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-center mt-4 mb-5">
    <div class="container">
        <div class="p-5 rounded-4 text-dark" style="background: linear-gradient(135deg, #fbbf24, #d97706);">
            <h2 class="fw-bold mb-3 display-6">Sẵn sàng để cháy hết mình?</h2>
            <p class="mb-4 fs-5 text-dark text-opacity-75">Hành trình bắt đầu từ những tấm vé. Hãy khám phá ngay các sự kiện đang hot nhất tuần này!</p>
            <a href="index.php?action=events" class="btn btn-dark btn-lg rounded-pill px-5 fw-bold">Khám phá sự kiện</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
