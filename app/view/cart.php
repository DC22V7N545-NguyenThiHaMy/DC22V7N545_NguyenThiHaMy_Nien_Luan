<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if (!$user || ($user['role'] ?? 'khach_hang') !== 'khach_hang') {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để xem giỏ hàng.'];
    header('Location: index.php?action=login');
    exit;
}

// Calculate total
$total = 0;
foreach ($cart as $item) {
    $total += (float)$item['gia_ve'] * (int)$item['so_luong'];
}
$pageTitle = 'Giỏ hàng - TicketHub';
$bodyClass = '';
require_once __DIR__ . '/layouts/header.php';
?>
<style>
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
    }

    .main-content {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
        margin: 30px 0;
    }

    .cart-section h1 {
        font-size: 28px;
        font-weight: bold;
        color: white;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cart-items-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .cart-item {
        display: grid;
        grid-template-columns: 1fr auto auto auto auto;
        align-items: center;
        gap: 20px;
        padding: 20px;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        margin-bottom: 15px;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        background: white;
        border-color: #ddd;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .cart-item-info h6 {
        font-weight: 700;
        color: #222;
        margin: 0 0 5px 0;
        font-size: 15px;
    }

    .cart-item-type {
        font-size: 12px;
        color: #999;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .cart-item-price {
        font-weight: 700;
        color: #667eea;
        font-size: 15px;
        white-space: nowrap;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f5f5f5;
        border-radius: 8px;
        padding: 6px 10px;
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: none;
        background: transparent;
        padding: 5px;
        font-weight: 600;
        color: #333;
    }

    .qty-input:focus {
        outline: none;
    }

    .subtotal-value {
        font-weight: 700;
        color: #f5576c;
        font-size: 16px;
        min-width: 100px;
        text-align: right;
        white-space: nowrap;
    }

    .btn-remove {
        background: #ff6b6b;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 13px;
        white-space: nowrap;
    }

    .btn-remove:hover {
        background: #ff5252;
        transform: scale(1.05);
    }

    .empty-cart {
        text-align: center;
        padding: 60px 30px;
        background: white;
        border-radius: 15px;
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }

    .empty-text {
        color: #999;
        margin-bottom: 30px;
        font-size: 15px;
    }

    .summary-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        height: fit-content;
        position: sticky;
        top: 20px;
    }

    .summary-title {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 14px;
        color: #666;
    }

    .summary-row.total {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }

    .total-amount {
        color: #f5576c;
        font-size: 24px;
        font-weight: bold;
    }

    .checkout-section {
        margin-top: 30px;
    }

    .btn-checkout {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 14px 24px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
        margin-bottom: 12px;
    }

    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-continue {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-continue:hover {
        background: #f8f9fa;
        color: #667eea;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
        }

        .cart-item {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .summary-card {
            position: static;
        }

        .cart-item-price,
        .subtotal-value {
            text-align: left;
        }
    }
</style>

<main class="container">
    <?php require __DIR__ . '/partials/toast.php'; ?>

    <?php if (empty($cart)): ?>
        <div style="margin: 50px 0;">
            <div class="empty-cart">
                <div class="empty-icon">🛒</div>
                <div class="empty-title">Giỏ hàng trống</div>
                <p class="empty-text">Bạn chưa thêm vé nào vào giỏ hàng</p>
                <a href="index.php?action=events" class="btn btn-continue">← Tiếp tục mua vé</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-section">
            <h1>🛒 Giỏ hàng của bạn</h1>

            <div class="main-content">
                <!-- Cart Items -->
                <div class="cart-items-container">
                    <?php foreach ($cart as $ma_loai_ve => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <h6><?= htmlspecialchars((string)$item['ten_su_kien']) ?></h6>
                                <p class="cart-item-type">🎟️ <?= htmlspecialchars((string)$item['ten_loai_ve']) ?></p>
                            </div>
                            <div class="cart-item-price">
                                <?= number_format((float)$item['gia_ve'], 0, ',', '.') ?> đ
                            </div>
                            <form method="POST" action="index.php?action=update_cart" style="display: contents;">
                                <div class="qty-control">
                                    <input type="hidden" name="id" value="<?= (int)$ma_loai_ve ?>">
                                    <input type="number" name="so_luong" class="qty-input" value="<?= (int)$item['so_luong'] ?>" min="1" onchange="this.form.submit()">
                                </div>
                            </form>
                            <div class="subtotal-value">
                                <?= number_format((float)$item['gia_ve'] * (int)$item['so_luong'], 0, ',', '.') ?> đ
                            </div>
                            <a href="index.php?action=remove_from_cart&id=<?= (int)$ma_loai_ve ?>" class="btn-remove" onclick="return confirm('Xóa vé này khỏi giỏ hàng?');">
                                ✕
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary -->
                <div class="summary-card">
                    <div class="summary-title">Thống kê</div>

                    <div class="summary-row">
                        <span>Số lượng vé:</span>
                        <strong><?= array_sum(array_map(fn($item) => (int)$item['so_luong'], $cart)) ?></strong>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng tiền:</span>
                        <div class="total-amount">
                            <?= number_format($total, 0, ',', '.') ?> đ
                        </div>
                    </div>

                    <div class="checkout-section">
                        <form action="index.php" method="POST" style="margin-bottom: 0;">
                            <input type="hidden" name="action" value="checkout_cart">
                            <?php foreach ($cart as $ma_loai_ve => $item): ?>
                                <input type="hidden" name="cart[<?= (int)$ma_loai_ve ?>]" value="<?= (int)$item['so_luong'] ?>">
                            <?php endforeach; ?>
                            <button type="submit" class="btn-checkout">
                                💳 Thanh toán ngay
                            </button>
                        </form>
                        <a href="index.php?action=events" class="btn-continue">← Tiếp tục mua</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

