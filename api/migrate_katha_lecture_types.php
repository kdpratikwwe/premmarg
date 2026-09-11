<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    echo "<h2>Running Database Migration: Katha Durations & Standalone Pravachans...</h2>";

    // 1. Update saptah table: add katha_type and total_days
    try {
        $pdo->exec("ALTER TABLE saptah ADD COLUMN katha_type VARCHAR(30) DEFAULT 'saptah' AFTER title_hi;");
        echo "<p>✅ Added 'katha_type' column to 'saptah' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'katha_type' already exists in 'saptah'.</p>";
        } else {
            throw $e;
        }
    }

    try {
        $pdo->exec("ALTER TABLE saptah ADD COLUMN total_days INT DEFAULT 7 AFTER katha_type;");
        echo "<p>✅ Added 'total_days' column to 'saptah' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'total_days' already exists in 'saptah'.</p>";
        } else {
            throw $e;
        }
    }

    // 2. Update posts table: modify day_id to be NULLABLE
    try {
        $pdo->exec("ALTER TABLE posts MODIFY day_id INT NULL DEFAULT NULL;");
        echo "<p>✅ Modified 'day_id' column in 'posts' table to be NULLABLE.</p>";
    } catch (PDOException $e) {
        echo "<p>⚠️ Notice on modifying 'day_id': " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    // 3. Update posts table: add post_type
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN post_type VARCHAR(30) DEFAULT 'katha_day' AFTER day_id;");
        echo "<p>✅ Added 'post_type' column to 'posts' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'post_type' already exists in 'posts'.</p>";
        } else {
            throw $e;
        }
    }

    // 4. Update posts table: add event_date
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN event_date DATE NULL AFTER post_type;");
        echo "<p>✅ Added 'event_date' column to 'posts' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'event_date' already exists in 'posts'.</p>";
        } else {
            throw $e;
        }
    }

    // 5. Update posts table: add event_location
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN event_location VARCHAR(100) NULL AFTER event_date;");
        echo "<p>✅ Added 'event_location' column to 'posts' table.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ 'event_location' already exists in 'posts'.</p>";
        } else {
            throw $e;
        }
    }

    echo "<h3>🎉 Migration Completed Successfully!</h3>";
    echo "<br><a href='../admin/index.php'>Return to Admin Dashboard</a>";

} catch (PDOException $e) {
    die("<div style='color:red;'><h3>Migration Error:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}
