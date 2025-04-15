<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Handle export request
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $format = isset($_GET['format']) ? $_GET['format'] : 'txt';
    
    // Get all user's entries
    $query = "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
    
    if ($format === 'txt') {
        // Set headers for text file download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="journal_export_' . date('Y-m-d') . '.txt"');
        
        // Output entries in text format
        foreach ($entries as $entry) {
            echo "Date: " . $entry['date'] . "\n";
            echo "Mood: " . $entry['sentiment_label'] . " (" . $entry['sentiment_score'] . ")\n";
            echo "Entry:\n" . $entry['content'] . "\n\n";
            echo "------------------------------------------------\n\n";
        }
        exit;
    } elseif ($format === 'pdf') {
        // Redirect to external PDF export if needed
        // For simplicity, we'll just do TXT export for this project
        redirect('settings.php?export_error=pdf_not_supported');
    }
}

// Handle delete all entries request
if (isset($_POST['delete_all_entries']) && $_POST['delete_all_entries'] === 'confirm') {
    $query = "DELETE FROM journal_entries WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "All journal entries have been deleted.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete entries. Please try again.";
        $_SESSION['message_type'] = "danger";
    }
    
    redirect('settings.php');
}

// Handle update privacy settings
if (isset($_POST['update_privacy'])) {
    $privacy_status = isset($_POST['make_all_private']) ? 1 : 0;
    
    $query = "UPDATE journal_entries SET is_private = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $privacy_status, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Privacy settings updated successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update privacy settings. Please try again.";
        $_SESSION['message_type'] = "danger";
    }
    
    redirect('settings.php');
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AI Journal</title>
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-inner">
            <div class="logo">
                <a href="../index.php">
                    <img src="../assets/img/logo.png" alt="AI Journal Logo" class="logo-img">
                    <span class="logo-text">AI Journal</span>
                </a>
            </div>
            
            <nav class="nav">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-home nav-link-icon"></i> Dashboard
                </a>
                <a href="write.php" class="nav-link">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="calendar.php" class="nav-link">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="settings.php" class="nav-link active">
                    <i class="fas fa-gear nav-link-icon"></i> Settings
                </a>
            </nav>
            
            <div class="header-actions">
                <div class="theme-toggle" id="themeToggle"></div>
                <div class="dropdown">
                    <button class="btn btn-outline dropdown-toggle">
                        <i class="fas fa-user btn-icon"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </button>
                    <div class="dropdown-menu">
                        <a href="settings.php" class="dropdown-item">Settings</a>
                        <a href="logout.php" class="dropdown-item">Logout</a>
                    </div>
                </div>
                <button class="hamburger" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="app-layout">
        <!-- Sidebar (Mobile) -->
        <aside class="sidebar" id="mobileSidebar">
            <div class="sidebar-menu">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-home nav-link-icon"></i> Dashboard
                </a>
                <a href="write.php" class="nav-link">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="calendar.php" class="nav-link">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="settings.php" class="nav-link active">
                    <i class="fas fa-gear nav-link-icon"></i> Settings
                </a>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="main-content">
            <div class="container">
                <h1 class="mb-4">Settings</h1>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> mb-4">
                        <div class="alert-icon">
                            <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'circle-exclamation'; ?>"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title"><?php echo $_SESSION['message_type'] === 'success' ? 'Success' : 'Error'; ?></div>
                            <div class="alert-message"><?php echo $_SESSION['message']; ?></div>
                        </div>
                        <button class="alert-close">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php 
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['export_error'])): ?>
                    <div class="alert alert-danger mb-4">
                        <div class="alert-icon">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Export Error</div>
                            <div class="alert-message">
                                <?php if ($_GET['export_error'] === 'pdf_not_supported'): ?>
                                    PDF export is not currently supported. Please use TXT format.
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="alert-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="settings-section mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user-circle mr-2"></i> Account Information
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <div class="form-control-static"><?php echo htmlspecialchars($user['name']); ?></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <div class="form-control-static"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-file-export mr-2"></i> Export Journal
                            </h2>
                        </div>
                        <div class="card-body">
                            <p class="mb-4">Download all your journal entries in a single file.</p>
                            
                            <div class="d-flex gap-3">
                                <a href="settings.php?action=export&format=txt" class="btn btn-primary">
                                    <i class="fas fa-file-alt btn-icon"></i> Export as TXT
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section mb-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-lock mr-2"></i> Privacy Settings
                            </h2>
                        </div>
                        <div class="card-body">
                            <form action="settings.php" method="POST">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="make_all_private" name="make_all_private" class="form-check-input">
                                        <label for="make_all_private" class="form-check-label">Make all journal entries private</label>
                                    </div>
                                    <div class="form-text">Private entries will not be shown in your mood statistics and insights.</div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="update_privacy" class="btn btn-primary">
                                        <i class="fas fa-save btn-icon"></i> Update Privacy Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section mb-5">
                    <div class="danger-zone p-4">
                        <h2 class="mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Danger Zone
                        </h2>
                        <p class="mb-3">This action will permanently delete all your journal entries. This cannot be undone.</p>
                        
                        <form action="settings.php" method="POST" onsubmit="return confirm('WARNING: This will permanently delete ALL your journal entries. This action CANNOT be undone. Are you absolutely sure?');">
                            <button type="submit" name="delete_all_entries" value="confirm" class="btn btn-danger">
                                <i class="fas fa-trash-alt btn-icon"></i> Delete All Entries
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark-mode');
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            document.cookie = `darkMode=${isDarkMode}; path=/; max-age=31536000`;
        });
        
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        
        mobileMenuToggle.addEventListener('click', () => {
            mobileSidebar.classList.toggle('active');
        });
        
        // Dropdown menu functionality
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
            
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        });
        
        // Alert close functionality
        document.querySelectorAll('.alert-close').forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.closest('.alert');
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        });
    </script>
</body>
</html> 