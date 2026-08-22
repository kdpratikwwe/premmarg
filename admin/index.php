<?php
require_once 'header.php';

$saptahs = $pdo->query('SELECT COUNT(*) FROM saptah')->fetchColumn();
$days = $pdo->query('SELECT COUNT(*) FROM days')->fetchColumn();
$posts = $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm p-4 rounded-3">
            <div class="d-flex align-items-center">
                <div class="bg-light-primary text-primary p-3 rounded-circle me-3" style="background: rgba(13,110,253,0.08);">
                    <i class="fas fa-hands-praying fa-2x"></i>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold">राधेश्याम, प्रेममार्गी!</h4>
                    <p class="text-muted mb-0">Welcome back to the Premmarg admin portal. Preserving the nectar of Shrimad Bhagwat Katha summaries.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Saptahs Stats Box -->
    <div class="col-lg-4 col-sm-6 col-12 mb-4">
        <div class="card border-0 shadow-sm bg-primary text-white position-relative overflow-hidden" style="min-height: 140px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-1"><?= $saptahs ?></h2>
                        <span class="text-white-50">Total Saptahs</span>
                    </div>
                    <div class="opacity-25" style="font-size: 3rem;">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <a href="saptahs.php" class="text-white text-decoration-none d-flex align-items-center mt-3 opacity-75 hover-opacity-100" style="font-size: 0.9rem;">
                    Manage Saptahs <i class="fas fa-arrow-circle-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Days Stats Box -->
    <div class="col-lg-4 col-sm-6 col-12 mb-4">
        <div class="card border-0 shadow-sm bg-success text-white position-relative overflow-hidden" style="min-height: 140px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-1"><?= $days ?></h2>
                        <span class="text-white-50">Total Days</span>
                    </div>
                    <div class="opacity-25" style="font-size: 3rem;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <a href="days.php" class="text-white text-decoration-none d-flex align-items-center mt-3 opacity-75 hover-opacity-100" style="font-size: 0.9rem;">
                    Manage Days <i class="fas fa-arrow-circle-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Posts Stats Box -->
    <div class="col-lg-4 col-sm-12 col-12 mb-4">
        <div class="card border-0 shadow-sm bg-warning text-dark position-relative overflow-hidden" style="min-height: 140px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-1 text-dark"><?= $posts ?></h2>
                        <span class="text-dark-50">Total Posts</span>
                    </div>
                    <div class="opacity-25" style="font-size: 3rem;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <a href="posts.php" class="text-dark text-decoration-none d-flex align-items-center mt-3 opacity-75 hover-opacity-100" style="font-size: 0.9rem;">
                    Manage Posts <i class="fas fa-arrow-circle-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-body-secondary py-3 border-0">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="saptahs.php?action=edit" class="btn btn-outline-primary py-2.5 text-start fw-semibold shadow-sm"><i class="fas fa-plus-circle me-2"></i>Create New Saptah</a>
                    <a href="days.php?action=edit" class="btn btn-outline-success py-2.5 text-start fw-semibold shadow-sm"><i class="fas fa-calendar-plus me-2"></i>Add New Saptah Day</a>
                    <a href="posts.php?action=edit" class="btn btn-outline-warning py-2.5 text-start fw-semibold text-dark shadow-sm"><i class="fas fa-file-signature me-2"></i>Add Post Summary</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-body-secondary py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-clock me-2 text-info"></i>Recently Added</h5>
                <a href="posts.php" class="btn btn-link text-decoration-none p-0 text-sm fw-semibold">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Saptah / Day</th>
                                <th>Title</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentStmt = $pdo->query('SELECT p.id, p.title, d.day_number, s.title as saptah_title 
                                                       FROM posts p 
                                                       JOIN days d ON p.day_id = d.id 
                                                       JOIN saptah s ON d.saptah_id = s.id 
                                                       ORDER BY p.id DESC LIMIT 4');
                            $recentPosts = $recentStmt->fetchAll();
                            if(count($recentPosts) > 0):
                                foreach($recentPosts as $rp):
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-secondary rounded-pill me-2">Day <?= $rp['day_number'] ?></span>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;"><?= htmlspecialchars($rp['saptah_title']) ?></small>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($rp['title']) ?></td>
                                <td class="text-end pe-4">
                                    <a href="posts.php?action=edit&id=<?= $rp['id'] ?>" class="btn btn-light btn-sm rounded-circle"><i class="fas fa-arrow-right text-primary"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No summaries added yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
