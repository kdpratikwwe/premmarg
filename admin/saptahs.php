<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $title_hi = $_POST['title_hi'];
    $katha_type = $_POST['katha_type'] ?? 'saptah';
    $total_days = (int)($_POST['total_days'] ?? 7);
    if ($katha_type === 'saptah') $total_days = 7;
    elseif ($katha_type === 'tridivasiya') $total_days = 3;

    $slug = $_POST['slug'];
    $location = $_POST['location'];
    $year = $_POST['year'];
    $description = $_POST['description'];
    $description_hi = $_POST['description_hi'];

    if ($id) {
        $stmt = $pdo->prepare('UPDATE saptah SET title=?, title_hi=?, katha_type=?, total_days=?, slug=?, location=?, year=?, description=?, description_hi=? WHERE id=?');
        $stmt->execute([$title, $title_hi, $katha_type, $total_days, $slug, $location, $year, $description, $description_hi, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO saptah (title, title_hi, katha_type, total_days, slug, location, year, description, description_hi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $title_hi, $katha_type, $total_days, $slug, $location, $year, $description, $description_hi]);
    }
    echo "<script>window.location='saptahs.php';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    $pdo->prepare('DELETE FROM saptah WHERE id=?')->execute([$id]);
    echo "<script>window.location='saptahs.php';</script>";
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Manage Kathas</h4>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Katha</a>
    <?php else: ?>
        <a href="saptahs.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-body-secondary py-3 border-0">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-book-open me-2 text-primary"></i>Katha List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Title</th>
                        <th>Title (Hindi)</th>
                        <th>Duration / Type</th>
                        <th>Year</th>
                        <th>Location</th>
                        <th class="text-center pe-4" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query('SELECT * FROM saptah ORDER BY year DESC, id DESC');
                    while ($row = $stmt->fetch()):
                        $type = $row['katha_type'] ?? 'saptah';
                        $daysCount = $row['total_days'] ?? 7;
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><span class="font-hindi text-success"><?= htmlspecialchars($row['title_hi']) ?></span></td>
                        <td>
                            <?php if ($type === 'tridivasiya' || $daysCount == 3): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-calendar-day me-1"></i>3-Day Katha (त्रिदिवसीय)</span>
                            <?php elseif ($type === 'saptah' || $daysCount == 7): ?>
                                <span class="badge bg-primary"><i class="fas fa-calendar-week me-1"></i>7-Day Saptah (सप्ताह)</span>
                            <?php else: ?>
                                <span class="badge bg-info"><i class="fas fa-calendar-alt me-1"></i><?= $daysCount ?> Days Series</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($row['location']) ?></td>
                        <td class="text-center pe-4">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit Katha"><i class="fas fa-edit"></i></a>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this Katha and all its days/posts?')" title="Delete Katha"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($action == 'edit'): 
    $saptah = ['title'=>'', 'title_hi'=>'', 'katha_type'=>'saptah', 'total_days'=>7, 'slug'=>'', 'location'=>'', 'year'=>date('Y'), 'description'=>'', 'description_hi'=>''];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM saptah WHERE id=?');
        $stmt->execute([$id]);
        $saptah = $stmt->fetch() ?: $saptah;
    }
?>
<div class="card border-0 shadow-sm rounded-3 p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Katha Details' : 'Create New Katha' ?>
    </h5>
    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Title (English)</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($saptah['title']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Title (Hindi)</label>
                <input type="text" name="title_hi" class="form-control" value="<?= htmlspecialchars($saptah['title_hi']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Katha Duration / Type</label>
                <select name="katha_type" id="katha_type" class="form-select" required onchange="handleKathaTypeChange(this.value)">
                    <option value="saptah" <?= ($saptah['katha_type'] ?? 'saptah') === 'saptah' ? 'selected' : '' ?>>7-Day Saptah (सात दिवसीय सप्ताह)</option>
                    <option value="tridivasiya" <?= ($saptah['katha_type'] ?? '') === 'tridivasiya' ? 'selected' : '' ?>>3-Day Katha (त्रिदिवसीय कथा)</option>
                    <option value="custom" <?= ($saptah['katha_type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Duration (अन्य अवधि)</option>
                </select>
            </div>
            <div class="col-md-6" id="total_days_wrap" style="<?= ($saptah['katha_type'] ?? 'saptah') === 'custom' ? '' : 'display:none;' ?>">
                <label class="form-label fw-semibold">Total Days (कुल दिन)</label>
                <input type="number" name="total_days" id="total_days" class="form-control" min="1" max="30" value="<?= htmlspecialchars($saptah['total_days'] ?? 7) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Slug (URL friendly, e.g., my-katha-2026)</label>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($saptah['slug']) ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($saptah['location']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Year</label>
                <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($saptah['year']) ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description (English)</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($saptah['description']) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Description (Hindi)</label>
            <textarea name="description_hi" class="form-control" rows="4"><?= htmlspecialchars($saptah['description_hi']) ?></textarea>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Katha</button>
        </div>
    </form>
</div>

<script>
function handleKathaTypeChange(type) {
    const wrap = document.getElementById('total_days_wrap');
    const input = document.getElementById('total_days');
    if (type === 'custom') {
        wrap.style.display = 'block';
    } else if (type === 'tridivasiya') {
        wrap.style.display = 'none';
        input.value = 3;
    } else {
        wrap.style.display = 'none';
        input.value = 7;
    }
}
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
