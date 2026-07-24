<?php
/**
 * User Profile Endpoint
 *
 * Fetches authenticated user profile details from MySQL
 * using user_id submitted via POST.
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

// Include database configuration
require_once __DIR__ . '/db.php';

// Read and sanitize input values from POST request
$user_id = trim($_POST['user_id'] ?? '');
$token   = trim($_POST['token'] ?? '');

// Validate user_id is not empty
if ($user_id === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'User ID is required'
    ]);
    exit;
}

// Fetch user details using a prepared statement
try {
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $columnMap = array_flip($columns);

    $selectColumns = ['id', 'name', 'email'];
    foreach (['age', 'dob', 'contact', 'address'] as $col) {
        if (isset($columnMap[$col])) {
            $selectColumns[] = $col;
        }
    }

    $sql = "SELECT " . implode(', ', $selectColumns) . " FROM users WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);

    $user = $stmt->fetch();

    // If user does not exist
    if (!$user) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'User not found'
        ]);
        exit;
    }

    // Return success response
    echo json_encode([
        'status'  => 'success',
        'data'    => [
            'id'      => $user['id'],
            'name'    => $user['name'] ?? '',
            'email'   => $user['email'] ?? '',
            'age'     => $user['age'] ?? '',
            'dob'     => $user['dob'] ?? '',
            'contact' => $user['contact'] ?? '',
            'address' => $user['address'] ?? ''
        ]
    ]);

} catch (PDOException $e) {
    error_log('Profile fetch SQL error: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to fetch profile'
    ]);
    exit;
}
