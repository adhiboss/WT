<?php
session_start();
require_once 'db.php';
$db = getDB();
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$product = $db->prepare('SELECT * FROM products WHERE id = :id');
$product->execute([':id' => $id]);
$p = $product->fetch();
if (!$p) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $cat   = trim($_POST['cat']   ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $desc  = trim($_POST['desc']  ?? '');

    if ($name === '')         $errors['name']  = 'Product name is required.';
    if ($cat === '')          $errors['cat']   = 'Please select a category.';
    if (!is_numeric($price) || $price < 0) $errors['price'] = 'Enter a valid price.';
    if (!ctype_digit($stock))              $errors['stock'] = 'Enter a valid stock number.';

    $imgFile = $p['image']; // keep old image by default
    if (empty($errors)) {
        try {
            $newImg = handleImageUpload('image');
            if ($newImg !== '') {
                // Delete old image
                if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])) {
                    unlink(UPLOAD_DIR . $p['image']);
                }
                $imgFile = $newImg;
            }
        } catch (Exception $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare('UPDATE products SET name=:n,category=:c,price=:p,stock=:s,description=:d,image=:i WHERE id=:id');
        $stmt->execute([':n'=>$name,':c'=>$cat,':p'=>(float)$price,':s'=>(int)$stock,':d'=>$desc,':i'=>$imgFile,':id'=>$id]);
        $_SESSION['flash'] = "Product \"$name\" updated successfully!";
        header('Location: index.php');
        exit;
    }
    // Repopulate from POST on error
    $p = array_merge($p, compact('name','cat','price','stock','desc'));
}

$cats = ['Electronics','Clothing','Food','Books','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product – Exp 10</title>
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
                <div class="topbar-title">Edit Product #<?= $id ?></div>
                <div class="topbar-sub">Update product information in the database</div>
            </div>
            <a href="index.php" class="btn btn-secondary">← Back to Products</a>
        </div>
        <div class="main">
            <div class="form-page">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">⚠️ Please fix the errors below.</div>
                <?php endif; ?>
                <div class="form-card">
                    <div class="form-card-title">✏️ Edit: <?= htmlspecialchars($p['name']) ?></div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Product Name <span class="req">*</span></label>
                                <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>">
                                <?php if (isset($errors['name'])): ?><span class="error-msg">❌ <?=$errors['name']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Category <span class="req">*</span></label>
                                <select name="cat">
                                    <?php foreach ($cats as $c): $s=($p['category']===$c||$p['cat']===$c)?'selected':''; ?>
                                    <option value="<?=$c?>" <?=$s?>><?=$c?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['cat'])): ?><span class="error-msg">❌ <?=$errors['cat']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Price (₹) <span class="req">*</span></label>
                                <input type="number" name="price" min="0" step="0.01" value="<?= $p['price'] ?>">
                                <?php if (isset($errors['price'])): ?><span class="error-msg">❌ <?=$errors['price']?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Stock <span class="req">*</span></label>
                                <input type="number" name="stock" min="0" value="<?= $p['stock'] ?>">
                                <?php if (isset($errors['stock'])): ?><span class="error-msg">❌ <?=$errors['stock']?></span><?php endif; ?>
                            </div>
                            <div class="form-group full">
                                <label>Description</label>
                                <textarea name="desc"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Replace Image (optional)</label>
                                <?php if ($p['image']): ?>
                                <img src="uploads/<?= htmlspecialchars($p['image']) ?>"
                                     style="width:80px;height:80px;object-fit:cover;border-radius:10px;margin-bottom:8px;">
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/*">
                                <span class="form-hint">Leave empty to keep current image</span>
                                <?php if (isset($errors['image'])): ?><span class="error-msg">❌ <?=$errors['image']?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="delete.php?id=<?= $id ?>" class="btn btn-danger">🗑️ Delete</a>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-submit">💾 Save Changes</button>
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
