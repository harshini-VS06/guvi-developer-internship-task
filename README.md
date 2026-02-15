# GUVI Developer Internship Task - User Authentication System

A complete user authentication and profile management system built for GUVI Internship Assignment.

## 📋 Project Overview

This is a full-stack web application that implements user registration, login, and profile management with secure session handling. The system features a modern, animated UI with GUVI's brand colors and interactive design elements.

### Flow
```
Register → Login → Profile
```

## 🎨 Design Features

- **Modern UI**: Clean, professional interface with GUVI green theme
- **Animated Background**: Floating stationery icons (books, pencils, calculators) that move away from cursor
- **Responsive Design**: Fully responsive using Bootstrap 5
- **Interactive Elements**: Smooth animations and transitions
- **Professional Typography**: Lato font family

## 🛠️ Tech Stack

| Technology | Purpose |
|-----------|---------|
| **HTML5** | Structure and markup |
| **CSS3** | Styling and animations |
| **JavaScript (jQuery)** | Client-side logic and AJAX |
| **PHP 7.4+** | Server-side logic |
| **MySQL** | User registration data storage |
| **MongoDB** | User profile details storage |
| **Redis** | Session management |
| **Bootstrap 5** | Responsive framework |

## 📁 Project Structure

```
internship-project/
├── assets/
│   └── css/
│       └── style.css          # All styling (login, register, profile)
├── js/
│   ├── login.js               # Login page logic
│   ├── register.js            # Registration page logic
│   ├── profile.js             # Profile page logic
│   └── mouse-interact.js      # Animated icons interaction
├── php/
│   ├── config.php             # Database connections
│   ├── login.php              # Login API endpoint
│   ├── register.php           # Registration API endpoint
│   └── profile.php            # Profile management API
├── index.html                 # Landing page
├── login.html                 # Login page
├── register.html              # Registration page
├── profile.html               # User profile page
└── README.md                  # This file
```

## ✨ Key Features

### 1. User Registration
- Username (min 3 characters)
- Email validation
- Password (min 6 characters)
- Password confirmation
- Duplicate email/username checking
- BCrypt password hashing

### 2. User Login
- Email and password authentication
- Session token generation
- localStorage session management
- Redis backend session storage
- Automatic redirect if already logged in

### 3. User Profile
- View username and email (read-only)
- Update personal information:
  - Full Name
  - Age (1-120)
  - Date of Birth
  - Contact Number
  - Address
- Data stored in MongoDB
- Session verification
- Secure logout

## 🔒 Security Features

- **Password Hashing**: BCrypt algorithm
- **Prepared Statements**: All MySQL queries use prepared statements (no SQL injection)
- **Input Validation**: Both client and server-side
- **Session Management**: Secure tokens stored in Redis
- **XSS Protection**: Sanitized inputs
- **No PHP Sessions**: Uses localStorage + Redis as per requirements

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+
- MongoDB 4.0+
- Redis 5.0+
- Apache/Nginx web server
- Composer (for MongoDB PHP driver)

### Step 1: Install Dependencies

```bash
# Install Composer dependencies
composer require mongodb/mongodb

# Install Redis PHP extension
pecl install redis
```

### Step 2: Configure Databases

Edit `php/config.php` with your credentials:

```php
// MySQL Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'user_management');

// Redis Configuration
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);

// MongoDB Configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles');
```

### Step 3: Create MySQL Database

```sql
CREATE DATABASE user_management;
USE user_management;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 4: Start Services

```bash
# Start MySQL
sudo systemctl start mysql

# Start MongoDB
sudo systemctl start mongod

# Start Redis
sudo systemctl start redis

# Start Apache
sudo systemctl start apache2
```

### Step 5: Deploy Application

```bash
# Copy project to web root
sudo cp -r GUVI-Internship-Submission/* /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### Step 6: Access Application

Open browser and navigate to:
```
http://localhost/index.html
```

## 💻 Usage

### Registration Flow
1. Click "Register" on index page
2. Fill in username, email, and password
3. Confirm password
4. Submit form (AJAX)
5. Automatic redirect to login page

### Login Flow
1. Enter registered email and password
2. Submit form (AJAX)
3. Session token stored in localStorage
4. Session info stored in Redis
5. Automatic redirect to profile page

### Profile Management
1. View username and email (read-only)
2. Update additional details
3. Submit changes (AJAX)
4. Data saved to MongoDB
5. Logout to clear session

## 📡 API Endpoints

### POST `/php/register.php`
Register a new user

**Request Body:**
```json
{
    "username": "Lakshmi",
    "email": "Lakshmi@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful!"
}
```

### POST `/php/login.php`
Authenticate user

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful!",
    "sessionToken": "abc123...",
    "userId": 1,
    "username": "Lakshmi",
    "email": "Lakshmi@example.com"
}
```

### POST `/php/profile.php`
Manage user profile (supports multiple actions)

**Actions:**
- `verify`: Verify session
- `get`: Retrieve profile data
- `update`: Update profile data
- `logout`: End session

**Update Request:**
```json
{
    "action": "update",
    "sessionToken": "abc123...",
    "userId": 1,
    "fullName": "Lakshmi Thyagu",
    "age": 25,
    "dob": "1999-01-01",
    "contact": "+1234567890",
    "address": "123 Main St"
}
```

## 🎯 Requirements Compliance

✅ **Separate Files**: HTML, CSS, JS, and PHP in separate files  
✅ **jQuery AJAX**: All backend interactions use jQuery AJAX (no form submissions)  
✅ **Bootstrap**: Forms designed with Bootstrap for responsiveness  
✅ **MySQL**: User registration data stored with prepared statements  
✅ **MongoDB**: User profile details stored  
✅ **Prepared Statements**: No simple SQL statements used  
✅ **localStorage**: Session management via browser localStorage  
✅ **Redis**: Backend session storage  

## 🎨 Design Highlights

### Color Scheme
- **Primary Green**: `#00d166` (GUVI brand)
- **Dark Green**: `#00b357` (hover states)
- **Light Background**: `#bfffd9` (login/register)
- **Dark Background**: `#008f45` (profile page)

### Animations
- Floating stationery icons
- Mouse-flee interaction (icons move away from cursor)
- Smooth button hover effects
- Card shadow depth
- Form input focus animations

### Typography
- **Font**: Lato (400, 700, 900 weights)
- **Responsive**: Scales properly on all devices
- **Readable**: High contrast for accessibility

## 🔧 Troubleshooting

### Common Issues

**Database Connection Failed:**
- Check credentials in `php/config.php`
- Verify MySQL service is running
- Ensure database exists

**Redis Connection Failed:**
- Verify Redis is running: `redis-cli ping`
- Check port in config (default 6379)

**MongoDB Connection Failed:**
- Verify MongoDB is running: `mongosh`
- Install MongoDB PHP driver: `pecl install mongodb`

**AJAX Requests Failing:**
- Check browser console for errors
- Verify file paths are correct
- Enable CORS if needed

## 📄 License

Created for GUVI Internship Assignment - 2026

## 👨‍💻 About

This project demonstrates full-stack development skills including:
- Frontend design and interactivity
- Backend API development
- Database integration (SQL and NoSQL)
- Session management
- Security best practices
- Responsive web design

