# Contributing to eMajelis

Thank you for your interest in contributing to eMajelis! This document provides guidelines and information for contributors.

## 📋 Table of Contents
- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contribution Guidelines](#contribution-guidelines)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Submitting Changes](#submitting-changes)
- [Bug Reports](#bug-reports)
- [Feature Requests](#feature-requests)

## 🤝 Code of Conduct

We are committed to providing a welcoming and inclusive environment for all contributors. Please be respectful and professional in all interactions.

### Our Standards
- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Git
- Basic knowledge of PHP, HTML, CSS, and JavaScript
- Familiarity with Bootstrap framework

### Development Setup
1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/yourusername/emajelis.git
   cd emajelis
   ```
3. Set up the upstream remote:
   ```bash
   git remote add upstream https://github.com/originalowner/emajelis.git
   ```
4. Follow the setup instructions in [SETUP.md](SETUP.md)

## 🛠️ Development Setup

### Local Environment
1. Set up MAMP/XAMPP or your preferred local server
2. Create a development database
3. Configure your `.env` file for development
4. Run the database setup script

### Recommended Tools
- **IDE**: VS Code, PHPStorm, or similar
- **Database**: MySQL Workbench, phpMyAdmin
- **Version Control**: Git with a GUI client (optional)

## 📝 Contribution Guidelines

### Types of Contributions
We welcome the following types of contributions:
- Bug fixes
- Feature enhancements
- Documentation improvements
- Performance optimizations
- Security improvements
- UI/UX enhancements

### Before You Start
1. Check existing [issues](https://github.com/originalowner/emajelis/issues) and [pull requests](https://github.com/originalowner/emajelis/pulls)
2. Create an issue to discuss major changes before implementing
3. Ensure your idea aligns with the project's goals

### Development Workflow
1. Create a feature branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Make your changes following our coding standards
3. Test your changes thoroughly
4. Commit your changes with clear messages
5. Push your branch to your fork
6. Create a pull request

## 🎯 Coding Standards

### PHP Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add docblock comments for functions and classes
- Use prepared statements for database queries
- Validate and sanitize all user inputs

### Frontend Standards
- Use Bootstrap classes consistently
- Follow existing CSS naming conventions
- Maintain responsive design principles
- Optimize for performance
- Ensure cross-browser compatibility

### File Organization
- Place new PHP files in appropriate directories
- Use consistent file naming (lowercase with underscores)
- Keep files focused and modular
- Separate concerns appropriately

### Code Examples

#### PHP Function Documentation
```php
/**
 * Get user by ID with optional jemaah data
 * 
 * @param mysqli $koneksi Database connection
 * @param int $user_id User ID to retrieve
 * @param bool $include_jemaah Include jemaah data if available
 * @return array|null User data or null if not found
 */
function getUserById($koneksi, $user_id, $include_jemaah = false) {
    // Implementation here
}
```

#### Database Query Standards
```php
// Good - Using prepared statements
$stmt = $koneksi->prepare("SELECT * FROM users WHERE id = ? AND status = ?");
$stmt->bind_param("is", $user_id, $status);
$stmt->execute();
$result = $stmt->get_result();

// Bad - Direct string concatenation
$query = "SELECT * FROM users WHERE id = " . $user_id;
```

## 🧪 Testing Guidelines

### Manual Testing
- Test all modified functionality
- Check different user roles (admin, operator, jemaah)
- Verify responsive design on different screen sizes
- Test form validations and error handling
- Ensure data integrity

### Test Cases to Cover
- User authentication and authorization
- CRUD operations for all entities
- Form submissions and validations
- Filter and search functionality
- Pagination and data display

### Browser Testing
Test on the following browsers:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## 📤 Submitting Changes

### Pull Request Process
1. Ensure your branch is up to date with `main`:
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```
2. Push your changes to your fork
3. Create a pull request with a descriptive title
4. Fill out the pull request template completely
5. Link any related issues

### Pull Request Template
```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Security enhancement

## Testing
- [ ] Manual testing completed
- [ ] All existing functionality verified
- [ ] New functionality tested

## Screenshots (if applicable)
Add screenshots for UI changes.

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated if needed
- [ ] No breaking changes (or clearly documented)
```

### Commit Message Format
Use clear and descriptive commit messages:
```
type(scope): brief description

Longer description if needed.

Fixes #issue-number
```

Examples:
```
feat(donations): add period-based filtering
fix(auth): resolve session timeout issue
docs(readme): update installation instructions
```

## 🐛 Bug Reports

### Before Reporting
1. Check if the bug has already been reported
2. Verify the bug exists in the latest version
3. Try to reproduce the bug consistently

### Bug Report Template
```markdown
**Bug Description**
Clear description of the bug.

**Steps to Reproduce**
1. Go to '...'
2. Click on '...'
3. See error

**Expected Behavior**
What should happen.

**Actual Behavior**
What actually happens.

**Environment**
- OS: [e.g., Windows 10, macOS, Ubuntu]
- Browser: [e.g., Chrome 91, Firefox 89]
- PHP Version: [e.g., 7.4.3]
- MySQL Version: [e.g., 8.0.25]

**Screenshots**
If applicable, add screenshots.

**Additional Context**
Any other relevant information.
```

## 💡 Feature Requests

### Guidelines for Feature Requests
1. Check if the feature has been requested before
2. Describe the problem you're trying to solve
3. Explain the proposed solution
4. Consider alternative solutions
5. Discuss potential impact on existing users

### Feature Request Template
```markdown
**Problem Statement**
What problem does this feature solve?

**Proposed Solution**
Describe your proposed solution.

**Alternative Solutions**
Any alternative approaches considered?

**Additional Context**
Screenshots, mockups, or examples.

**Implementation Considerations**
Technical considerations or constraints.
```

## 🎨 Design Guidelines

### UI/UX Principles
- Maintain consistency with existing design
- Follow Islamic design principles where applicable
- Ensure accessibility (WCAG guidelines)
- Prioritize user experience
- Keep interfaces clean and intuitive

### Color Scheme
- Primary: Navy blue gradient (#0f172a → #64748b)
- Success: Green (#28a745)
- Warning: Yellow (#ffc107)
- Danger: Red (#dc3545)
- Info: Blue (#17a2b8)

## 📚 Resources

### Documentation
- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [MDN Web Docs](https://developer.mozilla.org/)

### Tools
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan](https://phpstan.org/)
- [Composer](https://getcomposer.org/)

## 🤔 Questions?

If you have questions about contributing:
1. Check the [discussions](https://github.com/originalowner/emajelis/discussions)
2. Create a new discussion
3. Reach out to maintainers

## 📄 License

By contributing, you agree that your contributions will be licensed under the same MIT License that covers the project.

---

Thank you for contributing to eMajelis! Your efforts help make this system better for the entire Muslim community. 🙏