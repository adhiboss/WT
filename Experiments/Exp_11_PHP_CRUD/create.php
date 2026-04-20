<?php
session_start();
require_once 'db.php';
$db = getDB();
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $cat   = trim($_POST['cat']   ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $desc  = trim($_POST['desc']  ?? '');
    $old = compact('name','cat','price','stock','desc');

    if ($name === '')         $errors['name']  = 'Product name is required.';
    if ($cat === '')          $errors['cat']   = 'Please select a category.';
    if ($price === '' || !is_numeric($price) || $price < 0) $errors['price'] = 'Enter a valid positive price.';
    if ($stock === '' || !ctype_digit($stock))               $errors['stock'] = 'Enter a valid stock quantity.';

    $imgFile = '';
    if (empty($errors)) {
        try {
            $imgFile = handleImageUpload('image');
        } catch (Exception $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO products (name,category,price,stock,description,image) VALUES (:n,:c,:p,:s,:d,:i)');
        $stmt->execute([':n'=>$name,':c'=>$cat,':p'=>(float)$price,':s'=>(int)$stock,':d'=>$desc,':i'=>$imgFile]);
        $_SESSION['flash'] = "Product \"$name\" added successfully!";
        header('Location: index.php');
        exit;
    }
}

$cats = ['Electronics','Clothing','Food','Books','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product – Exp 10</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><div class="sidebar-logo-icon">🐘</div>PHP CRUD</div>
        <a href="index.php" class="nav-item"><span class="nav-icon">📦</span> All Products</a>
        <a href="create.php" class="nav-item active"><span class="nav-icon">➕</span> Add Product</a>
        <div class="sidebar-back"><a href="../../index.html">← Back to Portfolio</a></div>
    </aside>
    <div class="content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Add New Product</div>
                <div class="topbar-sub">Create a new product record in the database</div>
            </div>
            <a href="index.php" class="btn btn-secondary">← Back to Products</a>
        </div>
        <div class="main">
            <div class="form-page">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">⚠️ Please fix the errors below before submitting.</div>
                <?php endif; ?>
                <div class="form-card">
                    <div class="form-card-title">📦 Product Details</div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Product Name <span class="req">*</span></label>
                                <input type="text" id="name" name="name" placeholder="e.g. iPhone 15 Pro"
                                       value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                <?php if (isset($errors['name'])): ?><span class="error-msg">❌ <?=$errors['name']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="cat">Category <span class="req">*</span></label>
                                <select id="cat" name="cat">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($cats as $c): $s = (($old['cat']??'')===$c)?'selected':''; ?>
                                    <option value="<?=$c?>" <?=$s?>><?=$c?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['cat'])): ?><span class="error-msg">❌ <?=$errors['cat']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="price">Price (₹) <span class="req">*</span></label>
                                <input type="number" id="price" name="price" min="0" step="0.01"
                                       placeholder="0.00" value="<?= htmlspecialchars($old['price'] ?? '') ?>">
                                <?php if (isset($errors['price'])): ?><span class="error-msg">❌ <?=$errors['price']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock Quantity <span class="req">*</span></label>
                                <input type="number" id="stock" name="stock" min="0"
                                       placeholder="0" value="<?= htmlspecialchars($old['stock'] ?? '') ?>">
                                <?php if (isset($errors['stock'])): ?><span class="error-msg">❌ <?=$errors['stock']?></span><?php endif; ?>
                            </div>
                            <div class="form-group full">
                                <label for="desc">Description</label>
                                <textarea id="desc" name="desc" placeholder="Brief product description…"><?= htmlspecialchars($old['desc'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label for="image">Product Image (optional)</label>
                                <input type="file" id="image" name="image" accept="image/*">
                                <span class="form-hint">Max 3 MB · JPG, PNG, GIF, WebP</span>
                                <?php if (isset($errors['image'])): ?><span class="error-msg">❌ <?=$errors['image']?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-submit">➕ Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <footer class="footer">Experiment 10 — PHP + MySQL CRUD | <span>Adithya Gowda · 24BTRCN001</span></footer>
    </div>
</div>
</body>
</html>
