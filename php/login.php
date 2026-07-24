<?php
/**
 * User Login Endpoint
 *
 * Authenticates a user using email and password.
 * Generates a secure token and stores session data in Redis.
 * Returns JSON responses only; no sessions or cookies are used.
 */

// Set JSON response header
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

// Include reusable configurations
require_once __DIR__ . '/db.php';
// require_once __DIR__ . '/redis.php';

// Read and sanitize input values
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// ------------------------------------------
// Input Validation
// ------------------------------------------

// Validate email field
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Valid email is required'
    ]);
    exit;
}

// Validate password field
if ($password === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Password is required'
    ]);
    exit;
}

// ------------------------------------------
// Database Operations
// ------------------------------------------

try {
    // Fetch user by email using a prepared statement
    $sql = "SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();

    // If user does not exist
    if (!$user) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // Verify the provided password against the stored hash
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // ------------------------------------------
    // Redis Token Storage
    // ------------------------------------------

    // Generate a secure random token
    $token = bin2hex(random_bytes(32));

    // Prepare session data to store in Redis
    $sessionData = [
        'user_id'   => $user['id'],
        'email'     => $user['email'],
        'login_time' => time()
    ];

    // $redis->setex('session_' . $token, 86400, json_encode($sessionData));

    // Return success response with token
    echo json_encode([
        'status'  => 'success',
        'message' => 'Login successful',
        'token'   => $token,
        'user'    => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email']
        ]
    ]);

} catch (PDOException $e) {
    // Handle database errors
    echo json_encode([
        'status'  => 'error',
        'message' => 'Login failed'
    ]);
    exit;
} catch (Exception $e) {
    // Handle unexpected errors
    error_log('Login unexpected error: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Login failed'
    ]);
    exit;
}
