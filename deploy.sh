#!/bin/bash

# ====================================
# eMajelis Deployment Script
# ====================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_NAME="emajelis"
REQUIRED_PHP_VERSION="7.4"

# Functions
print_header() {
    echo -e "${BLUE}=================================${NC}"
    echo -e "${BLUE}  eMajelis Deployment Script${NC}"
    echo -e "${BLUE}=================================${NC}"
    echo ""
}

print_step() {
    echo -e "${GREEN}[STEP]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

check_requirements() {
    print_step "Checking system requirements..."
    
    # Check PHP
    if command -v php >/dev/null 2>&1; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        echo "PHP Version: $PHP_VERSION"
        
        if php -r "exit(version_compare(PHP_VERSION, '$REQUIRED_PHP_VERSION', '<'));"; then
            print_error "PHP $REQUIRED_PHP_VERSION or higher is required. Found: $PHP_VERSION"
            exit 1
        fi
    else
        print_error "PHP is not installed"
        exit 1
    fi
    
    # Check MySQL
    if command -v mysql >/dev/null 2>&1; then
        echo "MySQL: $(mysql --version)"
    else
        print_warning "MySQL client not found. Make sure MySQL server is available."
    fi
    
    # Check Git
    if command -v git >/dev/null 2>&1; then
        echo "Git: $(git --version)"
    else
        print_warning "Git not found. Manual download required."
    fi
    
    print_success "Requirements check completed"
    echo ""
}

setup_environment() {
    print_step "Setting up environment..."
    
    # Create .env file if it doesn't exist
    if [ ! -f .env ]; then
        print_step "Creating .env file..."
        cat > .env << EOF
# Database Configuration
DB_HOST=localhost
DB_USERNAME=emajelis_user
DB_PASSWORD=change_this_password
DB_DATABASE=emajelis

# Application Settings
APP_URL=http://localhost/emajelis
APP_ENV=development
APP_DEBUG=true

# Security Keys
SESSION_SECRET=$(openssl rand -base64 32)
ENCRYPTION_KEY=$(openssl rand -base64 32)
EOF
        print_success ".env file created"
    else
        print_warning ".env file already exists"
    fi
    
    echo ""
}

setup_permissions() {
    print_step "Setting up file permissions..."
    
    # Set directory permissions
    find . -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find . -type f -name "*.php" -exec chmod 644 {} \;
    
    # Protect sensitive files
    if [ -f .env ]; then
        chmod 600 .env
    fi
    
    print_success "Permissions set"
    echo ""
}

create_database() {
    print_step "Database setup..."
    
    read -p "Do you want to create the database now? (y/n): " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "MySQL root password: " -s MYSQL_ROOT_PASSWORD
        echo ""
        
        read -p "Database name [emajelis]: " DB_NAME
        DB_NAME=${DB_NAME:-emajelis}
        
        read -p "Database user [emajelis_user]: " DB_USER
        DB_USER=${DB_USER:-emajelis_user}
        
        read -p "Database password: " -s DB_PASSWORD
        echo ""
        
        # Create database and user
        mysql -u root -p$MYSQL_ROOT_PASSWORD << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
        
        # Update .env file
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
        
        print_success "Database created successfully"
    else
        print_warning "Skipping database creation"
    fi
    
    echo ""
}

setup_webserver() {
    print_step "Web server configuration..."
    
    # Create .htaccess file
    cat > .htaccess << 'EOF'
# eMajelis .htaccess Configuration

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Protect sensitive files
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.log">
    Order Allow,Deny
    Deny from all
</Files>

<FilesMatch "\.(md|txt)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# URL Rewriting (if needed)
RewriteEngine On

# Redirect to HTTPS in production
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
EOF
    
    print_success ".htaccess file created"
    echo ""
}

run_initial_setup() {
    print_step "Running initial application setup..."
    
    # Check if setup_database.php exists
    if [ -f "setup_database.php" ]; then
        echo "Please run the database setup by visiting:"
        echo "http://localhost/emajelis/setup_database.php"
        echo ""
        echo "Or run it via command line:"
        echo "php setup_database.php"
        echo ""
        
        read -p "Run setup_database.php now? (y/n): " -n 1 -r
        echo ""
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php setup_database.php
            print_success "Database setup completed"
        fi
    else
        print_warning "setup_database.php not found"
    fi
    
    echo ""
}

show_completion_info() {
    print_success "Deployment completed successfully!"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    echo "1. Visit your application: http://localhost/emajelis"
    echo "2. Login with default credentials:"
    echo "   - Admin: admin / admin123"
    echo "   - Operator: operator / operator123"
    echo "3. Change default passwords immediately"
    echo "4. Configure your environment in .env file"
    echo ""
    echo -e "${YELLOW}Security Reminders:${NC}"
    echo "- Change default passwords"
    echo "- Review .env file settings"
    echo "- Set up SSL certificate for production"
    echo "- Configure firewall rules"
    echo ""
    echo -e "${BLUE}Documentation:${NC}"
    echo "- Setup Guide: SETUP.md"
    echo "- User Manual: docs/USER_MANUAL.md"
    echo ""
}

# Main execution
main() {
    print_header
    
    # Change to script directory
    cd "$SCRIPT_DIR"
    
    # Run deployment steps
    check_requirements
    setup_environment
    setup_permissions
    create_database
    setup_webserver
    run_initial_setup
    show_completion_info
}

# Check if script is run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi