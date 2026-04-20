CREATE DATABASE IF NOT EXISTS procrastitrack;
USE procrastitrack;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    pw_hash VARCHAR(255) NOT NULL,
    school VARCHAR(100) DEFAULT '',
    xp INT DEFAULT 0,
    level INT DEFAULT 1,
    streak INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100) DEFAULT 'General',
    due_date DATE NULL,
    status VARCHAR(50) DEFAULT 'upcoming',
    done TINYINT(1) DEFAULT 0,
    xp INT DEFAULT 30,
    source VARCHAR(50) DEFAULT 'manual',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert dummy data if desired
INSERT INTO users (id, name, email, pw_hash, xp, level, streak) VALUES 
(1, 'Demo Student', 'demo@student.edu', 'demo_hash', 1240, 7, 5) 
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO tasks (user_id, title, subject, due_date, status, done, xp, source) VALUES 
(1, 'Chapter 6 exercises', 'Math 101', CURRENT_DATE - INTERVAL 1 DAY, 'overdue', 0, 50, 'manual'),
(1, 'Cell division report', 'Biology', CURRENT_DATE + INTERVAL 1 DAY, 'upcoming', 0, 80, 'manual'),
(1, 'Lab report', 'Chemistry', CURRENT_DATE, 'today', 0, 70, 'manual');
