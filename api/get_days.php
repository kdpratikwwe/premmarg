<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$saptah_id = isset($_GET['saptah_id']) ? (int)$_GET['saptah_id'] : 0;

try {
    if ($saptah_id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM days WHERE saptah_id = ? ORDER BY day_number ASC');
        $stmt->execute([$saptah_id]);
    } else {
        $stmt = $pdo->query('SELECT * FROM days ORDER BY saptah_id DESC, day_number ASC');
    }
    $days = $stmt->fetchAll();
    echo json_encode($days);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
