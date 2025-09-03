# Mary Mother of Mercy School Website

A comprehensive school management website with admin panel, student application system, and backup functionality.

## 🏫 About

Mary Mother of Mercy Pre & Primary School is a Catholic institution located in Mjimpya Relini, Dar es Salaam, Tanzania. This website provides:

- **Public Website**: Information about the school, teachers, courses, and contact details
- **Application System**: Online student application form with signature support
- **Admin Panel**: Complete management system for applications, content, and backups
- **Backup System**: Automated database and file backup functionality

## 🚀 Features

### Public Website
- Responsive design with modern UI
- School information and history
- Teacher profiles and courses
- Interactive application form
- Google Maps integration
- Image gallery
- Contact information

### Admin Panel
- Secure login system
- Dashboard with statistics
- Application management
- Teacher management
- Content management
- Gallery management
- Backup & restore system

### Application System
- Comprehensive student application form
- Multiple signature options (draw, upload, type)
- Automatic application number generation
- Email notifications
- Status tracking

### Backup System
- Database backup (SQL export)
- File backup (ZIP archive)
- Full system backup
- Restore functionality
- Automated cleanup

## 📋 Requirements

- **Web Server**: Apache/Nginx
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7 or higher
- **Extensions**: mysqli, zip, gd, json

### XAMPP Setup
This project is optimized for XAMPP development environment:
- XAMPP 8.0 or higher
- Apache and MySQL services running
- PHP extensions enabled

## 🛠️ Installation

### 1. Download and Setup
```bash
# Place files in XAMPP htdocs directory
/Applications/XAMPP/xamppfiles/htdocs/marry_mother_of_mercy/
```

### 2. Database Setup
1. Start XAMPP Apache and MySQL services
2. Visit: `http://localhost/marry_mother_of_mercy/admin/complete_setup.php`
3. Follow the automated setup process

### 3. Manual Database Setup (Alternative)
```sql
-- Create database
CREATE DATABASE marry_mother_mercy_db CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Import tables (automatically created by setup script)
```

### 4. Configuration
- Database settings are in `admin/includes/config.php`
- Default admin credentials:
  - Username: `admin`
  - Password: `mercy2024`

## 📁 Project Structure

```
marry_mother_of_mercy/
├── admin/                      # Admin panel
│   ├── includes/              # Configuration and includes
│   │   ├── config.php         # Database configuration
│   │   ├── header.php         # Admin header
│   │   └── sidebar.php        # Admin sidebar
│   ├── css/                   # Admin styles
│   ├── js/                    # Admin scripts
│   ├── backups/               # Backup storage
│   ├── login.php              # Admin login
│   ├── dashboard.php          # Admin dashboard
│   ├── applications.php       # Application management
│   ├── teachers.php           # Teacher management
│   ├── backup.php             # Backup system
│   └── complete_setup.php     # Setup script
├── css/                       # Public website styles
├── js/                        # Public website scripts
├── images/                    # Image assets
├── uploads/                   # File uploads
│   └── signatures/            # Signature files
├── fonts/                     # Font files
├── index.html                 # Main website
├── about.html                 # About page
├── contact.html               # Contact page
├── teacher.html               # Teachers page
├── courses.html               # Courses page
├── process_application.php    # Application processor
└── README.md                  # This file
```

## 🔧 Usage

### Accessing the Website
- **Main Website**: `http://localhost/marry_mother_of_mercy/`
- **Admin Panel**: `http://localhost/marry_mother_of_mercy/admin/`

### Admin Functions

#### 1. Login
- Navigate to admin panel
- Use default credentials (change after first login)

#### 2. Dashboard
- View application statistics
- Monitor system status
- Quick access to all functions

#### 3. Application Management
- View all applications
- Update application status
- Export application data

#### 4. Backup System
- Create database backups
- Create file backups
- Create full system backups
- Restore from backups
- Download backup files

### Student Applications
1. Visit main website
2. Click "Get Application Form"
3. Fill out the comprehensive form
4. Add signature (draw, upload, or type)
5. Submit application
6. Receive confirmation with application number

## 🔐 Security Features

- SQL injection protection
- XSS prevention
- CSRF protection
- File upload validation
- Admin session management
- Secure password handling

## 🛡️ Backup & Recovery

### Automatic Backups
The system supports three types of backups:

1. **Database Only**: Exports all database tables and data
2. **Files Only**: Creates ZIP archive of website files
3. **Full Backup**: Combines database and files

### Backup Schedule Recommendations
- **Daily**: Database backups
- **Weekly**: Full backups
- **Before Updates**: Complete system backup

### Restore Process
1. Access admin backup panel
2. Select backup file
3. Choose restore type
4. Confirm restoration

## 🎨 Customization

### Styling
- Main styles: `css/style.css`
- Admin styles: `admin/css/admin.css`
- Custom styles in HTML files

### Content Management
- Update school information via admin panel
- Manage teacher profiles
- Update course information
- Modify contact details

### Images
- School logo: `images/marry.jpeg`
- Teacher photos: `images/teacher-*.jpg`
- Gallery images: `images/`

## 📱 Mobile Responsiveness

The website is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🌐 Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## 🔍 Troubleshooting

### Common Issues

#### Database Connection Error
- Check XAMPP MySQL service is running
- Verify database credentials in `config.php`
- Ensure database exists

#### File Upload Issues
- Check directory permissions
- Verify PHP upload settings
- Ensure sufficient disk space

#### Backup Failures
- Check directory write permissions
- Verify PHP extensions (zip, mysqli)
- Ensure sufficient disk space

### Error Logs
- PHP errors: Check XAMPP error logs
- Application errors: Check browser console
- Database errors: Check MySQL logs

## 📞 Support

For technical support or questions:
- **Email**: motherofmercyprimaryschool@gmail.com
- **Phone**: 0784168758
- **Address**: Mjimpya Relini, P.O. Box 12986, Dar es Salaam

## 📄 License

This project is developed for Mary Mother of Mercy School. All rights reserved.

## 🙏 Acknowledgments

- **Development**: JUP TECHNOLOGY
- **School Administration**: Sisters of the Holy Spirit
- **Community**: Mary Mother of Mercy Parish

## 📈 Version History

- **v1.0**: Initial release with basic functionality
- **v1.1**: Added backup system and improved admin panel
- **v1.2**: Enhanced application form and mobile responsiveness
- **v1.3**: Complete system integration and security improvements

---

**© 2024 Mary Mother of Mercy School. All rights reserved.**