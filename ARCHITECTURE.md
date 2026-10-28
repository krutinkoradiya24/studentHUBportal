# Student Hub Portal - Architecture & Developer Guide

> **Junior Developer Guide**: This document explains how the code is organized, how the Admin and Student modules are strictly separated, and how Role-Based Access Control (RBAC) ensures security across the portal.

---

## 1. Directory Structure Overview

Every file in this project is cleanly separated by role. If you are looking for code related to **Admins**, look in the `/admin/` folder. If you are looking for code related to **Students**, look in the `/student/` folder. Reusable services are stored in `/includes/`.

```text
c:\xampp\htdocs\STUDENTPORTAL/
│
├── index.php                      # Root Router: Checks session and automatically sends user to
│                                  #   - /admin/index.php   (if logged in as Admin)
│                                  #   - /student/index.php (if logged in as Student)
│                                  #   - /login.php         (if not logged in)
│
├── login.php                      # Dual-Role Authentication Portal:
│                                  #   - Student Login Tab
│                                  #   - Administrator Login Tab (strictly checks role)
│
├── register.php                   # Student Self-Registration form (with Bcrypt password hashing)
├── logout.php                     # Secure CSRF logout and session invalidation
├── db.php                         # Root DB Connector wrapper (proxies to includes/db.php)
│
├── admin/                         # ========================================
│   │                              # 🛡️ DEDICATED ADMINISTRATOR MODULE (Admin Only)
│   │                              # Every page calls require_admin() at line 1!
│   │                              # ========================================
│   ├── index.php                  # Admin Dashboard overview & live KPI metrics
│   ├── events.php                 # Complete Event Management (Add, Edit, Delete, Filter)
│   ├── timetable.php              # Complete Timetable Management (Weekly matrix & slots)
│   ├── courses.php                # Academic Curriculum & Course Modules Catalog
│   ├── students.php               # Student Records CRUD (Add, Edit, Soft-delete, CSV export)
│   ├── attendance.php             # Attendance Management (Mark attendance & view percentages)
│   ├── notices.php                # Campus Circulars & Notice Board management
│   ├── accounts.php               # Admin Accounts Management (Provision new admins & audit users)
│   └── security.php               # Security Audit Center (Session Fixation & Inactivity proofs)
│
├── student/                       # ========================================
│   │                              # 🎓 DEDICATED STUDENT MODULE (Students Only)
│   │                              # Every page calls require_student() at line 1!
│   │                              # ========================================
│   ├── index.php                  # Student Dashboard (Today's classes, attendance, events)
│   ├── timetable.php              # Student Class Timetable (Weekly schedule for their branch)
│   ├── events.php                 # College Events Portal (Browse, filter, RSVP)
│   ├── attendance.php             # Personal Attendance Compliance Tracker (Target >= 75%)
│   ├── assignments.php            # Course Assignments (Deadlines, submission modal, grades)
│   ├── results.php                # Official Examination Results, Grade Sheets & SGPA/CGPA
│   ├── notices.php                # Official Campus Notice Board & Circulars
│   ├── courses.php                # Enrolled Academic Courses & Subjects
│   ├── library.php                # Digital E-Library (Catalog search, book reader links)
│   ├── fees.php                   # Academic Fee Receipts, Invoices & Payment Ledger
│   ├── counselling.php            # Academic Mentorship & Faculty Counselling Appointments
│   └── profile.php                # Student Identification Card & Profile
│
├── includes/                      # ========================================
│   │                              # ⚙️ SHARED BACKEND ENGINE & REUSABLE COMPONENTS
│   │                              # ========================================
│   ├── db.php                     # Resilient DB Connector (MySQLi + PDO with port 3307/3306 auto-fallback)
│   ├── rbac.php                   # Strict Role-Based Access Control: require_admin(), require_student()
│   ├── session.php                # Hardened session config, 15m timeout, CSRF tokens, XSS escape e()
│   ├── auth_helpers.php           # User authentication, registration, password hashing
│   ├── admin_header.php           # Admin Navigation Header & Sidebar (Indigo/Navy theme)
│   ├── admin_footer.php           # Admin Layout Footer
│   ├── student_header.php         # Student Navigation Header & Sidebar (Teal/Emerald theme)
│   └── student_footer.php         # Student Layout Footer
│
├── assets/                        # Static UI styling and client-side behavior
│   ├── css/portal.css             # Unified modern CSS design system with glassmorphism & responsive layout
│   └── js/portal.js              # Client UI helpers (live table search, column filters, modals)
│
├── data/
│   ├── schema.sql                 # Canonical MySQL database schema
│   └── seed.php                   # Database migration and sample seed script
│
└── tests/                         # ========================================
    │                              # 🧪 AUTOMATED TEST SUITES & VERIFICATION
    │                              # ========================================
    ├── architecture_test.php      # Architecture & Role Separation Automated Test Runner
    └── reports/                   # System test reports & verification screenshots
```

---

## 2. Standard File Format & Coding Rules (For Junior Developers)

Whenever you create or modify a file in this project, you **MUST** adhere to this standardized file format:

### Standard 1: File Docblock Header
Every file must start with a descriptive PHP docblock detailing its purpose, role, and security access:

```php
<?php
/**
 * Student Hub Portal - [Feature Name]
 * 
 * @package    StudentHubPortal
 * @subpackage Admin (or Student, Shared, Auth)
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        admin/[filename].php (or student/[filename].php)
 * Role:        Administrator (or Student)
 * Access:      Strictly enforced via require_admin() (or require_student())
 * Description: Brief summary of what this page allows the user to do.
 */
```

### Standard 2: Role Access Guard at the Very Top
- **Admin Pages (`/admin/`)**:
  ```php
  $pageTitle = 'Page Title';
  require_once dirname(__DIR__) . '/includes/admin_header.php';
  ```
  *(Inside `admin_header.php`, `require_admin()` is executed immediately. If a student tries to open this file, they are instantly redirected to `/student/index.php?error=admin_access_denied`).*

- **Student Pages (`/student/`)**:
  ```php
  $pageTitle = 'Page Title';
  require_once dirname(__DIR__) . '/includes/student_header.php';
  ```
  *(Inside `student_header.php`, `require_student()` is executed immediately. If an admin tries to open this file, they are redirected to `/admin/index.php`).*

### Standard 3: Database & Security Conventions
1. **Database Access**: Always use `$pdo = get_pdo();` for prepared statements, or `$conn = get_db();` for MySQLi.
2. **XSS Protection**: Never print raw user data. Always wrap output in `e()`:
   ```php
   <!-- Correct -->
   <h3><?php echo e($student['name']); ?></h3>
   <!-- Incorrect: NEVER do this -->
   <h3><?php echo $student['name']; ?></h3>
   ```
3. **CSRF Protection**: All POST forms must include a CSRF token:
   ```html
   <input type="hidden" name="csrf_token" value="<?php echo e(generate_csrf_token()); ?>">
   ```
   And the POST handler must validate it:
   ```php
   if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
       set_flash('danger', 'Security token mismatch. Please try again.');
       header('Location: ' . $_SERVER['PHP_SELF']);
       exit;
   }
   ```
4. **Flash Messages**: Use `set_flash('success|danger|warning|info', 'Message text')` and `get_flash()` for notifications.

---

## 3. Role-Based Access Control (RBAC) & Security Rules

### Rule 1: No Student Can Access Admin Pages
Every file inside `/admin/` starts with `includes/admin_header.php`. The authoritative guard `require_admin()` is executed immediately:
1. If the user is not logged in $\rightarrow$ redirected to `login.php?error=unauthorized&role=admin`.
2. If the user is logged in with role `Student` $\rightarrow$ **immediately blocked!** Redirected to `/student/index.php?error=admin_access_denied` with an alert.

### Rule 2: No Admin Can Access Student-Only Pages
Every file inside `/student/` starts with `includes/student_header.php`. The guard `require_student()` is executed:
1. If an Admin tries to open a student page $\rightarrow$ redirected to `/admin/index.php?notice=student_view_restricted`.

### Rule 3: Strict Login Isolation
In `login.php`:
- If an Administrator tries to log in via the **Student Login Tab** $\rightarrow$ rejected: *"Access Denied: This is an Administrator account. Please switch to the Administrator tab."*
- If a Student tries to log in via the **Administrator Tab** $\rightarrow$ rejected: *"Access Denied: This account is a Student account and cannot access the Administrator portal."*

### Rule 4: CSRF & Session Security
- All POST forms include a hidden CSRF token verified using `hash_equals()`.
- Sessions automatically time out after **15 minutes (900 seconds)** of inactivity.
- Session IDs are regenerated upon login using `session_regenerate_id(true)` to prevent session fixation.

---

## 4. Default Credentials for Testing

| Role | Email Address | Password | Landing Portal |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@example.com` | `admin123` | `http://localhost/STUDENTPORTAL/admin/index.php` |
| **Student** | `student@example.com` | `student123` | `http://localhost/STUDENTPORTAL/student/index.php` |

---

## 5. How to Add New Features (For Junior Developers)

### Adding a New Admin Page:
1. Create your file in `/admin/` (e.g. `admin/reports.php`).
2. Add the title and include the header at the top:
   ```php
   <?php
   $pageTitle = 'Academic Reports';
   require_once dirname(__DIR__) . '/includes/admin_header.php';
   ?>
   <!-- Your Admin HTML and cards here -->
   <?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
   ```
3. Add a navigation link inside `includes/admin_header.php`.

### Adding a New Student Page:
1. Create your file in `/student/` (e.g. `student/grades.php`).
2. Add the title and include the student header at the top:
   ```php
   <?php
   $pageTitle = 'My Grades';
   require_once dirname(__DIR__) . '/includes/student_header.php';
   ?>
   <!-- Your Student HTML here -->
   <?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
   ```
3. Add a navigation link inside `includes/student_header.php`.

---

## 6. Running Automated Tests

To verify that all role protections, directory separations, and database operations work correctly:

```powershell
# Run the Architecture & Role Separation Suite
c:\xampp\php\php.exe tests/architecture_test.php

# Run the 22-Requirement Security Suite
c:\xampp\php\php.exe php/test_suite.php
```
