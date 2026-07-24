<?php
/**
 * Update Profile Endpoint
 *
 * Updates authenticated user profile details in MongoDB
 * using a Redis session token. Creates the profile if it
 * does not already exist.
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

// Read POST parameters and trim whitespace
$token   = trim($_POST['token'] ?? '');
$user_id = trim($_POST['user_id'] ?? '');
$age     = trim($_POST['age'] ?? '');
$dob     = trim($_POST['dob'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$address = trim($_POST['address'] ?? '');

// ------------------------------------------
// Input Validation
// ------------------------------------------

// Validate user_id is required
if ($user_id === '' || !filter_var($user_id, FILTER_VALIDATE_INT)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Valid user ID is required'
    ]);
    exit;
}

// Validate token is not empty
if ($token === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Token is required'
    ]);
    exit;
}

// Validate age is required and a positive integer
if ($age === '' || !filter_var($age, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Valid age is required'
    ]);
    exit;
}

// Validate date of birth is required
if ($dob === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Date of birth is required'
    ]);
    exit;
}

// Validate contact is required, contains only digits, and is 10-15 characters long
if ($contact === '' || !ctype_digit($contact) || strlen($contact) < 10 || strlen($contact) > 15) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Valid contact number is required'
    ]);
    exit;
}

// Validate address is required
if ($address === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Address is required'
    ]);
    exit;
}

// ------------------------------------------
// MySQL Profile Update
// ------------------------------------------

try {
    $sql = "UPDATE users SET age = :age, dob = :dob, contact = :contact, address = :address WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'age'     => $age,
        'dob'     => $dob,
        'contact' => $contact,
        'address' => $address,
        'id'      => $user_id
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Profile updated successfully'
    ]);

} catch (PDOException $e) {
    error_log('Profile update SQL error: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unable to update profile'
    ]);
    exit;
}
