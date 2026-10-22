<?php
/**
 * Database connection.
 * Update these four values to match your hosting environment
 * (cPanel/phpMyAdmin will show you the DB name, user, and password
 * once you create the database there).
 */
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_NAME', 'if0_43240676_rs8');
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
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
            PDO::ATTR_TIMEOUT            => 5,     // don't hang if MySQL is stopped
        ]
    );
} catch (PDOException $e) {
    // Keep the details in the server log, show visitors a generic message.
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
