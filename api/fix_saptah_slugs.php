<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    // 1. Trim whitespace from saptah table
    $stmt1 = $pdo->exec("UPDATE saptah SET slug = TRIM(slug), title = TRIM(title), location = TRIM(location)");
    
    // 2. Trim whitespace from posts table
    $stmt2 = $pdo->exec("UPDATE posts SET slug = TRIM(slug), title = TRIM(title)");

    // 3. Trim whitespace from days table
    $stmt3 = $pdo->exec("UPDATE days SET title = TRIM(title)");

    // Fetch updated saptahs
    $saptahs = $pdo->query("SELECT id, title, slug FROM saptah")->fetchAll();

    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully trimmed all slugs and titles in saptah, posts, and days tables.',
        'updated_saptahs' => $saptahs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
