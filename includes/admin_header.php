<?php
/**
 * includes/admin_header.php - Dedicated Administrator Layout Header & Navigation
 * 
 * Role Guard: Strictly requires 'Admin' role.
 * Students attempting to access any page including this header are blocked!
 */

require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/db.php';

// Strict Role Enforcement
$adminUser = require_admin();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Administrator Panel';
$flash = get_flash();
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?> - Student Hub Admin</title>
  <link rel="stylesheet" href="/STUDENTPORTAL/assets/css/portal.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="portal-body">
  <div class="portal-layout">
    <!-- Dedicated Admin Sidebar -->
    <aside class="portal-sidebar" id="portalSidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <i class="fas fa-shield-alt"></i>
        </div>
        <div class="sidebar-title-wrap">
          <h2>Student Hub</h2>
          <span>Admin Console</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">Core Management</div>
        <a href="/STUDENTPORTAL/admin/index.php" class="nav-item <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
          <i class="fas fa-tachometer-alt"></i> Dashboard Overview
        </a>
        <a href="/STUDENTPORTAL/admin/events.php" class="nav-item <?php echo ($currentPage === 'events.php') ? 'active' : ''; ?>">
          <i class="fas fa-calendar-alt"></i> Events Management
        </a>
        <a href="/STUDENTPORTAL/admin/timetable.php" class="nav-item <?php echo ($currentPage === 'timetable.php') ? 'active' : ''; ?>">
          <i class="fas fa-clock"></i> Timetable Management
        </a>

        <div class="nav-section-title">Academic Administration</div>
        <a href="/STUDENTPORTAL/admin/courses.php" class="nav-item <?php echo ($currentPage === 'courses.php') ? 'active' : ''; ?>">
          <i class="fas fa-book-open"></i> Course Curriculum
        </a>
        <a href="/STUDENTPORTAL/admin/students.php" class="nav-item <?php echo ($currentPage === 'students.php') ? 'active' : ''; ?>">
          <i class="fas fa-user-graduate"></i> Student Records (CRUD)
        </a>
        <a href="/STUDENTPORTAL/admin/attendance.php" class="nav-item <?php echo ($currentPage === 'attendance.php') ? 'active' : ''; ?>">
          <i class="fas fa-clipboard-check"></i> Attendance System
        </a>
        <a href="/STUDENTPORTAL/admin/notices.php" class="nav-item <?php echo ($currentPage === 'notices.php') ? 'active' : ''; ?>">
          <i class="fas fa-bullhorn"></i> Notices & Circulars
        </a>

        <div class="nav-section-title">System & Security</div>
        <a href="/STUDENTPORTAL/admin/accounts.php" class="nav-item <?php echo ($currentPage === 'accounts.php') ? 'active' : ''; ?>">
          <i class="fas fa-users-cog"></i> Admin & User Accounts
        </a>
        <a href="/STUDENTPORTAL/admin/security.php" class="nav-item <?php echo ($currentPage === 'security.php') ? 'active' : ''; ?>">
          <i class="fas fa-lock"></i> Security Audit Center
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="user-mini-card">
          <div class="user-avatar">
            <?php echo strtoupper(substr($adminUser['full_name'], 0, 1)); ?>
          </div>
          <div class="user-mini-info">
            <div class="user-mini-name"><?php echo e($adminUser['full_name']); ?></div>
            <div class="user-mini-role"><?php echo e($adminUser['email']); ?></div>
          </div>
        </div>
        <form action="/STUDENTPORTAL/logout.php" method="POST" style="margin: 0;">
          <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
          <button type="submit" class="btn-logout-icon" title="Secure Logout">
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
          <span class="badge-role badge-role-admin">
            <i class="fas fa-shield-alt"></i> Administrator
          </span>
          <a href="/STUDENTPORTAL/admin/events.php?action=new" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Quick Event
          </a>
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

        <?php if (isset($_GET['notice']) && $_GET['notice'] === 'student_view_restricted'): ?>
          <div class="portal-banner portal-banner-info">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i class="fas fa-info-circle"></i>
              <span><strong>Notice:</strong> Administrator accounts cannot access the student-only portal. You have been redirected to your Administrator Console.</span>
            </div>
            <button type="button" class="portal-banner-close">&times;</button>
          </div>
        <?php endif; ?>
