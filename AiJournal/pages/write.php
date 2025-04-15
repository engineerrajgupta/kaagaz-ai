<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/sentiment.php';

// Set page title
$pageTitle = isset($_GET['id']) ? 'Edit Journal Entry' : 'New Journal Entry';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$entry = [
    'id' => null,
    'date' => date('Y-m-d'),
    'content' => '',
    'is_private' => 0
];

// Get prompt from URL if any
$prompt = isset($_GET['prompt']) ? $_GET['prompt'] : '';

// Check if editing an existing entry
if (isset($_GET['id'])) {
    $entry_id = (int)$_GET['id'];
    
    // Get entry from database
    $query = "SELECT * FROM journal_entries WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 1) {
        $entry = mysqli_fetch_assoc($result);
    } else {
        // Entry not found or doesn't belong to user
        $_SESSION['message'] = "Entry not found.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../index.php");
        exit();
    }
}

// Check if date parameter is provided (from calendar)
if (isset($_GET['date']) && !isset($_GET['id'])) {
    $entry['date'] = $_GET['date'];
    
    // Check if entry already exists for this date
    $query = "SELECT * FROM journal_entries WHERE date = ? AND user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $entry['date'], $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 1) {
        $entry = mysqli_fetch_assoc($result);
    }
}

// Get random writing prompt if not editing
if (!isset($_GET['id']) && empty($prompt)) {
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
    $prompt = $prompts[array_rand($prompts)];
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = sanitize($_POST['date']);
    $content = sanitize($_POST['content']);
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    // Validate inputs
    if (empty($date)) {
        $errors[] = "Date is required";
    }
    
    if (empty($content)) {
        $errors[] = "Journal content is required";
    }
    
    // If no errors, save the entry
    if (empty($errors)) {
        // Analyze sentiment
        $sentiment = analyzeSentiment($content);
        $score = $sentiment['score'];
        $label = $sentiment['label'];
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing entry
            $entry_id = (int)$_POST['id'];
            $query = "UPDATE journal_entries SET date = ?, content = ?, sentiment_score = ?, sentiment_label = ?, is_private = ? WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssdsiii", $date, $content, $score, $label, $is_private, $entry_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Journal entry updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = "Failed to update entry. Please try again.";
            }
        } else {
            // Insert new entry
            $query = "INSERT INTO journal_entries (user_id, date, content, sentiment_score, sentiment_label, is_private) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "issdsi", $user_id, $date, $content, $score, $label, $is_private);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Journal entry saved successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = "Failed to save entry. Please try again.";
            }
        }
    }
}

// Set dark mode
$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $darkMode ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - AI Journal</title>
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
                <a href="write.php" class="nav-link active">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="calendar.php" class="nav-link">
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
                <a href="write.php" class="nav-link active">
                    <i class="fas fa-pen-to-square nav-link-icon"></i> New Entry
                </a>
                <a href="calendar.php" class="nav-link">
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
                <h1 class="mb-4"><?php echo $pageTitle; ?></h1>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-4">
                        <div class="alert-icon">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Error Saving Entry</div>
                            <ul class="alert-message">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!isset($_GET['id']) && !empty($prompt)): ?>
                    <div class="writing-prompt slide-up mb-5">
                        <div class="writing-prompt-label">
                            <i class="fas fa-lightbulb mr-2"></i> Today's Writing Prompt
                        </div>
                        <p class="writing-prompt-text"><?php echo $prompt; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-5">
                    <form id="journal-form" action="write.php" method="POST">
                        <?php if (isset($entry['id'])): ?>
                            <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="card-header">
                            <div class="d-flex align-center gap-3">
                                <div class="form-group mb-0" style="min-width: 200px;">
                                    <label for="entry-date" class="form-label">Date</label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-calendar"></i>
                                        </span>
                                        <input type="date" id="entry-date" name="date" class="form-control" value="<?php echo $entry['date']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-check ml-3 d-flex align-center">
                                    <input type="checkbox" id="is_private" name="is_private" class="form-check-input" <?php echo $entry['is_private'] ? 'checked' : ''; ?>>
                                    <label for="is_private" class="form-check-label ml-2">Private Entry</label>
                                    <i class="fas fa-question-circle ml-1 text-muted" title="Private entries won't appear in mood statistics and insights"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="form-group">
                                <label for="journal-content" class="form-label">Your Thoughts</label>
                                <textarea id="journal-content" name="content" class="form-control" rows="15" placeholder="Write about your day..." required><?php echo $entry['content']; ?></textarea>
                                <div id="char-counter" class="form-text text-right">0 characters</div>
                            </div>
                        </div>
                        
                        <div class="card-footer d-flex justify-between align-center">
                            <div>
                                <button type="button" class="btn btn-outline" onclick="resetForm()">
                                    <i class="fas fa-rotate-left btn-icon"></i> Reset
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="../index.php" class="btn btn-outline">
                                    <i class="fas fa-times btn-icon"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save btn-icon"></i> Save Entry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Character counter
        const textarea = document.getElementById('journal-content');
        const charCounter = document.getElementById('char-counter');
        
        function updateCharCount() {
            const count = textarea.value.length;
            charCounter.textContent = count + ' characters';
        }
        
        textarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initialize
        
        // Form reset confirmation
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All unsaved changes will be lost.')) {
                document.getElementById('journal-form').reset();
                updateCharCount();
            }
        }
        
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