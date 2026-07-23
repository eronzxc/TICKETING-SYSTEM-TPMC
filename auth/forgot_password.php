<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['error' => 'Please enter a valid email address.']));
}

$stmt = $pdo->prepare('SELECT id, fullname, email FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Generic response regardless of whether the email matched an account —
// avoids leaking which emails are registered (account enumeration).
$genericResponse = [
    'success' => true,
    'message' => 'If that email is registered, we\'ve sent a reset code to it.',
];

if (!$user) {
    echo json_encode($genericResponse);
    exit;
}

$code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = $pdo->prepare('UPDATE users SET reset_code = ?, reset_code_expires = ? WHERE id = ?');
$stmt->execute([$code, $expires, $user['id']]);

$subject = 'TPMC IT Concern Desk — Password reset code';
$bodyHtml = "
    <p>Hi {$user['fullname']},</p>
    <p>Here's your password reset code. It expires in <strong>15 minutes</strong>.</p>
    <p style=\"font-size:28px;font-weight:bold;letter-spacing:4px;\">{$code}</p>
    <p>If you didn't request this, you can safely ignore this email — your password won't be changed.</p>
    <p style=\"color:#888;font-size:12px;\">TPMC IT Concern Desk</p>
";

$sent = sendMail($user['email'], $user['fullname'], $subject, $bodyHtml);

if (!$sent) {
    // Email failed to send (e.g. SMTP misconfigured) — don't leave the
    // person stuck with no way forward, but don't leak SMTP details either.
    http_response_code(500);
    die(json_encode(['error' => 'We could not send the reset email right now. Please try again later or contact IT directly.']));
}

echo json_encode($genericResponse);
