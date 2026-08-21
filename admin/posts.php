<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day_id = $_POST['day_id'];
    $title = $_POST['title'];
    $title_hi = $_POST['title_hi'];
    $slug = $_POST['slug'];
    $content = $_POST['content'];
    $content_hi = $_POST['content_hi'];
    $meta_description = $_POST['meta_description'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE posts SET day_id=?, title=?, title_hi=?, slug=?, content=?, content_hi=?, meta_description=?, featured=? WHERE id=?');
        $stmt->execute([$day_id, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts (day_id, title, title_hi, slug, content, content_hi, meta_description, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$day_id, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured]);
    }
    echo "<script>window.location='posts.php';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    $pdo->prepare('DELETE FROM posts WHERE id=?')->execute([$id]);
    echo "<script>window.location='posts.php';</script>";
    exit;
}
?>

<div class="header">
    <h1>Manage Posts</h1>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn">Add New Post</a>
    <?php else: ?>
        <a href="posts.php" class="btn btn-sm" style="background:#555">Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card">
    <table>
        <tr><th>ID</th><th>Day</th><th>Title</th><th>Featured</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query('SELECT p.*, d.title as day_title, s.title as saptah_title 
                             FROM posts p 
                             JOIN days d ON p.day_id = d.id 
                             JOIN saptah s ON d.saptah_id = s.id 
                             ORDER BY p.id DESC');
        while ($row = $stmt->fetch()):
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['saptah_title'] . ' - Day ' . $row['day_title']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['featured'] ? 'Yes' : 'No' ?></td>
            <td>
                <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php elseif ($action == 'edit'): 
    $post = ['day_id'=>'', 'title'=>'', 'title_hi'=>'', 'slug'=>'', 'content'=>'', 'content_hi'=>'', 'meta_description'=>'', 'featured'=>0];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id=?');
        $stmt->execute([$id]);
        $post = $stmt->fetch() ?: $post;
    }
    $days = $pdo->query('SELECT d.id, d.day_number, d.title, s.title as saptah_title 
                         FROM days d JOIN saptah s ON d.saptah_id = s.id 
                         ORDER BY s.year DESC, d.day_number ASC')->fetchAll();
?>
<div class="card">
    <form method="post">
        <div class="form-group">
            <label>Saptah & Day</label>
            <select name="day_id" required>
                <option value="">Select Day</option>
                <?php foreach($days as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id']==$post['day_id']?'selected':'' ?>>
                        <?= htmlspecialchars($d['saptah_title'] . ' - Day ' . $d['day_number'] . ': ' . $d['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex; gap:20px">
            <div class="form-group" style="flex:1">
                <label>Title (English)</label>
                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Title (Hindi)</label>
                <input type="text" name="title_hi" value="<?= htmlspecialchars($post['title_hi']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Slug (URL friendly, e.g., my-post-name)</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" required>
        </div>
        <div class="form-group">
            <label>Meta Description (for SEO)</label>
            <input type="text" name="meta_description" value="<?= htmlspecialchars($post['meta_description']) ?>">
        </div>
        <div class="form-group">
            <label>Content (English)</label>
            <textarea name="content" id="content_en"><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Content (Hindi)</label>
            <textarea name="content_hi" id="content_hi"><?= htmlspecialchars($post['content_hi']) ?></textarea>
        </div>
        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="featured" id="featured" value="1" <?= $post['featured']?'checked':'' ?> style="width:auto;">
            <label for="featured" style="margin:0;">Feature on Homepage</label>
        </div>
        <button type="submit" class="btn">Save Post</button>
    </form>
</div>
<script>
    ClassicEditor
        .create( document.querySelector( '#content_en' ) )
        .catch( error => {
            console.error( error );
        } );
    ClassicEditor
        .create( document.querySelector( '#content_hi' ) )
        .catch( error => {
            console.error( error );
        } );
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
