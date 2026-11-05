<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $stmt = $pdo->query('SELECT id, TRIM(title) AS title, TRIM(title_hi) AS title_hi, katha_type, total_days, TRIM(slug) AS slug, TRIM(location) AS location, year, description, description_hi, image_url, created_at FROM saptah ORDER BY year DESC, created_at DESC');
    $saptahs = $stmt->fetchAll();
    echo json_encode($saptahs);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
