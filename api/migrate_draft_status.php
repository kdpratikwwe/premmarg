<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    // Add status column to posts table
    $sql = "ALTER TABLE posts ADD COLUMN status VARCHAR(20) DEFAULT 'published' AFTER featured;";
    $pdo->exec($sql);
    echo "<h3>Success: Added 'status' column to 'posts' table.</h3>";
    echo "<br><a href='../admin/posts.php'>Go to Manage Posts</a>";
} catch (PDOException $e) {
    // Check if column already exists (SQLSTATE 42S21 is duplicate column)
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h3>Success: 'status' column already exists in 'posts' table.</h3>";
        echo "<br><a href='../admin/posts.php'>Go to Manage Posts</a>";
    } else {
        die("Database migration error: " . $e->getMessage());
    }
}
