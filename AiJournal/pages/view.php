<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Check if ID parameter is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No entry specified to view.";
    $_SESSION['message_type'] = "danger";
    redirect('../index.php');
}

$entry_id = (int)$_GET['id'];

// Get the journal entry
$query = "SELECT * FROM journal_entries WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['message'] = "Entry not found or you don't have permission to view it.";
    $_SESSION['message_type'] = "danger";
    redirect('../index.php');
}

$entry = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Entry - AI Journal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : ''; ?>">
    <header>
        <div class="container">
            <div class="logo">
                <h1>AI Journal</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="write.php">New Entry</a></li>
                    <li><a href="calendar.php">Calendar</a></li>
                    <li><a href="insights.php">Insights</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
            <div class="theme-toggle">
                <button id="theme-toggle-btn">
                    <span class="light-icon">🌞</span>
                    <span class="dark-icon">🌙</span>
                </button>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="entry-nav">
            <a href="../index.php">&laquo; Back to Dashboard</a>
        </div>
        
        <div class="entry-container">
            <div class="entry-header">
                <h1><?php echo date('F j, Y', strtotime($entry['date'])); ?></h1>
                
                <div class="entry-meta">
                    <span class="sentiment">
                        Mood: <?php echo getSentimentEmoji($entry['sentiment_label']); ?> <?php echo $entry['sentiment_label']; ?>
                    </span>
                    <span class="privacy-status">
                        <?php echo $entry['is_private'] ? 'Private Entry' : 'Public Entry'; ?>
                    </span>
                </div>
            </div>
            
            <div class="entry-content">
                <?php echo nl2br(htmlspecialchars($entry['content'])); ?>
            </div>
            
            <div class="entry-actions">
                <a href="write.php?id=<?php echo $entry['id']; ?>" class="btn">Edit</a>
                <a href="delete.php?id=<?php echo $entry['id']; ?>" class="btn delete-entry">Delete</a>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AI Journal - Your Personal Reflections</p>
        </div>
    </footer>
    
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html> 