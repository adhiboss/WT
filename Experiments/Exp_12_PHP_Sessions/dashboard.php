<?php
session_start();
require_once 'auth.php';
tryAutoLogin();
requireLogin();

$user = currentUser();
$justRegistered = !empty($_SESSION['just_registered']);
unset($_SESSION['just_registered']);

// Cart in session
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'qty'));
$cartTotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));

// Session info
$loginTime = isset($_SESSION['login_time'])
    ? date('d M Y, H:i:s', $_SESSION['login_time'])
    : '—';
$hasCookie = isset($_COOKIE[COOKIE_NAME]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Exp 11 Sessions & Cookies</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="sidebar-logo-icon">🍪</div>Sessions Lab</div>
        <a href="dashboard.php" class="nav-item active"><span>🏠</span> Dashboard</a>
        <a href="shop.php" class="nav-item"><span>🛍️</span> Shop</a>
        <a href="cart.php" class="nav-item"><span>🛒</span> Cart <?php if ($cartCount): ?><span class="pill pill-teal" style="margin-left:auto;"><?= $cartCount ?></span><?php endif; ?></a>
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
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-sub">Experiment 11 — PHP Sessions &amp; Cookies</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <span class="session-badge">🟢 Session Active</span>
                <?php if ($hasCookie): ?>
                <span class="session-badge" style="background:rgba(245,158,11,.1);color:#fbbf24;border-color:rgba(245,158,11,.3);">🍪 Cookie Set</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="main-content">

            <?php if ($justRegistered): ?>
            <div class="alert alert-success" style="margin-bottom:24px;">
                🎉 Welcome, <?= htmlspecialchars($user['name']) ?>! Your account was created successfully.
            </div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon teal">🟢</div>
                        <div class="stat-label-text">Session Status</div>
                    </div>
                    <div class="stat-value" style="font-size:24px;color:#2dd4bf;">ACTIVE</div>
                    <div class="stat-sub">ID: <?= session_id() ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon purple">🛒</div>
                        <div class="stat-label-text">Cart Items</div>
                    </div>
                    <div class="stat-value"><?= $cartCount ?></div>
                    <div class="stat-sub">Total: ₹<?= number_format($cartTotal, 2) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon yellow">🍪</div>
                        <div class="stat-label-text">Remember-Me Cookie</div>
                    </div>
                    <div class="stat-value" style="font-size:24px;color:<?= $hasCookie ? '#fbbf24' : 'var(--gray-500)' ?>;">
                        <?= $hasCookie ? 'SET' : 'NOT SET' ?>
                    </div>
                    <div class="stat-sub"><?= $hasCookie ? COOKIE_DAYS.' day expiry' : 'Login without "Remember Me"' ?></div>
                </div>
            </div>

            <!-- SESSION DATA -->
            <h2 class="section-title">🔍 Live Session Data ($_SESSION)</h2>
            <div class="session-card" style="margin-bottom:28px;">
                <div class="session-card-header">
                    <span class="session-card-title">Current $_SESSION contents</span>
                    <span class="pill pill-green">LIVE</span>
                </div>
                <div class="session-body">
                    <div class="session-row">
                        <span class="session-key">$_SESSION['user_id']</span>
                        <span class="session-val"><?= htmlspecialchars($user['id']) ?></span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">$_SESSION['login_time']</span>
                        <span class="session-val"><?= $loginTime ?></span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">$_SESSION['cart'] items</span>
                        <span class="session-val"><?= $cartCount ?> item(s) · ₹<?= number_format($cartTotal,2) ?></span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">session_id()</span>
                        <span class="session-val" style="font-family:monospace;font-size:12px;word-break:break-all;"><?= session_id() ?></span>
                    </div>
                </div>
            </div>

            <!-- COOKIE DATA -->
            <h2 class="section-title">🍪 Cookie Data ($_COOKIE)</h2>
            <div class="session-card" style="margin-bottom:28px;">
                <div class="session-card-header">
                    <span class="session-card-title">Browser cookies sent with request</span>
                    <span class="pill <?= $hasCookie ? 'pill-yellow' : 'pill-teal' ?>"><?= $hasCookie ? 'COOKIE FOUND' : 'NO REMEMBER COOKIE' ?></span>
                </div>
                <div class="session-body">
                    <?php if ($hasCookie): ?>
                    <div class="session-row">
                        <span class="session-key">$_COOKIE['<?= COOKIE_NAME ?>']</span>
                        <span class="session-val" style="font-family:monospace;font-size:12px;word-break:break-all;"><?= htmlspecialchars(substr($_COOKIE[COOKIE_NAME],0,40)) ?>…</span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">Expiry</span>
                        <span class="session-val"><?= COOKIE_DAYS ?> days from last login</span>
                    </div>
                    <?php else: ?>
                    <div class="session-row">
                        <span class="session-val" style="color:var(--gray-500);">No remember-me cookie set. Log in with "Remember Me" checked to see cookie data.</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- HOW IT WORKS -->
            <h2 class="section-title">📚 How It Works</h2>
            <div class="session-card">
                <div class="session-card-header">
                    <span class="session-card-title">Sessions vs Cookies — Key Concepts</span>
                </div>
                <div class="session-body">
                    <div class="session-row">
                        <span class="session-key">Session Storage</span>
                        <span class="session-val">Server-side · Expires when browser closes · Uses session_id cookie to link</span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">Remember Me Cookie</span>
                        <span class="session-val">Client-side · Persists for <?= COOKIE_DAYS ?> days · Secure token stored in JSON</span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">Shopping Cart</span>
                        <span class="session-val">Stored in $_SESSION['cart'] · Survives page navigation within session</span>
                    </div>
                    <div class="session-row">
                        <span class="session-key">Password Security</span>
                        <span class="session-val">password_hash() bcrypt · Verified with password_verify()</span>
                    </div>
                </div>
            </div>

        </div>
        <footer class="footer">Experiment 11 — PHP Sessions &amp; Cookies | <span>Adithya Gowda · 24BTRCN001</span></footer>
    </div>
</div>
</body>
</html>
