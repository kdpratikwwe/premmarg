<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Fetch all available Kathas (Saptahs)
$saptahs = $pdo->query('SELECT id, title, title_hi, year, katha_type, total_days FROM saptah ORDER BY year DESC, id DESC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saptah_id = (int)$_POST['saptah_id'];
    $day_number = (int)$_POST['day_number'];
    $title = trim($_POST['title']);
    $title_hi = trim($_POST['title_hi']);

    if ($id) {
        $stmt = $pdo->prepare('UPDATE days SET saptah_id=?, day_number=?, title=?, title_hi=? WHERE id=?');
        $stmt->execute([$saptah_id, $day_number, $title, $title_hi, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO days (saptah_id, day_number, title, title_hi) VALUES (?, ?, ?, ?)');
        $stmt->execute([$saptah_id, $day_number, $title, $title_hi]);
    }
    echo "<script>window.location='days.php?saptah_id={$saptah_id}';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    $stmt = $pdo->prepare('SELECT saptah_id FROM days WHERE id=?');
    $stmt->execute([$id]);
    $deletedDay = $stmt->fetch();
    $redirSaptah = $deletedDay ? $deletedDay['saptah_id'] : '';

    $pdo->prepare('DELETE FROM days WHERE id=?')->execute([$id]);
    $redirUrl = $redirSaptah ? "days.php?saptah_id={$redirSaptah}" : 'days.php';
    echo "<script>window.location='{$redirUrl}';</script>";
    exit;
}

// Resolve selected Katha filter
$selected_saptah_id = null;
if (isset($_GET['saptah_id'])) {
    $selected_saptah_id = $_GET['saptah_id'] === 'all' ? 'all' : (int)$_GET['saptah_id'];
} else {
    // Default to the first (latest) Katha
    $selected_saptah_id = !empty($saptahs) ? $saptahs[0]['id'] : 'all';
}

$current_saptah = null;
if ($selected_saptah_id !== 'all') {
    foreach ($saptahs as $s) {
        if ($s['id'] == $selected_saptah_id) {
            $current_saptah = $s;
            break;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold">Manage Days</h4>
        <p class="text-muted small mb-0">Organize day-wise journey and summaries for each Katha.</p>
    </div>
    <?php if($action == 'list'): ?>
        <a href="?action=edit<?= ($selected_saptah_id !== 'all' ? '&saptah_id='.$selected_saptah_id : '') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Day
        </a>
    <?php else: ?>
        <a href="days.php<?= ($selected_saptah_id !== 'all' ? '?saptah_id='.$selected_saptah_id : '') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): 
    // Query days based on selected Katha filter
    if ($selected_saptah_id === 'all') {
        $stmt = $pdo->query('SELECT d.*, s.title as saptah_title, s.year as saptah_year,
                             (SELECT COUNT(*) FROM posts WHERE day_id = d.id) as post_count
                             FROM days d 
                             JOIN saptah s ON d.saptah_id = s.id 
                             ORDER BY s.year DESC, d.saptah_id DESC, d.day_number ASC');
    } else {
        $stmt = $pdo->prepare('SELECT d.*, s.title as saptah_title, s.year as saptah_year,
                              (SELECT COUNT(*) FROM posts WHERE day_id = d.id) as post_count
                              FROM days d 
                              JOIN saptah s ON d.saptah_id = s.id 
                              WHERE d.saptah_id = ? 
                              ORDER BY d.day_number ASC');
        $stmt->execute([$selected_saptah_id]);
    }
    $days_list = $stmt->fetchAll();
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-body-secondary py-3 border-0">
        <div class="row g-2 align-items-center">
            <div class="col-md-5 d-flex align-items-center gap-2">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Day List</h5>
                <?php if ($current_saptah): ?>
                    <span class="badge bg-primary px-2.5 py-1.5"><?= htmlspecialchars($current_saptah['title']) ?> (<?= $current_saptah['year'] ?>)</span>
                <?php else: ?>
                    <span class="badge bg-secondary px-2.5 py-1.5">All Kathas</span>
                <?php endif; ?>
            </div>
            <div class="col-md-7 d-flex justify-content-md-end align-items-center gap-2">
                <label for="filter_saptah" class="form-label mb-0 fw-semibold text-muted small text-nowrap">
                    <i class="fas fa-filter text-primary me-1"></i>Select Katha:
                </label>
                <select id="filter_saptah" class="form-select form-select-sm" style="max-width: 320px;" onchange="window.location.href='days.php?saptah_id=' + this.value">
                    <?php foreach ($saptahs as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $selected_saptah_id == $s['id'] ? 'selected' : '' ?>>
                            🪷 <?= htmlspecialchars($s['title']) ?> (<?= $s['year'] ?>)
                        </option>
                    <?php endforeach; ?>
                    <option value="all" <?= $selected_saptah_id === 'all' ? 'selected' : '' ?>>-- View All Kathas (सभी कथाएं) --</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">ID</th>
                        <th>Katha</th>
                        <th style="width: 130px;">Day Number</th>
                        <th>Title (English)</th>
                        <th>Title (Hindi)</th>
                        <th class="text-center" style="width: 140px;">Posts</th>
                        <th class="text-center pe-4" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($days_list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-3x text-secondary opacity-50 mb-3 d-block"></i>
                            <h6 class="fw-bold mb-1">No Days Created for this Katha</h6>
                            <p class="small mb-3">Add the 7 days of this Katha to publish daily summaries.</p>
                            <a href="?action=edit<?= ($selected_saptah_id !== 'all' ? '&saptah_id='.$selected_saptah_id : '') ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Day 1 for this Katha
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($days_list as $row): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($row['saptah_title']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['saptah_year']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary px-2.5 py-1.5 rounded-pill">
                                    <i class="fas fa-sun me-1"></i>Day <?= $row['day_number'] ?>
                                </span>
                            </td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['title']) ?></td>
                            <td><span class="font-hindi text-success fw-medium"><?= htmlspecialchars($row['title_hi']) ?></span></td>
                            <td class="text-center">
                                <a href="posts.php?saptah_id=<?= $row['saptah_id'] ?>" class="badge bg-light text-dark border text-decoration-none" title="View posts for this Katha">
                                    <i class="fas fa-file-alt text-primary me-1"></i><?= $row['post_count'] ?> post<?= $row['post_count'] == 1 ? '' : 's' ?>
                                </a>
                            </td>
                            <td class="text-center pe-4">
                                <a href="?action=edit&id=<?= $row['id'] ?>&saptah_id=<?= $row['saptah_id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit Day"><i class="fas fa-edit"></i></a>
                                <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this Day and all its posts?')" title="Delete Day"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($action == 'edit'): 
    $day = [
        'saptah_id' => $_GET['saptah_id'] ?? ($selected_saptah_id !== 'all' ? $selected_saptah_id : ''), 
        'day_number' => '', 
        'title' => '', 
        'title_hi' => ''
    ];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM days WHERE id=?');
        $stmt->execute([$id]);
        $day = $stmt->fetch() ?: $day;
    }

    // Auto-suggest next day number if creating a new day
    if (!$id && !empty($day['saptah_id'])) {
        $maxDayStmt = $pdo->prepare('SELECT MAX(day_number) as max_day FROM days WHERE saptah_id=?');
        $maxDayStmt->execute([$day['saptah_id']]);
        $maxRow = $maxDayStmt->fetch();
        $nextDay = (int)($maxRow['max_day'] ?? 0) + 1;
        $day['day_number'] = min(7, $nextDay);
    }
?>
<div class="card border-0 shadow-sm rounded-3 p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Day details' : 'Create New Day' ?>
    </h5>
    <form method="post">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label fw-semibold">Saptah / Katha</label>
                <select name="saptah_id" class="form-select" required onchange="updateSuggestedDay(this.value)">
                    <option value="">Select Saptah</option>
                    <?php foreach($saptahs as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id']==$day['saptah_id']?'selected':'' ?>>
                            🪷 <?= htmlspecialchars($s['title'] . ' (' . $s['year'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Day Number (1-7)</label>
                <input type="number" name="day_number" id="day_number_input" class="form-control" value="<?= htmlspecialchars($day['day_number']) ?>" min="1" max="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Title (English, e.g., Shrimad Bhagwat Mahatmya &amp; Varaha Avatar)</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($day['title']) ?>" placeholder="e.g. Day 1: Bhagwat Mahatmya" required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Title (Hindi, e.g., श्रीमद्भागवत माहात्म्य एवं वराह अवतार)</label>
            <input type="text" name="title_hi" class="form-control" value="<?= htmlspecialchars($day['title_hi']) ?>" placeholder="e.g. प्रथम दिवस: भागवत माहात्म्य" required>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="days.php<?= (!empty($day['saptah_id']) ? '?saptah_id='.$day['saptah_id'] : '') ?>" class="btn btn-secondary px-4 me-md-2">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Day</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
