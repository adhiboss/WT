<?php
session_start();
require_once 'db.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();
if (!$p) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete image file
    if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])) {
        unlink(UPLOAD_DIR . $p['image']);
    }
    $db->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $id]);
    $_SESSION['flash'] = "Product \"" . $p['name'] . "\" deleted.";
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product – Exp 10</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="sidebar-logo-icon">🐘</div>PHP CRUD</div>
        <a href="index.php" class="nav-item"><span class="nav-icon">📦</span> All Products</a>
        <a href="create.php" class="nav-item"><span class="nav-icon">➕</span> Add Product</a>
        <div class="sidebar-back"><a href="../../index.html">← Back to Portfolio</a></div>
    </aside>
    <div class="content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Delete Product</div>
                <div class="topbar-sub">This action cannot be undone</div>
            </div>
        </div>
        <div class="main">
            <div class="confirm-box">
                <div class="confirm-icon">🗑️</div>
                <div class="confirm-title">Delete Product?</div>
                <p class="confirm-desc">
                    You are about to permanently delete:<br>
                    <strong style="color:white;"><?= htmlspecialchars($p['name']) ?></strong><br>
                    (<?= $p['category'] ?> · ₹<?= number_format($p['price'],2) ?>)<br><br>
                    This action <strong>cannot be undone</strong>.
                </p>
                <div class="confirm-actions">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <form method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-danger" style="padding:13px 28px;font-size:15px;border:none;cursor:pointer;">🗑️ Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <footer class="footer">Experiment 10 — PHP + MySQL CRUD | <span>Adithya Gowda · 24BTRCN001</span></footer>
    </div>
</div>
</body>
</html>
