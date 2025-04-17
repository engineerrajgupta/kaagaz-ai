-- AI Journal Database Setup SQL File

-- Create the database
CREATE DATABASE IF NOT EXISTS ai_journal;

-- Select the database
USE ai_journal;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create journal_entries table
CREATE TABLE IF NOT EXISTS journal_entries (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_journal_user_date ON journal_entries(user_id, date);
CREATE INDEX idx_journal_sentiment ON journal_entries(user_id, sentiment_label);

-- Optional: Add a sample user for testing (password: password123)
-- INSERT INTO users (name, email, password_hash) VALUES 
-- ('Test User', 'test@example.com', '$2y$10$6R.me5tX0bF9UudNV/sEPeUzZs/xYJGiy6QKtU7QrKHU3g3lQTwgm'); 