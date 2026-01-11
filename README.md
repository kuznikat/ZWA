# 🌍 Travel Agency "Let's Travel" - Product Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [User Guide](#user-guide)
4. [Installation](#installation)
5. [System Requirements](#system-requirements)
6. [Project Structure](#project-structure)
7. [Technologies Used](#technologies-used)
8. [Author](#author)

---

## Overview

**Let's Travel** is a web-based travel agency platform designed to simplify the process of booking trips online. The system allows users to browse available tours, create accounts, and book trips with ease. Administrators can manage tours, bookings, and user accounts through a dedicated admin interface.

### Project Goal
The goal of this project is to provide an intuitive, user-friendly platform where:
- Users can easily browse and book travel packages
- Administrators can efficiently manage tours and bookings
- The booking process is streamlined and accessible

---

## Features

### General Features
- ✅ **Responsive Design**: Fully responsive layout using HTML5, CSS3 (Flexbox & Grid), and vanilla JavaScript
- ✅ **User-Friendly Navigation**: Intuitive navigation between all pages
- ✅ **Modern UI/UX**: Clean, modern interface with smooth interactions
- ✅ **Cross-Browser Compatible**: Works on all modern browsers
- ✅ **Mobile-Friendly**: Optimized for mobile devices

### User Features
1. **Account Management**
   - Create a new account with email and username
   - Upload and customize profile avatar (with image cropping/resizing)
   - View and manage personal profile information
   - View booking history

2. **Tour Browsing**
   - Browse all available travel packages
   - View detailed information about each tour (destination, dates, price)
   - See tour availability and remaining spots
   - Filter and search tours

3. **Booking System**
   - Book tours with arrival and departure dates
   - Specify number of guests
   - View upcoming bookings
   - Manage existing bookings

4. **Avatar Management**
   - Upload profile pictures
   - Crop and resize images before upload
   - Real-time preview of avatar
   - Automatic image optimization

### Administrator Features
1. **Tour Management**
   - Add new tours with details (destination, dates, price, capacity)
   - Edit existing tour information
   - Delete tours
   - View tour availability and bookings

2. **User Management**
   - View all registered users
   - Promote users to administrator role
   - Manage user accounts
   - View user booking history

3. **Booking Management**
   - View all bookings across all users
   - Manage booking status
   - Track tour capacity

---

## User Guide

### For Regular Users

#### Creating an Account
1. Navigate to the **Register** page
2. Fill in the registration form:
   - Username (letters, numbers, and underscores only)
   - Email address
   - Password (minimum 6 characters)
   - Confirm password
   - Optional: Upload profile avatar
3. Click **Register** to create your account
4. You will be automatically logged in after registration

#### Logging In
1. Go to the **Login** page
2. Enter your username or email address
3. Enter your password
4. Click **Login**

#### Browsing Tours
1. Visit the **Package** page to see all available tours
2. Each tour displays:
   - Destination name
   - Tour dates
   - Price
   - Available spots
3. Click **Book Now** on any tour to proceed with booking

#### Booking a Tour
1. Select a tour from the **Package** page
2. Click **Book Now**
3. Fill in the booking form:
   - Select arrival date
   - Select departure date
   - Enter number of guests
4. Click **Submit** to complete the booking
5. View your booking in **My Bookings** page

#### Managing Profile
1. Go to **Profile** page (requires login)
2. View your account information
3. **Update Avatar**:
   - Click "select new avatar"
   - Choose an image file (JPEG, PNG, GIF, or WebP, max 2MB)
   - Adjust the image:
     - **Drag** to move the image
     - **Scroll** or use buttons to zoom in/out
     - Use **Reset** to restore original position
     - Check the **Preview** to see final result
   - Click **Update Avatar** to save

#### Viewing Bookings
1. Navigate to **My Bookings** page
2. View all your upcoming and past bookings
3. See booking details: destination, dates, number of guests

### For Administrators

#### Accessing Admin Panel
1. Log in with an administrator account
2. Navigate to **Admin** in the main menu
3. Access admin features:
   - **Tours Management**: Add, edit, or delete tours
   - **User Management**: Manage users and roles
   - **Bookings Management**: View all bookings

#### Managing Tours
1. Go to **Admin > Tours**
2. **Add New Tour**:
   - Click "Add New Tour"
   - Fill in tour details (destination, dates, price, capacity)
   - Click "Save"
3. **Edit Tour**:
   - Click "Edit" on any tour
   - Modify tour information
   - Click "Update"
4. **Delete Tour**:
   - Click "Delete" on any tour
   - Confirm deletion

#### Managing Users
1. Access user management from admin panel
2. **Promote to Admin**:
   - Find the user
   - Use the role management interface
   - Change role to "admin"

---

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server
- PHP extensions: PDO, PDO_MySQL, GD (for image processing)

### Step-by-Step Installation

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd ZWA/travel-website
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the database schema from `database_schema.sql`
   - Update database credentials in `includes/db.php`:
     ```php
     $db_host = 'localhost';
     $db_name = 'your_database_name';
     $db_user_name = 'your_username';
     $db_user_pass = 'your_password';
     ```

3. **Configure Upload Directory**
   - Ensure `uploads/avatars/` directory exists and is writable
   - Set permissions: `chmod 775 uploads/avatars/`

4. **Start the Server**
   - Using PHP built-in server:
     ```bash
     php -S localhost:8000
     ```
   - Or configure Apache/Nginx to point to the `travel-website` directory

5. **Access the Application**
   - Open browser and navigate to `http://localhost:8000`
   - Register a new account or use existing credentials
   - For admin access, promote a user to admin role via database or admin interface

---

## System Requirements

### Server Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+ (or PHP built-in server for development)
- **PHP Extensions**:
  - PDO
  - PDO_MySQL
  - GD (for image processing)
  - Session support

### Browser Requirements
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Recommended Settings
- PHP `upload_max_filesize`: 2M or higher
- PHP `post_max_size`: 8M or higher
- PHP `memory_limit`: 128M or higher

---

## Project Structure

```
travel-website/
├── admin/                 # Administrator pages
│   └── tours.php         # Tour management interface
├── css/                   # Stylesheets
│   └── style.css         # Main stylesheet
├── images/                # Image assets
├── includes/              # PHP includes
│   ├── auth.php          # Authentication functions
│   └── db.php            # Database connection
├── js/                    # JavaScript files
│   ├── script.js         # Main JavaScript
│   ├── validation.js     # Form validation
│   └── avatar-editor.js  # Avatar cropping/resizing
├── uploads/               # User uploads
│   └── avatars/          # User profile pictures
├── about.php             # About Us page
├── book.php              # Booking page
├── home.php              # Homepage
├── login.php             # Login page
├── logout.php            # Logout handler
├── my-bookings.php       # User bookings page
├── package.php           # Tours listing page
├── profile.php           # User profile page
└── register.php          # Registration page
```

---

## Technologies Used

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: 
  - Flexbox for layout
  - Grid for complex layouts
  - CSS Variables for theming
  - Responsive design with media queries
- **JavaScript (ES6+)**:
  - Vanilla JavaScript (no frameworks)
  - Canvas API for image manipulation
  - Fetch API for AJAX requests
  - DOM manipulation

### Backend
- **PHP 7.4+**: Server-side logic
- **PDO**: Database abstraction layer
- **MySQL**: Relational database
- **Session Management**: User authentication

### Development Tools
- **PHPDocumentor**: Code documentation generation
- **Git**: Version control

---

## Security Features

- ✅ Password hashing using PHP `password_hash()`
- ✅ Prepared statements (PDO) to prevent SQL injection
- ✅ Input validation and sanitization
- ✅ XSS protection with `htmlspecialchars()`
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ File upload validation (type and size)
- ✅ Image processing security

---

## Known Limitations

- Maximum file upload size: 2MB
- Supported image formats: JPEG, PNG, GIF, WebP
- Avatar size: Fixed at 300x300 pixels (automatically resized)
- No email verification for registration
- No password reset functionality (can be added)

---

## Troubleshooting

### Common Issues

**Issue**: "502 Bad Gateway" error
- **Solution**: Check PHP installation and ICU library compatibility
- Ensure PHP-CGI is properly configured

**Issue**: Avatar upload fails
- **Solution**: Check `uploads/avatars/` directory permissions (should be 775)
- Verify PHP `upload_max_filesize` and `post_max_size` settings

**Issue**: Database connection fails
- **Solution**: Verify database credentials in `includes/db.php`
- Ensure MySQL service is running
- Check database name and user permissions

**Issue**: Images not displaying
- **Solution**: Check file paths and permissions
- Verify image files exist in `images/` directory

---

## Support

For issues, questions, or contributions, please contact the project maintainer.

---

## Author

**Kateryna Kuznietsova**

Created as part of a semester project for the Web Programming course.

---

## License

This project is created for educational purposes as part of an academic assignment.

---

## Version History

- **v1.0** (Current): Initial release with core features
  - User registration and authentication
  - Tour browsing and booking
  - Admin interface
  - Avatar management with cropping
  - Responsive design

---

## Future Enhancements

- Email verification for new accounts
- Password reset functionality
- Payment integration
- Tour reviews and ratings
- Advanced search and filtering
- Export bookings to PDF
- Multi-language support
