<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Destroy session
session_destroy();

// Redirect to login page with message
session_start();
$_SESSION['message'] = "You have been logged out successfully.";
$_SESSION['message_type'] = "success";
redirect('login.php');
?> 