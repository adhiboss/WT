<?php
// ── CONFIG ──────────────────────────────────────────────────────────────────
define('SUBMISSIONS_FILE', __DIR__ . '/data/submissions.json');
define('UPLOADS_DIR',      __DIR__ . '/uploads/');
define('MAX_FILE_SIZE',    2 * 1024 * 1024); // 2 MB
define('ALLOWED_TYPES',    ['pdf','doc','docx','jpg','jpeg','png']);

// Ensure data dirs exist
if (!is_dir(__DIR__ . '/data'))    mkdir(__DIR__ . '/data', 0755, true);
if (!is_dir(UPLOADS_DIR))          mkdir(UPLOADS_DIR, 0755, true);

// ── SANITISATION HELPERS ─────────────────────────────────────────────────────
function sanitize(string $v): string {
    return htmlspecialchars(trim(strip_tags($v)), ENT_QUOTES, 'UTF-8');
}
function validEmail(string $e): bool {
    return (bool) filter_var($e, FILTER_VALIDATE_EMAIL);
}
function validPhone(string $p): bool {
    return preg_match('/^[6-9]\d{9}$/', $p);   // Indian mobile
}

// ── LOAD SUBMISSIONS ────────────────────────────────────────────────────────
function loadSubmissions(): array {
    if (!file_exists(SUBMISSIONS_FILE)) return [];
    $data = file_get_contents(SUBMISSIONS_FILE);
    return json_decode($data, true) ?? [];
}

// ── PROCESS FORM ─────────────────────────────────────────────────────────────
$errors  = [];
$success = false;
$old     = [];     // repopulate fields on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect & sanitize
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $phone   = sanitize($_POST['phone']   ?? '');
    $dept    = sanitize($_POST['dept']    ?? '');
    $year    = sanitize($_POST['year']    ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $old     = compact('name','email','phone','dept','year','message');

    // Validation
    if ($name === '')            $errors['name']    = 'Full name is required.';
    elseif (strlen($name) < 2)  $errors['name']    = 'Name must be at least 2 characters.';

    if ($email === '')           $errors['email']   = 'Email address is required.';
    elseif (!validEmail($email)) $errors['email']   = 'Please enter a valid email address.';

    if ($phone === '')           $errors['phone']   = 'Phone number is required.';
    elseif (!validPhone($phone)) $errors['phone']   = 'Enter a valid 10-digit Indian mobile number.';

    if ($dept === '')            $errors['dept']    = 'Please select your department.';
    if ($year === '')            $errors['year']    = 'Please select your year of study.';

    if ($message === '')         $errors['message'] = 'Message / feedback is required.';
    elseif (strlen($message)<10) $errors['message'] = 'Message must be at least 10 characters.';

    // File upload (optional)
    $uploadedFile = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['resume'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['resume'] = 'Upload failed. Please try again.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors['resume'] = 'File too large. Maximum size is 2 MB.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_TYPES)) {
                $errors['resume'] = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.';
            } else {
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
                if (move_uploaded_file($file['tmp_name'], UPLOADS_DIR . $safeName)) {
                    $uploadedFile = $safeName;
                } else {
                    $errors['resume'] = 'Could not save the file. Check server permissions.';
                }
            }
        }
    }

    // Save if no errors
    if (empty($errors)) {
        $submissions   = loadSubmissions();
        $submissions[] = [
            'id'        => count($submissions) + 1,
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'dept'      => $dept,
            'year'      => $year,
            'message'   => $message,
            'file'      => $uploadedFile,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        file_put_contents(SUBMISSIONS_FILE, json_encode($submissions, JSON_PRETTY_PRINT));

        // Redirect to success page with last record id
        $id = end($submissions)['id'];
        header("Location: success.php?id=$id");
        exit;
    }
}

// Load all submissions for the table at the bottom
$submissions = loadSubmissions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 09 – PHP Form Handling | WT Lab</title>
    <meta name="description" content="PHP server-side form validation and file upload experiment.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-inner">
        <a href="../../index.html" class="logo">
            <div class="logo-icon">🐘</div>
            Exp 09 – PHP Form
        </a>
        <div style="display:flex;align-items:center;gap:16px;">
            <span class="badge">PHP ONLY</span>
            <a href="../../index.html" class="back-link">← Back to Portfolio</a>
        </div>
    </div>
</header>

<main class="main">

    <!-- PAGE TITLE -->
    <div class="page-title-block">
        <span class="exp-label">⚙️ Experiment 09 — PHP Form Handling</span>
        <h1 class="page-title">Student <span>Registration</span> Form</h1>
        <p class="page-desc">
            Full server-side validation in PHP — sanitization, email/phone checks,
            optional file upload, and JSON-based data persistence.
        </p>
    </div>

    <!-- GLOBAL ERROR ALERT -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <span class="alert-icon">⚠️</span>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= $e ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="form-card">
        <p class="form-section-title">📋 Personal Information</p>

        <form method="POST" action="" enctype="multipart/form-data" novalidate>

            <!-- Row 1: Name + Email -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input
                        type="text" id="name" name="name"
                        placeholder="e.g. Adithya Gowda"
                        value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                        class="<?= isset($errors['name']) ? 'error' : '' ?>"
                        maxlength="100">
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-msg">❌ <?= $errors['name'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input
                        type="email" id="email" name="email"
                        placeholder="student@example.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        class="<?= isset($errors['email']) ? 'error' : '' ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-msg">❌ <?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 2: Phone + Dept -->
            <div class="form-grid" style="margin-top:20px;">
                <div class="form-group">
                    <label for="phone">Mobile Number <span class="required">*</span></label>
                    <input
                        type="tel" id="phone" name="phone"
                        placeholder="10-digit mobile number"
                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                        class="<?= isset($errors['phone']) ? 'error' : '' ?>"
                        maxlength="10">
                    <?php if (isset($errors['phone'])): ?>
                        <span class="error-msg">❌ <?= $errors['phone'] ?></span>
                    <?php else: ?>
                        <span class="help-text">Indian format: starts with 6-9, 10 digits</span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="dept">Department <span class="required">*</span></label>
                    <select id="dept" name="dept" class="<?= isset($errors['dept']) ? 'error' : '' ?>">
                        <option value="">-- Select Department --</option>
                        <?php
                        $depts = ['CSE','ISE','ECE','EEE','MECH','CIVIL','AIML','DS'];
                        foreach ($depts as $d):
                            $sel = (($old['dept'] ?? '') === $d) ? 'selected' : '';
                        ?>
                        <option value="<?= $d ?>" <?= $sel ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['dept'])): ?>
                        <span class="error-msg">❌ <?= $errors['dept'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 3: Year + File -->
            <div class="form-grid" style="margin-top:20px;">
                <div class="form-group">
                    <label for="year">Year of Study <span class="required">*</span></label>
                    <select id="year" name="year" class="<?= isset($errors['year']) ? 'error' : '' ?>">
                        <option value="">-- Select Year --</option>
                        <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $yr):
                            $sel = (($old['year'] ?? '') === $yr) ? 'selected' : '';
                        ?>
                        <option value="<?= $yr ?>" <?= $sel ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['year'])): ?>
                        <span class="error-msg">❌ <?= $errors['year'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="resume">Upload Resume / ID (optional)</label>
                    <input type="file" id="resume" name="resume"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="<?= isset($errors['resume']) ? 'error' : '' ?>">
                    <span class="file-hint">Max 2 MB · PDF, DOC, JPG, PNG</span>
                    <?php if (isset($errors['resume'])): ?>
                        <span class="error-msg">❌ <?= $errors['resume'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 4: Message -->
            <div class="form-grid single" style="margin-top:20px;">
                <div class="form-group">
                    <label for="message">Feedback / Message <span class="required">*</span></label>
                    <textarea id="message" name="message"
                              placeholder="Share your feedback, queries or suggestions..."
                              class="<?= isset($errors['message']) ? 'error' : '' ?>"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <span class="error-msg">❌ <?= $errors['message'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                🚀 Submit Registration
            </button>
        </form>
    </div>

    <!-- SUBMISSIONS TABLE -->
    <?php if (!empty($submissions)): ?>
    <div class="submissions-section">
        <div class="section-header">
            <h2>📂 All Submissions</h2>
            <span class="count-badge"><?= count($submissions) ?> record(s)</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Dept</th>
                        <th>Year</th>
                        <th>File</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($submissions) as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['phone']) ?></td>
                        <td><?= htmlspecialchars($s['dept']) ?></td>
                        <td><?= htmlspecialchars($s['year']) ?></td>
                        <td>
                            <?php if ($s['file']): ?>
                                <a href="uploads/<?= urlencode($s['file']) ?>"
                                   class="has-file" target="_blank">📎 view</a>
                            <?php else: ?>
                                <span class="no-file">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $s['timestamp'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</main>

<footer class="footer">
    Experiment 09 — PHP Form Handling &amp; Validation | Web Technology Lab |
    <span>Adithya Gowda · 24BTRCN001</span>
</footer>

</body>
</html>
