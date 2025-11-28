# 🎓 Helper Teacher Job Application System

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> A complete job application system for helper teacher positions that connects qualified assistant teachers with educational institutions.

## Features

### User Types
- **Employers**: Can post jobs and manage applications
- **Job Seekers**: Can apply for positions and manage their profile

### Main Functions
- User registration and login
- Job management (create, edit, view)
- Application system with status tracking
- User profiles and CV management
- Dashboard for both user types

## Technology Stack
- **Backend**: PHP 8.x with Object-Oriented Programming
- **Architecture**: Class-based structure with autoloading
- **Database**: MySQL (via XAMPP) - Array-based storage initially
- **Database**: MySQL via PDO (see `includes/config.php`) — the app connects to a MySQL database using PDO. A minimal schema is provided below.
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Server**: Apache (XAMPP)
- **Session Management**: PHP Sessions
- **Version Control**: Git with GitHub integration

## 🏗️ Architecture Overview

### Class Library
The system uses a modern object-oriented approach with the following core classes:

- **`Auth`** - Handles user authentication and authorization
- **`User`** - Manages user accounts and profiles
- **`Job`** - Handles job postings and management
- **`Application`** - Manages job applications and status tracking
- **`Validator`** - Provides input validation and data sanitization

### Include System
- **`config.php`** - System configuration and session management
- **`functions.php`** - Main include file that loads all components
- **`autoload.php`** - Automatic class loading
- **Helper functions** - Authentication and validation utilities

### Data Storage
The application uses a MySQL database accessed through PDO. Update your database credentials in `includes/config.php` (DB_HOST, DB_USER, DB_PASS, DB_NAME) before running the app. A minimal schema to create the required tables is included in the "Database setup" section below.

## 🚀 Complete Setup Guide

### Prerequisites
Before starting, make sure you have:
- **XAMPP** installed (includes Apache, MySQL, PHP)
- **Git** for version control
- A modern web browser
- Text editor or IDE (VS Code recommended)

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP to your preferred location (usually `C:\xampp\`)
3. Start the XAMPP Control Panel

### Step 2: Clone the Project
Open your terminal/command prompt and run:

```bash
# Navigate to XAMPP htdocs directory
cd C:\xampp\htdocs

# Clone the repository
git clone https://github.com/UngeBakern/soeknadssystem.git

# Navigate to project directory
cd soeknadssystem
```

### Step 3: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** service (click "Start" button)
3. Start **MySQL** service (click "Start" button)
4. Verify both services show "Running" status

### Step 4: Verify Installation
1. Open your web browser
2. Navigate to: `http://localhost/soeknadssystem/`
3. You should see the application homepage

### Step 5: Test with Demo Accounts

The login page includes example demo credentials. These accounts are not automatically created unless you seed the database or register them via the application. See the "Database setup" section below for how to create the database schema and seed users.

### Project structure (high level)

The repository contains the main application files and a simple class-based architecture. Key folders:

```
soeknadssystem/
├── index.php
├── classes/        # Class library (Auth, Database, User, Job, Application, Validator)
├── includes/       # Config, autoload, helpers
├── auth/           # Login, registration, logout
├── dashboard/      # User dashboards
├── jobs/           # Job CRUD and listings
├── applications/   # Application handling and status
├── profile/        # User profile management
├── assets/         # css/js/images
├── uploads/        # uploaded files
├── .github/        # CI workflows
├── LICENSE
└── README.md
```

Note: The project no longer relies on local `data/` PHP arrays for storage — it connects to MySQL via PDO. There is no `database/schema.sql` file in the repo by default; see the next section for a minimal schema you can run.

## Development Phases

### Phase 1: Foundation & Architecture ✅
- [x] Project setup and Git repository
- [x] Object-oriented class library (Auth, User, Job, Application, Validator)
- [x] Autoloading system for classes
- [x] Helper functions and validation system
- [x] Responsive design with Bootstrap
- [x] Clean file structure and organization

### Phase 2: Core Functionality
- [x] Complete authentication system with login/logout
- [x] Job management (create, edit, view, delete)
- [x] Basic application system (submit and track applications)
- [x] User profiles and basic dashboards
- [ ] File upload handling for applications (partial / verify upload limits)
- [ ] Search and advanced filtering of jobs
- [ ] Role-based access control (review and tighten permissions)

### Phase 3: Database Integration & Finalization
- [ ] MySQL database migration from array storage
- [ ] Advanced features (notifications, email integration)
- [ ] Testing, security hardening, and bug fixes
- [ ] Final documentation and course delivery

## 👥 Team

This is a 2-person course project for PHP development at the University of Agder (UiA).

## Database setup (minimal)

Reminder: I'll add a standalone `database/schema.sql` file and an optional seed script here later. For now, please create a MySQL database and configure your credentials in `includes/config.php` (DB_HOST, DB_USER, DB_PASS, DB_NAME). When you're ready, run the schema and seed steps to create tables and demo users.

## 📋 Project Resources

- 🔧 **[GitHub Repository](https://github.com/UngeBakern/soeknadssystem)** - Source code and version control
- 📄 **[MIT License](LICENSE)** - Project license

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Testing

Open `http://localhost/soeknadssystem/` in your browser after XAMPP is started.

Use the demo credentials provided above to test different user roles and functionality.
