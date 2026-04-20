<?php
// auth.php — Shared auth helpers for Exp 11
define('USERS_FILE', __DIR__ . '/data/users.json');
define('COOKIE_NAME', 'wt_exp11_remember');
define('COOKIE_DAYS', 30);

function loadUsers(): array {
    if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
    if (!file_exists(USERS_FILE)) return [];
    return json_decode(file_get_contents(USERS_FILE), true) ?? [];
}

function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function findUser(string $email): ?array {
    foreach (loadUsers() as $u) {
        if (strtolower($u['email']) === strtolower($e = $email)) return $u;
    }
    return null;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php?msg=login_required');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    foreach (loadUsers() as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

// Auto-login from remember-me cookie
function tryAutoLogin(): void {
    if (isLoggedIn()) return;
    if (!isset($_COOKIE[COOKIE_NAME])) return;

    $token = $_COOKIE[COOKIE_NAME];
    foreach (loadUsers() as $u) {
        if (($u['remember_token'] ?? '') === $token) {
            $_SESSION['user_id'] = $u['id'];
            return;
        }
    }
}
