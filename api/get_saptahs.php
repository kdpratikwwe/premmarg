<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $stmt = $pdo->query('SELECT * FROM saptah ORDER BY year DESC, created_at DESC');
    $saptahs = $stmt->fetchAll();
    echo json_encode($saptahs);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
