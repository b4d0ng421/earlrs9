<?php
/**
 * RS8 Racing - InfinityFree database settings.
 *
 * Replace DB_HOST, DB_NAME and DB_PASS with the values shown in the
 * InfinityFree control panel (MySQL Databases page).
 * DB_PASS is your InfinityFree account password (vPanel password). The host is NOT localhost.
 * Omit any :3306 port suffix.
 */
define('DB_HOST', 'sqlXXX.infinityfree.com');   // e.g. sql309.infinityfree.com
define('DB_NAME', 'if0_43240676_rs8');          // full name incl. prefix
define('DB_USER', 'if0_43240676');
define('DB_PASS', 'YBaB8yL6BNz0xO');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10,
        ]
    );
} catch (PDOException $e) {
    error_log('RS8 database connection failed: ' . $e->getMessage());
    http_response_code(503);

    if (defined('RS8_HEALTH_CHECK')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database unavailable.',
        ]);
        exit;
    }

    die('Database connection failed. Please try again later.');
}