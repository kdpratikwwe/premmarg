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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Premmarg</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">
</head>
<body class="login-page bg-body-secondary d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="login-box shadow-lg rounded-3 bg-white" style="width: 360px;">
        <div class="card border-0 p-3">
            <div class="card-header border-0 text-center bg-white pt-4">
                <a href="../index.html" class="h1 text-decoration-none fw-bold">
                    <i class="fas fa-feather-alt text-primary me-2"></i><b>Premmarg</b>
                </a>
                <p class="text-muted mt-2 mb-0">Sign in to start your admin session</p>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Username</label>
                        <div class="input-group">
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
