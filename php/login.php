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

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Valid email is required'
    ]);

    exit;
}

if ($password === '') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Password is required'
    ]);

    exit;
}

try {

    $sql = "SELECT id, name, email, password
            FROM users
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch();

    if (!$user) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password'
        ]);

        exit;
    }

    if (!password_verify($password, $user['password'])) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password'
        ]);

        exit;
    }

    $token = bin2hex(random_bytes(32));

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    exit;
}