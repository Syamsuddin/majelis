# eMajelis 🕌
### Religious Gathering Management System

![eMajelis](https://img.shields.io/badge/eMajelis-v2.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-purple.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

A comprehensive web-based system designed specifically for managing religious gatherings (Majelis Ta'lim) with modern features including attendance tracking, donation management, and real-time analytics.

## ✨ Key Features

### 🔐 Multi-Level Authentication
- **Admin**: Full system access and management
- **Operator**: Jemaah and attendance management
- **Jemaah**: Personal profile and attendance history

### 📊 Attendance Management
- QR/Barcode scanning for quick check-in
- Manual attendance entry with date/time selection
- Real-time attendance statistics
- Comprehensive attendance history

### 💰 Donation Management
- Link donations to attendance records
- Period-based filtering (weekly, monthly, yearly)
- Real-time donation analytics
- Average donation calculations

### 📈 Analytics & Reporting
- Real-time dashboard statistics
- Period-based data analysis
- Attendance and donation trends
- Exportable reports

### 🎨 Modern UI/UX
- Professional navy blue theme
- Responsive design for all devices
- Glassmorphism effects and animations
- Intuitive user interface

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Installation
1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/emajelis.git
   cd emajelis
   ```

2. **Setup database**
   - Create MySQL database named `emajelis`
   - Run `setup_database.php` in your browser
   - Or import the provided SQL file

3. **Configure database connection**
   - Update database credentials in PHP files
   - Set proper file permissions

4. **Access the system**
   ```
   http://localhost/emajelis
   ```

### Default Login Credentials
- **Admin**: `admin` / `admin123`
- **Operator**: `operator` / `operator123`

⚠️ **Important**: Change default passwords immediately after installation!

## 📖 Documentation

- **[Complete Setup Guide](SETUP.md)** - Detailed installation and deployment instructions
- **[User Manual](docs/USER_MANUAL.md)** - How to use the system
- **[API Documentation](docs/API.md)** - For developers (if applicable)

## 🏗️ System Architecture

### Database Schema
- **jemaah** - Member information
- **users** - System users and authentication
- **kehadiran** - Attendance records
- **sumbangan** - Donation records

### Technology Stack
- **Backend**: PHP 7.4+, MySQL
- **Frontend**: Bootstrap 5.3.3, JavaScript
- **Icons**: Bootstrap Icons
- **Fonts**: Inter (Google Fonts)

## 📱 Screenshots

### Login Page
![Login](docs/screenshots/login.png)

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Attendance Management
![Attendance](docs/screenshots/attendance.png)

### Donation Management
![Donations](docs/screenshots/donations.png)

## 🔧 Configuration

### Environment Setup
Create `.env` file for production:
```env
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_DATABASE=emajelis
APP_URL=https://yourdomain.com
APP_ENV=production
```

### Security Features
- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- Input sanitization and validation
- Permission-based access control

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Code Standards
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add comments for complex logic
- Maintain consistent indentation

## 📋 Roadmap

### Version 2.1 (Planned)
- [ ] Email notifications
- [ ] Advanced reporting with charts
- [ ] Mobile app integration
- [ ] Multi-language support

### Version 2.2 (Future)
- [ ] API endpoints for third-party integration
- [ ] Advanced analytics dashboard
- [ ] Automated backup system
- [ ] Calendar integration

## 🐛 Known Issues

- None currently reported

## 📞 Support

### Getting Help
1. Check the [documentation](docs/)
2. Search [existing issues](https://github.com/yourusername/emajelis/issues)
3. Create a new issue with:
   - Clear description of the problem
   - Steps to reproduce
   - Environment details
   - Screenshots (if applicable)

### Community
- Join our discussions in the [Discussions](https://github.com/yourusername/emajelis/discussions) tab
- Follow updates and announcements

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Bootstrap Team** - For the excellent CSS framework
- **PHP Community** - For continuous language improvements
- **Muslim Community** - For inspiration and requirements feedback

## 📊 Project Stats

![GitHub repo size](https://img.shields.io/github/repo-size/yourusername/emajelis)
![GitHub contributors](https://img.shields.io/github/contributors/yourusername/emajelis)
![GitHub last commit](https://img.shields.io/github/last-commit/yourusername/emajelis)
![GitHub issues](https://img.shields.io/github/issues/yourusername/emajelis)
![GitHub pull requests](https://img.shields.io/github/issues-pr/yourusername/emajelis)

---

**Made with ❤️ for the Muslim community**

*"And whoever does righteous deeds, whether male or female, while being a believer - those will enter Paradise and will not be wronged, [even as much as] the speck on a date seed."* - Quran 4:124