<?php
/**
 * ProcrastiTrack — Contact Form Handler
 * POST: name, email, subject, message
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name    = htmlspecialchars(trim($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = htmlspecialchars(trim($_POST['subject'] ?? 'General question'));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// --- In production: send email via PHPMailer / SendGrid ---
// $mail = new PHPMailer\PHPMailer\PHPMailer();
// $mail->setFrom('noreply@procrastitrack.app');
// $mail->addAddress('hello@procrastitrack.app');
// $mail->Subject = "[ProcrastiTrack Contact] $subject";
// $mail->Body    = "From: $name <$email>\n\n$message";
// $mail->send();

// Log to file for demo
$log = date('Y-m-d H:i:s') . " | $name | $email | $subject\n";
file_put_contents(__DIR__ . '/contact_log.txt', $log, FILE_APPEND | LOCK_EX);

echo json_encode([
    'success' => true,
    'message' => 'Your message has been received. We\'ll reply within 24 hours.'
]);
