-- MomCare AI Assistant Database Schema
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS momcare_ai;
USE momcare_ai;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    age INT,
    weeks_pregnant INT,
    pre_existing_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'bot') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medical documents table
CREATE TABLE medical_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    doctor_name VARCHAR(255),
    location VARCHAR(500),
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(500),
    author VARCHAR(255) DEFAULT 'MomCare Team',
    published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Emergency contacts table
CREATE TABLE emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    relationship VARCHAR(100),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Resources table
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    file_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample blog posts
INSERT INTO blog_posts (title, slug, content, excerpt, published) VALUES 
('Understanding Your First Trimester', 'understanding-first-trimester', 
'<h2>The First Trimester: What to Expect</h2><p>The first trimester of pregnancy is a time of incredible change and development...</p>', 
'Learn about the important changes happening in your first trimester and how to take care of yourself.',
TRUE),

('Nutrition During Pregnancy', 'nutrition-during-pregnancy',
'<h2>Essential Nutrition for You and Your Baby</h2><p>Proper nutrition during pregnancy is crucial for both mother and baby...</p>',
'Discover the essential nutrients you need during pregnancy and the best food sources.',
TRUE),

('Preparing for Labor and Delivery', 'preparing-labor-delivery',
'<h2>Getting Ready for the Big Day</h2><p>As your due date approaches, preparation becomes key...</p>',
'Essential tips and information to help you prepare for labor and delivery.',
TRUE);

-- Insert some sample resources
INSERT INTO resources (title, description, category, url) VALUES
('Pregnancy Nutrition Guide', 'Comprehensive guide to eating well during pregnancy', 'Nutrition', 'https://example.com/nutrition-guide'),
('Exercise During Pregnancy', 'Safe exercises and activities for pregnant women', 'Exercise', 'https://example.com/pregnancy-exercise'),
('Mental Health Support', 'Resources for emotional wellbeing during pregnancy', 'Mental Health', 'https://example.com/mental-health'),
('Labor Preparation Classes', 'Information about childbirth preparation classes', 'Education', 'https://example.com/labor-classes');
