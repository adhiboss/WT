<?php
// ══════════════════════════════════════════════════════
//  db.php — Database connection for Experiment 10
//  SETUP: Create database using setup.sql first
// ══════════════════════════════════════════════════════

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change if different
define('DB_PASS', '');           // Change if different
define('DB_NAME', 'wt_exp10');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:32px;background:#0f1422;color:#fca5a5;border:1px solid #ef4444;border-radius:12px;margin:32px auto;max-width:600px;">
                <h2>⚠️ Database Connection Failed</h2>
                <p style="margin-top:12px;color:#9ca3af;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                <p style="margin-top:20px;color:#6b7280;">
                    <strong style="color:#60a5fa">Fix:</strong><br>
                    1. Start MySQL (XAMPP → phpMyAdmin)<br>
                    2. Run <code style="background:#1f2937;padding:2px 8px;border-radius:4px;">setup.sql</code> in phpMyAdmin<br>
                    3. Update credentials in <code style="background:#1f2937;padding:2px 8px;border-radius:4px;">db.php</code> if needed
                </p>
            </div>');
        }
    }
    return $pdo;
}

// Upload helper
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_IMG_SIZE', 3 * 1024 * 1024);  // 3 MB
define('ALLOWED_IMG',  ['jpg','jpeg','png','gif','webp']);

function handleImageUpload(string $fieldName): string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    $file = $_FILES[$fieldName];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload error.');
    if ($file['size'] > MAX_IMG_SIZE)     throw new Exception('Image too large (max 3 MB).');
    if (!in_array($ext, ALLOWED_IMG))     throw new Exception('Invalid type. Use JPG, PNG, GIF, WebP.');

    $name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new Exception('Could not save image. Check folder permissions.');
    }
    return $name;
}
