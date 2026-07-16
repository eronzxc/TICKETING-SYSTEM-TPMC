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

if ($id === '') {
    http_response_code(400);
    die(json_encode(['error' => 'Ticket ID is required.']));
}

// Only these fields can be edited here: Status, Due date, Date resolved,
// and Remarks. We don't touch the original request (department, category,
// priority, description) so the requester's submission is never
// overwritten. Status changes go through this endpoint too (behind a
// confirmation dialog) so it can't be changed by an accidental click.
$status     = trim($input['status'] ?? '');
$dueDate    = trim($input['due_date'] ?? '');
$resolvedAt = trim($input['resolved_at'] ?? '');
$remarks    = trim($input['remarks'] ?? '');

if (!in_array($status, ['Open', 'In progress', 'Resolved'], true)) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid status.']));
}

$stmt = $pdo->prepare('SELECT status, resolved_at, resolved_by FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$existing = $stmt->fetch();
if (!$existing) {
    http_response_code(404);
    die(json_encode(['error' => 'Ticket not found.']));
}

$user = currentUser();
$dueDateVal = $dueDate !== '' ? date('Y-m-d H:i:s', strtotime($dueDate)) : null;
$remarksVal = $remarks !== '' ? $remarks : null;

if ($status === 'Resolved') {
    if ($existing['status'] !== 'Resolved') {
        // Just got resolved now: record who resolved it and when.
        $resolvedAtVal = $resolvedAt !== '' ? date('Y-m-d H:i:s', strtotime($resolvedAt)) : date('Y-m-d H:i:s');
        $resolvedByVal = $user['fullname'];
    } else {
        // Was already resolved — keep the existing resolved_by, unless the
        // Date resolved was intentionally changed in the form.
        $resolvedAtVal = $resolvedAt !== '' ? date('Y-m-d H:i:s', strtotime($resolvedAt)) : $existing['resolved_at'];
        $resolvedByVal = $existing['resolved_by'];
    }
} else {
    $resolvedAtVal = null;
    $resolvedByVal = null;
}

$stmt = $pdo->prepare(
    'UPDATE tickets SET status = ?, due_date = ?, resolved_at = ?, resolved_by = ?, remarks = ?, updated_at = NOW() WHERE id = ?'
);
$stmt->execute([$status, $dueDateVal, $resolvedAtVal, $resolvedByVal, $remarksVal, $id]);

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
$ticket['attachments'] = $ticket['attachments_json'] ? json_decode($ticket['attachments_json'], true) : [];
unset($ticket['attachments_json']);
$ticket['created_by'] = $ticket['created_by'] !== null ? (int)$ticket['created_by'] : null;

$stmt = $pdo->prepare('SELECT id, author, author_id AS authorId, message AS text, created_at AS createdAt, edited_at AS editedAt FROM ticket_comments WHERE ticket_id = ? ORDER BY created_at ASC');
$stmt->execute([$id]);
$ticket['comments'] = $stmt->fetchAll();

echo json_encode(['ticket' => $ticket]);
