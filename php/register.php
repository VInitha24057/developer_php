<?php
/**
 * User Registration Endpoint
 *
 * Handles user registration requests via AJAX POST.
 * Validates input, checks for duplicate emails,
 * hashes the password securely, and stores the user.
 */

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

// Include reusable database configuration
require_once __DIR__ . '/db.php';

// Include MongoDB configuration
require_once __DIR__ . '/mongo.php';

// Read and sanitize input values from POST request
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// ------------------------------------------
// Input Validation
// ------------------------------------------

// Validate name field
if ($name === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Name is required'
    ]);
    exit;
}

// Validate email field
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Valid email is required'
    ]);
    exit;
}

// Validate password field
if ($password === '' || strlen($password) < 6) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Password must be at least 6 characters'
    ]);
    exit;
}

// ------------------------------------------
// Database Operations
// ------------------------------------------

try {
    // Check if the email is already registered using a prepared statement
    $checkSql  = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['email' => $email]);

    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Email already registered'
        ]);
        exit;
    }

    // Hash the password using PHP's default algorithm
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user using a prepared statement
    $insertSql  = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        'name'     => $name,
        'email'    => $email,
        'password' => $hashedPassword
    ]);

    $profileCollection->insertOne([
        'name'        => $name,
        'email'       => $email,
        'password'    => $hashedPassword,
        'created_at'  => new MongoDB\BSON\UTCDateTime()
    ]);

    // Return success response
    echo json_encode([
        'status'  => 'success',
        'message' => 'Registration successful'
    ]);
} catch (PDOException $e) {
    // Return a generic error message without exposing internal details
    echo json_encode([
        'status'  => 'error',
        'message' => 'Registration failed'
    ]);
    exit;
}
