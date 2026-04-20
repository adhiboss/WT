<?php
session_start();
require_once 'auth.php';
tryAutoLogin();
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $old = compact('name','email');

    if (strlen($name) < 2)      $errors['name']     = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                                $errors['email']    = 'Enter a valid email address.';
    elseif (findUser($email))   $errors['email']    = 'An account with this email already exists.';
    if (strlen($password) < 6)  $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors['confirm']  = 'Passwords do not match.';

    if (empty($errors)) {
        $users   = loadUsers();
        $newUser = [
            'id'             => 'u' . time() . rand(100,999),
            'name'           => $name,
            'email'          => $email,
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'remember_token' => null,
            'joined'         => date('Y-m-d H:i:s'),
        ];
        $users[] = $newUser;
        saveUsers($users);

        $_SESSION['user_id']   = $newUser['id'];
        $_SESSION['login_time'] = time();
        $_SESSION['just_registered'] = true;
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Exp 11 Sessions & Cookies</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">🍪</div>
            Sessions &amp; Cookies
        </div>

        <h1 class="auth-title">Create Account</h1>
        <p class="auth-sub">Register to explore sessions &amp; cookies</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">⚠️ Please fix the errors below.</div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Your full name"
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                       class="<?= isset($errors['name']) ? 'err' : '' ?>">
                <?php if (isset($errors['name'])): ?><span class="error-msg">❌ <?= $errors['name'] ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       class="<?= isset($errors['email']) ? 'err' : '' ?>">
                <?php if (isset($errors['email'])): ?><span class="error-msg">❌ <?= $errors['email'] ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min 6 characters"
                       class="<?= isset($errors['password']) ? 'err' : '' ?>">
                <?php if (isset($errors['password'])): ?><span class="error-msg">❌ <?= $errors['password'] ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Re-enter password"
                       class="<?= isset($errors['confirm']) ? 'err' : '' ?>">
                <?php if (isset($errors['confirm'])): ?><span class="error-msg">❌ <?= $errors['confirm'] ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn-full" style="margin-top:8px;">✨ Create Account</button>
        </form>

        <p class="auth-switch">
            Already have an account? <a href="login.php">Sign in →</a>
        </p>
        <p style="text-align:center;margin-top:12px;">
            <a href="../../index.html" style="font-size:13px;color:var(--gray-500);text-decoration:none;">← Back to Portfolio</a>
        </p>
    </div>
</div>
</body>
</html>
