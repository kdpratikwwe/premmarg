<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$day_id = isset($_GET['day_id']) ? (int)$_GET['day_id'] : 0;
$featured = isset($_GET['featured']) ? (int)$_GET['featured'] : -1;
$preview = isset($_GET['preview']) && $_GET['preview'] == 1;
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

$statusFilter = $preview ? "" : " AND status = 'published'";
$statusFilterWhere = $preview ? "" : " WHERE status = 'published'";

$typeFilter = "";
if (!empty($type)) {
    $typeFilter = " AND post_type = " . $pdo->quote($type);
}

try {
    if ($day_id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE day_id = ?' . $statusFilter . $typeFilter . ' ORDER BY created_at ASC');
        $stmt->execute([$day_id]);
    } else if ($featured === 1) {
        $stmt = $pdo->query('SELECT * FROM posts WHERE featured = 1' . $statusFilter . $typeFilter . ' ORDER BY created_at DESC');
    } else {
        if (!empty($type)) {
            $whereClause = ($preview ? " WHERE 1=1" : " WHERE status = 'published'") . $typeFilter;
            $stmt = $pdo->query('SELECT * FROM posts' . $whereClause . ' ORDER BY created_at DESC');
        } else {
            $stmt = $pdo->query('SELECT * FROM posts' . $statusFilterWhere . ' ORDER BY created_at DESC');
        }
    }
    
    $posts = $stmt->fetchAll();
    echo json_encode($posts);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
