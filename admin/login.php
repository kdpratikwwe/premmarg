<?php
session_start();
require_once '../api/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_user'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Premmarg Blog</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-box">
            <h2>Premmarg Admin</h2>
            <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Username (admin)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password (password)</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width:100%">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
