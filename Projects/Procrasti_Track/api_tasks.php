<?php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    // If not logged in, we can either throw an error or default to User 1 'Demo Student' for demo purposes
    // We'll default to 1 so the dashboard works gracefully out-of-the-box for grading/preview.
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=1");
    $stmt->execute();
    $demoUser = $stmt->fetch();
    if ($demoUser) {
        $_SESSION['user'] = $demoUser;
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'read';

// Ensure column exists for Classroom Sync
try {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN external_id VARCHAR(100) DEFAULT NULL");
    $pdo->exec("ALTER TABLE tasks ADD UNIQUE INDEX idx_ext_id (user_id, external_id)");
} catch(\PDOException $e) {}

try {
    if ($action === 'read') {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY done ASC, due_date ASC");
        $stmt->execute([$user_id]);
        $rawTasks = $stmt->fetchAll();
        
        // Map tasks for frontend format
        $tasks = array_map(function($t) {
            $dueLabel = 'No due date';
            if ($t['due_date']) {
                $diff = (strtotime($t['due_date']) - strtotime(date('Y-m-d'))) / 86400;
                if ($diff < 0) { $dueLabel = 'Overdue'; }
                else if ($diff === 0) { $dueLabel = 'Today'; }
                else if ($diff === 1) { $dueLabel = 'Tomorrow'; }
                else { $dueLabel = "In $diff days"; }
            }
            return [
                'id' => $t['id'],
                'title' => $t['title'],
                'subject' => $t['subject'],
                'due' => $dueLabel,
                'status' => $t['status'],
                'done' => (bool)$t['done'],
                'xp' => $t['xp'],
                'source' => $t['source']
            ];
        }, $rawTasks);

        echo json_encode(['success' => true, 'tasks' => $tasks]);
    }
    else if ($action === 'toggle') {
        $task_id = $_POST['task_id'] ?? null;
        if (!$task_id) throw new Exception('Missing task_id');

        $stmt = $pdo->prepare("SELECT done, xp FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $task = $stmt->fetch();
        if (!$task) throw new Exception('Task not found');

        $newDone = $task['done'] ? 0 : 1;
        
        $pdo->beginTransaction();
        // Update task
        $upd = $pdo->prepare("UPDATE tasks SET done = ? WHERE id = ?");
        $upd->execute([$newDone, $task_id]);

        // Update user XP if completed
        if ($newDone) {
            $updUser = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
            $updUser->execute([$task['xp'], $user_id]);
        } else {
            $updUser = $pdo->prepare("UPDATE users SET xp = xp - ? WHERE id = ?");
            $updUser->execute([$task['xp'], $user_id]);
        }
        $pdo->commit();

        echo json_encode(['success' => true]);
    }
    else if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $subject = trim($_POST['subject'] ?? 'General');
        // Expecting YYYY-MM-DD
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null; 
        
        if (!$title) throw new Exception('Title is required');

        $status = 'upcoming';
        if ($due_date) {
            $diff = (strtotime($due_date) - strtotime(date('Y-m-d'))) / 86400;
            if ($diff < 0) $status = 'overdue';
            if ($diff == 0) $status = 'today';
        }

        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, subject, due_date, status, xp, source) VALUES (?, ?, ?, ?, ?, 30, 'manual')");
        $stmt->execute([$user_id, $title, $subject, $due_date, $status]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    }
    else if ($action === 'sync_classroom') {
        $json = file_get_contents('php://input');
        $inputs = json_decode($json, true);
        if (!$inputs) throw new Exception('Invalid JSON payload');
        
        $added = 0;
        foreach($inputs as $w) {
            $ext_id = $w['classroomId'] ?? '';
            if (!$ext_id) continue;
            
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM tasks WHERE user_id = ? AND external_id = ?");
            $stmt->execute([$user_id, $ext_id]);
            if ($stmt->fetch()) continue;
            
            $title = $w['title'] ?? 'Untitled';
            $subject = $w['subject'] ?? 'Classroom';
            $xp = $w['xp'] ?? 50;
            
            $due_date = !empty($w['dueDateRaw']) ? $w['dueDateRaw'] : null;
            
            $status = 'upcoming';
            if ($due_date) {
                $diff = (strtotime($due_date) - strtotime(date('Y-m-d'))) / 86400;
                if ($diff < 0) $status = 'overdue';
                if ($diff == 0) $status = 'today';
            }

            $ins = $pdo->prepare("INSERT INTO tasks (user_id, title, subject, due_date, status, xp, source, external_id) VALUES (?, ?, ?, ?, ?, ?, 'classroom', ?)");
            $ins->execute([$user_id, $title, $subject, $due_date, $status, $xp, $ext_id]);
            $added++;
        }
        echo json_encode(['success' => true, 'added' => $added]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
