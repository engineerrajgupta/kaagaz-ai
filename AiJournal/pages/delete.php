<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Check if ID parameter is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No entry specified for deletion.";
    $_SESSION['message_type'] = "danger";
    redirect('../index.php');
}

$entry_id = (int)$_GET['id'];

// Check if entry exists and belongs to the current user
$query = "SELECT id FROM journal_entries WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    $_SESSION['message'] = "Entry not found or you don't have permission to delete it.";
    $_SESSION['message_type'] = "danger";
    redirect('../index.php');
}

// Handle form submission (confirm deletion)
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    $query = "DELETE FROM journal_entries WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Journal entry deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete entry. Please try again.";
        $_SESSION['message_type'] = "danger";
    }
    
    redirect('../index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Entry - AI Journal</title>
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
        <h1>Delete Journal Entry</h1>
        
        <div class="form-container">
            <p>Are you sure you want to delete this journal entry? This action cannot be undone.</p>
            
            <form action="delete.php?id=<?php echo $entry_id; ?>" method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirm_delete" value="yes" class="btn btn-danger">Yes, Delete Entry</button>
                    <a href="../index.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AI Journal - Your Personal Reflections</p>
        </div>
    </footer>
    
    <script src="../assets/js/theme.js"></script>
</body>
</html> 