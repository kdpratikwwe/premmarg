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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
        if ($postId <= 0) {
            echo json_encode([]);
            exit;
        }

        $stmt = $pdo->prepare('
            SELECT c.id, c.comment_text, c.created_at, u.name as user_name 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? AND c.status = "approved" 
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([$postId]);
        $comments = $stmt->fetchAll();

        echo json_encode($comments);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Please log in to share your reflections on this katha.', 'require_login' => true]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $data = getRequestData();
        $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
        $commentText = isset($data['comment_text']) ? trim($data['comment_text']) : '';

        if ($postId <= 0) {
            echo json_encode(['error' => 'Invalid post ID.']);
            exit;
        }

        if (mb_strlen($commentText) < 3) {
            echo json_encode(['error' => 'Your comment is too short. Please write at least 3 characters.']);
            exit;
        }

        if (mb_strlen($commentText) > 2000) {
            echo json_encode(['error' => 'Comment cannot exceed 2000 characters.']);
            exit;
        }

        // Verify post exists
        $stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Post not found.']);
            exit;
        }

        // Rate limiting check (e.g., max 1 comment per 30 seconds from same user)
        $stmtCheck = $pdo->prepare('SELECT created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
        $stmtCheck->execute([$userId]);
        $lastComment = $stmtCheck->fetch();
        if ($lastComment) {
            $timeSince = time() - strtotime($lastComment['created_at']);
            if ($timeSince < 15) {
                echo json_encode(['error' => 'Please wait a few seconds before posting another comment.']);
                exit;
            }
        }

        // Insert pending comment
        $stmtInsert = $pdo->prepare('INSERT INTO comments (post_id, user_id, comment_text, status) VALUES (?, ?, ?, "pending")');
        $stmtInsert->execute([$postId, $userId, $commentText]);

        echo json_encode([
            'success' => true,
            'message' => 'Your reflection (भाव) has been submitted and is pending review by admin. Radhe Radhe! 🙏'
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
