<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../api/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Premmarg Admin</title>
    <link rel="stylesheet" href="admin_style.css">
    <!-- CKEditor 5 for rich text editing -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 250px;
            color: #000 !important;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="sidebar-header">Premmarg Admin</div>
            <ul class="nav-menu">
                <li><a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Dashboard</a></li>
                <li><a href="saptahs.php" class="<?= $current_page=='saptahs.php'?'active':'' ?>">Manage Saptahs</a></li>
                <li><a href="days.php" class="<?= $current_page=='days.php'?'active':'' ?>">Manage Days</a></li>
                <li><a href="posts.php" class="<?= $current_page=='posts.php'?'active':'' ?>">Manage Posts</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
