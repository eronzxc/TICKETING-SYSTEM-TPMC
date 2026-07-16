<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$fullname   = trim($input['fullname'] ?? '');
$username   = trim($input['username'] ?? '');
$department = trim($input['department'] ?? '');
$password   = $input['password'] ?? '';
$confirm    = $input['confirmPassword'] ?? '';

// ---- Validation ----
if ($fullname === '' || $username === '' || $department === '' || $password === '') {
    http_response_code(400);
    die(json_encode(['error' => 'Please fill in all fields.']));
}
if (strlen($password) < 6) {
    http_response_code(400);
    die(json_encode(['error' => 'Password must be at least 6 characters.']));
}
if ($password !== $confirm) {
    http_response_code(400);
    die(json_encode(['error' => 'Password and confirm password do not match.']));
}

// Check duplicate username
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    http_response_code(409);
    die(json_encode(['error' => 'This username is already taken.']));
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (fullname, username, password_hash, department) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$fullname, $username, $hash, $department]);

$user = [
    'id'         => $pdo->lastInsertId(),
    'fullname'   => $fullname,
    'username'   => $username,
    'department' => $department,
];

$_SESSION['user'] = $user;

echo json_encode(['user' => $user]);
