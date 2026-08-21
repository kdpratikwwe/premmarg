<?php
// Database connection configuration
$host = '127.0.0.1;port=3307';
$dbname = 'premmarg_blog';
$username = 'root'; // WAMP default
$password = '';     // WAMP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // If DB doesn't exist, PDO throws an exception. We'll handle it in the scripts if needed, 
    // but typically config just fails. We return JSON error for API.
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
?>
