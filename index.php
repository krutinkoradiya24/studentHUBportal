<?php
/**
 * index.php - Student Hub Portal Root Entry Router
 * 
 * Inspects active session state and dispatches users:
 * - Admin Role   -> /STUDENTPORTAL/admin/index.php
 * - Student Role -> /STUDENTPORTAL/student/index.php
 * - Guest        -> /STUDENTPORTAL/login.php
 */

require_once __DIR__ . '/includes/session.php';

init_secure_session();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: /STUDENTPORTAL/admin/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'Student') {
        header("Location: /STUDENTPORTAL/student/index.php");
        exit;
    }
}

// Unauthenticated: Direct to Login Portal
header("Location: /STUDENTPORTAL/login.php");
exit;
