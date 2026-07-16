<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireIT();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $input['id'] ?? '';
$status = $input['status'] ?? '';

if (!in_array($status, ['Open', 'In progress', 'Resolved'], true)) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid status.']));
}

$user = currentUser();

if ($status === 'Resolved') {
    $stmt = $pdo->prepare(
        'UPDATE tickets SET status = ?, updated_at = NOW(), resolved_at = NOW(), resolved_by = ? WHERE id = ?'
    );
    $stmt->execute([$status, $user['fullname'], $id]);
} else {
    $stmt = $pdo->prepare(
        'UPDATE tickets SET status = ?, updated_at = NOW(), resolved_at = NULL, resolved_by = NULL WHERE id = ?'
    );
    $stmt->execute([$status, $id]);
}

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) {
    http_response_code(404);
    die(json_encode(['error' => 'Ticket not found.']));
}
$ticket['attachments'] = $ticket['attachments_json'] ? json_decode($ticket['attachments_json'], true) : [];
unset($ticket['attachments_json']);

echo json_encode(['ticket' => $ticket]);
