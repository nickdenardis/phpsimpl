-- Test Database Schema for PHPSimpl
-- This database demonstrates all major features

CREATE DATABASE IF NOT EXISTS phpsimpl_test;
USE phpsimpl_test;

-- Test users table for CRUD operations
CREATE TABLE IF NOT EXISTS test_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data
INSERT INTO test_users (name, email, phone, status) VALUES
    ('John Doe', 'john@example.com', '555-1234', 'active'),
    ('Jane Smith', 'jane@example.com', '555-5678', 'active'),
    ('Bob Johnson', 'bob@example.com', '555-9012', 'inactive');

-- Posts table for relational testing
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES test_users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample posts
INSERT INTO posts (user_id, title, content) VALUES
    (1, 'First Post', 'This is John\'s first post'),
    (1, 'Second Post', 'This is John\'s second post'),
    (2, 'Jane\'s Post', 'This is Jane\'s post');
