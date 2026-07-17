<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$user = currentUser();

// Requester and department are prefilled from the logged-in account on
// the frontend, but stay editable there (e.g. submitting on behalf of
// someone else) — so we take the submitted values here. created_by is
// still always the logged-in account, so ownership/reply permissions
// stay tied to the real account regardless of what's typed in the form.
$requester   = trim($input['requester'] ?? '') ?: $user['fullname'];
$department  = trim($input['department'] ?? '') ?: $user['department'];
$createdBy   = $user['id'];
$category    = trim($input['category'] ?? '');
$priority    = trim($input['priority'] ?? '');
$description = trim($input['description'] ?? '');
$attachments = $input['attachments'] ?? [];

if ($requester === '' || $department === '') {
    http_response_code(400);
    die(json_encode(['error' => 'Please fill in all required fields.']));
}
if (!in_array($priority, ['Low', 'Medium', 'High', 'Urgent'], true)) {
    $priority = 'Medium';
}

// Generate ticket ID: IT-{year}-{0001, 0002, ...}
$year = date('Y');
$stmt = $pdo->prepare("SELECT id FROM tickets WHERE id LIKE ? ORDER BY id DESC LIMIT 1");
$stmt->execute(["IT-$year-%"]);
$last = $stmt->fetchColumn();
$nextNum = $last ? ((int)substr($last, -4) + 1) : 1;
$ticketId = sprintf('IT-%s-%04d', $year, $nextNum);

$dueDaysMap = ['Urgent' => 1, 'High' => 2, 'Medium' => 3, 'Low' => 5];
$dueDays = $dueDaysMap[$priority] ?? 3;

$customDueDate = trim($input['dueDate'] ?? '');
if ($customDueDate !== '') {
    // Requester specified their own preferred deadline (date only, e.g. "2026-07-20")
    $dueDate = date('Y-m-d H:i:s', strtotime($customDueDate . ' 23:59:59'));
} else {
    // Fallback: auto-computed based on priority
    $dueDate = date('Y-m-d H:i:s', strtotime("+$dueDays days"));
}

$attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;

$stmt = $pdo->prepare(
    'INSERT INTO tickets (id, requester, department, category, priority, description, status, due_date, attachments_json, created_by)
     VALUES (?, ?, ?, ?, ?, ?, "Open", ?, ?, ?)'
);
$stmt->execute([$ticketId, $requester, $department, $category, $priority, $description, $dueDate, $attachmentsJson, $createdBy]);

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();
$ticket['attachments'] = $attachments;

echo json_encode(['ticket' => $ticket]);
