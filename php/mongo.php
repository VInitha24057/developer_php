<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {

    $uri = getenv('MONGO_URI');

    $client = new Client($uri);

    $database = $client->selectDatabase('internship');

    $profileCollection = $database->selectCollection('profiles');

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    exit;
}