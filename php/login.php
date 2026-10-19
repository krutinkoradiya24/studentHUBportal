<?php
/**
 * User Login & Authentication Page (php/login.php)
 * Demonstrates secure password verification using PHP's native password_verify(),
 * session fixation prevention, and post-authentication profile rendering.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

init_secure_session();

// Handle Logout Action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: login.php?logged_out=1');
    exit;
}

$csrfToken      = generate_csrf_token();
$errorMessage   = '';
$successMessage = '';

if (isset($_GET['registered'])) {
    $successMessage = 'Registration successful! You can now log in with your credentials.';
} elseif (isset($_GET['logged_out']) || (isset($_GET['message']) && $_GET['message'] === 'logged_out')) {
    $successMessage = 'You have been successfully logged out.';
} elseif (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $errorMessage = 'Access Denied: You must log in first to view the dashboard.';
} elseif (isset($_GET['error']) && $_GET['error'] === 'session_timeout') {
    $errorMessage = 'Session Expired: You were logged out due to inactivity. Please log in again.';
}

$identifier = '';
$authUser   = $_SESSION['auth_user'] ?? null;

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$authUser) {
    $submittedCsrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($submittedCsrf)) {
        $errorMessage = 'Security token expired. Please refresh the page and try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $errorMessage = 'Please provide both username/email and password.';
        } else {
            try {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare('
                    SELECT id, full_name, username, email, password_hash, created_at 
                    FROM users 
                    WHERE username = :id1 OR email = :id2 
                    LIMIT 1
                ');
                $stmt->execute([
                    ':id1' => $identifier,
                    ':id2' => $identifier
                ]);
                $user = $stmt->fetch();

                // Constant-time password verification using password_verify()
                if ($user && password_verify($password, $user['password_hash'])) {
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehashStmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                        $rehashStmt->execute([':hash' => $newHash, ':id' => $user['id']]);
                    }

                    // Prevent session fixation attacks
                    session_regenerate_id(true);
                    $_SESSION['last_activity'] = time();

                    $_SESSION['auth_user'] = [
                        'id'         => (int)$user['id'],
                        'full_name'  => $user['full_name'],
                        'username'   => $user['username'],
                        'email'      => $user['email'],
                        'created_at' => $user['created_at']
                    ];

                    $authUser = $_SESSION['auth_user'];
                    $successMessage = 'Authentication successful! Welcome back, ' . htmlspecialchars($authUser['full_name'], ENT_QUOTES, 'UTF-8') . '.';
                } else {
                    $errorMessage = 'Invalid username/email or password.';
                }
            } catch (PDOException $e) {
                log_app_error('Login authentication error: ' . $e->getMessage(), $e);
                $errorMessage = 'Unable to log in at this time. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure Login Page - User Authentication with password_verify">
    <title><?= $authUser ? 'User Profile | Secure Portal' : 'Account Login | Secure User System'; ?></title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <header class="app-header">
            <div class="brand-pill">
                <span class="brand-dot"></span>
                <span class="brand-title">SecureAuth PHP &bull; MySQL</span>
            </div>
            <nav class="top-nav">
                <a href="../index.php" class="nav-link">Register</a>
                <a href="login.php" class="nav-link active">Login</a>
                <a href="test_dashboard.php" class="nav-link test-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                    Test Suite
                </a>
                <?php if ($authUser): ?>
                    <a href="login.php?action=logout" class="nav-link logout-link">Log Out</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($authUser): ?>
                <!-- Authenticated User Profile View -->
                <div class="auth-card dashboard-card">
                    <div class="card-header text-left">
                        <div class="user-avatar-badge">
                            <?= strtoupper(substr(htmlspecialchars($authUser['full_name'], ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                        </div>
                        <div>
                            <h1 class="card-title">Welcome, <?= htmlspecialchars($authUser['full_name'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
                            <p class="card-subtitle">Authenticated securely via MySQL &amp; PHP <code>password_verify()</code></p>
                        </div>
                    </div>

                    <div class="dashboard-section">
                        <div class="status-banner">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <div>
                                <strong>Session Authenticated &amp; Verified</strong>
                                <p>Your password hash was safely validated on the server. No plain-text passwords were transmitted or stored.</p>
                            </div>
                        </div>

                        <div class="profile-details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Database Record ID</span>
                                <span class="detail-value">#<?= htmlspecialchars((string)$authUser['id'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Username</span>
                                <span class="detail-value">@<?= htmlspecialchars($authUser['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email Address</span>
                                <span class="detail-value"><?= htmlspecialchars($authUser['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Member Since</span>
                                <span class="detail-value"><?= htmlspecialchars($authUser['created_at'] ?? 'Just now', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>

                        <div class="security-card">
                            <h3 class="security-heading">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Password Verification Architecture
                            </h3>
                            <ul class="security-list">
                                <li><strong>Hashing:</strong> PHP <code>password_hash($password, PASSWORD_DEFAULT)</code> (Bcrypt <code>$2y$10$...</code>)</li>
                                <li><strong>Verification:</strong> Constant-time check using <code>password_verify($password, $hash)</code></li>
                                <li><strong>Protection:</strong> PDO prepared statements with strict parameter binding (SQL Injection proof)</li>
                                <li><strong>Uniqueness:</strong> Database-level <code>UNIQUE</code> constraints on email &amp; username</li>
                            </ul>
                        </div>

                        <div class="action-buttons-row">
                            <a href="test_dashboard.php" class="btn btn-secondary">
                                View 7 Automated Test Cases
                            </a>
                            <a href="login.php?action=logout" class="btn btn-outline">
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Login Form View -->
                <div class="auth-card">
                    <div class="card-header">
                        <div class="icon-badge">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                        </div>
                        <h1 class="card-title">Welcome Back</h1>
                        <p class="card-subtitle">Sign in to your account with your username or email address</p>
                    </div>

                    <!-- Alert Banners -->
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success" role="alert">
                            <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-error" role="alert">
                            <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="auth-form" novalidate id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Username / Email Field -->
                        <div class="form-group">
                            <label for="identifier" class="form-label">Username or Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="identifier" 
                                    name="identifier" 
                                    class="form-input" 
                                    placeholder="e.g. johndoe or john@example.com"
                                    value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    autocomplete="username"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="form-group">
                            <label for="loginPassword" class="form-label">Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                <input 
                                    type="password" 
                                    id="loginPassword" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Enter your password"
                                    required
                                    autocomplete="current-password"
                                >
                                <button type="button" class="btn-toggle-password" data-target="loginPassword" aria-label="Toggle password visibility">
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
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary" id="loginBtn">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-spinner hidden" aria-hidden="true"></span>
                        </button>
                    </form>

                    <div class="card-footer">
                        <p class="footer-text">
                            Don't have an account yet? 
                            <a href="../index.php" class="auth-link">Create an account</a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <footer class="app-page-footer">
            <p>Secure User Registration System &bull; Built with PHP 8 &amp; MySQL</p>
        </footer>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>
