<?php
// This file establishes the database connection

// Database connection is already established in config.php,
// but we'll keep this file for organizational purposes and potential
// additional database functionality

// If not already connected, establish the connection
if (!isset($conn) || !$conn) {
    // Include the configuration file if not already included
    if (!defined('DB_HOST')) {
        require_once 'config.php';
    }
    
    // Create connection
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    
    // Set charset
    mysqli_set_charset($conn, "utf8");
}
?> 