<?php
/**
 * Protected User Dashboard (php/dashboard.php)
 * Demonstrates:
 * 1. Session Access Control: Denies access and redirects to login if unauthenticated or after logout.
 * 2. Session Inactivity Timeout: Inactivity expires session after configured seconds.
 * 3. Session Security: Shows active Session ID (regenerated after login).
 */

require_once __DIR__ . '/config.php';
init_secure_session();

// Check if user is authenticated
if (empty($_SESSION['auth_user'])) {
    header('Location: login.php?error=access_denied');
    exit;
}

// Inactivity Session Timeout (default 120 seconds for easy demonstration)
$timeoutLimit = 120;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutLimit)) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: login.php?error=session_timeout');
    exit;
}
$_SESSION['last_activity'] = time();

$user = $_SESSION['auth_user'];
$currentSessionId = session_id();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard &bull; Secure User Session</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sec-badge {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        .info-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .info-table td:first-child {
            color: var(--text-dim, #94a3b8);
            width: 35%;
            font-weight: 500;
        }
        .timeout-banner {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #f59e0b;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <header class="app-header">
            <div class="brand-pill">
                <span class="brand-dot"></span>
                <span class="brand-title">SecureAuth PHP &bull; Dashboard</span>
            </div>
            <nav class="top-nav">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="test_dashboard.php" class="nav-link test-link">Test Suite</a>
                <a href="login.php?action=logout" class="nav-link logout-link">Log Out</a>
            </nav>
        </header>

        <main class="main-content">
            <div class="auth-card dashboard-card">
                <div class="card-header text-left">
                    <div class="user-avatar-badge">
                        <?= strtoupper(substr(htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.25rem;">Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <span class="sec-badge">&#10003; Session Authenticated</span>
                    </div>
                </div>

                <div class="timeout-banner">
                    <span>&#9203; Inactivity Timeout Active: <strong>120 seconds</strong></span>
                    <a href="login.php?error=session_timeout" style="color: #f59e0b; text-decoration: underline; font-weight: 600;">Simulate Timeout</a>
                </div>

                <table class="info-table">
                    <tr>
                        <td>Full Name</td>
                        <td><strong><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Username</td>
                        <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Email Address</td>
                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Current Session ID</td>
                        <td><code style="background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; color: #38bdf8; word-break: break-all;"><?= htmlspecialchars($currentSessionId, ENT_QUOTES, 'UTF-8'); ?></code></td>
                    </tr>
                    <tr>
                        <td>Session Protection</td>
                        <td><span style="color: #10b981;">&#10003; Regenerated via session_regenerate_id(true)</span></td>
                    </tr>
                </table>

                <div style="margin-top: 25px; display: flex; gap: 12px;">
                    <a href="login.php?action=logout" class="btn btn-primary" style="background: #ef4444; border-color: #dc2626; text-align: center; text-decoration: none;">
                        Log Out of Session
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
