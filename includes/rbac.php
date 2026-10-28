<?php
/**
 * includes/rbac.php - Strict Role-Based Access Control (RBAC)
 * 
 * AUTHORITATIVE AUTHORIZATION GUARDS:
 * 1. Admin area (admin/*) is EXCLUSIVELY accessible to users with role 'Admin'.
 *    Students attempting access are strictly blocked and redirected.
 * 2. Student area (student/*) is EXCLUSIVELY accessible to users with role 'Student'.
 *    Admins attempting access are redirected back to the Admin Panel.
 * 3. Unauthenticated visitors are redirected to the designated login tab.
 */

require_once __DIR__ . '/session.php';

init_secure_session();

/**
 * Strict Guard for Administrator Pages
 * Halts execution and redirects if the user is not an authenticated Admin.
 */
function require_admin(): array {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header("Location: /STUDENTPORTAL/login.php?error=unauthorized&role=admin");
        exit;
    }

    if ($_SESSION['role'] !== 'Admin') {
        // Logged in, but NOT an Admin (Student account attempted admin access)
        header("Location: /STUDENTPORTAL/student/index.php?error=admin_access_denied");
        exit;
    }

    return [
        'id'        => (int)$_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? 'Administrator',
        'email'     => $_SESSION['email'] ?? 'admin@example.com',
        'role'      => 'Admin'
    ];
}

/**
 * Strict Guard for Student Pages
 * Halts execution and redirects if the user is not an authenticated Student.
 */
function require_student(): array {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header("Location: /STUDENTPORTAL/login.php?error=unauthorized&role=student");
        exit;
    }

    if ($_SESSION['role'] !== 'Student') {
        // Logged in, but an Admin attempted direct student dashboard access
        header("Location: /STUDENTPORTAL/admin/index.php?notice=student_view_restricted");
        exit;
    }

    return [
        'id'        => (int)$_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? 'Student',
        'email'     => $_SESSION['email'] ?? '',
        'role'      => 'Student'
    ];
}

/**
 * Strict Guard for Admin REST/JSON API Endpoints
 */
function require_api_admin(): void {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'status'  => 'forbidden',
            'message' => 'Access Denied: Administrator role privileges are strictly required for this action.'
        ]);
        exit;
    }
}

/**
 * Strict Guard for Student REST/JSON API Endpoints
 */
function require_api_student(): void {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'status'  => 'forbidden',
            'message' => 'Access Denied: Student account credentials required.'
        ]);
        exit;
    }
}

/**
 * Check if the currently logged-in user is an Admin
 */
function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

/**
 * Check if the currently logged-in user is a Student
 */
function is_student(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Student';
}

/**
 * Get current session user payload or null if guest
 */
function get_logged_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'        => (int)$_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
        'role'      => $_SESSION['role'] ?? 'Guest'
    ];
}
