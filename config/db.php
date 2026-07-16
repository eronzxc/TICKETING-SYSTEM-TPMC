<?php
// config/db.php — single place for the database connection settings.
// Adjust this if you use a different MySQL username/password (default XAMPP: root, no password).

$DB_HOST = 'localhost';
$DB_NAME = 'tpmc_ticketing';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed. Kausapin ang IT admin.']));
}
