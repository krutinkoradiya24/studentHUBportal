# Student Hub Portal

A secure PHP and MySQL student portal with Role-Based Access Control (RBAC) for Admins and Students, built for XAMPP.

---

## Setup

1. Open XAMPP Control Panel and start **Apache** and **MySQL**
2. Open phpMyAdmin at `http://localhost/phpmyadmin`
3. Click **Import**, select `studenthub.sql`, and click **Go**
4. Open the portal at `http://localhost/STUDENTPORTAL/`

---

## Login Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | krutin@gmail.com | Krutin@1234 |
| Admin (backup) | admin@example.com | admin123 |
| Student | student@example.com | student123 |
| New Student | Register at /register.php | — |

---

## Running Tests

```
C:\xampp\php\php.exe C:\xampp\htdocs\STUDENTPORTAL\tests\architecture_test.php
C:\xampp\php\php.exe C:\xampp\htdocs\STUDENTPORTAL\tests\test_suite.php
C:\xampp\php\php.exe C:\xampp\htdocs\STUDENTPORTAL\tests\system_verification.php
```

Result: **65 / 65 tests passed (100%)**

---

## Security Features

- Bcrypt password hashing
- CSRF token on every form
- SQL Injection prevention via prepared statements
- Session fixation defense
- 15-minute inactivity timeout
- Role-based access control on every page
- XSS prevention via output escaping

---

## Troubleshooting

| Problem | Fix |
| :--- | :--- |
| MySQL won't start | Stop conflicting MySQL service in services.msc |
| Database not found | Re-import studenthub.sql via phpMyAdmin |
| Session expires instantly | Enable cookies in browser and check system clock |
| CSRF error on form | Refresh the page to get a new token |
