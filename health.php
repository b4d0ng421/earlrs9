<?php
/**
 * Lightweight health check for Selenium IDE and manual troubleshooting.
 * Returns HTTP 200 + a JSON body when PHP can reach MySQL, or HTTP 503.
 */
define('RS8_HEALTH_CHECK', true);
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo->query('SELECT 1');
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
