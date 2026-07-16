<?php
// config/session.php — include this at the top of every PHP file that needs to know who is logged in.
session_start();
header('Content-Type: application/json');

// So the browser doesn't cache session-dependent responses
header('Cache-Control: no-store, no-cache, must-revalidate');

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        http_response_code(401);
        die(json_encode(['error' => 'You are not logged in.']));
    }
}

function requireIT() {
    requireLogin();
    if (($_SESSION['user']['department'] ?? '') !== 'IT Department') {
        http_response_code(403);
        die(json_encode(['error' => 'Only IT Department has access to this.']));
    }
}
