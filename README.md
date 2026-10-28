# Secure PHP MySQLi Authentication System with Role-Based Dashboards

A complete, production-grade User Authentication and Role-Based Access Control (RBAC) System built with **PHP 8+**, **MySQLi (Strict Mode & Prepared Statements)**, **HTML5**, **CSS3**, and **MySQL/MariaDB**.

Engineered specifically for local execution on **XAMPP**, this project implements zero client-side authentication simulation—all authentication, session management, and authorization checks are enforced authoritatively on the server.

---

## 1. Technology Stack & Prerequisites

- **Server Environment**: XAMPP (Apache 2.4+ and MySQL / MariaDB 10.4+)
- **Backend Language**: PHP 8.0+ (Tested and verified on PHP 8.2.12)
- **Database Engine**: MySQL / MariaDB (InnoDB, `utf8mb4_unicode_ci`)
- **Database Extension**: PHP `mysqli` with strict error reporting (`MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT`)
- **Password Security**: Native `password_hash()` with `PASSWORD_DEFAULT` (Bcrypt) and `password_verify()`
- **Session Security**: OWASP-compliant strict sessions, `HttpOnly`, `SameSite=Lax`, dynamic `Secure` cookie flag, and session fixation defense via `session_regenerate_id(true)`
- **CSRF Defense**: Cryptographically secure per-session tokens (`random_bytes(32)`) with timing-attack resistant `hash_equals()` validation
- **Frontend**: Responsive HTML5 and Vanilla CSS3 design system with accessible form controls and distinct themes for Admin and Student portals.

---
## 2. Project File Structure (Individual Admin & Student Architecture)

```text
STUDENTPORTAL/
├── index.php                      # Portal root entry router (dispatches to admin/, student/, or login.php)
├── login.php                      # Dual-role Student & Admin secure login portal
├── register.php                   # Student self-registration portal with CSRF and Bcrypt hashing
├── logout.php                     # Secure session termination & cookie clearance
├── db.php                         # Root DB Connector wrapper (proxies to includes/db.php)
├── ARCHITECTURE.md                # Junior Developer Architecture & Directory Guide
│
├── admin/                         # ========================================
│   │                              # 🛡️ DEDICATED ADMINISTRATOR MODULE (Admin Only)
│   │                              # Strict require_admin() access control at top of every page
│   │                              # ========================================
│   ├── index.php                  # Admin Dashboard overview & live KPI metrics
│   ├── events.php                 # Complete Events Management (Add, Edit, Delete, Categories)
│   ├── timetable.php              # Complete Academic Timetable Management (Weekly matrix & slots)
│   ├── courses.php                # Academic Curriculum & Course Modules Catalog
│   ├── students.php               # Student Records Management (CRUD, Soft-delete, CSV export)
│   ├── attendance.php             # Attendance System (Mark attendance, compliance percentages)
│   ├── notices.php                # Campus Circulars & Notice Board management
│   ├── accounts.php               # Admin Accounts Management (Provision new admins, audit roles)
│   └── security.php               # Security Audit Center (Session Fixation & Inactivity proofs)
│
├── student/                       # ========================================
│   │                              # 🎓 DEDICATED STUDENT MODULE (Students Only)
│   │                              # Strict require_student() access control at top of every page
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
│   │                              # ⚙️ SHARED CORE BACKEND & REUSABLE MODULES
│   │                              # ========================================
│   ├── db.php                     # Resilient DB connection (MySQLi + PDO with port 3307/3306 auto-failover)
│   ├── rbac.php                   # Strict Role-Based Access Control: require_admin(), require_student()
│   ├── session.php                # Hardened session config, 15m timeout, CSRF tokens, XSS escape e()
│   ├── auth_helpers.php           # User authentication, registration, password hashing
│   ├── admin_header.php           # Admin Navigation Header & Sidebar (Indigo/Navy theme)
│   ├── admin_footer.php           # Admin Layout Footer
│   ├── student_header.php         # Student Navigation Header & Sidebar (Teal/Emerald theme)
│   └── student_footer.php         # Student Layout Footer
│
├── assets/                        # Static UI styling and client-side behavior
│   ├── css/portal.css             # Modern Glassmorphism CSS design system
│   └── js/portal.js              # Client UI helpers (live table search, column filters, modals)
│
├── data/                          # Database schema and seed runners
│   ├── schema.sql                 # Canonical complete SQL schema with timetables & events
│   └── seed.php                   # Database Migration & Auto-seed CLI / Web script
└── README.md                      # Complete setup, testing, and security documentation
```

---

## 3. Local Access URLs

The application is deployed inside your local XAMPP `htdocs` directory and is accessible via either:

- **Primary URL**: [http://localhost/registration_system/](http://localhost/registration_system/)
- **Workspace URL**: [http://localhost/STUDENTPORTAL/](http://localhost/STUDENTPORTAL/)

Both endpoints route directly to `index.php`, which inspects the session and dispatches authenticated users to their dashboard or unauthenticated visitors to `login.php`.

---

## 4. Setup and Installation Guide

### Step 1: Start Apache and MySQL in XAMPP

1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

*Alternatively via Command Line / PowerShell:*
```powershell
# Start Apache
& "C:\xampp\apache\bin\httpd.exe"

# Start MySQL / MariaDB
& "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --console
```

---

### Step 2: Database Creation & SQL Schema Import

The database export file is located at `user_registration.sql`.

#### Option A: Using phpMyAdmin
1. Open your browser and navigate to `http://localhost/phpmyadmin/`.
2. Click on the **Import** tab.
3. Click **Choose File** and select `user_registration.sql`.
4. Click **Import** (or **Go**) at the bottom.
5. The database `user_registration_db` and table `users` will be created with sample accounts.

#### Option B: Using MySQL Command Line
Run the following in PowerShell:
```powershell
Get-Content "c:\xampp\htdocs\STUDENTPORTAL\user_registration.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
```

---

### Step 3: Database Connection Configuration (`db.php`)

The connection file `db.php` is pre-configured with local XAMPP defaults:

| Parameter | Value | Configurable Environment Variable |
| :--- | :--- | :--- |
| **Host** | `127.0.0.1` | `DB_HOST` |
| **User** | `root` | `DB_USER` |
| **Password** | `""` (Empty string) | `DB_PASS` |
| **Database** | `user_registration_db` | `DB_NAME` |
| **Port** | `3306` | `DB_PORT` |

Connection logic is encapsulated inside a `try/catch` block handling `mysqli_sql_exception`. Internal credentials or database stack traces are never printed to the client browser.

---

## 5. Authorized Credentials & Accounts

The system strictly requires registration for student accounts and uses dedicated administrator credentials:

| Role | Email Address | Password | Intended Dashboard | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `krutin@gmail.com` | `Krutin@1234` | `/admin/index.php` | Primary Administrator |
| **Admin (Fallback)** | `admin@example.com` | `admin123` | `/admin/index.php` | Secondary Admin |
| **Student** | `student@example.com` | `student123` | `/student/index.php` | Default Demo Student |
| **New Student** | *(Self-registered)* | *(Created in Register)* | `/student/index.php` | Use `register.php` / `registration.html` |

---

## 6. Security Architecture & Feature Implementation

### 1. User Registration (`register.php`)
- **Authoritative Server Validation**: Checks for empty fields, RFC email structure, password minimum length (8 characters), and password matching.
- **Strict Role Assignment**: The `role` column is hardcoded to `'Student'`. Self-registration as Admin is strictly blocked.
- **Race Condition & Duplicate Prevention**: Queries the database to prevent duplicate emails and catches MySQL unique constraint violations (Error Code `1062`).
- **CSRF Protection**: All form submissions require a hidden, session-bound CSRF token.
- **Bcrypt Password Hashing**: Passwords are saved only as `password_hash($password, PASSWORD_DEFAULT)`.

### 2. Login & Authentication (`login.php` & `authenticate.php`)
- **Prepared Statements**: Parameterized queries `SELECT ... WHERE email = ?` eliminate SQL Injection vulnerabilities.
- **Constant-Time Verification**: `password_verify()` prevents timing attacks.
- **Enumeration Defense**: Displays generic error *"Invalid email or password."* regardless of whether the email was not found or the password was incorrect.
- **Session Fixation Defense**: Immediately executes `session_regenerate_id(true)` upon successful verification.
- **Minimal Session Storage**: Never stores passwords or password hashes in the session. Only `user_id`, `full_name`, `email`, `role`, and `last_activity` are persisted.

### 3. Role-Based Access Control (RBAC)
- **Admin Dashboard (`admin_dashboard.php`)**: Accessible only to users where `$_SESSION['role'] === 'Admin'`. Student accounts attempting to access this page are immediately rejected and redirected.
- **Student Dashboard (`student_dashboard.php`)**: Accessible only to users where `$_SESSION['role'] === 'Student'`. Admins attempting to access this student-only page are redirected to the Admin dashboard.
- **Unauthenticated Access**: Guests attempting to load any dashboard are redirected to `login.php?error=unauthorized`.

### 4. Inactivity Session Timeout (`timeout.php`)
- Tracks user engagement via `$_SESSION['last_activity']`.
- Threshold is set to **15 minutes (900 seconds)**.
- If inactivity exceeds 900 seconds:
  1. Session array `$_SESSION` is cleared.
  2. The browser session cookie is expired (`time() - 42000`).
  3. Server session is terminated via `session_destroy()`.
  4. User is redirected to `login.php?error=timeout`.
- Active requests dynamically refresh `$_SESSION['last_activity'] = time()`.

### 5. Secure Logout (`logout.php`)
- Accepts requests **only via HTTP POST**. Direct GET requests are rejected.
- Requires CSRF token validation to prevent forced-logout CSRF attacks.
- Purges `$_SESSION`, destroys the cookie, executes `session_destroy()`, and redirects with a confirmation message.

---

## 7. How to Create an Admin Safely

To maintain security, public self-registration as an Admin is intentionally blocked. To promote a user or create an Administrator safely:

### Method A: Promote an Existing User via MySQL Command Line
```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE user_registration_db; UPDATE users SET role = 'Admin' WHERE email = 'student@example.com';"
```

### Method B: Generate a New Admin Using PHP CLI
Run the following one-liner in PowerShell:
```powershell
& "C:\xampp\php\php.exe" -r "
require 'db.php';
\$name = 'Portal Director';
\$email = 'director@example.com';
\$pass = password_hash('Director@Secure99!', PASSWORD_DEFAULT);
\$stmt = \$conn->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, \'Admin\')');
\$stmt->bind_param('sss', \$name, \$email, \$pass);
\$stmt->execute();
echo 'Admin successfully created: director@example.com' . PHP_EOL;
"
```

---

## 8. Verification & Test Results

An automated test suite (`test_suite.php`) was executed directly against PHP 8.2 and MariaDB MySQL to test all 20 specified requirements.

### Test Execution Command
```powershell
& "C:\xampp\php\php.exe" -f "c:\xampp\htdocs\STUDENTPORTAL\test_suite.php"
```

### Verified Test Results

```text
====================================================================
  PHP MySQLi AUTHENTICATION SYSTEM - AUTOMATED VERIFICATION SUITE   
====================================================================
PHP Version: 8.2.12
MySQL/MariaDB Version: 10.4.32-MariaDB
Execution Time: 2026-10-16 17:28:13
--------------------------------------------------------------------

[01] Testing: Empty-field registration rejection                      [PASS]
[02] Testing: Invalid email format rejection                          [PASS]
[03] Testing: Password mismatch rejection                             [PASS]
[04] Testing: Password minimum length (8 chars) enforcement           [PASS]
[05] Testing: Duplicate email detection against MySQL database        [PASS]
[06] Testing: Successful user registration workflow                   [PASS]
[07] Testing: Database persistence of registered student record       [PASS]
[08] Testing: Password storage format is Bcrypt hash (not plaintext)  [PASS]
[09] Testing: Self-registration as Admin is strictly blocked          [PASS]
[10] Testing: Successful Student login authentication                 [PASS]
[11] Testing: Successful Admin login authentication                   [PASS]
[12] Testing: Login rejection for incorrect password (generic error)  [PASS]
[13] Testing: Login rejection for unregistered email                  [PASS]
[14] Testing: Admin dashboard authorizes Admin role and loads stats   [PASS]
[15] Testing: Student rejected from Admin dashboard                   [PASS]
[16] Testing: Admin rejected from Student-only dashboard (Req 11)     [PASS]
[17] Testing: Unauthenticated user redirected to login.php            [PASS]
[18] Testing: Session ID regeneration mechanism (session fixation guard) [PASS]
[19] Testing: Session timeout expiration after 900 seconds inactivity [PASS]
[20] Testing: Logout clears session variables and terminates session  [PASS]
[21] Testing: SQL Injection defense in email authentication           [PASS]
[22] Testing: CSRF token generation and timing-safe hash_equals verification [PASS]

--------------------------------------------------------------------
TEST SUMMARY: 22 / 22 TESTS PASSED (100% SUCCESS RATE)
====================================================================
```

---

## 9. Common Errors and Troubleshooting

1. **MySQL Service Won't Start (Port 3306 in use)**:
   - If an existing Oracle MySQL instance is running on Windows, open `services.msc` and stop the `MySQL` or `MySQL97` service, then start MySQL from the XAMPP Control Panel.
2. **"Database Service Unavailable" Screen**:
   - Ensure the MySQL service in XAMPP is in the **Running** state (green indicator in XAMPP Control Panel).
   - Verify that the database `user_registration_db` was imported from `user_registration.sql`.
3. **Session Expired Immediately**:
   - Verify that your system clock matches the current time and that cookies are enabled in your browser settings.
4. **Forbidden / CSRF Mismatch**:
   - If a form sits idle for days or if multiple tabs overwrite the session, simply refresh the form to obtain a fresh CSRF token.
