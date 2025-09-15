# 🔐 Mary Mother of Mercy School - Admin Login Guide

## 📋 Quick Reference

### **Admin Login Credentials**
- **URL:** `http://localhost/marry_mother_of_mercy/admin/login.php`
- **Username:** `admin`
- **Password:** `mercy2024`

---

## 🚀 Getting Started

### **Prerequisites**
1. **XAMPP Running:**
   - ✅ Apache server must be running
   - ✅ MySQL database must be running
   - Check XAMPP Control Panel - both should show "Running" status

### **Step-by-Step Login Process**

1. **Start XAMPP Services:**
   ```
   Open XAMPP Control Panel
   Click "Start" for Apache
   Click "Start" for MySQL
   ```

2. **Access Admin Login:**
   ```
   Open your web browser
   Navigate to: http://localhost/marry_mother_of_mercy/admin/login.php
   ```

3. **Enter Login Credentials:**
   ```
   Username: admin
   Password: mercy2024
   ```

4. **Access Dashboard:**
   ```
   After successful login, you'll be redirected to:
   http://localhost/marry_mother_of_mercy/admin/dashboard.php
   ```

---

### **📊 Admin Panel Features:**

Once logged in, you'll have access to:

#### **🏠 Dashboard**
- Applications statistics overview
- Recent applications list
- Quick access to all sections

#### **📋 Applications Management**
- View all student applications submitted through the website
- Filter by status (Pending, Approved, Rejected)
- Search by student name or application number
- Update application status
- View detailed application information
- Print application forms

#### **📞 Contact Information Management**
- Update school contact details
- Modify phone numbers and addresses
- Edit email addresses

---

## 🔧 Database Setup (First Time Only)

If the admin panel doesn't work initially, set up the database:

### **Automatic Setup:**
```
1. Go to: http://localhost/marry_mother_of_mercy/admin/complete_setup.php
2. Wait for "Database setup completed!" message
3. Database and all tables will be created automatically
```

### **Manual Setup (if needed):**
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: marry_mother_mercy_db
3. Import SQL file if provided
```

---

## 🔒 Security & Access Control

### **Session Management**
- Admin sessions expire after inactivity
- Secure session-based authentication
- Automatic logout on browser close

### **Access Protection**
- All admin pages require login
- SQL injection protection enabled
- Input sanitization on all forms
- Admin-only access to sensitive areas

### **Logout Process**
```
To logout safely:
1. Click "Logout" in admin panel, OR
2. Go to: http://localhost/marry_mother_of_mercy/admin/logout.php
```

---

## ⚙️ Configuration & Customization

### **Changing Admin Credentials**
Edit file: `admin/includes/config.php`

```php
// Find these lines and change values:
define('ADMIN_USERNAME', 'your_new_username');
define('ADMIN_PASSWORD', 'your_new_password');
```

### **Database Configuration**
Edit file: `admin/includes/config.php`

```php
// Database settings:
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'marry_mother_mercy_db');
```

---

## 📱 Mobile Access

The admin panel is fully responsive and works on:
- ✅ Desktop computers
- ✅ Tablets
- ✅ Mobile phones
- ✅ All modern browsers

---

## 🔍 Application Form Integration

### **How Online Applications Work:**

1. **Student Submission:**
   - Students fill form on main website
   - Click "Get Application Form" button
   - Complete all required fields
   - Submit application online

2. **Database Storage:**
   - Applications automatically saved to database
   - Unique application number generated
   - Email notifications sent (if configured)

3. **Admin Review:**
   - View applications in admin panel
   - Review student information
   - Update application status
   - Print forms for physical filing

4. **Status Management:**
   - **Pending:** New applications awaiting review
   - **Approved:** Accepted students
   - **Rejected:** Declined applications

---

## 🚨 Troubleshooting

### **Common Issues & Solutions:**

#### **Cannot Access Admin Login Page**
```
Problem: Page not loading
Solutions:
1. Check XAMPP - ensure Apache is running
2. Verify URL: http://localhost/marry_mother_of_mercy/admin/login.php
3. Clear browser cache
4. Try different browser
```

#### **Login Credentials Not Working**
```
Problem: "Invalid username or password"
Solutions:
1. Verify credentials: admin / mercy2024
2. Check caps lock is off
3. Clear browser cookies
4. Check config.php for correct credentials
```

#### **Database Connection Error**
```
Problem: "Connection failed" message
Solutions:
1. Start MySQL in XAMPP
2. Run database setup: admin/complete_setup.php
3. Check database exists in phpMyAdmin
4. Verify database credentials in config.php
```

#### **Application Form Not Working**
```
Problem: Applications not saving
Solutions:
1. Check database is set up correctly
2. Verify process_application.php exists
3. Check browser console for errors
4. Ensure proper file permissions
```

#### **Images Not Uploading**
```
Problem: Gallery/slider images fail to upload
Solutions:
1. Check images/ folder permissions
2. Verify file size (max 5MB)
3. Use supported formats: JPG, PNG, GIF
4. Check available disk space
```

---

## 📂 File Structure Reference

```
marry_mother_of_mercy/
├── admin/                          # Admin panel directory
│   ├── includes/
│   │   ├── config.php             # Database & admin config
│   │   ├── header.php             # Admin header template
│   │   └── sidebar.php            # Admin navigation
│   ├── css/
│   │   └── admin.css              # Admin panel styles
│   ├── js/
│   │   └── admin.js               # Admin panel scripts
│   ├── backups/                   # Backup files storage
│   ├── login.php                  # Admin login page
│   ├── dashboard.php              # Main admin dashboard
│   ├── applications.php           # Applications management
│   ├── view_application.php       # Single application view
│   ├── content.php                # Website content editor
│   ├── teachers.php               # Teachers management
│   ├── gallery.php                # Gallery management
│   ├── slider.php                 # Homepage slider management
│   ├── testimonials.php           # Testimonials management
│   ├── contact_info.php           # Contact info management
│   ├── backup.php                 # Backup & restore
│   ├── logout.php                 # Logout functionality
│   ├── complete_setup.php         # Database setup script
│   └── README.md                  # Technical documentation
├── images/                        # Website images
├── css/                          # Website stylesheets
├── js/                           # Website scripts
├── index.html                    # Main homepage
├── about.html                    # About page
├── contact.html                  # Contact page
├── teacher.html                  # Teachers page
├── courses.html                  # Courses page
├── events.html                   # School events page
├── results.html                  # Student results page
├── process_application.php       # Form processing script
└── ADMIN_LOGIN_GUIDE.md          # This documentation file
```

---

## 📞 Support & Maintenance

### **Regular Maintenance Tasks:**
- ✅ Weekly backup creation
- ✅ Review pending applications
- ✅ Update website content as needed
- ✅ Monitor system performance
- ✅ Check for security updates

### **Emergency Contacts:**
- **Technical Support:** Contact development team
- **Database Issues:** Check XAMPP documentation
- **Server Problems:** Verify hosting configuration

---

## 📝 Quick Commands Reference

### **Essential URLs:**
```bash
# Admin Login
http://localhost/marry_mother_of_mercy/admin/login.php

# Admin Dashboard
http://localhost/marry_mother_of_mercy/admin/dashboard.php

# Database Setup
http://localhost/marry_mother_of_mercy/admin/complete_setup.php

# phpMyAdmin
http://localhost/phpmyadmin

# Main Website
http://localhost/marry_mother_of_mercy/
```

### **Default Credentials:**
```
Admin Username: admin
Admin Password: mercy2024
Database User: root
Database Password: (empty)
Database Name: marry_mother_mercy_db
```

---

## 🎯 Best Practices

### **Security:**
- ✅ Change default admin password
- ✅ Regular backups
- ✅ Monitor login attempts
- ✅ Keep system updated

### **Content Management:**
- ✅ Regular content updates
- ✅ Optimize images before upload
- ✅ Test changes on staging first
- ✅ Maintain content consistency

### **Application Processing:**
- ✅ Review applications promptly
- ✅ Maintain clear status updates
- ✅ Keep records organized
- ✅ Communicate with applicants

---

**📅 Last Updated:** $(date)
**🔧 System Version:** Mary Mother of Mercy School Admin Panel v1.0
**📧 Support:** Contact development team for technical assistance

---

*This guide contains all essential information for managing the Mary Mother of Mercy School admin panel. Keep this document accessible for quick reference.*