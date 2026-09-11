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

// Helper to get input data (supports JSON and form-urlencoded)
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
    switch ($action) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }

            $data = getRequestData();
            $name = isset($data['name']) ? trim($data['name']) : '';
            $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
            $password = isset($data['password']) ? $data['password'] : '';

            if (empty($name) || mb_strlen($name) < 2) {
                echo json_encode(['error' => 'Please enter a valid name (at least 2 characters).']);
                exit;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['error' => 'Please enter a valid email address.']);
                exit;
            }

            if (empty($password) || strlen($password) < 6) {
                echo json_encode(['error' => 'Password must be at least 6 characters long.']);
                exit;
            }

            // Check existing user
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'An account with this email already exists. Please log in.']);
                exit;
            }

            // Hash password and save
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $insert->execute([$name, $email, $hash]);
            $userId = (int)$pdo->lastInsertId();

            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            echo json_encode([
                'success' => true,
                'message' => 'Account created successfully! Welcome to Premmarg.',
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email
                ]
            ]);
            break;

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }

            $data = getRequestData();
            $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
            $password = isset($data['password']) ? $data['password'] : '';

            if (empty($email) || empty($password)) {
                echo json_encode(['error' => 'Please enter both email and password.']);
                exit;
            }

            $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                echo json_encode(['error' => 'Invalid email or password. Please try again.']);
                exit;
            }

            // Valid user
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            echo json_encode([
                'success' => true,
                'message' => 'Logged in successfully.',
                'user' => [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);
            break;

        case 'logout':
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_email']);

            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
            break;

        case 'status':
        default:
            if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                echo json_encode([
                    'logged_in' => true,
                    'user' => [
                        'id' => (int)$_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email']
                    ]
                ]);
            } else {
                echo json_encode([
                    'logged_in' => false,
                    'user' => null
                ]);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
