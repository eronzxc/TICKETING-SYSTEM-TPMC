<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$user = currentUser();
$isIT = ($user['department'] ?? '') === 'IT Department';

if (!$isIT) {
    http_response_code(403);
    die(json_encode(['error' => 'IT Department access only.']));
}

// Auto-purge: tickets that have been soft-deleted for 30+ days are
// permanently removed. This runs as a lazy check whenever the Recently
// Deleted list is opened, so no cron job is required. Once purged here,
// the row (and its ticket number) is gone for good.
$pdo->exec("DELETE FROM tickets WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

$stmt = $pdo->query('SELECT * FROM tickets WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC');
$rows = $stmt->fetchAll();

$tickets = array_map(function ($row) {
    $row['attachments'] = $row['attachments_json'] ? json_decode($row['attachments_json'], true) : [];
    unset($row['attachments_json']);
    $row['created_by'] = $row['created_by'] !== null ? (int)$row['created_by'] : null;

    // Days remaining before this ticket is permanently purged (30-day window).
    $deletedAt = strtotime($row['deleted_at']);
    $purgeAt = strtotime('+30 days', $deletedAt);
    $row['daysLeft'] = max(0, (int)ceil(($purgeAt - time()) / 86400));

    return $row;
}, $rows);

echo json_encode(['tickets' => $tickets]);
