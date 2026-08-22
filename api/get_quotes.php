<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    // Select quotes that are either not scheduled or scheduled for today/past.
    // Order today's quote first, then other quotes by publish date or creation date.
    $stmt = $pdo->query("SELECT *, 
                               (CASE WHEN publish_date = CURDATE() THEN 1 ELSE 0 END) as is_today 
                        FROM quotes 
                        WHERE publish_date IS NULL OR publish_date <= CURDATE()
                        ORDER BY is_today DESC, COALESCE(publish_date, DATE(created_at)) DESC, id DESC");
    $quotes = $stmt->fetchAll();
    echo json_encode($quotes);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
