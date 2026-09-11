<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

function getRequestData() {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $json = json_decode($input, true);
        if (is_array($json)) {
            return $json;
        }
    }
    return $_POST;
}

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

try {
    // 1. Check if specific post is bookmarked
    if ($action === 'check') {
        $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
        if (!isset($_SESSION['user_id']) || $postId <= 0) {
            echo json_encode(['bookmarked' => false]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1');
        $stmt->execute([$userId, $postId]);
        $exists = (bool)$stmt->fetch();

        echo json_encode(['bookmarked' => $exists]);
        exit;
    }

    // 2. Toggle bookmark
    if ($action === 'toggle') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Please log in to save summaries to your bookmarks.', 'require_login' => true]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $data = getRequestData();
        $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;

        if ($postId <= 0) {
            echo json_encode(['error' => 'Invalid post ID.']);
            exit;
        }

        // Check if currently bookmarked
        $stmt = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1');
        $stmt->execute([$userId, $postId]);
        $bookmark = $stmt->fetch();

        if ($bookmark) {
            // Remove
            $del = $pdo->prepare('DELETE FROM bookmarks WHERE id = ?');
            $del->execute([$bookmark['id']]);
            echo json_encode([
                'success' => true,
                'bookmarked' => false,
                'message' => 'Removed from your bookmarks.'
            ]);
        } else {
            // Add
            $ins = $pdo->prepare('INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)');
            $ins->execute([$userId, $postId]);
            echo json_encode([
                'success' => true,
                'bookmarked' => true,
                'message' => 'Saved to your bookmarks! 🔖'
            ]);
        }
        exit;
    }

    // 3. List bookmarked posts of logged in user
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Please log in to view your bookmarks.', 'require_login' => true]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare('
            SELECT b.id AS bookmark_id, b.created_at AS bookmarked_at, p.* 
            FROM bookmarks b 
            JOIN posts p ON b.post_id = p.id 
            WHERE b.user_id = ? AND p.status = "published" 
            ORDER BY b.created_at DESC
        ');
        $stmt->execute([$userId]);
        $bookmarks = $stmt->fetchAll();

        echo json_encode($bookmarks);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
