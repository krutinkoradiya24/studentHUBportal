<?php
/**
 * register.php - Student Self-Registration Portal
 * 
 * Target Role: Student Self-Registration
 * Security: Bcrypt password hashing, CSRF verification, and input sanitation
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth_helpers.php';

init_secure_session();

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken       = $_POST['csrf_token'] ?? '';
    $fullName        = trim($_POST['full_name'] ?? '');
    $email           = strtolower(trim($_POST['email'] ?? ''));
    $mobile          = trim($_POST['mobile'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $course          = trim($_POST['course'] ?? 'Computer Science');
    $year            = trim($_POST['year'] ?? '1st Year');
    $gender          = trim($_POST['gender'] ?? 'Not Specified');

    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = "Security token mismatch. Please try again.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match. Please verify your password.";
    } else {
        $res = register_student_account([
            'full_name' => $fullName,
            'email'     => $email,
            'mobile'    => $mobile,
            'password'  => $password,
            'course'    => $course,
            'year'      => $year,
            'gender'    => $gender
        ]);

        if ($res['success']) {
            header("Location: /STUDENTPORTAL/login.php?message=registered&role=student");
            exit;
        } else {
            $errorMessage = $res['message'];
        }
    }
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration - Student Hub</title>
  <link rel="stylesheet" href="/STUDENTPORTAL/assets/css/portal.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body.register-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #0f2e2b 50%, #134e4a 100%);
      padding: 30px 20px;
    }

    .register-card {
      max-width: 600px;
      width: 100%;
      background: #ffffff;
      border-radius: var(--radius-xl);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
      padding: 36px;
      animation: popIn 0.3s ease;
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(0.97); }
      to { opacity: 1; transform: scale(1); }
    }
  </style>
</head>
<body class="register-page">

  <div class="register-card">
    <div style="text-align: center; margin-bottom: 24px;">
      <div style="width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, #0d9488, #0284c7); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; margin: 0 auto 12px; box-shadow: 0 6px 14px rgba(13,148,136,0.3);">
        <i class="fas fa-user-plus"></i>
      </div>
      <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">
        Student Registration
      </h2>
      <p style="font-size: 0.88rem; color: var(--text-muted);">
        Create your student account to access timetable, events, and academic records
      </p>
    </div>

    <?php if (!empty($errorMessage)): ?>
      <div class="portal-banner portal-banner-danger" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo e($errorMessage); ?></span>
        </div>
      </div>
    <?php endif; ?>

    <form action="/STUDENTPORTAL/register.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="form-grid">
        <div class="form-group full-width">
          <label for="fullName">Full Legal Name *</label>
          <input type="text" id="fullName" name="full_name" class="form-control" placeholder="e.g. John Doe" required value="<?php echo e($_POST['full_name'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label for="regEmail">Email Address *</label>
          <input type="email" id="regEmail" name="email" class="form-control" placeholder="john.doe@example.com" required value="<?php echo e($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label for="regMobile">10-Digit Mobile Number *</label>
          <input type="tel" id="regMobile" name="mobile" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" required value="<?php echo e($_POST['mobile'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label for="regCourse">Enrolled Course / Branch *</label>
          <select id="regCourse" name="course" class="form-control" required>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Tech">Information Tech</option>
            <option value="Mechanical">Mechanical</option>
            <option value="Civil Eng">Civil Eng</option>
            <option value="Electrical">Electrical</option>
          </select>
        </div>

        <div class="form-group">
          <label for="regYear">Academic Year *</label>
          <select id="regYear" name="year" class="form-control" required>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
          </select>
        </div>

        <div class="form-group">
          <label for="regPassword">Password * (Min 8 Characters)</label>
          <input type="password" id="regPassword" name="password" class="form-control" placeholder="••••••••" minlength="8" required>
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password *</label>
          <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="••••••••" minlength="8" required>
        </div>
      </div>

      <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-student" style="width: 100%; padding: 12px; font-size: 0.95rem; font-weight: 700;">
          <i class="fas fa-user-check"></i> Complete Student Registration
        </button>
      </div>

      <div style="text-align: center; margin-top: 18px; font-size: 0.88rem; color: var(--text-muted);">
        Already have an account? <a href="/STUDENTPORTAL/login.php?role=student" style="color: #0d9488; font-weight: 600; text-decoration: none;">Sign in here</a>
      </div>
    </form>
  </div>

</body>
</html>
