<?php
session_start();
require_once 'auth.php';
tryAutoLogin();
requireLogin();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemId = (int)($_POST['item_id'] ?? 0);

    if ($action === 'increase') {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $itemId) { $item['qty']++; break; }
        }
    } elseif ($action === 'decrease') {
        foreach ($_SESSION['cart'] as $k => &$item) {
            if ($item['id'] === $itemId) {
                $item['qty']--;
                if ($item['qty'] <= 0) unset($_SESSION['cart'][$k]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    } elseif ($action === 'remove') {
        $_SESSION['cart'] = array_values(array_filter(
            $_SESSION['cart'] ?? [],
            fn($i) => $i['id'] !== $itemId
        ));
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    } elseif ($action === 'checkout') {
        $_SESSION['cart'] = [];
        $_SESSION['checkout_done'] = true;
        header('Location: dashboard.php');
        exit;
    }
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'qty'));
$cartTotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
$tax = $cartTotal * 0.18;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart – Exp 11 Sessions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="sidebar-logo-icon">🍪</div>Sessions Lab</div>
        <a href="dashboard.php" class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="shop.php" class="nav-item"><span>🛍️</span> Shop</a>
        <a href="cart.php" class="nav-item active"><span>🛒</span> Cart <span class="pill pill-teal" style="margin-left:auto;"><?= $cartCount ?></span></a>
        <div class="sidebar-divider"></div>
        <a href="login.php" class="nav-item"><span>🔐</span> Login Page</a>
        <a href="register.php" class="nav-item"><span>📝</span> Register Page</a>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <a href="logout.php" class="btn-logout">🚪 Sign Out</a>
        </div>
    </aside>

    <div class="content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Shopping Cart</div>
                <div class="topbar-sub">Cart persists via $_SESSION throughout your session</div>
            </div>
            <a href="shop.php" class="session-badge" style="text-decoration:none;">← Continue Shopping</a>
        </div>
        <div class="main-content">

            <?php if (empty($cart)): ?>
            <div class="cart-items" style="border-radius:14px;">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <div>Your cart is empty</div>
                    <a href="shop.php" class="btn-shop">🛍️ Browse Products</a>
                </div>
            </div>

            <?php else: ?>
            <div class="cart-grid">
                <!-- ITEMS -->
                <div>
                    <h2 class="section-title" style="margin-bottom:14px;">🛒 Cart Items (<?= $cartCount ?>)</h2>
                    <div class="cart-items">
                        <?php foreach ($cart as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-icon"><?= $item['icon'] ?></div>
                            <div>
                                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="cart-item-price">₹<?= number_format($item['price'],2) ?> each</div>
                            </div>
                            <div class="cart-item-actions">
                                <form method="POST" style="display:contents">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button name="action" value="decrease" class="qty-btn">−</button>
                                    <span class="qty-display"><?= $item['qty'] ?></span>
                                    <button name="action" value="increase" class="qty-btn">+</button>
                                    <button name="action" value="remove" class="remove-btn" title="Remove">✕</button>
                                </form>
                                <span style="font-family:var(--font-heading);font-size:16px;font-weight:700;color:var(--teal-light);min-width:80px;text-align:right;">
                                    ₹<?= number_format($item['price'] * $item['qty'],2) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SUMMARY -->
                <div>
                    <h2 class="section-title" style="margin-bottom:14px;">💰 Order Summary</h2>
                    <div class="cart-summary">
                        <div class="summary-title">Price Breakdown</div>
                        <div class="summary-row">
                            <span>Subtotal (<?= $cartCount ?> items)</span>
                            <span>₹<?= number_format($cartTotal,2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>GST (18%)</span>
                            <span>₹<?= number_format($tax,2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span style="color:var(--green);">FREE</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₹<?= number_format($cartTotal + $tax,2) ?></span>
                        </div>

                        <form method="POST">
                            <button name="action" value="checkout" class="btn-checkout">
                                ✅ Place Order
                            </button>
                        </form>
                        <form method="POST">
                            <button name="action" value="clear" class="btn-clear">🗑️ Clear Cart</button>
                        </form>
                    </div>

                    <!-- Session info box -->
                    <div class="session-card" style="margin-top:20px;">
                        <div class="session-card-header">
                            <span class="session-card-title">$_SESSION['cart']</span>
                            <span class="pill pill-teal">LIVE</span>
                        </div>
                        <div class="session-body">
                            <div class="session-row">
                                <span class="session-key">Items stored</span>
                                <span class="session-val"><?= count($cart) ?> product type(s)</span>
                            </div>
                            <div class="session-row">
                                <span class="session-key">Total qty</span>
                                <span class="session-val"><?= $cartCount ?></span>
                            </div>
                            <div class="session-row">
                                <span class="session-key">Subtotal</span>
                                <span class="session-val">₹<?= number_format($cartTotal,2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <footer class="footer">Experiment 11 — PHP Sessions &amp; Cookies | <span>Adithya Gowda · 24BTRCN001</span></footer>
    </div>
</div>
</body>
</html>
