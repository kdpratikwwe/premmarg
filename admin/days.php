<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saptah_id = $_POST['saptah_id'];
    $day_number = $_POST['day_number'];
    $title = $_POST['title'];
    $title_hi = $_POST['title_hi'];

    if ($id) {
        $stmt = $pdo->prepare('UPDATE days SET saptah_id=?, day_number=?, title=?, title_hi=? WHERE id=?');
        $stmt->execute([$saptah_id, $day_number, $title, $title_hi, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO days (saptah_id, day_number, title, title_hi) VALUES (?, ?, ?, ?)');
        $stmt->execute([$saptah_id, $day_number, $title, $title_hi]);
    }
    echo "<script>window.location='days.php';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    $pdo->prepare('DELETE FROM days WHERE id=?')->execute([$id]);
    echo "<script>window.location='days.php';</script>";
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Manage Days</h4>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Day</a>
    <?php else: ?>
        <a href="days.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-body-secondary py-3 border-0">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Day List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Saptah</th>
                        <th>Day Number</th>
                        <th>Title</th>
                        <th>Title (Hindi)</th>
                        <th class="text-center pe-4" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query('SELECT d.*, s.title as saptah_title FROM days d JOIN saptah s ON d.saptah_id = s.id ORDER BY d.saptah_id DESC, d.day_number ASC');
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['saptah_title']) ?></td>
                        <td><span class="badge bg-secondary px-2.5 py-1.5 rounded-pill">Day <?= $row['day_number'] ?></span></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><span class="font-hindi text-success"><?= htmlspecialchars($row['title_hi']) ?></span></td>
                        <td class="text-center pe-4">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit Day"><i class="fas fa-edit"></i></a>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this Day and all its posts?')" title="Delete Day"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($action == 'edit'): 
    $day = ['saptah_id'=>'', 'day_number'=>'', 'title'=>'', 'title_hi'=>''];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM days WHERE id=?');
        $stmt->execute([$id]);
        $day = $stmt->fetch() ?: $day;
    }
    $saptahs = $pdo->query('SELECT id, title, year FROM saptah ORDER BY year DESC')->fetchAll();
?>
<div class="card border-0 shadow-sm rounded-3 p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Day details' : 'Create New Day' ?>
    </h5>
    <form method="post">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label fw-semibold">Saptah</label>
                <select name="saptah_id" class="form-select" required>
                    <option value="">Select Saptah</option>
                    <?php foreach($saptahs as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id']==$day['saptah_id']?'selected':'' ?>><?= htmlspecialchars($s['title'] . ' (' . $s['year'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Day Number (1-7)</label>
                <input type="number" name="day_number" class="form-control" value="<?= htmlspecialchars($day['day_number']) ?>" min="1" max="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Title (English)</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($day['title']) ?>" required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Title (Hindi)</label>
            <input type="text" name="title_hi" class="form-control" value="<?= htmlspecialchars($day['title_hi']) ?>" required>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Day</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
