<?php
require_once 'db.php';
$db = getDB();

// Stats
$totalProducts = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalValue    = $db->query('SELECT SUM(price * stock) FROM products')->fetchColumn() ?? 0;
$totalStock    = $db->query('SELECT SUM(stock) FROM products')->fetchColumn() ?? 0;
$categories    = $db->query('SELECT COUNT(DISTINCT category) FROM products')->fetchColumn();

// Search & filter
$search   = trim($_GET['search'] ?? '');
$catFilter= $_GET['cat'] ?? '';
$sql      = 'SELECT * FROM products WHERE 1=1';
$params   = [];
if ($search !== '') {
    $sql .= ' AND (name LIKE :s OR description LIKE :s2)';
    $params[':s']  = "%$search%";
    $params[':s2'] = "%$search%";
}
if ($catFilter !== '') {
    $sql .= ' AND category = :cat';
    $params[':cat'] = $catFilter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? '';
session_start();
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 10 – PHP MySQL CRUD | WT Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">🐘</div>
            PHP CRUD
        </div>
        <span style="font-size:10px;font-weight:700;letter-spacing:2px;color:var(--gray-500);text-transform:uppercase;padding:0 20px 8px;">Navigation</span>
        <a href="index.php" class="nav-item active">
            <span class="nav-icon">📦</span> All Products
        </a>
        <a href="create.php" class="nav-item">
            <span class="nav-icon">➕</span> Add Product
        </a>
        <div class="sidebar-divider"></div>
        <span style="font-size:10px;font-weight:700;letter-spacing:2px;color:var(--gray-500);text-transform:uppercase;padding:0 20px 8px;">Database</span>
        <a href="setup.sql" class="nav-item" download>
            <span class="nav-icon">🗄️</span> setup.sql
        </a>
        <div class="sidebar-back">
            <a href="../../index.html">← Back to Portfolio</a>
        </div>
    </aside>

    <!-- CONTENT -->
    <div class="content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Product Management</div>
                <div class="topbar-sub">Experiment 10 — PHP + MySQL CRUD</div>
            </div>
            <div class="topbar-actions">
                <a href="create.php" class="btn btn-primary">➕ Add Product</a>
            </div>
        </div>

        <div class="main">

            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">📦</div>
                    <div>
                        <div class="stat-value"><?= $totalProducts ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">💰</div>
                    <div>
                        <div class="stat-value">₹<?= number_format((float)$totalValue, 0) ?></div>
                        <div class="stat-label">Inventory Value</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">📋</div>
                    <div>
                        <div class="stat-value"><?= $totalStock ?></div>
                        <div class="stat-label">Total Stock</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">🏷️</div>
                    <div>
                        <div class="stat-value"><?= $categories ?></div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
            </div>

            <!-- FLASH -->
            <?php if ($flash): ?>
            <div class="alert alert-success" style="margin-bottom:24px;">✅ <?= htmlspecialchars($flash) ?></div>
            <?php endif; ?>

            <!-- PRODUCTS TABLE -->
            <div class="table-section">
                <div class="section-header">
                    <h2 class="section-title">All Products</h2>
                    <span style="font-size:13px;color:var(--gray-500);"><?= count($products) ?> result(s)</span>
                </div>
                <div class="table-card">
                    <form method="GET" class="search-bar">
                        <input type="text" name="search" placeholder="🔍  Search products…"
                               value="<?= htmlspecialchars($search) ?>">
                        <select name="cat" onchange="this.form.submit()"
                            style="background:var(--bg-input);border:1.5px solid var(--border);border-radius:10px;padding:10px 16px;color:var(--white);font-size:14px;outline:none;">
                            <option value="">All Categories</option>
                            <?php foreach (['Electronics','Clothing','Food','Books','Other'] as $c): ?>
                            <option value="<?=$c?>" <?= $catFilter===$c?'selected':'' ?>><?=$c?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-size:14px;">Search</button>
                        <?php if ($search||$catFilter): ?>
                        <a href="index.php" class="btn btn-secondary" style="padding:10px 16px;font-size:14px;">Clear</a>
                        <?php endif; ?>
                    </form>

                    <?php if (empty($products)): ?>
                    <div class="no-products">
                        <div class="no-products-icon">📭</div>
                        <div class="no-products-text">No products found.</div>
                        <a href="create.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex;">➕ Add First Product</a>
                    </div>
                    <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <?php if ($p['image']): ?>
                                        <img src="uploads/<?= htmlspecialchars($p['image']) ?>"
                                             alt="" class="product-img">
                                        <?php else: ?>
                                        <div class="product-img-placeholder">📦</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="product-desc"><?= htmlspecialchars(substr($p['description'],0,60)).'…' ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $catClass = 'cat-'.strtolower($p['category']);
                                        ?>
                                        <span class="category-badge <?= $catClass ?>"><?= $p['category'] ?></span>
                                    </td>
                                    <td><span class="price">₹<?= number_format($p['price'], 2) ?></span></td>
                                    <td><?= $p['stock'] ?></td>
                                    <td style="font-size:12px;color:var(--gray-500);"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn-edit">✏️ Edit</a>
                                            <a href="delete.php?id=<?= $p['id'] ?>" class="btn-danger">🗑️ Del</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div><!--/.table-section-->

        </div><!--/.main-->

        <footer class="footer">
            Experiment 10 — PHP + MySQL CRUD | Web Technology Lab |
            <span>Adithya Gowda · 24BTRCN001</span>
        </footer>
    </div><!--/.content-->

</div><!--/.layout-->

<script>
// Live search filter (client-side fallback)
document.querySelector('.search-bar input').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') this.closest('form').submit();
});
</script>
</body>
</html>
