<?php
/**
 * ProcrastiTrack — Authentication Handler
 * Handles email login/signup + Google OAuth callback
 */
session_start();
header('Content-Type: application/json');

require_once 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'signup':
        $name   = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email  = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $pw     = $_POST['password'] ?? '';
        $school = htmlspecialchars(trim($_POST['school'] ?? ''));

        if (!$name || !$email || strlen($pw) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input. Minimum 8 characters password.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered']);
            exit;
        }

        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (name, email, pw_hash, school, xp, level, streak) VALUES (?, ?, ?, ?, 0, 1, 0)");
        $ins->execute([$name, $email, $hash, $school]);
        $id = $pdo->lastInsertId();

        $user = [
            'id'      => $id,
            'name'    => $name,
            'email'   => $email,
            'school'  => $school,
            'xp'      => 0,
            'level'   => 1,
            'streak'  => 0
        ];
        
        $_SESSION['user'] = $user;
        echo json_encode(['success' => true, 'user' => $user]);
        break;

    case 'login':
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $pw    = $_POST['password'] ?? '';
        
        if (!$email || !$pw) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($pw, $row['pw_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        unset($row['pw_hash']); // Security measure
        $_SESSION['user'] = $row;
        echo json_encode(['success' => true, 'user' => $row]);
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'google_callback':
        /**
         * Google OAuth 2.0 Callback
         */
        $code = $_GET['code'] ?? null;
        if (!$code) {
            http_response_code(400);
            echo 'Missing OAuth code';
            exit;
        }
        // Simulated Google sync callback for demo
        $_SESSION['user'] = [
            'id'       => 1, // mapping to demo user
            'name'     => 'Demo Student',
            'email'    => 'demo@student.edu',
            'google'   => true,
            'xp'       => 1240,
            'level'    => 7,
            'streak'   => 5,
            'classroom'=> true,
        ];
        header('Location: ../dashboard.html');
        exit;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
?>
