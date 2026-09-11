<?php
require_once 'header.php';

// Flash message helper
$msg = '';
$msgType = 'success';

// Handle Actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    if ($action === 'approve') {
        $stmt = $pdo->prepare('UPDATE comments SET status = "approved" WHERE id = ?');
        $stmt->execute([$id]);
        $msg = 'Comment approved and published to the website! 🎉';
    } elseif ($action === 'pending') {
        $stmt = $pdo->prepare('UPDATE comments SET status = "pending" WHERE id = ?');
        $stmt->execute([$id]);
        $msg = 'Comment status set back to Pending Review.';
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $msg = 'Comment has been deleted.';
        $msgType = 'danger';
    }
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Counts
$pendingCount = (int)$pdo->query('SELECT COUNT(*) FROM comments WHERE status = "pending"')->fetchColumn();
$approvedCount = (int)$pdo->query('SELECT COUNT(*) FROM comments WHERE status = "approved"')->fetchColumn();
$totalCount = (int)$pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();

// Query builder
$where = [];
$params = [];

if ($filter === 'pending') {
    $where[] = 'c.status = "pending"';
} elseif ($filter === 'approved') {
    $where[] = 'c.status = "approved"';
}

if (!empty($search)) {
    $where[] = '(u.name LIKE ? OR u.email LIKE ? OR c.comment_text LIKE ? OR p.title LIKE ?)';
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name, u.email as user_email, p.title as post_title, p.slug as post_slug 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN posts p ON c.post_id = p.id 
    {$whereSql} 
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$comments = $stmt->fetchAll();
?>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show shadow-sm" role="alert">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-comments me-2"></i>Manage Devotional Comments</h4>
                <p class="text-muted mb-0">Review and moderate bhav & reflections shared by readers before they appear on the blog.</p>
            </div>
            <!-- Search -->
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search comments, devotee..." value="<?= htmlspecialchars($search) ?>">
                    <?php if (!empty($search)): ?>
                        <a href="comments.php?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabs for Filtering -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item me-2">
        <a class="nav-link <?= $filter === 'pending' ? 'active bg-warning text-dark fw-bold' : 'text-dark' ?>" href="comments.php?filter=pending<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
            <i class="fas fa-hourglass-half me-1"></i> Pending Approval 
            <span class="badge <?= $filter === 'pending' ? 'bg-dark text-white' : 'bg-warning text-dark' ?> ms-1"><?= $pendingCount ?></span>
        </a>
    </li>
    <li class="nav-item me-2">
        <a class="nav-link <?= $filter === 'approved' ? 'active bg-success fw-bold' : 'text-dark' ?>" href="comments.php?filter=approved<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
            <i class="fas fa-check-circle me-1"></i> Approved 
            <span class="badge <?= $filter === 'approved' ? 'bg-white text-success' : 'bg-success text-white' ?> ms-1"><?= $approvedCount ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'all' ? 'active bg-primary fw-bold' : 'text-dark' ?>" href="comments.php?filter=all<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
            <i class="fas fa-list me-1"></i> All Comments 
            <span class="badge <?= $filter === 'all' ? 'bg-white text-primary' : 'bg-secondary text-white' ?> ms-1"><?= $totalCount ?></span>
        </a>
    </li>
</ul>

<!-- Comments List -->
<?php if (empty($comments)): ?>
    <div class="card border-0 shadow-sm rounded-3 text-center p-5">
        <div class="text-muted mb-3" style="font-size: 3rem;"><i class="fas fa-comment-slash"></i></div>
        <h5 class="fw-bold">No comments found</h5>
        <p class="text-muted mb-0">
            <?php if ($filter === 'pending'): ?>
                Hooray! No pending comments awaiting moderation right now. 🙏
            <?php else: ?>
                No comments match your current filter criteria.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($comments as $c): ?>
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden <?= $c['status'] === 'pending' ? 'border-start border-warning border-4' : 'border-start border-success border-4' ?>">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; font-size: 1.1rem; background: rgba(13,110,253,0.1);">
                                    <?= strtoupper(mb_substr($c['user_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($c['user_name']) ?></h6>
                                    <span class="text-muted small"><?= htmlspecialchars($c['user_email']) ?> · <?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($c['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i> Pending Review</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> Approved</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Post Link -->
                        <div class="mb-2">
                            <span class="text-muted small">Post:</span>
                            <a href="../post.html?slug=<?= htmlspecialchars($c['post_slug']) ?>" target="_blank" class="fw-semibold text-decoration-none">
                                <?= htmlspecialchars($c['post_title']) ?> <i class="fas fa-external-link-alt small ms-1"></i>
                            </a>
                        </div>

                        <!-- Comment Text -->
                        <div class="p-3 bg-light rounded-2 text-dark mb-3" style="border-left: 3px solid #ffc107; font-size: 0.98rem; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($c['comment_text'])) ?>
                        </div>

                        <!-- Action buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($c['status'] === 'pending'): ?>
                                <a href="comments.php?action=approve&id=<?= $c['id'] ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-success btn-sm px-3 fw-semibold shadow-sm">
                                    <i class="fas fa-check me-1"></i> Approve Comment
                                </a>
                            <?php else: ?>
                                <a href="comments.php?action=pending&id=<?= $c['id'] ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-warning btn-sm px-3">
                                    <i class="fas fa-undo me-1"></i> Move to Pending
                                </a>
                            <?php endif; ?>

                            <a href="comments.php?action=delete&id=<?= $c['id'] ?>&filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Are you sure you want to permanently delete this comment?')">
                                <i class="fas fa-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
