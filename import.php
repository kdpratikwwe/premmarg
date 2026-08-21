<?php
// Simple script to import database.sql automatically
$host = '127.0.0.1;port=3307';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
    
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo "<h1>Database Imported Successfully!</h1>";
    echo "<p>You can now safely delete this <code>import.php</code> file.</p>";
    echo "<a href='index.html'>Go to Home</a> | <a href='admin/index.php'>Go to Admin</a>";
} catch (Exception $e) {
    echo "<h1>Error Importing Database</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
