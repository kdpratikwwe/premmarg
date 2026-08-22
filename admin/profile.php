<?php
require_once 'header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch user details
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password_hash'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
                $update->execute([$new_hash, $_SESSION['admin_id']]);
                $message = 'Password updated successfully!';
            } else {
                $error = 'New password must be at least 6 characters long.';
            }
        } else {
            $error = 'New passwords do not match.';
        }
    } else {
        $error = 'Incorrect current password.';
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">
                <i class="fas fa-user-cog text-primary me-2"></i>Admin Profile Settings
            </h5>
            
            <?php if($message): ?>
                <div class="alert alert-success py-2" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($_SESSION['admin_user']) ?>" readonly disabled>
                    <small class="text-muted">Username cannot be changed.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-key me-2"></i>Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
