# Student Hub Portal

[![GitHub](https://img.shields.io/badge/GitHub-studentHUBportal-blue?logo=github)](https://github.com/krutinkoradiya24/studentHUBportal)
[![PHP](https://img.shields.io/badge/PHP-8.2-purple?logo=php)](https://www.php.net/)
[![Tests](https://img.shields.io/badge/Tests-65%2F65%20PASS-brightgreen)](./TEST_AND_SECURITY_REPORT.md)

A secure PHP + MySQL student portal with **Role-Based Access Control (RBAC)** for Admins and Students, built for XAMPP.

---

## Quick Setup

**1. Start XAMPP** — Start **Apache** and **MySQL** from the XAMPP Control Panel.

**2. Import Database** — Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) → Import → select `studenthub.sql` → Go.

**3. Open the Portal** — Visit [http://localhost/STUDENTPORTAL/](http://localhost/STUDENTPORTAL/)

---

## Login Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | `krutin@gmail.com` | `Krutin@1234` |
| Admin (backup) | `admin@example.com` | `admin123` |
| Student | `student@example.com` | `student123` |
| New Student | Register at `/register.php` | — |

---

## Running Tests

```powershell
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\architecture_test.php"
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\test_suite.php"
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\STUDENTPORTAL\tests\system_verification.php"
```

**Result: 65 / 65 tests passed (100%)**

---

## Security Features

- ✅ Bcrypt password hashing (`password_hash`)
- ✅ CSRF token on every form
- ✅ SQL Injection prevention via prepared statements
- ✅ Session fixation defense (`session_regenerate_id`)
- ✅ 15-minute inactivity timeout
- ✅ Role-based access control on every page
- ✅ XSS prevention via `htmlspecialchars()`

---

## Troubleshooting

| Problem | Fix |
| :--- | :--- |
| MySQL won't start | Stop conflicting `MySQL` service in `services.msc` |
| Database not found | Re-import `studenthub.sql` via phpMyAdmin |
| Session expires instantly | Enable cookies in browser; check system clock |
| CSRF error on form | Refresh the page to get a new token |

---

> 📋 Full report: [`TEST_AND_SECURITY_REPORT.md`](./TEST_AND_SECURITY_REPORT.md) &nbsp;|&nbsp; 🗄️ Database: [`studenthub.sql`](./studenthub.sql)
