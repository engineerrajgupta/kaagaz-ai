<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get the date range (default to last 30 days)
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
if ($days < 7 || $days > 90) {
    $days = 30;
}

$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-$days days"));

// Get journal entries for the date range
$entries = [];
$query = "SELECT * FROM journal_entries WHERE user_id = ? AND date BETWEEN ? AND ? AND is_private = 0 ORDER BY date";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $entries[] = $row;
}

// Prepare data for mood trend chart
$dates = [];
$scores = [];
$labels = [];

foreach ($entries as $entry) {
    $dates[] = date('M d', strtotime($entry['date']));
    $scores[] = $entry['sentiment_score'];
    $labels[] = $entry['sentiment_label'];
}

// Get weekly mood summary (last 7 days)
$weekly_positive = 0;
$weekly_neutral = 0;
$weekly_negative = 0;
$weekly_start_date = date('Y-m-d', strtotime('-7 days'));

$query = "SELECT sentiment_label, COUNT(*) as count FROM journal_entries 
          WHERE user_id = ? AND date BETWEEN ? AND ? AND is_private = 0
          GROUP BY sentiment_label";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $weekly_start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    switch ($row['sentiment_label']) {
        case 'Positive':
            $weekly_positive = $row['count'];
            break;
        case 'Neutral':
            $weekly_neutral = $row['count'];
            break;
        case 'Negative':
            $weekly_negative = $row['count'];
            break;
    }
}

// Get most positive and negative entries
$most_positive = null;
$most_negative = null;

$query = "SELECT * FROM journal_entries WHERE user_id = ? AND is_private = 0 ORDER BY sentiment_score DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $most_positive = mysqli_fetch_assoc($result);
}

$query = "SELECT * FROM journal_entries WHERE user_id = ? AND is_private = 0 ORDER BY sentiment_score ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $most_negative = mysqli_fetch_assoc($result);
}

// Get entry distribution by mood
$query = "SELECT sentiment_label, COUNT(*) as count FROM journal_entries 
          WHERE user_id = ? AND is_private = 0
          GROUP BY sentiment_label";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$mood_distribution = [
    'Positive' => 0,
    'Neutral' => 0,
    'Negative' => 0
];

while ($row = mysqli_fetch_assoc($result)) {
    $mood_distribution[$row['sentiment_label']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insights - AI Journal</title>
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="insights.php" class="nav-link active">
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
                <a href="calendar.php" class="nav-link">
                    <i class="fas fa-calendar-days nav-link-icon"></i> Calendar
                </a>
                <a href="insights.php" class="nav-link active">
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
                <h1 class="mb-4">Mood Insights</h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="insights.php" method="GET" class="d-flex align-center">
                            <label for="days" class="mr-3">Time Period:</label>
                            <select name="days" id="days" class="form-control" style="width: auto;" onchange="this.form.submit()">
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <?php if (empty($entries)): ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted">You don't have enough journal entries yet to generate insights.</p>
                            <a href="write.php" class="btn btn-primary mt-3">
                                <i class="fas fa-pen-to-square btn-icon"></i> Write Your First Entry
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card mb-5">
                        <div class="card-header">
                            <h2 class="card-title">Mood Trend (Last <?php echo $days; ?> Days)</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="moodChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-grid mb-5">
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="stat-title">This Week's Mood</div>
                            <div class="stat-value">
                                <?php 
                                    $total = $weekly_positive + $weekly_neutral + $weekly_negative;
                                    if ($total > 0) {
                                        if ($weekly_positive > $weekly_neutral && $weekly_positive > $weekly_negative) {
                                            echo '<span class="mood-positive">Mostly Positive</span>';
                                        } elseif ($weekly_negative > $weekly_neutral && $weekly_negative > $weekly_positive) {
                                            echo '<span class="mood-negative">Mostly Negative</span>';
                                        } else {
                                            echo '<span class="mood-neutral">Mostly Neutral</span>';
                                        }
                                    } else {
                                        echo 'No entries';
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="stat-title">Weekly Summary</div>
                            <div class="stat-value">
                                <div class="d-flex align-center gap-2 mb-1">
                                    <span class="mood-badge mood-badge-positive">Positive</span>
                                    <span><?php echo $weekly_positive; ?></span>
                                </div>
                                <div class="d-flex align-center gap-2 mb-1">
                                    <span class="mood-badge mood-badge-neutral">Neutral</span>
                                    <span><?php echo $weekly_neutral; ?></span>
                                </div>
                                <div class="d-flex align-center gap-2">
                                    <span class="mood-badge mood-badge-negative">Negative</span>
                                    <span><?php echo $weekly_negative; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="stat-title">Overall Distribution</div>
                            <div class="stat-value">
                                <canvas id="distributionChart" style="max-height: 150px;"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <?php if ($most_positive): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Most Positive Day</h2>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-between align-center mb-3">
                                        <span class="fs-sm"><?php echo date('M d, Y', strtotime($most_positive['date'])); ?></span>
                                        <span class="mood-positive"><i class="fas fa-face-smile-beam"></i></span>
                                    </div>
                                    <div class="mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($most_positive['content'], 0, 200))); ?>
                                        <?php if (strlen($most_positive['content']) > 200): ?>...<?php endif; ?>
                                    </div>
                                    <a href="view.php?id=<?php echo $most_positive['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye btn-icon"></i> Read Entry
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($most_negative): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Most Negative Day</h2>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-between align-center mb-3">
                                        <span class="fs-sm"><?php echo date('M d, Y', strtotime($most_negative['date'])); ?></span>
                                        <span class="mood-negative"><i class="fas fa-face-frown"></i></span>
                                    </div>
                                    <div class="mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($most_negative['content'], 0, 200))); ?>
                                        <?php if (strlen($most_negative['content']) > 200): ?>...<?php endif; ?>
                                    </div>
                                    <a href="view.php?id=<?php echo $most_negative['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye btn-icon"></i> Read Entry
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
            
            // Update chart colors if charts exist
            if (window.moodChart) {
                updateChartColors(window.moodChart);
            }
            if (window.distributionChart) {
                updateChartColors(window.distributionChart);
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

        <?php if (!empty($entries)): ?>
        // Chart colors based on theme
        function getChartColors() {
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            return {
                grid: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                text: isDarkMode ? '#e5e7eb' : '#374151',
                line: '#4f46e5',
                background: isDarkMode ? 'rgba(79, 70, 229, 0.2)' : 'rgba(79, 70, 229, 0.1)',
                positive: isDarkMode ? '#34d399' : '#10b981',
                neutral: isDarkMode ? '#a3a3a3' : '#6b7280',
                negative: isDarkMode ? '#f87171' : '#ef4444'
            };
        }
        
        function updateChartColors(chart) {
            const colors = getChartColors();
            
            if (chart.config.type === 'line') {
                chart.options.scales.y.grid.color = colors.grid;
                chart.options.scales.x.grid.color = colors.grid;
                chart.options.scales.y.ticks.color = colors.text;
                chart.options.scales.x.ticks.color = colors.text;
                
                chart.data.datasets[0].borderColor = colors.line;
                chart.data.datasets[0].backgroundColor = colors.background;
            } else if (chart.config.type === 'doughnut') {
                chart.data.datasets[0].backgroundColor = [
                    colors.positive,
                    colors.neutral,
                    colors.negative
                ];
            }
            
            chart.update();
        }
        
        // Mood trend chart
        const moodCtx = document.getElementById('moodChart').getContext('2d');
        const colors = getChartColors();
        
        window.moodChart = new Chart(moodCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Mood Score',
                    data: <?php echo json_encode($scores); ?>,
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
                        caretSize: 6,
                        callbacks: {
                            label: function(context) {
                                const labels = <?php echo json_encode($labels); ?>;
                                const label = labels[context.dataIndex];
                                const score = context.parsed.y.toFixed(2);
                                return `${label} (${score})`;
                            }
                        }
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
        
        // Distribution chart
        const distCtx = document.getElementById('distributionChart').getContext('2d');
        
        window.distributionChart = new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [
                        <?php echo $mood_distribution['Positive']; ?>,
                        <?php echo $mood_distribution['Neutral']; ?>,
                        <?php echo $mood_distribution['Negative']; ?>
                    ],
                    backgroundColor: [
                        colors.positive,
                        colors.neutral,
                        colors.negative
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colors.text,
                            font: {
                                size: 12
                            },
                            padding: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        caretSize: 6
                    }
                },
                cutout: '70%'
            }
        });
        <?php endif; ?>
    </script>
</body>
</html> 