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

<div class="header">
    <h1>Manage Days</h1>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn">Add New Day</a>
    <?php else: ?>
        <a href="days.php" class="btn btn-sm" style="background:#555">Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card">
    <table>
        <tr><th>ID</th><th>Saptah</th><th>Day #</th><th>Title</th><th>Title (Hi)</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query('SELECT d.*, s.title as saptah_title FROM days d JOIN saptah s ON d.saptah_id = s.id ORDER BY d.saptah_id DESC, d.day_number ASC');
        while ($row = $stmt->fetch()):
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['saptah_title']) ?></td>
            <td>Day <?= $row['day_number'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['title_hi']) ?></td>
            <td>
                <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this Day and all its posts?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
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
<div class="card">
    <form method="post">
        <div style="display:flex; gap:20px">
            <div class="form-group" style="flex:2">
                <label>Saptah</label>
                <select name="saptah_id" required>
                    <option value="">Select Saptah</option>
                    <?php foreach($saptahs as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id']==$day['saptah_id']?'selected':'' ?>><?= htmlspecialchars($s['title'] . ' (' . $s['year'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1">
                <label>Day Number (1-7)</label>
                <input type="number" name="day_number" value="<?= htmlspecialchars($day['day_number']) ?>" min="1" max="7" required>
            </div>
        </div>
        <div class="form-group">
            <label>Title (English)</label>
            <input type="text" name="title" value="<?= htmlspecialchars($day['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Title (Hindi)</label>
            <input type="text" name="title_hi" value="<?= htmlspecialchars($day['title_hi']) ?>" required>
        </div>
        <button type="submit" class="btn">Save Day</button>
    </form>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
