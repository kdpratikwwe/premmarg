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
    // 1. Get note for a specific post
    if ($action === 'get') {
        $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $postId <= 0) {
            echo json_encode(['logged_in' => false, 'note' => null]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT id, post_id, content, created_at, updated_at FROM notes WHERE user_id = ? AND post_id = ? LIMIT 1');
        $stmt->execute([$userId, $postId]);
        $note = $stmt->fetch();

        echo json_encode([
            'logged_in' => true,
            'note' => $note ? $note : null
        ]);
        exit;
    }

    // 2. Get all notes for the logged-in devotee
    if ($action === 'list') {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Please log in to view your saved Katha notes.', 'require_login' => true]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare('
            SELECT n.id, n.post_id, n.content, n.created_at, n.updated_at,
                   p.title as post_title, p.title_hi as post_title_hi, p.slug as post_slug,
                   p.post_type, p.event_date, p.event_location, p.youtube_url,
                   d.day_number, d.title as day_title, d.title_hi as day_title_hi,
                   s.title as saptah_title, s.title_hi as saptah_title_hi, s.year as saptah_year
            FROM notes n
            JOIN posts p ON n.post_id = p.id
            LEFT JOIN days d ON p.day_id = d.id
            LEFT JOIN saptah s ON d.saptah_id = s.id
            WHERE n.user_id = ?
            ORDER BY n.updated_at DESC
        ');
        $stmt->execute([$userId]);
        $notes = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'notes' => $notes
        ]);
        exit;
    }

    // 3. Save or update note for a post
    if ($action === 'save') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Please log in to save your notes to your account.', 'require_login' => true]);
            exit;
        }

        $data = getRequestData();
        $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
        $content = isset($data['content']) ? trim($data['content']) : '';
        $userId = (int)$_SESSION['user_id'];

        if ($postId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid post ID']);
            exit;
        }

        if ($content === '') {
            // Delete if emptied
            $stmt = $pdo->prepare('DELETE FROM notes WHERE user_id = ? AND post_id = ?');
            $stmt->execute([$userId, $postId]);
            echo json_encode(['success' => true, 'action' => 'deleted', 'message' => 'Empty note cleared']);
            exit;
        }

        // Upsert note
        $stmt = $pdo->prepare('
            INSERT INTO notes (user_id, post_id, content) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([$userId, $postId, $content]);

        echo json_encode([
            'success' => true,
            'action' => 'saved',
            'message' => 'Note saved successfully to your spiritual diary 🙏'
        ]);
        exit;
    }

    // 4. Delete note
    if ($action === 'delete') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $data = getRequestData();
        $userId = (int)$_SESSION['user_id'];
        $postId = isset($data['post_id']) ? (int)$data['post_id'] : (isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0);
        $noteId = isset($data['id']) ? (int)$data['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        if ($postId > 0) {
            $stmt = $pdo->prepare('DELETE FROM notes WHERE user_id = ? AND post_id = ?');
            $stmt->execute([$userId, $postId]);
        } elseif ($noteId > 0) {
            $stmt = $pdo->prepare('DELETE FROM notes WHERE user_id = ? AND id = ?');
            $stmt->execute([$userId, $noteId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing note or post ID']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Note removed']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
