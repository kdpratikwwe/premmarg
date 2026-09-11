<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$msgType = 'success';

// Handle POST: Create or Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($name) || mb_strlen($name) < 2) {
        $msg = 'Please provide a valid devotee name (at least 2 characters).';
        $msgType = 'danger';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please provide a valid email address.';
        $msgType = 'danger';
    } else {
        // Check email uniqueness
        $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmtCheck->execute([$email, $id]);
        if ($stmtCheck->fetch()) {
            $msg = 'An account with this email address already exists.';
            $msgType = 'danger';
        } else {
            if ($id > 0) {
                // Update
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $msg = 'New password must be at least 6 characters long.';
                        $msgType = 'danger';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?');
                        $stmt->execute([$name, $email, $hash, $id]);
                        $msg = 'User account and password updated successfully! 🎉';
                        $action = 'list';
                    }
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $id]);
                    $msg = 'User profile details updated successfully! 🎉';
                    $action = 'list';
                }
            } else {
                // Insert new
                if (empty($password) || strlen($password) < 6) {
                    $msg = 'Please enter a password with at least 6 characters.';
                    $msgType = 'danger';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
                    $stmt->execute([$name, $email, $hash]);
                    $msg = 'New devotee account created successfully! 🎉';
                    $action = 'list';
                }
            }
        }
    }
}

// Handle Delete User
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $msg = 'User account has been deleted.';
    $msgType = 'danger';
    $action = 'list';
}

// Fetch user for edit or view
$user = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        $action = 'list';
        $msg = 'User not found.';
        $msgType = 'danger';
    }
}
?>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show shadow-sm" role="alert">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-users me-2"></i>Manage Devotees & Readers</h4>
        <p class="text-muted mb-0">View registered devotees, oversee their reflections, and manage reader accounts.</p>
    </div>
    <div>
        <?php if ($action === 'list'): ?>
            <a href="users.php?action=edit" class="btn btn-primary shadow-sm"><i class="fas fa-user-plus me-2"></i>Add New Devotee</a>
        <?php else: ?>
            <a href="users.php" class="btn btn-outline-secondary shadow-sm"><i class="fas fa-arrow-left me-2"></i>Back to Devotee List</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'list'): ?>
    <?php
    $totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $totalUserComments = (int)$pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
    $totalUserBookmarks = (int)$pdo->query('SELECT COUNT(*) FROM bookmarks')->fetchColumn();

    $search = trim($_GET['search'] ?? '');
    $whereSql = '';
    $params = [];
    if (!empty($search)) {
        $whereSql = 'WHERE name LIKE ? OR email LIKE ?';
        $params = ["%{$search}%", "%{$search}%"];
    }

    $stmtUsers = $pdo->prepare("SELECT * FROM users {$whereSql} ORDER BY created_at DESC");
    $stmtUsers->execute($params);
    $usersList = $stmtUsers->fetchAll();
    ?>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold">Registered Devotees</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $totalUsers ?></h3>
                    </div>
                    <div class="opacity-25 fs-1"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold">Total Reflections</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $totalUserComments ?></h3>
                    </div>
                    <div class="opacity-25 fs-1"><i class="fas fa-comments"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-dark-50 small text-uppercase fw-semibold">Total Bookmarks</span>
                        <h3 class="fw-bold mb-0 mt-1"><?= $totalUserBookmarks ?></h3>
                    </div>
                    <div class="opacity-25 fs-1"><i class="fas fa-bookmark"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search bar & Users Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-body-secondary py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Devotee Directory</h5>
            <form method="GET" class="d-flex gap-2 flex-grow-1 flex-md-grow-0" style="max-width: 320px; width: 100%;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
                    <?php if (!empty($search)): ?>
                        <a href="users.php" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 70px;">ID</th>
                            <th>Devotee Name</th>
                            <th>Email Address</th>
                            <th>Joined Date</th>
                            <th class="text-center">Reflections</th>
                            <th class="text-center">Bookmarks</th>
                            <th class="text-end pe-4" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usersList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fs-2 mb-2 d-block opacity-50"></i>
                                    No devotee accounts found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usersList as $u): ?>
                                <?php
                                $commentsCount = (int)$pdo->query("SELECT COUNT(*) FROM comments WHERE user_id = {$u['id']}")->fetchColumn();
                                $bookmarksCount = (int)$pdo->query("SELECT COUNT(*) FROM bookmarks WHERE user_id = {$u['id']}")->fetchColumn();
                                $initial = strtoupper(mb_substr($u['name'], 0, 1));
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?= $u['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-size: 0.9rem; background: rgba(13,110,253,0.1);">
                                                <?= $initial ?>
                                            </div>
                                            <span class="fw-semibold"><?= htmlspecialchars($u['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $commentsCount > 0 ? 'bg-success' : 'bg-light text-muted border' ?> rounded-pill px-2.5">
                                            <?= $commentsCount ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $bookmarksCount > 0 ? 'bg-warning text-dark' : 'bg-light text-muted border' ?> rounded-pill px-2.5">
                                            <?= $bookmarksCount ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <a href="users.php?action=view&id=<?= $u['id'] ?>" class="btn btn-outline-info" title="View Activity">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="users.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-outline-primary" title="Edit Devotee">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="users.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-outline-danger" title="Delete Account" onclick="return confirm('Are you sure you want to delete devotee account <?= htmlspecialchars(addslashes($u['name'])) ?>? This will also remove their comments and bookmarks.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action === 'edit'): ?>
    <!-- Create or Edit User Form -->
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-body-secondary py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas <?= $id ? 'fa-user-edit text-primary' : 'fa-user-plus text-success' ?> me-2"></i>
                        <?= $id ? 'Edit Devotee Account' : 'Create New Devotee Account' ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Devotee Full Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="e.g. Radheshyam Sharma">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="devotee@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <?= $id ? 'New Password (Optional)' : 'Password' ?>
                            </label>
                            <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?> minlength="6" placeholder="<?= $id ? 'Leave blank to keep existing password' : 'At least 6 characters' ?>">
                            <?php if ($id): ?>
                                <div class="form-text text-muted">Only fill this if you want to reset or change the user's password.</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                            <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i><?= $id ? 'Save Changes' : 'Create Account' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view' && $user): ?>
    <?php
    // Fetch comments by user
    $stmtComments = $pdo->prepare('
        SELECT c.*, p.title as post_title, p.slug as post_slug 
        FROM comments c 
        JOIN posts p ON c.post_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ');
    $stmtComments->execute([$user['id']]);
    $userComments = $stmtComments->fetchAll();

    // Fetch bookmarks by user
    $stmtBookmarks = $pdo->prepare('
        SELECT b.created_at as bookmarked_at, p.id, p.title, p.slug, p.event_location, p.post_type 
        FROM bookmarks b 
        JOIN posts p ON b.post_id = p.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ');
    $stmtBookmarks->execute([$user['id']]);
    $userBookmarks = $stmtBookmarks->fetchAll();

    $initial = strtoupper(mb_substr($user['name'], 0, 1));
    ?>

    <!-- Devotee Header Info -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <?= $initial ?>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold"><?= htmlspecialchars($user['name']) ?></h4>
                        <div class="text-muted small">
                            <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
                            <span class="mx-2">·</span>
                            <i class="fas fa-calendar-alt me-1"></i>Joined <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-pencil-alt me-1"></i> Edit Account
                    </a>
                    <a href="users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Delete this user account?')">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Devotee's Submitted Comments -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-body-secondary py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-comments me-2 text-primary"></i>Devotional Reflections (<?= count($userComments) ?>)
                    </h5>
                    <a href="comments.php?search=<?= urlencode($user['email']) ?>" class="btn btn-link text-decoration-none p-0 text-sm fw-semibold">
                        View in Moderation
                    </a>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($userComments)): ?>
                        <div class="text-center py-4 text-muted">
                            <p class="mb-0">This devotee has not submitted any comments yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($userComments as $c): ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <a href="../post.html?slug=<?= htmlspecialchars($c['post_slug']) ?>" target="_blank" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($c['post_title']) ?> <i class="fas fa-external-link-alt small ms-1"></i>
                                        </a>
                                        <?php if ($c['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-2 bg-light rounded text-dark small mb-2" style="border-left: 3px solid #ffc107;">
                                        <?= nl2br(htmlspecialchars($c['comment_text'])) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small"><?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></span>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($c['status'] === 'pending'): ?>
                                                <a href="comments.php?action=approve&id=<?= $c['id'] ?>" class="btn btn-outline-success btn-sm py-0">Approve</a>
                                            <?php else: ?>
                                                <a href="comments.php?action=pending&id=<?= $c['id'] ?>" class="btn btn-outline-warning btn-sm py-0">Move to Pending</a>
                                            <?php endif; ?>
                                            <a href="comments.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-sm py-0" onclick="return confirm('Delete this comment?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Devotee's Saved Bookmarks -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-body-secondary py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-bookmark me-2 text-warning"></i>Saved Summaries (<?= count($userBookmarks) ?>)
                    </h5>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($userBookmarks)): ?>
                        <div class="text-center py-4 text-muted">
                            <p class="mb-0">This devotee has not bookmarked any summaries yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($userBookmarks as $b): ?>
                                <div class="list-group-item px-0 py-2.5 border-bottom d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="../post.html?slug=<?= htmlspecialchars($b['slug']) ?>" target="_blank" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($b['title']) ?> <i class="fas fa-external-link-alt small ms-1"></i>
                                        </a>
                                        <div class="text-muted small">Saved <?= date('M d, Y', strtotime($b['bookmarked_at'])) ?></div>
                                    </div>
                                    <span class="badge bg-light text-secondary border">
                                        <?= $b['post_type'] === 'lecture' ? 'Pravachan' : 'Katha Day' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
