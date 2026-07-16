<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed.']));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$commentId = $input['comment_id'] ?? '';
$text = trim($input['text'] ?? '');

if ($commentId === '' || $text === '') {
    http_response_code(400);
    die(json_encode(['error' => 'Reply text is required.']));
}

$user = currentUser();

$stmt = $pdo->prepare('SELECT id, ticket_id, author_id FROM ticket_comments WHERE id = ?');
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

if (!$comment) {
    http_response_code(404);
    die(json_encode(['error' => 'Reply not found.']));
}

// Accountability: only the author of this SPECIFIC reply can edit it.
// This is not simply granted to IT — even IT cannot modify someone else's
// reply once it has been sent, so accountability is preserved.
if ($comment['author_id'] === null || (int)$comment['author_id'] !== (int)$user['id']) {
    http_response_code(403);
    die(json_encode(['error' => 'Only the person who wrote this reply can edit it.']));
}

$stmt = $pdo->prepare('UPDATE ticket_comments SET message = ?, edited_at = NOW() WHERE id = ?');
$stmt->execute([$text, $commentId]);

$stmt = $pdo->prepare('SELECT id, author, author_id AS authorId, message AS text, created_at AS createdAt, edited_at AS editedAt FROM ticket_comments WHERE ticket_id = ? ORDER BY created_at ASC');
$stmt->execute([$comment['ticket_id']]);
$comments = $stmt->fetchAll();

echo json_encode(['comments' => $comments]);
