<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');


if(!$host || !$dbname || !$username || !$password){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing database environment variables"
    ]);

    exit;
}


try {

    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch(PDOException $e){

    error_log("MYSQL ERROR: ".$e->getMessage());

    echo json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ]);

    exit;
}