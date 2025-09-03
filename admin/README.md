# Mary Mother of Mercy School - Admin Panel

## Setup Instructions

### 1. Database Setup
1. Make sure XAMPP is running (Apache and MySQL)
2. Open your browser and go to: `http://localhost/marry_mother_of_mercy/admin/setup_database.php`
3. This will create the database and all required tables
4. You should see "Database setup completed!" message

### 2. Admin Login
- **URL**: `http://localhost/marry_mother_of_mercy/admin/login.php`
- **Username**: `admin`
- **Password**: `mercy2024`

### 3. Features Available

#### Dashboard
- Overview of applications statistics
- Recent applications list
- Quick access to all sections

#### Applications Management
- View all student applications
- Filter by status (Pending, Approved, Rejected)
- Search by student name or application number
- Update application status
- View detailed application information
- Print application forms

#### Website Content Management
- Edit school mission, vision, and objectives
- Update about us content
- Modify contact information
- Real-time content updates

#### Additional Features
- Secure login system
- Responsive design for mobile devices
- Print-friendly application views
- Export functionality for applications

### 4. How Applications Work

1. **Student Submission**: Students fill out the application form on the main website
2. **Database Storage**: Applications are automatically saved to the database
3. **Admin Review**: Admins can view, review, and update application status
4. **Status Tracking**: Applications can be marked as Pending, Approved, or Rejected

### 5. Security Features

- Session-based authentication
- SQL injection protection
- Input sanitization
- Admin-only access to sensitive areas

### 6. File Structure

```
admin/
├── includes/
│   ├── config.php          # Database configuration
│   ├── header.php          # Admin header
│   └── sidebar.php         # Admin sidebar
├── css/
│   └── admin.css           # Admin panel styles
├── login.php               # Admin login page
├── dashboard.php           # Main dashboard
├── applications.php        # Applications management
├── view_application.php    # View single application
├── content.php             # Website content management
├── logout.php              # Logout functionality
└── setup_database.php     # Database setup script
```

### 7. Customization

To change admin credentials, edit the following lines in `admin/includes/config.php`:

```php
define('ADMIN_USERNAME', 'your_username');
define('ADMIN_PASSWORD', 'your_password');
```

### 8. Troubleshooting

**Database Connection Issues:**
- Make sure XAMPP MySQL is running
- Check database credentials in `config.php`
- Ensure the database exists

**Login Issues:**
- Verify username and password
- Clear browser cache and cookies
- Check if sessions are working

**Application Form Not Working:**
- Ensure the database is set up correctly
- Check browser console for JavaScript errors
- Verify the `process_application.php` file exists

### 9. Support

For technical support or questions about the admin panel, please contact the development team.

---

**Note**: This admin panel is designed specifically for Mary Mother of Mercy Pre & Primary School. All features are tailored to the school's requirements and workflow.