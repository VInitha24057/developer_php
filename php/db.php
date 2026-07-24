<?php
/**
 * Database Connection Configuration
 *
 * This file establishes a PDO connection to the MySQL database
 * and exposes a reusable $pdo instance for the application.
 */

// Set headers to ensure JSON output and prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Database configuration
$host     = 'localhost';
$dbname   = 'internship';
$username = 'root';
$password = '';

// Ensure the PDO extension is available
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    // Create a new PDO connection with MySQL driver
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );
} catch (PDOException $e) {
    // Return a generic error message without exposing sensitive details
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}
