<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    echo "<h2>Running Database Migration: User Role, Comments & Bookmarks...</h2>";

    // 1. Create `users` table
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sqlUsers);
    echo "<p>✅ 'users' table ensured.</p>";

    // 2. Create `comments` table
    $sqlComments = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post_status (post_id, status),
        INDEX idx_user (user_id),
        CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sqlComments);
    echo "<p>✅ 'comments' table ensured.</p>";

    // 3. Create `bookmarks` table
    $sqlBookmarks = "CREATE TABLE IF NOT EXISTS bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_post (user_id, post_id),
        CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_bookmarks_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sqlBookmarks);
    echo "<p>✅ 'bookmarks' table ensured.</p>";

    echo "<h3>🎉 User System Migration Completed Successfully!</h3>";
    echo "<br><a href='../admin/index.php'>Return to Admin Dashboard</a>";

} catch (PDOException $e) {
    die("<div style='color:red;'><h3>Migration Error:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}
?>
