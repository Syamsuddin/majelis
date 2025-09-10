@echo off
REM ====================================
REM eMajelis Windows Deployment Script
REM ====================================

title eMajelis Deployment

echo.
echo ================================
echo  eMajelis Deployment Script
echo ================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 7.4 or higher
    pause
    exit /b 1
)

echo [INFO] PHP detected
php --version

echo.
echo [STEP] Setting up environment...

REM Create .env file if it doesn't exist
if not exist .env (
    echo [INFO] Creating .env file...
    (
        echo # Database Configuration
        echo DB_HOST=localhost
        echo DB_USERNAME=emajelis_user
        echo DB_PASSWORD=change_this_password
        echo DB_DATABASE=emajelis
        echo.
        echo # Application Settings
        echo APP_URL=http://localhost/emajelis
        echo APP_ENV=development
        echo APP_DEBUG=true
        echo.
        echo # Security Keys
        echo SESSION_SECRET=your_random_session_secret_key
        echo ENCRYPTION_KEY=your_32_character_encryption_key
    ) > .env
    echo [SUCCESS] .env file created
) else (
    echo [WARNING] .env file already exists
)

echo.
echo [STEP] Creating .htaccess file...

REM Create .htaccess file
(
    echo # eMajelis .htaccess Configuration
    echo.
    echo # Security Headers
    echo ^<IfModule mod_headers.c^>
    echo     Header always set X-Frame-Options DENY
    echo     Header always set X-Content-Type-Options nosniff
    echo     Header always set X-XSS-Protection "1; mode=block"
    echo ^</IfModule^>
    echo.
    echo # Protect sensitive files
    echo ^<Files ".env"^>
    echo     Order Allow,Deny
    echo     Deny from all
    echo ^</Files^>
    echo.
    echo ^<Files "*.log"^>
    echo     Order Allow,Deny
    echo     Deny from all
    echo ^</Files^>
) > .htaccess

echo [SUCCESS] .htaccess file created

echo.
echo [STEP] Database setup...
echo.
set /p setup_db="Do you want to run database setup now? (y/n): "

if /i "%setup_db%"=="y" (
    if exist setup_database.php (
        echo [INFO] Running database setup...
        php setup_database.php
        echo [SUCCESS] Database setup completed
    ) else (
        echo [WARNING] setup_database.php not found
    )
) else (
    echo [INFO] Skipping database setup
    echo Please run setup_database.php manually or visit:
    echo http://localhost/emajelis/setup_database.php
)

echo.
echo ================================
echo  Deployment Completed!
echo ================================
echo.
echo Next Steps:
echo 1. Visit: http://localhost/emajelis
echo 2. Login with default credentials:
echo    - Admin: admin / admin123
echo    - Operator: operator / operator123
echo 3. Change default passwords immediately
echo 4. Configure .env file for your environment
echo.
echo Security Reminders:
echo - Change default passwords
echo - Review .env file settings
echo - Set up SSL for production
echo.
echo Documentation:
echo - Setup Guide: SETUP.md
echo - User Manual: docs/USER_MANUAL.md
echo.

pause