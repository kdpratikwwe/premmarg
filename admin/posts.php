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
    $status = $_POST['status'] ?? 'draft';

    if ($id) {
        $stmt = $pdo->prepare('UPDATE posts SET day_id=?, title=?, title_hi=?, slug=?, content=?, content_hi=?, meta_description=?, featured=?, status=? WHERE id=?');
        $stmt->execute([$day_id, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured, $status, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts (day_id, title, title_hi, slug, content, content_hi, meta_description, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$day_id, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured, $status]);
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Manage Posts</h4>
    <?php if($action == 'list'): ?>
        <a href="?action=edit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Post</a>
    <?php else: ?>
        <a href="posts.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
    <?php endif; ?>
</div>

<?php if ($action == 'list'): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-body-secondary py-3 border-0">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Post List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Day / Saptah</th>
                        <th>Title</th>
                        <th class="text-center">Featured</th>
                        <th class="text-center">Status / Preview</th>
                        <th class="text-center pe-4" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query('SELECT p.*, d.day_number, d.title as day_title, s.title as saptah_title 
                                         FROM posts p 
                                         JOIN days d ON p.day_id = d.id 
                                         JOIN saptah s ON d.saptah_id = s.id 
                                         ORDER BY p.id DESC');
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['saptah_title']) ?></div>
                            <small class="text-muted"><span class="badge bg-secondary">Day <?= $row['day_number'] ?></span> - <?= htmlspecialchars($row['day_title']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td class="text-center">
                            <?php if($row['featured']): ?>
                                <span class="badge bg-success"><i class="fas fa-star me-1"></i>Yes</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                           data-id="<?= $row['id'] ?>" 
                                           data-slug="<?= htmlspecialchars($row['slug']) ?>"
                                           <?= ($row['status'] ?? 'published') == 'published' ? 'checked' : '' ?>
                                           title="Toggle Status (Checked = Published, Unchecked = Draft)">
                                </div>
                                <button class="btn btn-outline-secondary btn-sm ms-2 preview-link-btn" 
                                        onclick="copyPreviewLink('<?= htmlspecialchars($row['slug']) ?>')" 
                                        title="Copy Review Link"
                                        style="display: <?= ($row['status'] ?? 'published') == 'draft' ? 'inline-block' : 'none' ?>;">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit Post"><i class="fas fa-edit"></i></a>
                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this post?')" title="Delete Post"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyPreviewLink(slug) {
    const path = window.location.pathname.includes('/Premmarg-Blog/') ? '/Premmarg-Blog/' : '/';
    const previewUrl = window.location.origin + path + 'post.html?slug=' + slug + '&preview=1';
    
    navigator.clipboard.writeText(previewUrl).then(() => {
        alert("Review Preview Link copied to clipboard:\n" + previewUrl);
    }).catch(err => {
        alert("Failed to copy link: " + err);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const postId = this.dataset.id;
            const postSlug = this.dataset.slug;
            const isPublished = this.checked;
            const newStatus = isPublished ? 'published' : 'draft';
            const tr = this.closest('tr');
            const btn = tr.querySelector('.preview-link-btn');

            // Instantly toggle preview button visibility
            if (newStatus === 'draft') {
                btn.style.display = 'inline-block';
            } else {
                btn.style.display = 'none';
            }

            // AJAX call to update status
            const formData = new FormData();
            formData.append('id', postId);
            formData.append('status', newStatus);

            fetch('ajax_update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert("Error: " + data.error);
                    // Revert state
                    this.checked = !isPublished;
                    btn.style.display = isPublished ? 'none' : 'inline-block';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Network error updating status.");
                // Revert state
                this.checked = !isPublished;
                btn.style.display = isPublished ? 'none' : 'inline-block';
            });
        });
    });
});
</script>

<?php elseif ($action == 'edit'): 
    $post = ['day_id'=>'', 'title'=>'', 'title_hi'=>'', 'slug'=>'', 'content'=>'', 'content_hi'=>'', 'meta_description'=>'', 'featured'=>0, 'status'=>'draft'];
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id=?');
        $stmt->execute([$id]);
        $post = $stmt->fetch() ?: $post;
    }
    $days = $pdo->query('SELECT d.id, d.day_number, d.title, s.title as saptah_title 
                         FROM days d JOIN saptah s ON d.saptah_id = s.id 
                         ORDER BY s.year DESC, d.day_number ASC')->fetchAll();
?>
<div class="card border-0 shadow-sm rounded-3 p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Post summary' : 'Create New Post Summary' ?>
    </h5>
    <form method="post">
        <div class="mb-3">
            <label class="form-label fw-semibold">Saptah & Day</label>
            <select name="day_id" class="form-select" required>
                <option value="">Select Day</option>
                <?php foreach($days as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id']==$post['day_id']?'selected':'' ?>>
                        <?= htmlspecialchars($d['saptah_title'] . ' - Day ' . $d['day_number'] . ': ' . $d['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Title (English)</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Title (Hindi)</label>
                <input type="text" name="title_hi" class="form-control" value="<?= htmlspecialchars($post['title_hi']) ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Slug (URL friendly, e.g., my-post-name)</label>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($post['slug']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Meta Description (for SEO)</label>
            <input type="text" name="meta_description" class="form-control" value="<?= htmlspecialchars($post['meta_description']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Content (English)</label>
            <textarea name="content" id="content_en" class="form-control"><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Content (Hindi)</label>
            <textarea name="content_hi" id="content_hi" class="form-control"><?= htmlspecialchars($post['content_hi']) ?></textarea>
        </div>
        <div class="row mb-4 align-items-center">
            <div class="col-md-6 mb-3 mb-md-0 form-check form-switch ps-5">
                <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" <?= $post['featured']?'checked':'' ?>>
                <label for="featured" class="form-check-label fw-semibold">Feature on Homepage</label>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Post Status</label>
                <select name="status" class="form-select" required>
                    <option value="draft" <?= ($post['status'] ?? 'draft') == 'draft' ? 'selected' : '' ?>>Draft (Internal Preview Only)</option>
                    <option value="published" <?= ($post['status'] ?? 'draft') == 'published' ? 'selected' : '' ?>>Published (Live to Public)</option>
                </select>
            </div>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Post Summary</button>
        </div>
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
