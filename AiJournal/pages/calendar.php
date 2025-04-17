<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}

if ($year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

// Get the first day of the month
$first_day = mktime(0, 0, 0, $month, 1, $year);
$day_of_week = date('N', $first_day); // 1 (Mon) to 7 (Sun)
$days_in_month = date('t', $first_day);

// Get previous and next month navigation links
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Get all journal entries for this month
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";
$entries = [];

$query = "SELECT id, date, sentiment_label FROM journal_entries WHERE user_id = ? AND date BETWEEN ? AND ? ORDER BY date";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $entries[date('j', strtotime($row['date']))] = [
        'id' => $row['id'],
        'sentiment_label' => $row['sentiment_label']
    ];
}

// Get month name
$month_name = date('F', $first_day);
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - AI Journal</title>
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
                <a href="calendar.php" class="nav-link active">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="settings.php" class="nav-link">
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
                <a href="calendar.php" class="nav-link active">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-gear nav-link-icon"></i> Settings
                </a>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="main-content">
            <div class="container">
                <h1 class="mb-4">Calendar View</h1>
                
                <div class="card mb-5">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <h2 class="calendar-month"><?php echo $month_name . ' ' . $year; ?></h2>
                            <div class="calendar-controls">
                                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-outline btn-sm">Today</a>
                                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="calendar-grid">
                            <!-- Day names -->
                            <div class="calendar-weekday">Mon</div>
                            <div class="calendar-weekday">Tue</div>
                            <div class="calendar-weekday">Wed</div>
                            <div class="calendar-weekday">Thu</div>
                            <div class="calendar-weekday">Fri</div>
                            <div class="calendar-weekday">Sat</div>
                            <div class="calendar-weekday">Sun</div>
                            
                            <!-- Empty cells before the first day -->
                            <?php for ($i = 1; $i < $day_of_week; $i++): ?>
                                <div class="calendar-day empty"></div>
                            <?php endfor; ?>
                            
                            <!-- Days of the month -->
                            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                                <?php
                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $has_entry = isset($entries[$day]);
                                $is_today = ($date === date('Y-m-d'));
                                $mood_class = $has_entry ? strtolower($entries[$day]['sentiment_label']) : '';
                                ?>
                                <div class="calendar-day <?php echo $has_entry ? 'has-entry' : ''; ?> <?php echo $is_today ? 'today' : ''; ?>" 
                                     onclick="<?php echo $has_entry ? 'window.location.href=\'view.php?id=' . $entries[$day]['id'] . '\'' : 'window.location.href=\'write.php?date=' . $date . '\''; ?>">
                                    <?php echo $day; ?>
                                    <?php if ($has_entry): ?>
                                        <div class="mood-indicator" style="background-color: <?php 
                                            if ($entries[$day]['sentiment_label'] == 'Positive') echo 'var(--positive)';
                                            else if ($entries[$day]['sentiment_label'] == 'Negative') echo 'var(--negative)';
                                            else echo 'var(--neutral)';
                                        ?>"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                            
                            <!-- Empty cells after the last day -->
                            <?php
                            $total_cells = $day_of_week - 1 + $days_in_month;
                            $remaining_cells = 7 - ($total_cells % 7);
                            if ($remaining_cells < 7):
                                for ($i = 0; $i < $remaining_cells; $i++):
                            ?>
                                <div class="calendar-day empty"></div>
                            <?php 
                                endfor;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-4 mb-5 flex-wrap">
                    <a href="write.php" class="btn btn-primary">
                        <i class="fas fa-plus btn-icon"></i> New Journal Entry
                    </a>
                    <a href="insights.php" class="btn btn-outline">
                        <i class="fas fa-chart-line btn-icon"></i> View Mood Insights
                    </a>
                </div>
                
                <div class="card p-4 mb-5">
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="d-flex align-center gap-2">
                            <div style="width: 16px; height: 16px; border-radius: 50%; background-color: var(--positive);"></div>
                            <span>Positive</span>
                        </div>
                        <div class="d-flex align-center gap-2">
                            <div style="width: 16px; height: 16px; border-radius: 50%; background-color: var(--neutral);"></div>
                            <span>Neutral</span>
                        </div>
                        <div class="d-flex align-center gap-2">
                            <div style="width: 16px; height: 16px; border-radius: 50%; background-color: var(--negative);"></div>
                            <span>Negative</span>
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Click on any day to view or create an entry
                        </div>
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
    </script>
</body>
</html> 