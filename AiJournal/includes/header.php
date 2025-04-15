<?php
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - AI Journal' : 'AI Journal'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : ''; ?>">
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <span class="logo-icon"><i class="bi bi-journal-text"></i></span>
                    <span>AI Journal</span>
                </a>
                
                <nav class="nav">
                    <button class="theme-toggle" id="themeToggle">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    
                    <button class="mobile-nav-toggle" id="mobileNavToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <ul class="nav-list" id="navList">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a href="index.php" class="nav-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
                                    <i class="bi bi-house"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="new_entry.php" class="nav-link <?php echo $currentPage == 'new_entry' ? 'active' : ''; ?>">
                                    <i class="bi bi-plus-circle"></i> New Entry
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="journal.php" class="nav-link <?php echo $currentPage == 'journal' ? 'active' : ''; ?>">
                                    <i class="bi bi-journal"></i> Journal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="insights.php" class="nav-link <?php echo $currentPage == 'insights' ? 'active' : ''; ?>">
                                    <i class="bi bi-graph-up"></i> Insights
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="settings.php" class="nav-link <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="login.php" class="nav-link <?php echo $currentPage == 'login' ? 'active' : ''; ?>">Login</a>
                            </li>
                            <li class="nav-item">
                                <a href="register.php" class="nav-link <?php echo $currentPage == 'register' ? 'active' : ''; ?>">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> mb-4">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?> 