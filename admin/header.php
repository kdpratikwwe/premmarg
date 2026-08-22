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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Premmarg Admin</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">
    <!-- CKEditor 5 for rich text editing -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 250px;
            color: #000 !important;
        }
        /* Custom tweaks to fit AdminLTE 4 beta layout */
        .sidebar-brand {
            height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #4b545c;
        }
        .brand-link {
            color: rgba(255, 255, 255, 0.8) !important;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 300;
        }
    </style>
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out of the admin panel?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Top Navbar -->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>
                
                <!-- Right elements: User Profile settings dropdown -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: rgba(13,110,253,0.08) !important;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span class="fw-semibold">Premmargi</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li>
                                <a class="dropdown-item py-2" href="profile.php">
                                    <i class="fas fa-user-cog me-2 text-muted"></i>Profile Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="logout.php" onclick="confirmLogout(event)">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="index.php" class="brand-link">
                    <i class="fas fa-feather-alt me-2"></i>
                    <span class="brand-text">Premmarg Admin</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-3">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?= $current_page=='index.php'?'active':'' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="saptahs.php" class="nav-link <?= $current_page=='saptahs.php'?'active':'' ?>">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Manage Saptahs</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="days.php" class="nav-link <?= $current_page=='days.php'?'active':'' ?>">
                                <i class="nav-icon fas fa-calendar-day"></i>
                                <p>Manage Days</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="posts.php" class="nav-link <?= $current_page=='posts.php'?'active':'' ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Manage Posts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="quotes.php" class="nav-link <?= $current_page=='quotes.php'?'active':'' ?>">
                                <i class="nav-icon fas fa-quote-left"></i>
                                <p>Manage Quotes</p>
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a href="logout.php" class="nav-link text-danger" onclick="confirmLogout(event)">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="app-main">
            <div class="app-content-header shadow-sm bg-body-secondary mb-4">
                <div class="container-fluid">
                    <div class="row align-items-center py-2">
                        <div class="col-sm-6">
                            <h3 class="mb-0">
                                <?php
                                if ($current_page == 'index.php') echo 'Dashboard';
                                elseif ($current_page == 'saptahs.php') echo 'Manage Saptahs';
                                elseif ($current_page == 'days.php') echo 'Manage Days';
                                elseif ($current_page == 'posts.php') echo 'Manage Posts';
                                elseif ($current_page == 'quotes.php') echo 'Manage Quotes';
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
