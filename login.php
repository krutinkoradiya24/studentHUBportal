<?php
/**
 * Student Hub Portal - Unified Authentication Portal
 * 
 * @package    StudentHubPortal
 * @subpackage Auth
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        login.php
 * Description: Single unified login form for all users. Automatically detects
 *              whether the credentials belong to an Administrator or Student,
 *              and routes directly to the appropriate dashboard.
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth_helpers.php';

init_secure_session();

// If already authenticated, redirect directly to respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: /STUDENTPORTAL/admin/index.php");
        exit;
    } else {
        header("Location: /STUDENTPORTAL/student/index.php");
        exit;
    }
}

$errorMessage = '';
$successMessage = '';

// POST: Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken   = $_POST['csrf_token'] ?? '';
    $identifier  = trim($_POST['identifier'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = "Security token mismatch. Please try submitting again.";
    } elseif (empty($identifier) || empty($password)) {
        $errorMessage = "Please enter both your email address and password.";
    } else {
        // Authenticate credentials without requiring a pre-selected role tab
        $auth = authenticate_user($identifier, $password, null);
        if ($auth['success']) {
            // Automatically route to Admin or Student dashboard based on DB role
            if ($auth['user']['role'] === 'Admin') {
                header("Location: /STUDENTPORTAL/admin/index.php");
                exit;
            } else {
                header("Location: /STUDENTPORTAL/student/index.php");
                exit;
            }
        } else {
            $errorMessage = $auth['error'];
        }
    }
}

// Query parameters error handling
$errorCode = $_GET['error'] ?? '';
$messageCode = $_GET['message'] ?? '';

if ($errorCode === 'timeout') {
    $errorMessage = "Your session expired after 15 minutes of inactivity. Please log in again.";
} elseif ($errorCode === 'unauthorized') {
    $errorMessage = "Please sign in to access your portal.";
} elseif ($errorCode === 'admin_access_denied') {
    $errorMessage = "Access Denied: You do not have Administrator permissions.";
}

if ($messageCode === 'logged_out') {
    $successMessage = "You have been securely logged out.";
} elseif ($messageCode === 'registered') {
    $successMessage = "Registration successful! You can now sign in with your credentials.";
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - Student Hub Portal</title>
  <link rel="stylesheet" href="/STUDENTPORTAL/assets/css/portal.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body.login-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f2e2b 100%);
      padding: 24px;
    }

    .login-container {
      max-width: 440px;
      width: 100%;
      background: #ffffff;
      border-radius: var(--radius-xl);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
      overflow: hidden;
      animation: popIn 0.3s ease;
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(0.97); }
      to { opacity: 1; transform: scale(1); }
    }

    .login-header {
      padding: 36px 32px 20px;
      text-align: center;
    }

    .login-brand-icon {
      width: 58px;
      height: 58px;
      margin: 0 auto 16px;
      border-radius: 16px;
      background: linear-gradient(135deg, #0d9488, #4f46e5);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 1.6rem;
      box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4);
    }

    .login-header h1 {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--text-main);
      margin: 0 0 6px;
    }

    .login-header p {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin: 0;
    }

    .login-body {
      padding: 0 32px 32px;
    }

    .demo-credentials-box {
      background: #f8fafc;
      border: 1px dashed var(--border-color);
      border-radius: var(--radius-md);
      padding: 12px 14px;
      margin-top: 20px;
      font-size: 0.8rem;
      color: var(--text-muted);
    }
  </style>
</head>
<body class="login-page">

  <div class="login-container">
    <div class="login-header">
      <div class="login-brand-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <h1>Student Hub Portal</h1>
      <p>Sign in with your email to access your account</p>
    </div>

    <div class="login-body">
      <?php if (!empty($errorMessage)): ?>
        <div class="portal-banner portal-banner-danger" style="margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo e($errorMessage); ?></span>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMessage)): ?>
        <div class="portal-banner portal-banner-success" style="margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-check-circle"></i>
            <span><?php echo e($successMessage); ?></span>
          </div>
        </div>
      <?php endif; ?>

      <!-- Single Unified Login Form -->
      <form action="/STUDENTPORTAL/login.php" method="POST" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div class="form-group">
            <label for="identifierInput">Email Address or Mobile Number</label>
            <div style="position: relative;">
              <input type="text" id="identifierInput" name="identifier" class="form-control" placeholder="Enter your email or ID" required autocomplete="username" style="padding-left: 38px;">
              <i class="fas fa-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 0.95rem;"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="passwordInput">Password</label>
            <div style="position: relative;">
              <input type="password" id="passwordInput" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password" style="padding-left: 38px;">
              <i class="fas fa-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 0.95rem;"></i>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" id="submitBtn" style="padding: 12px; font-size: 0.95rem; font-weight: 700; width: 100%; margin-top: 6px;">
            <i class="fas fa-sign-in-alt"></i> Sign In
          </button>
        </div>
      </form>

      <!-- Student self-registration link -->
      <div style="text-align: center; margin-top: 18px; font-size: 0.88rem; color: var(--text-muted);">
        Don't have an account? <a href="/STUDENTPORTAL/register.php" style="color: #0d9488; font-weight: 600; text-decoration: none;">Register here</a>
      </div>

      <!-- Quick Demo Credentials Box -->
      <div class="demo-credentials-box">
        <strong style="color: var(--text-main); display: block; margin-bottom: 6px;">
          <i class="fas fa-key" style="color: #4f46e5;"></i> Login Credentials:
        </strong>
        <div style="display: flex; flex-direction: column; gap: 4px; font-size: 0.82rem;">
          <div>
            <strong style="color: #4f46e5;">Admin:</strong> <code>krutin@gmail.com</code> &nbsp;|&nbsp; Pass: <code>Krutin@1234</code>
          </div>
          <div>
            <strong style="color: #0d9488;">Student:</strong> Registered students only (Demo: <code>student@example.com</code> / <code>student123</code> or <a href="/STUDENTPORTAL/register.php" style="color: #0d9488; text-decoration: underline; font-weight: 600;">Register Account</a>)
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
