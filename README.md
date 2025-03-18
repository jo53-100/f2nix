# Proyecto Fenix - University News System

A PHP-based news management system for universities.

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/YOUR-USERNAME/proyectof2nix.git
```

2. Place the files in your web server directory:
- If using XAMPP: `C:/xampp/htdocs/proyectof2nix`

3. Create the database:
- Open phpMyAdmin (http://localhost/phpmyadmin)
- Create a new database named 'proyectof2nix'
- Import the `new_db.sql` file

4. Configure the database connection:
- Copy `.env.example` to `.env`
- Update the database credentials in `.env`

5. Set up the admin account:
- Visit http://localhost/proyectof2nix/setup_admin.php
- Create your admin account
- Delete setup_admin.php after creating the account

6. Start using the system:
- Frontend: http://localhost/proyectof2nix
- Admin panel: http://localhost/proyectof2nix/admin

## Features
- Article management system
- Category organization
- Media support (images, PDFs)
- YouTube video embedding
- Instagram post/reel embedding
- Responsive design

## Directory Structure
- `/admin` - Administration panel
- `/assets` - CSS, JavaScript, and images
- `/includes` - PHP includes and functions
- `/uploads` - User uploaded files
- `/backup_files` - Backup files (can be deleted)

## Security Notes
- Change default admin password immediately
- Remove setup files after installation
- Keep your PHP version updated
- Regular database backups recommended 