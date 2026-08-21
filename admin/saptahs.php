<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $title_hi = $_POST['title_hi'];
    $slug = $_POST['slug'];
    $location = $_POST['location'];
    $year = $_POST['year'];
    $description = $_POST['description'];
    $description_hi = $_POST['description_hi'];

    if ($id) {
        $stmt = $pdo->prepare('UPDATE saptah SET title=?, title_hi=?, slug=?, location=?, year=?, description=?, description_hi=? WHERE id=?');
        $stmt->execute([$title, $title_hi, $slug, $location, $year, $description, $description_hi, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO saptah (title, title_hi, slug, location, year, description, description_hi) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $title_hi, $slug, $location, $year, $description, $description_hi]);
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

<div class="header">
    <h1>Manage Saptahs</h1>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn">Add New Saptah</a>
    <?php else: ?>
        <a href="saptahs.php" class="btn btn-sm" style="background:#555">Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card">
    <table>
        <tr><th>ID</th><th>Title</th><th>Title (Hi)</th><th>Year</th><th>Location</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query('SELECT * FROM saptah ORDER BY year DESC, id DESC');
        while ($row = $stmt->fetch()):
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['title_hi']) ?></td>
            <td><?= htmlspecialchars($row['year']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td>
                <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this Saptah and all its days/posts?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php elseif ($action == 'edit'): 
    $saptah = ['title'=>'', 'title_hi'=>'', 'slug'=>'', 'location'=>'', 'year'=>date('Y'), 'description'=>'', 'description_hi'=>''];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM saptah WHERE id=?');
        $stmt->execute([$id]);
        $saptah = $stmt->fetch() ?: $saptah;
    }
?>
<div class="card">
    <form method="post">
        <div class="form-group">
            <label>Title (English)</label>
            <input type="text" name="title" value="<?= htmlspecialchars($saptah['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Title (Hindi)</label>
            <input type="text" name="title_hi" value="<?= htmlspecialchars($saptah['title_hi']) ?>" required>
        </div>
        <div class="form-group">
            <label>Slug (URL friendly, e.g., my-saptah-2026)</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($saptah['slug']) ?>" required>
        </div>
        <div style="display:flex; gap:20px">
            <div class="form-group" style="flex:1">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($saptah['location']) ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label>Year</label>
                <input type="number" name="year" value="<?= htmlspecialchars($saptah['year']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Description (English)</label>
            <textarea name="description"><?= htmlspecialchars($saptah['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Description (Hindi)</label>
            <textarea name="description_hi"><?= htmlspecialchars($saptah['description_hi']) ?></textarea>
        </div>
        <button type="submit" class="btn">Save Saptah</button>
    </form>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
