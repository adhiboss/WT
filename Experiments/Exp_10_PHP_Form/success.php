<?php
define('SUBMISSIONS_FILE', __DIR__ . '/data/submissions.json');

function loadSubmissions(): array {
    if (!file_exists(SUBMISSIONS_FILE)) return [];
    return json_decode(file_get_contents(SUBMISSIONS_FILE), true) ?? [];
}

$id = (int)($_GET['id'] ?? 0);
$submissions = loadSubmissions();
$record = null;
foreach ($submissions as $s) {
    if ($s['id'] === $id) { $record = $s; break; }
}
if (!$record) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful – Exp 09</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="header-inner">
        <a href="../../index.html" class="logo">
            <div class="logo-icon">🐘</div>
            Exp 09 – PHP Form
        </a>
        <span class="badge">PHP ONLY</span>
    </div>
</header>

<main class="main">
    <div class="success-page">
        <div class="success-icon">✅</div>
        <h1 class="success-title">Registration Successful!</h1>
        <p class="success-desc">
            Your form has been validated server-side and stored securely.
            Below is a summary of the submitted data.
        </p>

        <div class="data-preview">
            <h4>📋 Submitted Data (Record #<?= $record['id'] ?>)</h4>
            <div class="data-row">
                <span class="data-key">Full Name</span>
                <span class="data-val"><?= htmlspecialchars($record['name']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-key">Email</span>
                <span class="data-val"><?= htmlspecialchars($record['email']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-key">Phone</span>
                <span class="data-val"><?= htmlspecialchars($record['phone']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-key">Department</span>
                <span class="data-val"><?= htmlspecialchars($record['dept']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-key">Year</span>
                <span class="data-val"><?= htmlspecialchars($record['year']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-key">File Uploaded</span>
                <span class="data-val">
                    <?= $record['file']
                        ? '<a href="uploads/'.urlencode($record['file']).'" target="_blank" style="color:#a78bfa">📎 '.$record['file'].'</a>'
                        : '—' ?>
                </span>
            </div>
            <div class="data-row">
                <span class="data-key">Submitted At</span>
                <span class="data-val"><?= $record['timestamp'] ?></span>
            </div>
        </div>

        <div class="btn-group">
            <a href="index.php" class="btn-primary">📝 Submit Another</a>
            <a href="../../index.html" class="btn-secondary">← Back to Portfolio</a>
        </div>
    </div>
</main>

<footer class="footer">
    Experiment 09 — PHP Form Handling | <span>Adithya Gowda · 24BTRCN001</span>
</footer>
</body>
</html>
