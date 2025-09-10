# eMajelis - Setup & Deployment Guide

![eMajelis](https://img.shields.io/badge/eMajelis-v2.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-purple.svg)

## 📋 Table of Contents
- [Project Overview](#project-overview)
- [Prerequisites](#prerequisites)
- [Local Development Setup](#local-development-setup)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [GitHub Repository Setup](#github-repository-setup)
- [Deployment Steps](#deployment-steps)
- [Environment Variables](#environment-variables)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)

## 🎯 Project Overview

**eMajelis** is a comprehensive religious gathering management system featuring:
- Multi-level user authentication (Admin, Operator, Jemaah)
- Attendance management with barcode scanning
- Donation tracking with period-based filtering
- Real-time analytics and reporting
- Responsive design with navy blue theme

## 🔧 Prerequisites

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache or Nginx
- **Git**: Latest version
- **Composer**: For dependency management (optional)

### Development Tools (Recommended)
- **MAMP/XAMPP/WAMP**: For local development
- **VS Code/PHPStorm**: IDE with PHP support
- **GitHub Desktop**: For easy Git management

## 🚀 Local Development Setup

### 1. Clone or Download Project
```bash
# If using Git
git clone https://github.com/yourusername/emajelis.git
cd emajelis

# Or download and extract ZIP file
```

### 2. Web Server Configuration

#### For MAMP/XAMPP:
1. Copy project folder to `htdocs` directory
2. Start Apache and MySQL services
3. Access via `http://localhost/emajelis`

#### For Apache (Production):
```apache
<VirtualHost *:80>
    ServerName emajelis.yourdomain.com
    DocumentRoot /var/www/html/emajelis
    
    <Directory /var/www/html/emajelis>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/emajelis_error.log
    CustomLog ${APACHE_LOG_DIR}/emajelis_access.log combined
</VirtualHost>
```

### 3. File Permissions (Linux/Mac)
```bash
# Set proper permissions
chmod 755 /path/to/emajelis
chmod 644 /path/to/emajelis/*.php
chmod 666 /path/to/emajelis/config.php  # If using config file
```

## 🗄️ Database Setup

### 1. Create Database
```sql
CREATE DATABASE emajelis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'emajelis_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON emajelis.* TO 'emajelis_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Import Database Schema
```bash
# Run setup script via browser
http://localhost/emajelis/setup_database.php

# Or import manually if you have SQL dump
mysql -u emajelis_user -p emajelis < database/emajelis.sql
```

### 3. Default Admin Account
After setup, login with:
- **Username**: `admin`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change default password immediately!

## ⚙️ Configuration

### Database Configuration
Edit database connection settings in each PHP file or create a centralized config:

```php
// config/database.php
<?php
$db_config = [
    'host' => 'localhost',
    'username' => 'emajelis_user',
    'password' => 'your_secure_password',
    'database' => 'emajelis',
    'charset' => 'utf8mb4'
];
?>
```

### Environment-Specific Settings
Create `.env` file for sensitive information:
```env
# Database Configuration
DB_HOST=localhost
DB_USERNAME=emajelis_user
DB_PASSWORD=your_secure_password
DB_DATABASE=emajelis

# Application Settings
APP_URL=http://localhost/emajelis
APP_ENV=development
APP_DEBUG=true

# Security Keys
SESSION_SECRET=your_random_session_secret_key
ENCRYPTION_KEY=your_32_character_encryption_key
```

## 📚 GitHub Repository Setup

### 1. Create GitHub Repository
1. Go to [GitHub](https://github.com)
2. Click "New repository"
3. Name it `emajelis`
4. Add description: "Religious gathering management system with attendance tracking and donation management"
5. Choose visibility (Public/Private)
6. Initialize with README

### 2. Prepare Project for GitHub

#### Create .gitignore
```gitignore
# Sensitive files
.env
config/database.php
*.log

# IDE files
.vscode/
.idea/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db

# Temporary files
tmp/
temp/
cache/

# Upload directories (if any)
uploads/
files/

# Backup files
*.bak
*.backup
*.sql

# Node modules (if using frontend build tools)
node_modules/
npm-debug.log

# Composer (if used)
vendor/
composer.lock
```

#### Create README.md
```markdown
# eMajelis - Religious Gathering Management System

A comprehensive web-based system for managing religious gatherings, attendance tracking, and donation management.

## Features
- Multi-level user authentication
- Attendance management with barcode scanning
- Donation tracking with period-based filtering
- Real-time analytics and reporting
- Responsive design with modern UI

## Quick Start
See [SETUP.md](SETUP.md) for detailed installation instructions.

## Demo Accounts
- Admin: admin / admin123
- Operator: operator / operator123

## License
MIT License - See LICENSE file for details
```

### 3. Initial Git Setup
```bash
# Initialize Git repository
git init

# Add remote origin
git remote add origin https://github.com/yourusername/emajelis.git

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: eMajelis v2.0 - Religious gathering management system

Features:
- Multi-level authentication (Admin, Operator, Jemaah)
- Attendance management with barcode scanning
- Donation management with period-based filtering
- Real-time analytics and reporting
- Navy blue themed responsive UI
- Complete CRUD operations for all modules"

# Push to GitHub
git push -u origin main
```

## 🚀 Deployment Steps

### 1. Production Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-xml php-curl -y

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2. Clone Repository
```bash
# Clone to web directory
cd /var/www/html
sudo git clone https://github.com/yourusername/emajelis.git
sudo chown -R www-data:www-data emajelis
```

### 3. Database Setup (Production)
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

### 4. Configure Environment
```bash
# Copy environment file
cp .env.example .env
nano .env  # Edit with production values
```

### 5. Set Permissions
```bash
sudo chmod 755 /var/www/html/emajelis
sudo chmod 644 /var/www/html/emajelis/*.php
sudo chown -R www-data:www-data /var/www/html/emajelis
```

## 🔐 Environment Variables

### Production .env Example
```env
# Database Configuration
DB_HOST=localhost
DB_USERNAME=emajelis_prod
DB_PASSWORD=super_secure_production_password
DB_DATABASE=emajelis_production

# Application Settings
APP_URL=https://emajelis.yourdomain.com
APP_ENV=production
APP_DEBUG=false

# Security Keys
SESSION_SECRET=your_64_character_random_session_secret
ENCRYPTION_KEY=your_32_character_encryption_key

# Email Configuration (if needed)
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=email_password
```

## 🔒 Security Considerations

### 1. File Security
```bash
# Protect sensitive files
sudo chmod 600 .env
sudo chmod 600 config/database.php

# Remove setup files in production
rm setup_database.php  # After initial setup
```

### 2. Apache Security (.htaccess)
```apache
# Deny access to sensitive files
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>

<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# Enable security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### 3. PHP Security
```php
// Add to main configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
```

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Connection failed: Access denied for user
```
**Solution**: Check database credentials in configuration files

#### Permission Denied
```
Error: Permission denied
```
**Solution**: 
```bash
sudo chown -R www-data:www-data /var/www/html/emajelis
sudo chmod -R 755 /var/www/html/emajelis
```

#### Session Issues
```
Error: session_start(): Failed to initialize storage module
```
**Solution**: Check PHP session configuration and directory permissions

### Debug Mode
Enable debug mode in development:
```php
// Add to main files
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support

### Documentation
- Technical documentation in `/docs` folder
- API documentation (if applicable)
- User manual for administrators

### Getting Help
1. Check [Issues](https://github.com/yourusername/emajelis/issues) page
2. Create new issue with:
   - Error details
   - Steps to reproduce
   - Environment information

### Contributing
1. Fork the repository
2. Create feature branch
3. Make changes
4. Submit pull request

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 🔄 Version History

- **v2.0.0** - Complete rewrite with modern UI and enhanced features
- **v1.0.0** - Initial release

---

**Made with ❤️ for religious communities**