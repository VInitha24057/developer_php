<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mongo.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($name === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Name is required'
    ]);

    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Valid email is required'
    ]);

    exit;
}

if ($password === '' || strlen($password) < 6) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must be at least 6 characters'
    ]);

    exit;
}

try {

    $checkSql = "SELECT id FROM users WHERE email = :email LIMIT 1";

    $checkStmt = $pdo->prepare($checkSql);

    $checkStmt->execute([
        'email' => $email
    ]);

    if ($checkStmt->rowCount() > 0) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Email already registered'
        ]);

        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertSql = "INSERT INTO users (name, email, password)
                  VALUES (:name, :email, :password)";

    $insertStmt = $pdo->prepare($insertSql);

    $insertStmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword
    ]);

    $profileCollection->insertOne([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful'
    ]);

} catch (Exception $e) {

    error_log("REGISTER ERROR: ".$e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    exit;
}