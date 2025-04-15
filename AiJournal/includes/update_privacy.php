<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if required parameters are provided
if (!isset($_POST['entry_id']) || !isset($_POST['is_private'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$entry_id = (int)$_POST['entry_id'];
$is_private = (int)$_POST['is_private'];

// Check if entry exists and belongs to the current user
$query = "SELECT id FROM journal_entries WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    echo json_encode(['success' => false, 'message' => 'Entry not found or not authorized']);
    exit;
}

// Update the privacy setting
$query = "UPDATE journal_entries SET is_private = ? WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iii", $is_private, $entry_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Privacy setting updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update privacy setting']);
}
?> 