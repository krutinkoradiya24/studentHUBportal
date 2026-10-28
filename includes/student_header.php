<?php
/**
 * includes/student_header.php - Dedicated Student Layout Header & Navigation
 * 
 * Role Guard: Strictly requires 'Student' role.
 * Admins are blocked and redirected to the Admin Console.
 */

require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/db.php';

// Strict Role Enforcement
$studentUser = require_student();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Student Portal';
$flash = get_flash();
$csrfToken = generate_csrf_token();

// Fetch detailed student data if available
$studCourse = $_SESSION['course'] ?? 'Computer Science';
$studYear = $_SESSION['year'] ?? '3rd Year';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?> - Student Hub</title>
  <link rel="stylesheet" href="/STUDENTPORTAL/assets/css/portal.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="portal-body">
  <div class="portal-layout">
    <!-- Dedicated Student Sidebar -->
    <aside class="portal-sidebar student-theme" id="portalSidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="sidebar-title-wrap">
          <h2>Student Hub</h2>
          <span>Student Portal</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">Academic Space</div>
        <a href="/STUDENTPORTAL/student/index.php" class="nav-item <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
          <i class="fas fa-home"></i> My Dashboard
        </a>
        <a href="/STUDENTPORTAL/student/timetable.php" class="nav-item <?php echo ($currentPage === 'timetable.php') ? 'active' : ''; ?>">
          <i class="fas fa-clock"></i> Class Timetable
        </a>
        <a href="/STUDENTPORTAL/student/events.php" class="nav-item <?php echo ($currentPage === 'events.php') ? 'active' : ''; ?>">
          <i class="fas fa-calendar-alt"></i> College Events
        </a>
        <a href="/STUDENTPORTAL/student/attendance.php" class="nav-item <?php echo ($currentPage === 'attendance.php') ? 'active' : ''; ?>">
          <i class="fas fa-chart-pie"></i> Attendance Tracker
        </a>
        <a href="/STUDENTPORTAL/student/assignments.php" class="nav-item <?php echo ($currentPage === 'assignments.php') ? 'active' : ''; ?>">
          <i class="fas fa-tasks"></i> My Assignments
        </a>
        <a href="/STUDENTPORTAL/student/results.php" class="nav-item <?php echo ($currentPage === 'results.php') ? 'active' : ''; ?>">
          <i class="fas fa-award"></i> Exam Results
        </a>

        <div class="nav-section-title">Campus Resources</div>
        <a href="/STUDENTPORTAL/student/notices.php" class="nav-item <?php echo ($currentPage === 'notices.php') ? 'active' : ''; ?>">
          <i class="fas fa-bullhorn"></i> Notices & Circulars
        </a>
        <a href="/STUDENTPORTAL/student/courses.php" class="nav-item <?php echo ($currentPage === 'courses.php') ? 'active' : ''; ?>">
          <i class="fas fa-book-open"></i> Enrolled Courses
        </a>
        <a href="/STUDENTPORTAL/student/library.php" class="nav-item <?php echo ($currentPage === 'library.php') ? 'active' : ''; ?>">
          <i class="fas fa-book-reader"></i> Digital E-Library
        </a>
        <a href="/STUDENTPORTAL/student/fees.php" class="nav-item <?php echo ($currentPage === 'fees.php') ? 'active' : ''; ?>">
          <i class="fas fa-file-invoice-dollar"></i> Fee Receipts
        </a>
        <a href="/STUDENTPORTAL/student/counselling.php" class="nav-item <?php echo ($currentPage === 'counselling.php') ? 'active' : ''; ?>">
          <i class="fas fa-user-friends"></i> Mentorship & Counselling
        </a>
        <a href="/STUDENTPORTAL/student/profile.php" class="nav-item <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
          <i class="fas fa-id-card"></i> Student Profile
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="user-mini-card">
          <div class="user-avatar" style="background: rgba(13, 148, 136, 0.4);">
            <?php echo strtoupper(substr($studentUser['full_name'], 0, 1)); ?>
          </div>
          <div class="user-mini-info">
            <div class="user-mini-name"><?php echo e($studentUser['full_name']); ?></div>
            <div class="user-mini-role"><?php echo e($studCourse); ?></div>
          </div>
        </div>
        <form action="/STUDENTPORTAL/logout.php" method="POST" style="margin: 0;">
          <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
          <button type="submit" class="btn-logout-icon" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </form>
      </div>
    </aside>

    <!-- Main Workspace -->
    <main class="portal-main">
      <header class="portal-topbar">
        <div class="topbar-left">
          <button class="btn-toggle-sidebar" id="toggleSidebarBtn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
          </button>
          <div class="topbar-page-title">
            <h1><?php echo e($pageTitle); ?></h1>
          </div>
        </div>
        <div class="topbar-right">
          <span class="badge-role badge-role-student">
            <i class="fas fa-user-graduate"></i> Student Account
          </span>
          <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">
            <i class="fas fa-graduation-cap"></i> <?php echo e($studCourse); ?>
          </span>
        </div>
      </header>

      <div class="portal-content">
        <!-- Flash Message Banner -->
        <?php if ($flash): ?>
          <div class="portal-banner portal-banner-<?php echo e($flash['type']); ?>">
            <div style="display: flex; align-items: center; gap: 10px;">
              <?php if ($flash['type'] === 'success'): ?>
                <i class="fas fa-check-circle"></i>
              <?php elseif ($flash['type'] === 'warning'): ?>
                <i class="fas fa-exclamation-triangle"></i>
              <?php else: ?>
                <i class="fas fa-times-circle"></i>
              <?php endif; ?>
              <span><?php echo e($flash['message']); ?></span>
            </div>
            <button type="button" class="portal-banner-close">&times;</button>
          </div>
        <?php endif; ?>

        <!-- Access Denied Banner when Student tried to access Admin -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'admin_access_denied'): ?>
          <div class="portal-banner portal-banner-danger">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i class="fas fa-shield-alt" style="font-size: 1.2rem;"></i>
              <div>
                <strong>Access Denied:</strong> You are logged in with a Student account and do not have privileges to view or manage the Administrator Panel.
              </div>
            </div>
            <button type="button" class="portal-banner-close">&times;</button>
          </div>
        <?php endif; ?>
