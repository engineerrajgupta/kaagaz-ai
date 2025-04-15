<?php
// Include configuration and database connection
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_info = getUserInfo($conn, $user_id);

// Get recent journal entries
$entries = getRecentEntries($conn, $user_id, 6);

// Get user stats
$stats = getUserStats($conn, $user_id);

// Get mood trend data for chart
$moodData = getMoodTrendData($conn, $user_id, 7);

// Get random writing prompt
$prompts = [
    "What made you smile today?",
    "What's something you're looking forward to?",
    "Describe a challenge you're currently facing.",
    "What's something you're grateful for today?",
    "If you could change one thing about today, what would it be?",
    "What's a goal you're working toward right now?",
    "Describe your current emotional state using a weather metaphor.",
    "What's something new you learned recently?",
    "Write about a meaningful conversation you had.",
    "What's something you'd like to tell your future self?"
];
$randomPrompt = $prompts[array_rand($prompts)];

// Check if dark mode is enabled
$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $darkMode ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Journal - Dashboard</title>
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-inner">
            <div class="logo">
                <img src="assets/images/logo.png" alt="AI Journal Logo" class="logo-img">
                <span class="logo-text">AI Journal</span>
            </div>
            
            <nav class="nav">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home nav-link-icon"></i> Dashboard
                </a>
                <a href="pages/write.php" class="nav-link">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="pages/calendar.php" class="nav-link">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="pages/insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="pages/settings.php" class="nav-link">
                    <i class="fas fa-gear nav-link-icon"></i> Settings
                </a>
            </nav>
            
            <div class="header-actions">
                <div class="theme-toggle" id="themeToggle"></div>
                <div class="dropdown">
                    <button class="btn btn-outline dropdown-toggle">
                        <i class="fas fa-user btn-icon"></i>
                        <?php echo htmlspecialchars($user_info['name']); ?>
                    </button>
                    <div class="dropdown-menu">
                        <a href="pages/settings.php" class="dropdown-item">Settings</a>
                        <a href="pages/logout.php" class="dropdown-item">Logout</a>
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
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home nav-link-icon"></i> Dashboard
                </a>
                <a href="pages/write.php" class="nav-link">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="pages/calendar.php" class="nav-link">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="pages/insights.php" class="nav-link">
                    <i class="fas fa-chart-line nav-link-icon"></i> Insights
                </a>
                <a href="pages/settings.php" class="nav-link">
                    <i class="fas fa-gear nav-link-icon"></i> Settings
                </a>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="main-content">
            <div class="container">
                <h1 class="mb-4">Welcome back, <?php echo htmlspecialchars($user_info['name']); ?></h1>
                
                <!-- Quick Actions -->
                <div class="d-flex mb-5 gap-3 flex-wrap">
                    <a href="pages/write.php" class="btn btn-primary">
                        <i class="fas fa-plus btn-icon"></i> New Journal Entry
                    </a>
                    <a href="pages/calendar.php" class="btn btn-outline">
                        <i class="fas fa-calendar btn-icon"></i> View Calendar
                    </a>
                    <a href="pages/insights.php" class="btn btn-outline">
                        <i class="fas fa-chart-simple btn-icon"></i> Mood Analytics
                    </a>
                </div>

                <!-- Writing Prompt -->
                <div class="writing-prompt slide-up mb-5">
                    <div class="writing-prompt-label">
                        <i class="fas fa-lightbulb mr-2"></i> Today's Writing Prompt
                    </div>
                    <p class="writing-prompt-text"><?php echo $randomPrompt; ?></p>
                    <button class="btn btn-sm btn-primary mt-3" onclick="window.location.href='pages/write.php?prompt=<?php echo urlencode($randomPrompt); ?>'">
                        Write about this
                    </button>
                </div>

                <!-- Stats Overview -->
                <section class="mb-6">
                    <h2 class="mb-4">Overview</h2>
                    <div class="stats-grid">
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-title">Total Entries</div>
                            <div class="stat-value"><?php echo $stats['total_entries']; ?></div>
                            <div class="stat-change stat-change-positive">
                                <i class="fas fa-arrow-up mr-1"></i> <?php echo $stats['entries_this_week']; ?> this week
                            </div>
                        </div>
                        
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-title">Current Streak</div>
                            <div class="stat-value"><?php echo $stats['current_streak']; ?> days</div>
                            <div class="stat-change">
                                Best: <?php echo $stats['best_streak']; ?> days
                            </div>
                        </div>
                        
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-face-smile"></i>
                            </div>
                            <div class="stat-title">Average Mood</div>
                            <div class="stat-value">
                                <?php 
                                    if ($stats['avg_sentiment'] > 0.3) {
                                        echo '<span class="mood-positive">Positive</span>';
                                    } elseif ($stats['avg_sentiment'] < -0.3) {
                                        echo '<span class="mood-negative">Negative</span>';
                                    } else {
                                        echo '<span class="mood-neutral">Neutral</span>';
                                    }
                                ?>
                            </div>
                            <div class="stat-change">
                                Based on the last 30 days
                            </div>
                        </div>
                        
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-pen-fancy"></i>
                            </div>
                            <div class="stat-title">Words Written</div>
                            <div class="stat-value"><?php echo number_format($stats['total_words']); ?></div>
                            <div class="stat-change">
                                Avg: <?php echo round($stats['avg_words_per_entry']); ?> per entry
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Mood Trend Chart -->
                <section class="mb-6">
                    <h2 class="mb-4">Mood Trend</h2>
                    <div class="chart-container">
                        <canvas id="moodChart"></canvas>
                    </div>
                </section>

                <!-- Recent Entries -->
                <section class="mb-6">
                    <div class="d-flex justify-between align-center mb-4">
                        <h2 class="mb-0">Recent Journal Entries</h2>
                        <a href="pages/calendar.php" class="btn btn-sm btn-link">View All</a>
                    </div>
                    
                    <div class="entry-grid">
                        <?php if (empty($entries)): ?>
                            <div class="card">
                                <div class="card-body text-center">
                                    <p class="text-muted">No journal entries yet. Start writing today!</p>
                                    <a href="pages/write.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus btn-icon"></i> Create First Entry
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($entries as $entry): ?>
                                <div class="card entry-card">
                                    <div class="card-body">
                                        <div class="entry-header">
                                            <div class="entry-date">
                                                <?php echo date('M d, Y', strtotime($entry['date'])); ?>
                                            </div>
                                            <div class="entry-mood">
                                                <?php 
                                                    if ($entry['sentiment_score'] > 0.3) {
                                                        echo '<span class="mood-positive"><i class="fas fa-face-smile-beam"></i></span>';
                                                    } elseif ($entry['sentiment_score'] < -0.3) {
                                                        echo '<span class="mood-negative"><i class="fas fa-face-frown"></i></span>';
                                                    } else {
                                                        echo '<span class="mood-neutral"><i class="fas fa-face-meh"></i></span>';
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="entry-content">
                                            <?php echo htmlspecialchars(substr($entry['content'], 0, 150)) . (strlen($entry['content']) > 150 ? '...' : ''); ?>
                                        </div>
                                        <div class="entry-footer">
                                            <a href="pages/view.php?id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline">
                                                <i class="fas fa-eye btn-icon"></i> View
                                            </a>
                                            <a href="pages/write.php?id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline">
                                                <i class="fas fa-edit btn-icon"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
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
            
            // Update chart colors if chart exists
            if (window.moodChart) {
                updateChartColors(window.moodChart);
            }
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
        
        // Mood Chart
        const moodData = <?php echo json_encode($moodData); ?>;
        
        // Chart colors based on theme
        function getChartColors() {
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            return {
                grid: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                text: isDarkMode ? '#e5e7eb' : '#374151',
                line: '#4f46e5',
                background: isDarkMode ? 'rgba(79, 70, 229, 0.2)' : 'rgba(79, 70, 229, 0.1)'
            };
        }
        
        function updateChartColors(chart) {
            const colors = getChartColors();
            
            chart.options.scales.y.grid.color = colors.grid;
            chart.options.scales.x.grid.color = colors.grid;
            chart.options.scales.y.ticks.color = colors.text;
            chart.options.scales.x.ticks.color = colors.text;
            
            chart.data.datasets[0].borderColor = colors.line;
            chart.data.datasets[0].backgroundColor = colors.background;
            
            chart.update();
        }
        
        if (moodData && moodData.labels && moodData.values) {
            const colors = getChartColors();
            const ctx = document.getElementById('moodChart').getContext('2d');
            
            window.moodChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: moodData.labels,
                    datasets: [{
                        label: 'Mood Score',
                        data: moodData.values,
                        borderColor: colors.line,
                        backgroundColor: colors.background,
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: colors.line,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            cornerRadius: 4,
                            caretSize: 6
                        }
                    },
                    scales: {
                        y: {
                            min: -1,
                            max: 1,
                            grid: {
                                color: colors.grid,
                                drawBorder: false
                            },
                            ticks: {
                                color: colors.text,
                                callback: function(value) {
                                    if (value === 1) return 'Very Positive';
                                    if (value === 0.5) return 'Positive';
                                    if (value === 0) return 'Neutral';
                                    if (value === -0.5) return 'Negative';
                                    if (value === -1) return 'Very Negative';
                                    return '';
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: colors.grid,
                                drawBorder: false,
                                display: false
                            },
                            ticks: {
                                color: colors.text
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html> 