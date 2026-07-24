<?php
/**
 * MongoDB Connection Configuration
 *
 * Establishes a connection to MongoDB and exposes
 * a reusable $profileCollection instance for the application.
 */

// Set JSON response header
header('Content-Type: application/json');

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;

try {
    // ------------------------------------------
    // MongoDB Connection
    // ------------------------------------------

    // Define the MongoDB connection URI
    $uri = 'mongodb://127.0.0.1:27017';

    // Create the MongoDB client instance
    $client = new Client($uri);

    // Select the internship database
    $database = $client->selectDatabase('internship');

    // Select the profiles collection and expose it globally
    $profileCollection = $database->selectCollection('profiles');

} catch (Exception $e) {
    // Return a generic error message without exposing internal details
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'MongoDB connection failed'
    ]);
    exit;
}
