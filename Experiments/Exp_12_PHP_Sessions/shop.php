<?php
session_start();
require_once 'auth.php';
tryAutoLogin();
requireLogin();

// Sample products (no DB needed)
$products = [
    ['id'=>1, 'name'=>'Wireless Headphones', 'price'=>2999,  'icon'=>'🎧', 'cat'=>'Electronics'],
    ['id'=>2, 'name'=>'Mechanical Keyboard', 'price'=>4500,  'icon'=>'⌨️', 'cat'=>'Electronics'],
    ['id'=>3, 'name'=>'Running Shoes',        'price'=>3200,  'icon'=>'👟', 'cat'=>'Clothing'],
    ['id'=>4, 'name'=>'Backpack 30L',         'price'=>1800,  'icon'=>'🎒', 'cat'=>'Clothing'],
    ['id'=>5, 'name'=>'Python Crash Course',  'price'=>699,   'icon'=>'📗', 'cat'=>'Books'],
    ['id'=>6, 'name'=>'Bluetooth Speaker',    'price'=>1500,  'icon'=>'🔊', 'cat'=>'Electronics'],
    ['id'=>7, 'name'=>'Yoga Mat',             'price'=>850,   'icon'=>'🧘', 'cat'=>'Sports'],
    ['id'=>8, 'name'=>'Coffee Mug Premium',   'price'=>350,   'icon'=>'☕', 'cat'=>'Lifestyle'],
];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_id'])) {
    $addId = (int)$_POST['add_id'];
    foreach ($products as $prod) {
        if ($prod['id'] === $addId) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] === $addId) { $item['qty']++; $found = true; break; }
            }
            if (!$found) {
                $_SESSION['cart'][] = array_merge($prod, ['qty' => 1]);
            }
            header('Location: shop.php?added=' . urlencode($prod['name']));
            exit;
        }
    }
}

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'qty'));
$added = htmlspecialchars($_GET['added'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop – Exp 11 Sessions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="sidebar-logo-icon">🍪</div>Sessions Lab</div>
        <a href="dashboard.php" class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="shop.php" class="nav-item active"><span>🛍️</span> Shop</a>
        <a href="cart.php" class="nav-item"><span>🛒</span> Cart <?php if ($cartCount): ?><span class="pill pill-teal" style="margin-left:auto;"><?= $cartCount ?></span><?php endif; ?></a>
        <div class="sidebar-divider"></div>
        <a href="login.php" class="nav-item"><span>🔐</span> Login Page</a>
        <a href="register.php" class="nav-item"><span>📝</span> Register Page</a>
        <div class="sidebar-footer">
            <?php $user = currentUser(); ?>
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
                <div class="topbar-title">Shop</div>
                <div class="topbar-sub">Add products — cart persists via $_SESSION</div>
            </div>
            <a href="cart.php" class="session-badge" style="text-decoration:none;cursor:pointer;">
                🛒 Cart: <?= $cartCount ?> item(s)
            </a>
        </div>
        <div class="main-content">

            <?php if ($added): ?>
            <div class="alert alert-success" style="margin-bottom:20px;">✅ "<?= $added ?>" added to cart!</div>
            <?php endif; ?>

            <h2 class="section-title" style="margin-bottom:20px;">🛍️ Available Products</h2>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="product-card-img"><?= $p['icon'] ?></div>
                    <div class="product-card-body">
                        <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div style="font-size:12px;color:var(--gray-500);margin-top:2px;"><?= $p['cat'] ?></div>
                        <div class="product-card-price" style="margin-top:10px;">₹<?= number_format($p['price'],2) ?></div>
                        <form method="POST">
                            <input type="hidden" name="add_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-add">🛒 Add to Cart</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
        <footer class="footer">Experiment 11 — PHP Sessions &amp; Cookies | <span>Adithya Gowda · 24BTRCN001</span></footer>
    </div>
</div>
</body>
</html>
