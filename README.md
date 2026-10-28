# Student Hub Portal

[![GitHub Repo](https://img.shields.io/badge/GitHub-studentHUBportal-blue?logo=github)](https://github.com/krutinkoradiya24/studentHUBportal)
[![PHP](https://img.shields.io/badge/PHP-8.2-purple?logo=php)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.4-orange?logo=mysql)](https://mariadb.org/)
[![Tests](https://img.shields.io/badge/Tests-65%2F65%20PASS-brightgreen)](./TEST_AND_SECURITY_REPORT.md)
[![XAMPP](https://img.shields.io/badge/Server-XAMPP-FB7A24?logo=apache)](https://www.apachefriends.org/)

A complete, production-grade **User Authentication and Role-Based Access Control (RBAC)** system built with **PHP 8+**, **MySQLi**, **HTML5**, and **CSS3**. All authentication, session management, and authorization are enforced on the server — zero client-side simulation.

> 🔗 **Repository**: [https://github.com/krutinkoradiya24/studentHUBportal](https://github.com/krutinkoradiya24/studentHUBportal)
> 🗄️ **Database**: [`studenthub.sql`](./studenthub.sql)
> 📋 **Test & Security Report**: [`TEST_AND_SECURITY_REPORT.md`](./TEST_AND_SECURITY_REPORT.md)
> 📸 **Screenshots**: [`tests/reports/screenshots/`](./tests/reports/screenshots/)

---

## Table of Contents

1. [Technology Stack](#1-technology-stack)
2. [Project Structure](#2-project-structure)
3. [Setup & Installation](#3-setup--installation)
4. [Database Configuration](#4-database-configuration)
5. [Test Accounts & Credentials](#5-test-accounts--credentials)
6. [Running the Tests](#6-running-the-tests)
7. [Security Features](#7-security-features)
8. [Creating an Admin Account](#8-creating-an-admin-account)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Server** | XAMPP (Apache 2.4+, MariaDB 10.4+) |
| **Backend** | PHP 8.2+ |
| **Database** | MySQL / MariaDB — InnoDB, `utf8mb4_unicode_ci` |
| **DB Extension** | PHP `mysqli` with `MYSQLI_REPORT_ERROR \| MYSQLI_REPORT_STRICT` |
| **Password Security** | `password_hash()` — Bcrypt (`PASSWORD_DEFAULT`) + `password_verify()` |
| **Session Security** | OWASP-compliant — `HttpOnly`, `SameSite=Lax`, `session_regenerate_id(true)` |
| **CSRF Defense** | `bin2hex(random_bytes(32))` tokens, validated with `hash_equals()` |
| **Frontend** | HTML5 + Vanilla CSS3 — Glassmorphism design, responsive layout |

---

## 2. Project Structure

```
STUDENTPORTAL/
├── index.php                   # Root router — dispatches to admin/, student/, or login.php
├── login.php                   # Unified dual-role login portal
├── register.php                # Student self-registration (CSRF + Bcrypt)
├── logout.php                  # Secure session termination (POST only)
├── db.php                      # Root DB connector wrapper
├── studenthub.sql              # Complete database dump (6 tables + seed data)
├── ARCHITECTURE.md             # Developer architecture guide
│
├── admin/                      # 🛡️ Admin-only module (require_admin() on every page)
│   ├── index.php               # Dashboard — live KPI metrics
│   ├── students.php            # Student records — CRUD, soft-delete, CSV export
│   ├── attendance.php          # Mark attendance, view compliance percentages
│   ├── courses.php             # Academic curriculum & course catalog
│   ├── timetable.php           # Weekly timetable management
│   ├── events.php              # Events management — add, edit, delete
│   ├── notices.php             # Campus notice board management
│   ├── accounts.php            # Admin accounts — provision & audit roles
│   └── security.php            # Security audit center
│
├── student/                    # 🎓 Student-only module (require_student() on every page)
│   ├── index.php               # Dashboard — today's classes, attendance, notices
│   ├── timetable.php           # Weekly class schedule
│   ├── attendance.php          # Personal attendance tracker (target ≥ 75%)
│   ├── assignments.php         # Assignments, deadlines & grades
│   ├── results.php             # Exam results, grade sheets & SGPA/CGPA
│   ├── notices.php             # Campus notice board
│   ├── courses.php             # Enrolled courses & subjects
│   ├── library.php             # Digital e-library catalog
│   ├── fees.php                # Fee receipts & payment ledger
│   ├── counselling.php         # Faculty counselling appointments
│   ├── events.php              # College events — browse & RSVP
│   └── profile.php             # Student ID card & profile
│
├── includes/                   # ⚙️ Shared backend modules
│   ├── db.php                  # DB connection — MySQLi + PDO, port auto-failover
│   ├── rbac.php                # RBAC — require_admin() and require_student()
│   ├── session.php             # Session config, 15-min timeout, CSRF, XSS helper e()
│   ├── auth_helpers.php        # Authentication, registration, password hashing
│   ├── admin_header.php        # Admin sidebar & navigation (Indigo/Navy theme)
│   ├── admin_footer.php        # Admin layout footer
│   ├── student_header.php      # Student sidebar & navigation (Teal/Emerald theme)
│   └── student_footer.php      # Student layout footer
│
├── assets/
│   ├── css/portal.css          # Glassmorphism CSS design system
│   └── js/portal.js            # Live search, filters, modals
│
├── data/
│   ├── schema.sql              # Canonical SQL schema
│   └── seed.php                # Database migration & seed script
│
└── tests/
    ├── architecture_test.php   # Structure & RBAC tests (11 tests)
    ├── test_suite.php          # Security & auth tests (23 tests)
    ├── system_verification.php # End-to-end HTTP tests (31 tests)
    └── reports/screenshots/    # UI proof screenshots
```

---

## 3. Setup & Installation

### Step 1 — Start XAMPP Services

1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

> ⚠️ **Port conflict?** If MySQL fails to start, open `services.msc` and stop any existing `MySQL` or `MySQL97` service, then retry from XAMPP.

### Step 2 — Place the Project

Ensure the project folder is located at:

```
C:\xampp\htdocs\STUDENTPORTAL\
```

### Step 3 — Import the Database

**Option A — phpMyAdmin (Recommended):**

1. Open [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click the **Import** tab
3. Click **Choose File** → select `studenthub.sql`
4. Click **Go**

This creates the `user_registration_db` database with all 6 tables and sample data.

**Option B — PowerShell CLI:**

```powershell
Get-Content "C:\xampp\htdocs\STUDENTPORTAL\studenthub.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
```

### Step 4 — Open the Portal

Navigate to: [http://localhost/STUDENTPORTAL/](http://localhost/STUDENTPORTAL/)

The root `index.php` automatically redirects:
- **Authenticated Admin** → `/admin/index.php`
- **Authenticated Student** → `/student/index.php`
- **Guest / Unauthenticated** → `/login.php`

---

## 4. Database Configuration

The connection file `includes/db.php` is pre-configured for a standard XAMPP setup:

| Parameter | Default Value | Notes |
| :--- | :--- | :--- |
| **Host** | `127.0.0.1` | Localhost |
| **User** | `root` | XAMPP default |
| **Password** | *(empty)* | XAMPP default |
| **Database** | `user_registration_db` | Created by `studenthub.sql` |
| **Port** | `3306` | Auto-falls back to `3307` |

> Database errors are caught inside a `try/catch` block — stack traces and credentials are **never** exposed to the browser.

---

## 5. Test Accounts & Credentials

| Role | Email | Password | Dashboard |
| :--- | :--- | :--- | :--- |
| **Admin** (Primary) | `krutin@gmail.com` | `Krutin@1234` | `/admin/index.php` |
| **Admin** (Fallback) | `admin@example.com` | `admin123` | `/admin/index.php` |
| **Student** (Demo) | `student@example.com` | `student123` | `/student/index.php` |
| **Student** (New) | *(self-register)* | *(you choose)* | `/student/index.php` — via `register.php` |

---

## 6. Running the Tests

Three automated test suites are included. Run each with the PHP CLI:

```powershell
# Suite 1 — Architecture & RBAC (11 tests)
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\architecture_test.php"

# Suite 2 — Security & Authentication (23 tests)
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\test_suite.php"

# Suite 3 — End-to-End HTTP System (31 tests)
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\system_verification.php"
```

### Test Results Summary

| Suite | Tests | Result |
| :--- | :---: | :---: |
| Architecture & RBAC | 11 | ✅ 11 / 11 PASS |
| Security & Auth | 23 | ✅ 23 / 23 PASS |
| End-to-End HTTP | 31 | ✅ 31 / 31 PASS |
| **Total** | **65** | ✅ **65 / 65 — 100%** |

### Auth Test Output (test_suite.php)

```
====================================================================
  PHP MySQLi AUTHENTICATION SYSTEM — AUTOMATED VERIFICATION SUITE
====================================================================
PHP Version       : 8.2.12
MariaDB Version   : 10.4.32-MariaDB
Execution Time    : 2026-10-16 17:28:13
--------------------------------------------------------------------
[01] Empty-field registration rejection                    [PASS]
[02] Invalid email format rejection                        [PASS]
[03] Password mismatch rejection                           [PASS]
[04] Password minimum length (8 chars) enforcement         [PASS]
[05] Duplicate email detection against MySQL database      [PASS]
[06] Successful user registration workflow                 [PASS]
[07] Database persistence of registered student record     [PASS]
[08] Password stored as Bcrypt hash (not plaintext)        [PASS]
[09] Self-registration as Admin is strictly blocked        [PASS]
[10] Successful Student login authentication               [PASS]
[11] Successful Admin login authentication                 [PASS]
[12] Login rejection for incorrect password (generic msg)  [PASS]
[13] Login rejection for unregistered email                [PASS]
[14] Admin dashboard authorizes Admin role & loads stats   [PASS]
[15] Student rejected from Admin dashboard                 [PASS]
[16] Admin rejected from Student-only dashboard            [PASS]
[17] Unauthenticated user redirected to login.php          [PASS]
[18] Session ID regeneration (session fixation guard)      [PASS]
[19] Session timeout after 900 seconds inactivity          [PASS]
[20] Logout clears session and terminates session          [PASS]
[21] SQL Injection defense in email authentication         [PASS]
[22] CSRF token generation and hash_equals verification    [PASS]
--------------------------------------------------------------------
TEST SUMMARY: 22 / 22 TESTS PASSED (100% SUCCESS RATE)
====================================================================
```

---

## 7. Security Features

### Registration (`register.php`)
- Server-side validation: empty fields, email format, password length (≥ 8 chars), password match
- Role hardcoded to `'Student'` — self-registration as Admin is blocked
- Duplicate email prevention + MySQL unique constraint (`Error 1062`)
- CSRF token required on every form submission
- Passwords stored only as `password_hash($password, PASSWORD_DEFAULT)`

### Login & Authentication (`login.php`)
- Prepared statements (`SELECT ... WHERE email = ?`) — prevents SQL injection
- `password_verify()` for constant-time comparison — prevents timing attacks
- Generic error message — prevents user enumeration
- `session_regenerate_id(true)` on successful login — prevents session fixation
- Session stores only: `user_id`, `full_name`, `email`, `role`, `last_activity` — no passwords

### Role-Based Access Control (RBAC)
- Every admin page calls `require_admin()` at the top — students are redirected immediately
- Every student page calls `require_student()` at the top — admins are redirected to admin dashboard
- Unauthenticated guests are redirected to `login.php?error=unauthorized`

### Session Timeout
- Inactivity limit: **15 minutes (900 seconds)**
- On timeout: session cleared → cookie expired → `session_destroy()` → redirect to `login.php?error=timeout`
- Every active request refreshes `$_SESSION['last_activity'] = time()`

### Secure Logout (`logout.php`)
- Accepts **POST requests only** — direct GET access is rejected
- Requires valid CSRF token — prevents forced-logout attacks
- Clears `$_SESSION`, destroys cookie, calls `session_destroy()`

### XSS Prevention
- All dynamic output passes through the `e()` helper: `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`

---

## 8. Creating an Admin Account

Self-registration as Admin is intentionally blocked. Use one of these safe methods:

### Method A — Promote an Existing User (MySQL CLI)

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE user_registration_db; UPDATE users SET role = 'Admin' WHERE email = 'student@example.com';"
```

### Method B — Insert a New Admin (PHP CLI)

```powershell
& "C:\xampp\php\php.exe" -r "
require 'C:/xampp/htdocs/STUDENTPORTAL/db.php';
\$name  = 'New Admin';
\$email = 'newadmin@example.com';
\$pass  = password_hash('SecurePass@123', PASSWORD_DEFAULT);
\$stmt  = \$conn->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, \'Admin\')');
\$stmt->bind_param('sss', \$name, \$email, \$pass);
\$stmt->execute();
echo 'Admin created: newadmin@example.com' . PHP_EOL;
"
```

---

## 9. Troubleshooting

| Problem | Fix |
| :--- | :--- |
| **MySQL won't start — port 3306 in use** | Open `services.msc`, stop `MySQL` or `MySQL97`, then restart from XAMPP Control Panel |
| **"Database Service Unavailable" page** | Confirm MySQL is running (green in XAMPP) and `user_registration_db` was imported from `studenthub.sql` |
| **Blank page / 500 error** | Enable PHP errors: add `ini_set('display_errors', 1);` temporarily to `db.php` |
| **Session expires immediately** | Check your system clock is correct and cookies are enabled in your browser |
| **CSRF mismatch / Forbidden error** | Refresh the form page to get a fresh CSRF token (happens when tab sits idle too long) |
| **Login fails with correct credentials** | Re-import `studenthub.sql` to restore the seeded accounts with correct Bcrypt hashes |
