<?php
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_type = $_POST['post_type'] ?? 'katha_day';
    $day_id = ($post_type === 'lecture' || empty($_POST['day_id'])) ? null : (int)$_POST['day_id'];
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
    $event_location = !empty($_POST['event_location']) ? trim($_POST['event_location']) : null;
    $youtube_url = !empty($_POST['youtube_url']) ? trim($_POST['youtube_url']) : null;

    $title = $_POST['title'];
    $title_hi = $_POST['title_hi'];
    $slug = $_POST['slug'];
    $content = $_POST['content'];
    $content_hi = $_POST['content_hi'];
    $meta_description = $_POST['meta_description'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';

    if ($id) {
        $stmt = $pdo->prepare('UPDATE posts SET day_id=?, post_type=?, event_date=?, event_location=?, youtube_url=?, title=?, title_hi=?, slug=?, content=?, content_hi=?, meta_description=?, featured=?, status=? WHERE id=?');
        $stmt->execute([$day_id, $post_type, $event_date, $event_location, $youtube_url, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured, $status, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts (day_id, post_type, event_date, event_location, youtube_url, title, title_hi, slug, content, content_hi, meta_description, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$day_id, $post_type, $event_date, $event_location, $youtube_url, $title, $title_hi, $slug, $content, $content_hi, $meta_description, $featured, $status]);
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
                        <th>Category / Association</th>
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
                                         LEFT JOIN days d ON p.day_id = d.id 
                                         LEFT JOIN saptah s ON d.saptah_id = s.id 
                                         ORDER BY p.id DESC');
                    while ($row = $stmt->fetch()):
                        $isLecture = ($row['post_type'] ?? 'katha_day') === 'lecture' || empty($row['day_id']);
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= $row['id'] ?></td>
                        <td>
                            <?php if ($isLecture): ?>
                                <span class="badge" style="background:#6f42c1;color:#fff;"><i class="fas fa-microphone me-1"></i>Pravachan / Lecture</span>
                                <?php if (!empty($row['event_location']) || !empty($row['event_date'])): ?>
                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($row['event_location'] ?? '') ?>
                                        <?= (!empty($row['event_location']) && !empty($row['event_date'])) ? ' · ' : '' ?>
                                        <?= htmlspecialchars($row['event_date'] ?? '') ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="fw-semibold"><?= htmlspecialchars($row['saptah_title'] ?? '') ?></div>
                                <small class="text-muted"><span class="badge bg-secondary">Day <?= $row['day_number'] ?? '' ?></span> - <?= htmlspecialchars($row['day_title'] ?? '') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                <?= htmlspecialchars($row['title']) ?>
                                <?php if(!empty($row['youtube_url'])): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 ms-1" title="YouTube Video Attached: <?= htmlspecialchars($row['youtube_url']) ?>">
                                        <i class="fab fa-youtube me-1"></i>Video
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if(!empty($row['title_hi'])): ?>
                                <small class="text-muted font-hindi"><?= htmlspecialchars($row['title_hi']) ?></small>
                            <?php endif; ?>
                        </td>
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
            const isPublished = this.checked;
            const newStatus = isPublished ? 'published' : 'draft';
            const tr = this.closest('tr');
            const btn = tr.querySelector('.preview-link-btn');

            if (newStatus === 'draft') {
                btn.style.display = 'inline-block';
            } else {
                btn.style.display = 'none';
            }

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
                    this.checked = !isPublished;
                    btn.style.display = isPublished ? 'none' : 'inline-block';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Network error updating status.");
                this.checked = !isPublished;
                btn.style.display = isPublished ? 'none' : 'inline-block';
            });
        });
    });
});
</script>

<?php elseif ($action == 'edit'): 
    $post = [
        'day_id'=>'', 
        'post_type'=>'katha_day', 
        'event_date'=>'', 
        'event_location'=>'', 
        'youtube_url'=>'',
        'title'=>'', 
        'title_hi'=>'', 
        'slug'=>'', 
        'content'=>'', 
        'content_hi'=>'', 
        'meta_description'=>'', 
        'featured'=>0, 
        'status'=>'draft'
    ];
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
        <i class="fas fa-edit text-primary me-2"></i><?= $id ? 'Edit Post Details' : 'Create New Post' ?>
    </h5>
    <form method="post">
        <!-- Post Category / Type -->
        <div class="card bg-light border-0 p-3 mb-4 rounded-3">
            <label class="form-label fw-bold mb-2">Select Post Type (प्रकार चुनें)</label>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="post_type" id="type_katha" value="katha_day" <?= ($post['post_type'] ?? 'katha_day') === 'katha_day' ? 'checked' : '' ?> onchange="togglePostTypeFields('katha_day')">
                    <label class="form-check-label fw-semibold" for="type_katha">
                        🪷 Katha Day Summary (कथा सारांश)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="post_type" id="type_lecture" value="lecture" <?= ($post['post_type'] ?? '') === 'lecture' ? 'checked' : '' ?> onchange="togglePostTypeFields('lecture')">
                    <label class="form-check-label fw-semibold" for="type_lecture">
                        🎙️ Standalone Lecture / Pravachan (प्रवचन / उपदेश)
                    </label>
                </div>
            </div>
        </div>

        <!-- Katha & Day Fields -->
        <div id="katha_fields_wrap" class="mb-3" style="<?= ($post['post_type'] ?? 'katha_day') === 'lecture' ? 'display:none;' : '' ?>">
            <label class="form-label fw-semibold">Saptah / Katha & Day</label>
            <select name="day_id" id="day_id_select" class="form-select">
                <option value="">Select Katha Day</option>
                <?php foreach($days as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id']==$post['day_id']?'selected':'' ?>>
                        <?= htmlspecialchars($d['saptah_title'] . ' - Day ' . $d['day_number'] . ': ' . $d['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Standalone Lecture Fields -->
        <div id="lecture_fields_wrap" class="row mb-3" style="<?= ($post['post_type'] ?? 'katha_day') === 'lecture' ? '' : 'display:none;' ?>">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Lecture Date (प्रवचन दिनांक)</label>
                <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($post['event_date'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Lecture Location (स्थान, e.g., वृन्दावन धाम)</label>
                <input type="text" name="event_location" class="form-control" placeholder="e.g. वृन्दावन धाम / बरसाना" value="<?= htmlspecialchars($post['event_location'] ?? '') ?>">
            </div>
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
            <label class="form-label fw-semibold">
                <i class="fab fa-youtube text-danger me-1"></i> YouTube Video Link (यूट्यूब लिंक - वैकल्पिक)
            </label>
            <input type="url" name="youtube_url" id="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/..." value="<?= htmlspecialchars($post['youtube_url'] ?? '') ?>" oninput="previewYoutubeVideo(this.value)">
            <div class="form-text text-muted">
                Paste any YouTube video or live stream link for this Katha Day / Pravachan. An embedded player will automatically appear on the blog post.
            </div>
            <div id="yt_preview_wrap" class="mt-2" style="display:none; max-width: 460px;">
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border border-secondary border-opacity-25">
                    <iframe id="yt_preview_iframe" src="" allowfullscreen></iframe>
                </div>
            </div>
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
            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i>Save Post</button>
        </div>
    </form>
</div>

<script>
function togglePostTypeFields(type) {
    const kathaWrap = document.getElementById('katha_fields_wrap');
    const lectureWrap = document.getElementById('lecture_fields_wrap');
    const daySelect = document.getElementById('day_id_select');

    if (type === 'lecture') {
        kathaWrap.style.display = 'none';
        lectureWrap.style.display = 'flex';
        daySelect.removeAttribute('required');
    } else {
        kathaWrap.style.display = 'block';
        lectureWrap.style.display = 'none';
    }
}

function extractYTId(url) {
    if (!url) return null;
    url = url.trim();
    if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
    const match = url.match(/(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?.*v=|embed\/|v\/|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return match ? match[1] : null;
}

function previewYoutubeVideo(url) {
    const wrap = document.getElementById('yt_preview_wrap');
    const iframe = document.getElementById('yt_preview_iframe');
    const videoId = extractYTId(url);
    if (videoId) {
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId;
        wrap.style.display = 'block';
    } else {
        iframe.src = '';
        wrap.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const ytInput = document.getElementById('youtube_url');
    if (ytInput && ytInput.value) {
        previewYoutubeVideo(ytInput.value);
    }
});

ClassicEditor
    .create( document.querySelector( '#content_en' ) )
    .catch( error => { console.error( error ); } );
ClassicEditor
    .create( document.querySelector( '#content_hi' ) )
    .catch( error => { console.error( error ); } );
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
