<?php
// Include database configuration
require_once 'config.php';

/**
 * Sanitize user input
 * @param string $data User input data
 * @return string Sanitized data
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to another page
 * @param string $location URL to redirect to
 */
function redirect($location) {
    header("Location: $location");
    exit;
}

/**
 * Get user information by ID
 * @param int $user_id User ID
 * @return array|bool User data or false if not found
 */
function getUserById($user_id) {
    global $conn;
    $query = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

/**
 * Get user information for the dashboard
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array User information
 */
function getUserInfo($conn, $user_id) {
    $query = "SELECT id, name, email, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    // Return default values if user not found
    return [
        'id' => $user_id,
        'name' => 'User',
        'email' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Get recent journal entries for a user
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $limit Number of entries to retrieve
 * @return array Recent journal entries
 */
function getRecentEntries($conn, $user_id, $limit = 6) {
    $query = "SELECT id, date, content, sentiment_score, sentiment_label, created_at 
              FROM journal_entries 
              WHERE user_id = ? 
              ORDER BY date DESC, created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
    
    return $entries;
}

/**
 * Get user statistics for dashboard display
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array User statistics
 */
function getUserStats($conn, $user_id) {
    // Initialize stats array with default values
    $stats = [
        'total_entries' => 0,
        'entries_this_week' => 0,
        'current_streak' => 0,
        'best_streak' => 0,
        'avg_sentiment' => 0,
        'total_words' => 0,
        'avg_words_per_entry' => 0
    ];
    
    // Get total entries
    $query = "SELECT COUNT(*) as count FROM journal_entries WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['total_entries'] = $row['count'];
    }
    
    // Get entries this week
    $query = "SELECT COUNT(*) as count FROM journal_entries 
              WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['entries_this_week'] = $row['count'];
    }
    
    // Get current streak
    // Use PHP to calculate the current streak instead of complex SQL with window functions
    $today = date('Y-m-d');
    $query = "SELECT DISTINCT date FROM journal_entries WHERE user_id = ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $dates = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dates[] = $row['date'];
    }
    
    $current_streak = 0;
    $yesterday = new DateTime($today);
    $yesterday->modify('-1 day');
    
    // Check if there's an entry for today or yesterday to start the streak
    if (count($dates) > 0) {
        $latest_entry_date = new DateTime($dates[0]);
        $diff_from_today = $latest_entry_date->diff(new DateTime($today))->days;
        $diff_from_yesterday = $latest_entry_date->diff($yesterday)->days;
        
        if ($diff_from_today == 0 || $diff_from_yesterday == 0) {
            $current_streak = 1;
            $prev_date = $latest_entry_date;
            
            // Check consecutive days
            for ($i = 1; $i < count($dates); $i++) {
                $curr_date = new DateTime($dates[$i]);
                $diff = $curr_date->diff($prev_date)->days;
                
                if ($diff == 1) {
                    $current_streak++;
                    $prev_date = $curr_date;
                } else {
                    break;
                }
            }
        }
    }
    
    $stats['current_streak'] = $current_streak;
    
    // Get best streak
    // Use PHP to calculate the best streak instead of complex SQL with window functions
    $query = "SELECT DISTINCT date FROM journal_entries WHERE user_id = ? ORDER BY date ASC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $dates = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dates[] = $row['date'];
    }
    
    $best_streak = 0;
    $current_streak = 0;
    
    for ($i = 0; $i < count($dates); $i++) {
        if ($i == 0) {
            $current_streak = 1;
        } else {
            $prev_date = new DateTime($dates[$i-1]);
            $curr_date = new DateTime($dates[$i]);
            $diff = $prev_date->diff($curr_date);
            
            if ($diff->days == 1) {
                $current_streak++;
            } else {
                $best_streak = max($best_streak, $current_streak);
                $current_streak = 1;
            }
        }
    }
    
    // Don't forget to check the last streak
    $best_streak = max($best_streak, $current_streak);
    $stats['best_streak'] = $best_streak;
    
    // Get average sentiment
    $query = "SELECT AVG(sentiment_score) as avg_score 
              FROM journal_entries 
              WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['avg_sentiment'] = $row['avg_score'] ?: 0;
    }
    
    // Get total words and average words per entry
    $query = "SELECT 
                SUM(LENGTH(content) - LENGTH(REPLACE(content, ' ', '')) + 1) as total_words,
                AVG(LENGTH(content) - LENGTH(REPLACE(content, ' ', '')) + 1) as avg_words
              FROM journal_entries 
              WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['total_words'] = $row['total_words'] ?: 0;
        $stats['avg_words_per_entry'] = $row['avg_words'] ?: 0;
    }
    
    return $stats;
}

/**
 * Get mood trend data for chart display
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $days Number of days to include
 * @return array Mood trend data with labels and values
 */
function getMoodTrendData($conn, $user_id, $days = 7) {
    $labels = [];
    $values = [];
    
    // Generate date labels for the last $days days
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));
    
    $current_date = $start_date;
    while (strtotime($current_date) <= strtotime($end_date)) {
        $labels[] = date('M j', strtotime($current_date));
        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
    }
    
    // Get sentiment scores for each day
    $query = "SELECT 
                date, 
                AVG(sentiment_score) as avg_score
              FROM journal_entries 
              WHERE user_id = ? AND date BETWEEN ? AND ?
              GROUP BY date
              ORDER BY date ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Create an associative array of dates and scores
    $scores_by_date = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $date_label = date('M j', strtotime($row['date']));
        $scores_by_date[$date_label] = $row['avg_score'];
    }
    
    // Fill in values array, using null for days with no entries
    foreach ($labels as $label) {
        if (isset($scores_by_date[$label])) {
            $values[] = $scores_by_date[$label];
        } else {
            $values[] = null;
        }
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

/**
 * Get sentiment label based on score
 * @param float $score Sentiment score
 * @return string Sentiment label (Positive, Neutral, or Negative)
 */
function getSentimentLabel($score) {
    if ($score > 0.1) {
        return "Positive";
    } elseif ($score < -0.1) {
        return "Negative";
    } else {
        return "Neutral";
    }
}

/**
 * Get emoji based on sentiment label
 * @param string $label Sentiment label
 * @return string Emoji representation
 */
function getSentimentEmoji($label) {
    switch ($label) {
        case 'Positive':
            return '😊';
        case 'Neutral':
            return '😐';
        case 'Negative':
            return '😔';
        default:
            return '❓';
    }
}
?> 