<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$user = currentUser();
$isIT = ($user['department'] ?? '') === 'IT Department';

// Same reasoning as restore.php: permanent deletion is IT-only, even
// though a requester can soft-delete their own ticket. This prevents a
// non-IT user from wiping a ticket beyond recovery.
if (!$isIT) {
    http_response_code(403);
    die(json_encode(['error' => 'Only IT can permanently delete tickets.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $input['id'] ?? '';

if ($id === '') {
    http_response_code(400);
    die(json_encode(['error' => 'Ticket ID is required.']));
}

$stmt = $pdo->prepare('SELECT id, deleted_at FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    die(json_encode(['error' => 'Ticket not found.']));
}
if ($ticket['deleted_at'] === null) {
    http_response_code(400);
    die(json_encode(['error' => 'This ticket is not in Recently deleted.']));
}

// Hard delete: unlike delete.php, this is not reversible. The row is
// fully removed, so the ticket number can be considered permanently
// retired. Related replies are cleaned up too so nothing orphaned is
// left behind in ticket_comments.
$stmt = $pdo->prepare('DELETE FROM ticket_comments WHERE ticket_id = ?');
$stmt->execute([$id]);

$stmt = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['success' => true, 'id' => $id]);
