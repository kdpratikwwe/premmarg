<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    echo "<h2>Running Database Migration: YouTube Video Support...</h2>";

    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN youtube_url VARCHAR(255) NULL AFTER meta_description;");
        echo "<p>✅ Added 'youtube_url' column to 'posts' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'youtube_url' column already exists in 'posts' table.</p>";
        } else {
            throw $e;
        }
    }

    echo "<h3>🎉 YouTube URL Migration Completed Successfully!</h3>";
    echo "<br><a href='../admin/posts.php'>Return to Manage Posts</a>";

} catch (PDOException $e) {
    die("<div style='color:red;'><h3>Migration Error:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}
?>
