<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once '../api/config.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = $_POST['status'] ?? '';

if ($id > 0 && ($status === 'draft' || $status === 'published')) {
    try {
        $stmt = $pdo->prepare('UPDATE posts SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
}
?>
