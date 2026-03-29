<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/EventTicket.php';
require_once __DIR__ . '/../model/News.php';

class AdminController
{
    private User $userModel;
    private EventTicket $eventTicketModel;
    private News $newsModel;

    public function __construct($conn)
    {
        $this->userModel = new User($conn);
        $this->eventTicketModel = new EventTicket($conn);
        $this->newsModel = new News($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function createUser(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền thực hiện thao tác này.'];
            header('Location: index.php');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'khach_hang');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!in_array($role, ['khach_hang', 'nhan_vien'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Quyền không hợp lệ.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if ($fullName === '' || $email === '' || $password === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin tài khoản.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không đúng định dạng.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $pwOk = (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password);
        if (!$pwOk) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu phải ≥ 8 ký tự và gồm chữ hoa, chữ thường, số, ký tự đặc biệt.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $result = $this->userModel->register($fullName, $email, $password, null, null, $role);

        if ($result['success']) {
            $roleLabel = $role === 'nhan_vien' ? 'nhân viên' : 'khách hàng';
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tạo tài khoản ' . $roleLabel . ' thành công!'];
            header('Location: index.php?action=admin&tab=users');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'danger', 'message' => $result['message'] ?? 'Tạo tài khoản thất bại.'];
        header('Location: index.php?action=admin&tab=users');
        exit;
    }

    public function createCategory(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $result = $this->eventTicketModel->createCategory($name, $description ?: null);

        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_categories');
        exit;
    }

    public function updateCategory(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        $id = (int)($_POST['ma_danh_muc'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $result = $this->eventTicketModel->updateCategory($id, $name, $description ?: null);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_categories');
        exit;
    }

    public function deleteCategory(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        $id = (int)($_POST['ma_danh_muc'] ?? 0);
        $result = $this->eventTicketModel->deleteCategory($id);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_categories');
        exit;
    }

    public function createEvent(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $creatorId = $this->eventTicketModel->resolveCreatorIdByEmail((string)($_SESSION['user']['email'] ?? ''));
        if (!$creatorId) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy người tạo sự kiện trong hệ thống.'];
            header('Location: index.php?action=admin_tickets');
            exit;
        }

        // Xử lý upload hình ảnh
        $hinhAnh = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->handleImageUpload($_FILES['hinh_anh'], 'events');
            if ($upload['success']) {
                $hinhAnh = $upload['path'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Lỗi upload hình ảnh: ' . $upload['message']];
                header('Location: index.php?action=admin_tickets');
                exit;
            }
        }

        $payload = [
            'ma_danh_muc' => (int)($_POST['ma_danh_muc'] ?? 0),
            'ma_nguoi_tao' => $creatorId,
            'ten_su_kien' => trim($_POST['ten_su_kien'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'ngay_to_chuc' => trim($_POST['ngay_to_chuc'] ?? ''),
            'gio_to_chuc' => trim($_POST['gio_to_chuc'] ?? ''),
            'dia_diem' => trim($_POST['dia_diem'] ?? ''),
            'hinh_anh' => $hinhAnh,
        ];

        if ($payload['ma_danh_muc'] <= 0 || $payload['ten_su_kien'] === '' || $payload['ngay_to_chuc'] === '' || $payload['gio_to_chuc'] === '' || $payload['dia_diem'] === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin sự kiện.'];
            header('Location: index.php?action=admin_tickets');
            exit;
        }

        $result = $this->eventTicketModel->createEvent($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function updateEvent(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $hinhAnh = trim($_POST['current_hinh_anh'] ?? '');
        if (isset($_FILES['hinh_anh']) && ($_FILES['hinh_anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload = $this->handleImageUpload($_FILES['hinh_anh'], 'events');
            if (!$upload['success']) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Lỗi upload hình ảnh: ' . $upload['message']];
                header('Location: index.php?action=admin_tickets');
                exit;
            }
            $hinhAnh = (string)$upload['path'];
        }

        $payload = [
            'ma_su_kien' => (int)($_POST['ma_su_kien'] ?? 0),
            'ma_danh_muc' => (int)($_POST['ma_danh_muc'] ?? 0),
            'ten_su_kien' => trim($_POST['ten_su_kien'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'ngay_to_chuc' => trim($_POST['ngay_to_chuc'] ?? ''),
            'gio_to_chuc' => trim($_POST['gio_to_chuc'] ?? ''),
            'dia_diem' => trim($_POST['dia_diem'] ?? ''),
            'hinh_anh' => $hinhAnh,
        ];

        if ($payload['ma_su_kien'] <= 0 || $payload['ma_danh_muc'] <= 0 || $payload['ten_su_kien'] === '' || $payload['ngay_to_chuc'] === '' || $payload['gio_to_chuc'] === '' || $payload['dia_diem'] === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Dữ liệu cập nhật sự kiện không hợp lệ.'];
            header('Location: index.php?action=admin_tickets');
            exit;
        }

        $result = $this->eventTicketModel->updateEvent($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function deleteEvent(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $id = (int)($_POST['ma_su_kien'] ?? 0);
        $result = $this->eventTicketModel->deleteEvent($id);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function createTicketType(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $payload = [
            'ma_su_kien' => (int)($_POST['ma_su_kien'] ?? 0),
            'ten_loai_ve' => trim($_POST['ten_loai_ve'] ?? ''),
            'gia_ve' => (float)($_POST['gia_ve'] ?? 0),
            'so_luong' => (int)($_POST['so_luong'] ?? 0),
            'so_luong_con' => (int)($_POST['so_luong'] ?? 0),
        ];

        if ($payload['ma_su_kien'] <= 0 || $payload['ten_loai_ve'] === '' || $payload['gia_ve'] <= 0 || $payload['so_luong'] <= 0) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đúng thông tin loại vé.'];
            header('Location: index.php?action=admin_tickets');
            exit;
        }

        $result = $this->eventTicketModel->createTicketType($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function updateTicketType(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        $payload = [
            'ma_loai_ve' => (int)($_POST['ma_loai_ve'] ?? 0),
            'ten_loai_ve' => trim($_POST['ten_loai_ve'] ?? ''),
            'gia_ve' => (float)($_POST['gia_ve'] ?? 0),
            'so_luong' => (int)($_POST['so_luong'] ?? 0),
            'so_luong_con' => (int)($_POST['so_luong_con'] ?? 0),
        ];
        if ($payload['ten_loai_ve'] === '' || $payload['gia_ve'] <= 0 || $payload['so_luong'] <= 0 || $payload['so_luong_con'] < 0) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Dữ liệu cập nhật loại vé không hợp lệ.'];
            header('Location: index.php?action=admin_tickets');
            exit;
        }
        $result = $this->eventTicketModel->updateTicketType($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function deleteTicketType(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
        $id = (int)($_POST['ma_loai_ve'] ?? 0);
        $result = $this->eventTicketModel->deleteTicketType($id);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_tickets');
        exit;
    }

    public function confirmOrderPayment(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Đơn hàng không hợp lệ.'];
            header('Location: index.php?action=admin');
            exit;
        }

        $result = $this->eventTicketModel->confirmOrderPaymentByOrderId($orderId);
        $_SESSION['flash'] = ['type' => ($result['success'] ?? false) ? 'success' : 'danger', 'message' => $result['message'] ?? ''];
        header('Location: index.php?action=admin');
        exit;
    }

    public function deleteOrder(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Đơn hàng không hợp lệ.'];
            header('Location: index.php?action=admin&tab=orders');
            exit;
        }

        $result = $this->eventTicketModel->deleteOrderById($orderId);
        $_SESSION['flash'] = ['type' => ($result['success'] ?? false) ? 'success' : 'danger', 'message' => $result['message'] ?? ''];
        header('Location: index.php?action=admin&tab=orders');
        exit;
    }

    public function deleteAllOrders(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $result = $this->eventTicketModel->deleteAllPendingOrders();
        $_SESSION['flash'] = ['type' => ($result['success'] ?? false) ? 'success' : 'danger', 'message' => $result['message'] ?? ''];
        header('Location: index.php?action=admin&tab=orders');
        exit;
    }

    public function createNews(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $creatorId = $this->eventTicketModel->resolveCreatorIdByEmail((string)($_SESSION['user']['email'] ?? ''));
        if (!$creatorId) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy người tạo tin tức trong hệ thống.'];
            header('Location: index.php?action=admin_news');
            exit;
        }

        // Xử lý upload hình ảnh
        $hinhAnh = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->handleImageUpload($_FILES['hinh_anh'], 'news');
            if ($upload['success']) {
                $hinhAnh = $upload['path'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Lỗi upload hình ảnh: ' . $upload['message']];
                header('Location: index.php?action=admin_news');
                exit;
            }
        }

        $payload = [
            'ma_nguoi_tao' => $creatorId,
            'tieu_de' => trim($_POST['tieu_de'] ?? ''),
            'noi_dung' => trim($_POST['noi_dung'] ?? ''),
            'hinh_anh' => $hinhAnh,
        ];

        if ($payload['tieu_de'] === '' || $payload['noi_dung'] === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin tin tức.'];
            header('Location: index.php?action=admin_news');
            exit;
        }

        $result = $this->newsModel->createNews($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_news');
        exit;
    }

    public function updateNews(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $hinhAnh = trim($_POST['current_hinh_anh'] ?? '');
        if (isset($_FILES['hinh_anh']) && ($_FILES['hinh_anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload = $this->handleImageUpload($_FILES['hinh_anh'], 'news');
            if (!$upload['success']) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Lỗi upload hình ảnh: ' . $upload['message']];
                header('Location: index.php?action=admin_news');
                exit;
            }
            $hinhAnh = (string)$upload['path'];
        }

        $payload = [
            'ma_tin_tuc' => (int)($_POST['ma_tin_tuc'] ?? 0),
            'tieu_de' => trim($_POST['tieu_de'] ?? ''),
            'noi_dung' => trim($_POST['noi_dung'] ?? ''),
            'hinh_anh' => $hinhAnh,
        ];

        if ($payload['ma_tin_tuc'] <= 0 || $payload['tieu_de'] === '' || $payload['noi_dung'] === '') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Dữ liệu cập nhật tin tức không hợp lệ.'];
            header('Location: index.php?action=admin_news');
            exit;
        }

        $result = $this->newsModel->updateNews($payload);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_news');
        exit;
    }

    public function deleteNews(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $id = (int)($_POST['ma_tin_tuc'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'ID tin tức không hợp lệ.'];
            header('Location: index.php?action=admin_news');
            exit;
        }

        $result = $this->newsModel->deleteNews($id);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
        header('Location: index.php?action=admin_news');
        exit;
    }

    private function isAdmin(): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? null) !== 'quan_tri_vien') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Bạn không có quyền thực hiện thao tác này.'];
            header('Location: index.php');
            exit;
        }
        return true;
    }

    private function handleImageUpload(array $file, string $subfolder = ''): array
    {
        // Kiểm tra kích thước file (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File quá lớn (max 5MB)', 'path' => null];
        }

        // Kiểm tra loại file
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Định dạng file không hợp lệ', 'path' => null];
        }

        // Tạo thư mục nếu chưa có
        $uploadDir = __DIR__ . '/../../public/images';
        if ($subfolder) {
            $uploadDir .= '/' . $subfolder;
        }
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Tạo tên file duy nhất
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('img_', true) . '.' . $ext;
        $uploadPath = $uploadDir . '/' . $newFileName;
        $publicPath = '/public/images' . ($subfolder ? '/' . $subfolder : '') . '/' . $newFileName;

        // Di chuyển file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'message' => 'Upload thành công', 'path' => $publicPath];
        }

        return ['success' => false, 'message' => 'Không thể lưu file', 'path' => null];
    }
}

