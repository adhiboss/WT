<?php
session_start();
require_once 'auth.php';
tryAutoLogin();
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $old = ['email' => $email];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Enter a valid email address.';
    if ($password === '')
        $errors['password'] = 'Password is required.';

    if (empty($errors)) {
        $user = findUser($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $errors['general'] = 'Invalid email or password.';
        } else {
            // Start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login_time'] = time();

            // Remember Me cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $users = loadUsers();
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) { $u['remember_token'] = $token; break; }
                }
                saveUsers($users);
                setcookie(COOKIE_NAME, $token, time() + (COOKIE_DAYS * 86400), '/', '', false, true);
            }

            header('Location: dashboard.php');
            exit;
        }
    }
}

$msgMap = ['login_required' => '🔒 Please log in to access that page.'];
$infoMsg = $msgMap[$_GET['msg'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Exp 11 Sessions & Cookies</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">🍪</div>
            Sessions &amp; Cookies
        </div>

        <?php if ($infoMsg): ?>
        <div class="alert alert-success"><?= $infoMsg ?></div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error">⚠️ <?= $errors['general'] ?></div>
        <?php endif; ?>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-sub">Sign in to your account to continue</p>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       class="<?= isset($errors['email']) ? 'err' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                <span class="error-msg">❌ <?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       class="<?= isset($errors['password']) ? 'err' : '' ?>">
                <?php if (isset($errors['password'])): ?>
                <span class="error-msg">❌ <?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me for <?= COOKIE_DAYS ?> days</label>
            </div>

            <button type="submit" class="btn-full">🚀 Sign In</button>
        </form>

        <p class="auth-switch">
            Don't have an account? <a href="register.php">Create one →</a>
        </p>
        <p class="cookie-note">🍪 "Remember Me" stores a secure token cookie on your browser</p>
        <p style="text-align:center;margin-top:12px;">
            <a href="../../index.html" style="font-size:13px;color:var(--gray-500);text-decoration:none;">← Back to Portfolio</a>
        </p>
    </div>
</div>
</body>
</html>
