<?php
require_once 'header.php';

$saptahs = $pdo->query('SELECT COUNT(*) FROM saptah')->fetchColumn();
$days = $pdo->query('SELECT COUNT(*) FROM days')->fetchColumn();
$posts = $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
?>
<div class="header">
    <h1>Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['admin_user']) ?></p>
</div>

<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
    <div class="card">
        <h3>Total Saptahs</h3>
        <p style="font-size: 2rem; color: var(--primary); margin-top: 10px;"><?= $saptahs ?></p>
    </div>
    <div class="card">
        <h3>Total Days</h3>
        <p style="font-size: 2rem; color: var(--primary); margin-top: 10px;"><?= $days ?></p>
    </div>
    <div class="card">
        <h3>Total Posts</h3>
        <p style="font-size: 2rem; color: var(--primary); margin-top: 10px;"><?= $posts ?></p>
    </div>
</div>
<?php require_once 'footer.php'; ?>
