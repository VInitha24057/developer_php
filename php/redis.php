<?php
/**
 * Redis Connection Configuration
 *
 * Establishes a connection to Redis and exposes
 * a reusable $redis instance for the application.
 */

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if Redis extension is loaded
    if (!extension_loaded('redis')) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Redis connection failed'
        ]);
        exit;
    }

    // Create Redis instance and connect to localhost on default port
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

} catch (RedisException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Redis connection failed'
    ]);
    exit;
}
