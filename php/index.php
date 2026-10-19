<?php
/**
 * User Registration Page (index.php)
 * Modern, responsive registration interface with client and server-side validation,
 * live strength meter, show/hide password, and CSRF protection.
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';

init_secure_session();

$csrfToken = generate_csrf_token();

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
$formErrors   = $_SESSION['form_errors'] ?? [];
$oldInput     = $_SESSION['old_input'] ?? [];

// Clear flash messages after reading
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure User Registration System with PHP 8, MySQL PDO, Bcrypt Password Hashing and Input Validation">
    <title>Create Your Account | Secure Registration Portal</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Application Top Navigation -->
        <header class="app-header">
            <div class="brand-pill">
                <span class="brand-dot"></span>
                <span class="brand-title">SecureAuth PHP &bull; MySQL</span>
            </div>
            <nav class="top-nav">
                <a href="index.php" class="nav-link active">Register</a>
                <a href="php/login.php" class="nav-link">Login</a>
                <a href="php/test_dashboard.php" class="nav-link test-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                    Test Suite (7 Cases)
                </a>
            </nav>
        </header>

        <!-- Main Registration Container -->
        <main class="main-content">
            <div class="auth-card" id="registrationCard">
                <div class="card-header">
                    <div class="icon-badge">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </div>
                    <h1 class="card-title">Create an Account</h1>
                    <p class="card-subtitle">Register securely with automated backend MySQL validation &amp; bcrypt encryption</p>
                </div>

                <!-- Global Feedback Alert (Dynamic via JS or Server Fallback) -->
                <div id="alertContainer" class="alert-container <?= !empty($flashSuccess) ? 'has-success' : (!empty($flashError) ? 'has-error' : 'hidden'); ?>">
                    <?php if (!empty($flashSuccess)): ?>
                        <div class="alert alert-success" role="alert">
                            <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span class="alert-text"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php elseif (!empty($flashError)): ?>
                        <div class="alert alert-error" role="alert">
                            <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <span class="alert-text"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Registration Form -->
                <form action="php/register.php" method="POST" class="auth-form" id="registerForm" novalidate>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <!-- AJAX Submission Flag -->
                    <input type="hidden" name="is_ajax" value="1">

                    <!-- Field 1: Full Name -->
                    <div class="form-group" id="group-full_name">
                        <label for="full_name" class="form-label">
                            Full Name <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="form-input" 
                                placeholder="e.g. John Doe"
                                value="<?= htmlspecialchars($oldInput['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                autocomplete="name"
                            >
                        </div>
                        <span class="field-feedback error-message" id="error-full_name">
                            <?= htmlspecialchars($formErrors['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <!-- Field 2: Username -->
                    <div class="form-group" id="group-username">
                        <label for="username" class="form-label">
                            Username <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                placeholder="e.g. johndoe_99"
                                value="<?= htmlspecialchars($oldInput['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                autocomplete="username"
                                spellcheck="false"
                            >
                        </div>
                        <span class="field-feedback error-message" id="error-username">
                            <?= htmlspecialchars($formErrors['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <!-- Field 3: Email Address -->
                    <div class="form-group" id="group-email">
                        <label for="email" class="form-label">
                            Email Address <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="e.g. john@example.com"
                                value="<?= htmlspecialchars($oldInput['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                autocomplete="email"
                            >
                        </div>
                        <span class="field-feedback error-message" id="error-email">
                            <?= htmlspecialchars($formErrors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <!-- Field 4: Password -->
                    <div class="form-group" id="group-password">
                        <label for="password" class="form-label">
                            Password <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="At least 6 characters"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="btn-toggle-password" data-target="password" aria-label="Toggle password visibility">
                                <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-hide hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="password-meter-container" id="passwordMeter">
                            <div class="meter-bar-track">
                                <div class="meter-bar-fill" id="meterFill"></div>
                            </div>
                            <div class="meter-label-row">
                                <span class="meter-label-text">Strength: <strong id="meterText">None</strong></span>
                                <span class="meter-hint" id="meterHint">Min. 6 characters</span>
                            </div>
                        </div>
                        <span class="field-feedback error-message" id="error-password">
                            <?= htmlspecialchars($formErrors['password'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <!-- Field 5: Confirm Password -->
                    <div class="form-group" id="group-confirm_password">
                        <label for="confirm_password" class="form-label">
                            Confirm Password <span class="required-asterisk">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Re-enter password to confirm"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="btn-toggle-password" data-target="confirm_password" aria-label="Toggle confirm password visibility">
                                <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-hide hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <span class="field-feedback error-message" id="error-confirm_password">
                            <?= htmlspecialchars($formErrors['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn btn-primary" id="registerBtn">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-spinner hidden" id="btnSpinner" aria-hidden="true"></span>
                    </button>
                </form>

                <!-- Footer with link to Login Page -->
                <div class="card-footer">
                    <p class="footer-text">
                        Already have an account? 
                        <a href="php/login.php" class="auth-link">Sign in here</a>
                    </p>
                    <div class="security-badge-pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span>Encrypted with PHP <code>password_hash()</code> &amp; MySQL InnoDB</span>
                    </div>
                </div>
            </div>
        </main>

        <footer class="app-page-footer">
            <p>Secure User Registration System &bull; Built with PHP 8, MySQL PDO, &amp; Vanilla JavaScript</p>
        </footer>
    </div>

    <!-- Client-Side Validation and Real-Time Feedback Script -->
    <script src="js/script.js"></script>
</body>
</html>
