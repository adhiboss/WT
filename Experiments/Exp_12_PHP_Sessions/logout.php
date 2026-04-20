<?php
session_start();
require_once 'auth.php';
// Clear remember-me cookie
if (isset($_COOKIE[COOKIE_NAME])) {
    // Remove token from user record
    $users = loadUsers();
    foreach ($users as &$u) {
        if (($u['remember_token'] ?? '') === $_COOKIE[COOKIE_NAME]) {
            $u['remember_token'] = null;
            break;
        }
    }
    saveUsers($users);
    // Expire cookie immediately
    setcookie(COOKIE_NAME, '', time() - 3600, '/', '', false, true);
}
// Destroy session
$_SESSION = [];
session_destroy();
header('Location: login.php?msg=logged_out');
exit;
