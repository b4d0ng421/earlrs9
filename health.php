<?php
/**
 * Lightweight health check for SideReplay / Selenium IDE and manual troubleshooting.
 * Returns HTTP 200 + JSON when PHP can reach MySQL and every table exists,
 * otherwise HTTP 503 with a hint about what is missing.
 */
define('RS8_HEALTH_CHECK', true);
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$required = ['users', 'orders', 'order_items', 'contact_messages'];

try {
    $pdo->query('SELECT 1');

    $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $missing  = array_values(array_diff($required, $existing));

    if ($missing) {
        http_response_code(503);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Connected, but tables are missing. Import schema.sql in phpMyAdmin.',
            'missing' => $missing,
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'status'   => 'ok',
        'database' => DB_NAME,
    ]);
} catch (Throwable $e) {
    error_log('RS8 health check failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database unavailable.',
    ]);
}
