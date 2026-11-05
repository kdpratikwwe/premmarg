<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    echo "<h2>Running Database Migration: Katha Notes / Devotee Diary System...</h2>";

    $sqlNotes = "CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        content MEDIUMTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_note_post (user_id, post_id),
        CONSTRAINT fk_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_notes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlNotes);
    echo "<p>✅ 'notes' table created/ensured successfully.</p>";

    echo "<h3>🎉 Katha Notes Migration Completed Successfully!</h3>";
    echo "<br><a href='../admin/index.php'>Return to Admin Dashboard</a>";

} catch (PDOException $e) {
    die("<div style='color:red;'><h3>Migration Error:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}
?>
