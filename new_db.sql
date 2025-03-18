-- Create database
CREATE DATABASE IF NOT EXISTS proyectof2nix;
USE proyectof2nix;

-- Articles table
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    summary TEXT NOT NULL,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    pdf_url VARCHAR(255),
    category VARCHAR(100) NOT NULL,
    author VARCHAR(100),
    date_published DATETIME DEFAULT CURRENT_TIMESTAMP,
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    UNIQUE KEY (slug)
);

-- Users table (for admin access)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (username),
    UNIQUE KEY (email)
);

-- Insert default categories
INSERT INTO categories (name, slug) VALUES
('Campus', 'campus'),
('Academic', 'academic'),
('Sports', 'sports'),
('Events', 'events');

-- Create directories if they don't exist
-- Note: This is a reminder for manual creation, as SQL can't create directories
-- Create these directories after setting up the database:
-- /uploads/
-- /uploads/pdfs/

-- Sample article (optional)
INSERT INTO articles (title, content, summary, category, author, featured) VALUES
('Welcome to Proyecto Fenix', 
'Welcome to our new university news platform. This is a sample article to get you started.', 
'Welcome to our new university news platform.',
'campus',
'Admin',
true);