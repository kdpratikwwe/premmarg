<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_hi = $_POST['text_hi'];
    $text_en = $_POST['text_en'];
    $source = $_POST['source'] ?: 'श्री गुरुदेव महाराज जी';
    $publish_date = $_POST['publish_date'] ?: null; // Nullable if not scheduled

    if ($id) {
        $stmt = $pdo->prepare('UPDATE quotes SET text_hi=?, text_en=?, source=?, publish_date=? WHERE id=?');
        $stmt->execute([$text_hi, $text_en, $source, $publish_date, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO quotes (text_hi, text_en, source, publish_date) VALUES (?, ?, ?, ?)');
        $stmt->execute([$text_hi, $text_en, $source, $publish_date]);
    }
    echo "<script>window.location='quotes.php';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    $pdo->prepare('DELETE FROM quotes WHERE id=?')->execute([$id]);
    echo "<script>window.location='quotes.php';</script>";
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Manage Quotes</h4>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Quote</a>
    <?php else: ?>
        <a href="quotes.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-body-secondary py-3 border-0">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-quote-left me-2 text-primary"></i>Amrit Vachan List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Hindi Quote</th>
                        <th>English Quote</th>
                        <th>Source</th>
                        <th>Scheduled Date</th>
                        <th class="text-center pe-4" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query('SELECT * FROM quotes ORDER BY publish_date DESC, id DESC');
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                        <td><span class="font-hindi text-success d-inline-block text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($row['text_hi']) ?>"><?= htmlspecialchars($row['text_hi']) ?></span></td>
                        <td><span class="text-secondary d-inline-block text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($row['text_en']) ?>"><?= htmlspecialchars($row['text_en']) ?></span></td>
                        <td><?= htmlspecialchars($row['source']) ?></td>
                        <td>
                            <?php if ($row['publish_date']): ?>
                                <span class="badge bg-info"><i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($row['publish_date']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">General Archive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit Quote"><i class="fas fa-edit"></i></a>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this Quote?')" title="Delete Quote"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($action == 'edit'): 
    $quote = ['text_hi'=>'', 'text_en'=>'', 'source'=>'श्री गुरुदेव महाराज जी', 'publish_date'=>''];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM quotes WHERE id=?');
        $stmt->execute([$id]);
        $quote = $stmt->fetch() ?: $quote;
    }
?>
<div class="card border-0 shadow-sm rounded-3 p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Amrit Vachan Details' : 'Create New Amrit Vachan' ?>
    </h5>
    <form method="post">
        <div class="mb-3">
            <label class="form-label fw-semibold">Hindi Quote (अमृत वचन)</label>
            <textarea name="text_hi" class="form-control font-hindi" rows="3" required placeholder="हिंदी में विचार लिखें…"><?= htmlspecialchars($quote['text_hi']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">English Quote</label>
            <textarea name="text_en" class="form-control" rows="3" required placeholder="Write the English translation…"><?= htmlspecialchars($quote['text_en']) ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Source</label>
                <input type="text" name="source" class="form-control" value="<?= htmlspecialchars($quote['source']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Publish Date (Schedule Quote of the Day)</label>
                <input type="date" name="publish_date" class="form-control" value="<?= htmlspecialchars($quote['publish_date']) ?>">
                <small class="text-muted">Leave blank to make it a general archive quote.</small>
            </div>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Quote</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
