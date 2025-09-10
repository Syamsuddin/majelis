#!/bin/bash

# ====================================
# eMajelis GitHub Setup Script
# ====================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_NAME="emajelis"

# Functions
print_header() {
    echo -e "${BLUE}=================================${NC}"
    echo -e "${BLUE}  eMajelis GitHub Setup Script${NC}"
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

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

check_git() {
    print_step "Checking Git installation..."
    
    if ! command -v git >/dev/null 2>&1; then
        print_error "Git is not installed. Please install Git and try again."
        exit 1
    fi
    
    print_success "Git is installed: $(git --version)"
    echo ""
}

setup_git_config() {
    print_step "Setting up Git configuration..."
    
    # Check if user.name and user.email are set
    if ! git config user.name >/dev/null 2>&1; then
        read -p "Enter your Git username: " git_username
        git config --global user.name "$git_username"
    fi
    
    if ! git config user.email >/dev/null 2>&1; then
        read -p "Enter your Git email: " git_email
        git config --global user.email "$git_email"
    fi
    
    print_info "Git user: $(git config user.name) <$(git config user.email)>"
    echo ""
}

init_repository() {
    print_step "Initializing Git repository..."
    
    # Initialize Git repository if not already initialized
    if [ ! -d .git ]; then
        git init
        print_success "Git repository initialized"
    else
        print_info "Git repository already exists"
    fi
    
    # Set default branch to main
    git branch -M main
    echo ""
}

create_gitignore() {
    print_step "Setting up .gitignore..."
    
    if [ ! -f .gitignore ]; then
        print_warning ".gitignore file not found!"
        echo "Please ensure .gitignore file exists."
        return 1
    fi
    
    print_success ".gitignore file exists"
    echo ""
}

add_remote_origin() {
    print_step "Setting up remote repository..."
    
    echo "Please create a repository on GitHub first:"
    echo "1. Go to https://github.com/new"
    echo "2. Repository name: $PROJECT_NAME"
    echo "3. Description: Religious gathering management system with attendance tracking and donation management"
    echo "4. Choose Public or Private"
    echo "5. Do NOT initialize with README, .gitignore, or license"
    echo ""
    
    read -p "Enter your GitHub username: " github_username
    read -p "Repository name [$PROJECT_NAME]: " repo_name
    repo_name=${repo_name:-$PROJECT_NAME}
    
    REMOTE_URL="https://github.com/$github_username/$repo_name.git"
    
    # Check if remote origin already exists
    if git remote get-url origin >/dev/null 2>&1; then
        print_warning "Remote origin already exists"
        current_origin=$(git remote get-url origin)
        echo "Current origin: $current_origin"
        
        read -p "Do you want to update the origin URL? (y/n): " -n 1 -r
        echo ""
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            git remote set-url origin "$REMOTE_URL"
            print_success "Remote origin updated"
        fi
    else
        git remote add origin "$REMOTE_URL"
        print_success "Remote origin added: $REMOTE_URL"
    fi
    
    echo ""
}

prepare_files() {
    print_step "Preparing files for commit..."
    
    # Make deploy scripts executable
    if [ -f deploy.sh ]; then
        chmod +x deploy.sh
        print_info "Made deploy.sh executable"
    fi
    
    # Create docs directory if it doesn't exist
    if [ ! -d docs ]; then
        mkdir docs
        print_info "Created docs directory"
        
        # Create placeholder files
        echo "# User Manual" > docs/USER_MANUAL.md
        echo "# API Documentation" > docs/API.md
        mkdir -p docs/screenshots
        echo "Place screenshots here" > docs/screenshots/README.md
    fi
    
    # Remove setup file from version control (if exists)
    if [ -f setup_database.php ]; then
        echo "setup_database.php" >> .gitignore
        print_warning "Added setup_database.php to .gitignore"
        print_info "Remember to run setup_database.php on the target server"
    fi
    
    print_success "Files prepared"
    echo ""
}

create_initial_commit() {
    print_step "Creating initial commit..."
    
    # Add all files
    git add .
    
    # Check if there are changes to commit
    if git diff --staged --quiet; then
        print_warning "No changes to commit"
        return 0
    fi
    
    # Create detailed commit message
    COMMIT_MESSAGE="Initial commit: eMajelis v2.0 - Religious gathering management system

Features implemented:
✨ Multi-level authentication system (Admin, Operator, Jemaah)
📊 Comprehensive attendance management with barcode scanning
💰 Advanced donation management with period-based filtering
📈 Real-time analytics and reporting dashboard
🎨 Modern navy blue themed responsive UI with glassmorphism effects
🔐 Security features with prepared statements and input validation
📱 Mobile-responsive design with Bootstrap 5.3.3
🔧 Complete CRUD operations for all modules
📋 Pagination and advanced filtering capabilities
🌟 Professional UI/UX with smooth animations

Technical Stack:
- Backend: PHP 7.4+, MySQL 5.7+
- Frontend: Bootstrap 5.3.3, JavaScript ES6
- Security: Password hashing, SQL injection prevention
- Database: Normalized schema with foreign key constraints
- UI Framework: Bootstrap Icons, Google Fonts (Inter)

Project Structure:
- halaman/ - Page modules (dashboard, users, attendance, donations)
- auth_functions.php - Authentication and authorization
- setup_database.php - Database initialization script
- index.php - Main application controller
- login.php - Authentication interface

Deployment:
- Includes deployment scripts for Linux/Mac (deploy.sh) and Windows (deploy.bat)
- Comprehensive documentation in SETUP.md
- Contributing guidelines in CONTRIBUTING.md
- Environment configuration with .env support

This system is designed specifically for Islamic religious gatherings (Majelis Ta'lim)
and provides comprehensive management tools for attendance tracking, donation
management, and community analytics."
    
    git commit -m "$COMMIT_MESSAGE"
    print_success "Initial commit created"
    echo ""
}

push_to_github() {
    print_step "Pushing to GitHub..."
    
    echo "Attempting to push to GitHub..."
    echo "If prompted for authentication:"
    echo "- Use your GitHub username"
    echo "- Use a Personal Access Token as password (not your GitHub password)"
    echo ""
    echo "To create a Personal Access Token:"
    echo "1. Go to GitHub Settings > Developer settings > Personal access tokens"
    echo "2. Generate new token with 'repo' permissions"
    echo ""
    
    read -p "Press Enter to continue with push..."
    
    if git push -u origin main; then
        print_success "Successfully pushed to GitHub!"
        echo ""
        echo -e "${GREEN}Repository URL:${NC} https://github.com/$github_username/$repo_name"
    else
        print_error "Failed to push to GitHub"
        echo ""
        echo "Common solutions:"
        echo "1. Check your internet connection"
        echo "2. Verify repository exists on GitHub"
        echo "3. Ensure you have push permissions"
        echo "4. Use Personal Access Token for authentication"
        echo ""
        echo "You can try pushing manually later with:"
        echo "git push -u origin main"
        return 1
    fi
    
    echo ""
}

create_release_notes() {
    print_step "Creating release notes..."
    
    cat > RELEASE_NOTES.md << 'EOF'
# eMajelis v2.0 Release Notes

## 🎉 Initial Release

This is the initial release of eMajelis, a comprehensive religious gathering management system designed specifically for Islamic communities.

### ✨ Key Features

#### Authentication & Authorization
- Multi-level user system (Admin, Operator, Jemaah)
- Secure password hashing with PHP's password_hash()
- Session-based authentication
- Role-based access control

#### Attendance Management
- QR/Barcode scanning for quick check-in
- Manual attendance entry with date/time selection
- Real-time attendance statistics
- Comprehensive attendance history with pagination
- Export capabilities

#### Donation Management
- Link donations to attendance records
- Period-based filtering (weekly, monthly, yearly)
- Real-time donation analytics
- Average donation calculations
- Advanced search and filtering

#### Analytics & Reporting
- Real-time dashboard statistics
- Period-based data analysis
- Attendance and donation trends
- Interactive charts and graphs

#### User Interface
- Professional navy blue theme
- Responsive design for all devices
- Glassmorphism effects and smooth animations
- Bootstrap 5.3.3 framework
- Mobile-first approach

### 🔧 Technical Specifications

- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: Bootstrap 5.3.3, JavaScript ES6
- **Security**: Prepared statements, input validation, CSRF protection
- **Database**: Normalized schema with foreign key constraints
- **Deployment**: Docker support, automated deployment scripts

### 📦 Installation

See [SETUP.md](SETUP.md) for detailed installation instructions.

### 🔐 Default Credentials

- **Admin**: admin / admin123
- **Operator**: operator / operator123

⚠️ **Important**: Change default passwords immediately after installation!

### 🐛 Known Issues

None currently reported.

### 🔮 Roadmap

#### Version 2.1 (Planned)
- Email notifications
- Advanced reporting with charts
- Mobile app integration
- Multi-language support

#### Version 2.2 (Future)
- API endpoints for third-party integration
- Advanced analytics dashboard
- Automated backup system
- Calendar integration

### 📞 Support

- Documentation: [SETUP.md](SETUP.md)
- Issues: [GitHub Issues](https://github.com/yourusername/emajelis/issues)
- Discussions: [GitHub Discussions](https://github.com/yourusername/emajelis/discussions)

### 🙏 Acknowledgments

Special thanks to the Muslim community for inspiration and requirements feedback.

---

**Made with ❤️ for the Muslim community**
EOF
    
    git add RELEASE_NOTES.md
    git commit -m "docs: add release notes for v2.0"
    
    print_success "Release notes created"
    echo ""
}

show_completion_info() {
    print_success "GitHub setup completed successfully!"
    echo ""
    echo -e "${PURPLE}═══════════════════════════════════════${NC}"
    echo -e "${PURPLE}           SETUP COMPLETE!              ${NC}"
    echo -e "${PURPLE}═══════════════════════════════════════${NC}"
    echo ""
    echo -e "${BLUE}Repository Information:${NC}"
    echo "• Repository URL: https://github.com/$github_username/$repo_name"
    echo "• Clone URL: git clone https://github.com/$github_username/$repo_name.git"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    echo "1. 🌐 Visit your repository on GitHub"
    echo "2. 📝 Update repository description and topics"
    echo "3. ⭐ Add repository topics: php, mysql, bootstrap, religious, attendance, donation"
    echo "4. 📖 Review and customize README.md if needed"
    echo "5. 🔧 Set up GitHub Pages (if desired)"
    echo "6. 🏷️ Create your first release tag"
    echo ""
    echo -e "${BLUE}Repository Features to Configure:${NC}"
    echo "• Issues: Enable for bug tracking"
    echo "• Wiki: Enable for additional documentation"
    echo "• Discussions: Enable for community support"
    echo "• Actions: Set up CI/CD workflows"
    echo "• Security: Configure security policies"
    echo ""
    echo -e "${BLUE}Recommended GitHub Settings:${NC}"
    echo "• Branch protection rules for main branch"
    echo "• Require pull request reviews"
    echo "• Dismiss stale reviews when new commits are pushed"
    echo "• Require status checks to pass"
    echo ""
    echo -e "${YELLOW}Security Reminders:${NC}"
    echo "• Never commit sensitive data (.env files, passwords)"
    echo "• Use GitHub secrets for sensitive CI/CD variables"
    echo "• Enable two-factor authentication on GitHub"
    echo "• Regularly update dependencies"
    echo ""
    echo -e "${GREEN}Documentation Available:${NC}"
    echo "• SETUP.md - Complete setup and deployment guide"
    echo "• CONTRIBUTING.md - Guidelines for contributors"
    echo "• README.md - Project overview and quick start"
    echo "• RELEASE_NOTES.md - Version history and features"
    echo ""
    echo -e "${BLUE}Share Your Project:${NC}"
    echo "• Add project to your GitHub profile README"
    echo "• Share with the Muslim developer community"
    echo "• Consider submitting to awesome lists"
    echo "• Write blog posts about your implementation"
    echo ""
    echo "Happy coding! 🚀"
}

# Main execution
main() {
    print_header
    
    # Change to script directory
    cd "$SCRIPT_DIR"
    
    # Run setup steps
    check_git
    setup_git_config
    init_repository
    create_gitignore
    prepare_files
    add_remote_origin
    create_initial_commit
    push_to_github
    create_release_notes
    
    # Push release notes
    if git push origin main; then
        print_success "Release notes pushed successfully"
    fi
    
    show_completion_info
}

# Check if script is run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi