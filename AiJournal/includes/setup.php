<?php
// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';

// Connect to MySQL without database
$conn = mysqli_connect($host, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS ai_journal";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, 'ai_journal');

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully<br>";
} else {
    die("Error creating users table: " . mysqli_error($conn));
}

// Create journal_entries table
$sql = "CREATE TABLE IF NOT EXISTS journal_entries (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    date DATE NOT NULL,
    content TEXT NOT NULL,
    sentiment_score FLOAT,
    sentiment_label VARCHAR(20),
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Journal entries table created successfully<br>";
} else {
    die("Error creating journal entries table: " . mysqli_error($conn));
}

echo "Database setup completed!";

// Close connection
mysqli_close($conn);
?> 