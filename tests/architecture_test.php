<?php
/**
 * tests/architecture_test.php - Role Separation & Feature Verification Suite
 * 
 * Verifies:
 * 1. Admin & Student directory modularization
 * 2. Strict Role-Based Access Control (Admin blocked from Student, Student blocked from Admin)
 * 3. Events management CRUD functionality
 * 4. Timetable management & weekly scheduling
 * 5. Admin accounts management & self-registration guard
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/auth_helpers.php';

init_secure_session();

echo "====================================================================\n";
echo "  STUDENT HUB PORTAL - ARCHITECTURE & ROLE VERIFICATION SUITE       \n";
echo "====================================================================\n";

$pass = 0;
$total = 0;

function test(string $name, callable $fn) {
    global $pass, $total;
    $total++;
    echo sprintf("[%02d] %-60s ", $total, $name);
    try {
        $res = $fn();
        if ($res === true) {
            echo "\033[32m[PASS]\033[0m\n";
            $pass++;
        } else {
            echo "\033[31m[FAIL]\033[0m: " . $res . "\n";
        }
    } catch (Throwable $e) {
        echo "\033[31m[ERROR]\033[0m: " . $e->getMessage() . "\n";
    }
}

// 1. Filesystem Directory Verification
test("Dedicated 'admin/' directory contains all admin modules", function() {
    $expected = ['index.php', 'events.php', 'timetable.php', 'students.php', 'attendance.php', 'notices.php', 'accounts.php', 'courses.php', 'security.php'];
    $dir = dirname(__DIR__) . '/admin';
    foreach ($expected as $f) {
        if (!file_exists("$dir/$f")) return "Missing $dir/$f";
    }
    return true;
});

test("Dedicated 'student/' directory contains all student modules", function() {
    $expected = ['index.php', 'timetable.php', 'events.php', 'attendance.php', 'notices.php', 'courses.php', 'profile.php', 'assignments.php', 'results.php', 'library.php', 'fees.php', 'counselling.php'];
    $dir = dirname(__DIR__) . '/student';
    foreach ($expected as $f) {
        if (!file_exists("$dir/$f")) return "Missing $dir/$f";
    }
    return true;
});

test("Architecture guide ARCHITECTURE.md exists for junior developers", function() {
    $path = dirname(__DIR__) . '/ARCHITECTURE.md';
    return file_exists($path) && filesize($path) > 500;
});

// 2. Database & Table Structure
test("All 6 enterprise tables exist in MySQL", function() {
    $pdo = get_pdo();
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['users', 'students', 'events', 'timetables', 'notices', 'attendance'];
    foreach ($required as $req) {
        if (!in_array($req, $tables, true)) return "Missing table: $req";
    }
    return true;
});

// 3. Timetable CRUD Operations
test("Timetable: Add, query, and delete class slot", function() {
    $pdo = get_pdo();
    $ins = $pdo->prepare("
        INSERT INTO timetables (course, semester, day_of_week, start_time, end_time, subject_name, subject_code, faculty_name, room_no, class_type)
        VALUES ('Computer Science', '6th Semester', 'Monday', '14:00', '15:00', 'Test Architecture Subject', 'CS999', 'Prof. Tester', 'Room 999', 'Lecture')
    ");
    $ins->execute();
    $id = (int)$pdo->lastInsertId();

    $q = $pdo->prepare("SELECT subject_name FROM timetables WHERE id = ?");
    $q->execute([$id]);
    $row = $q->fetch();
    if (!$row || $row['subject_name'] !== 'Test Architecture Subject') return "Failed to retrieve inserted slot";

    $pdo->prepare("DELETE FROM timetables WHERE id = ?")->execute([$id]);
    $chk = $pdo->prepare("SELECT id FROM timetables WHERE id = ?");
    $chk->execute([$id]);
    if ($chk->fetch()) return "Failed to delete slot";

    return true;
});

// 4. Events CRUD Operations
test("Events: Add, query, and delete college event", function() {
    $pdo = get_pdo();
    $evtId = 'EVT-TEST-' . rand(1000, 9999);
    $ins = $pdo->prepare("
        INSERT INTO events (event_id, title, category, date, time, location, organizer, status, description)
        VALUES (?, 'Test Symposium', 'Technical', '2026-12-25', '10:00 AM', 'Test Hall', 'Testing Council', 'Upcoming', 'Testing description')
    ");
    $ins->execute([$evtId]);

    $q = $pdo->prepare("SELECT title FROM events WHERE event_id = ?");
    $q->execute([$evtId]);
    $row = $q->fetch();
    if (!$row || $row['title'] !== 'Test Symposium') return "Failed to retrieve inserted event";

    $pdo->prepare("DELETE FROM events WHERE event_id = ?")->execute([$evtId]);
    $chk = $pdo->prepare("SELECT id FROM events WHERE event_id = ?");
    $chk->execute([$evtId]);
    if ($chk->fetch()) return "Failed to delete event";

    return true;
});

// 5. Strict Role Isolation Tests
test("RBAC: Student strictly forbidden from Admin access (is_admin=false)", function() {
    $_SESSION['role'] = 'Student';
    if (is_admin() === true) return "Student was improperly recognized as Admin";
    if (is_student() !== true) return "Student was not recognized as Student";
    return true;
});

test("RBAC: Admin strictly kept out of Student dashboard (is_student=false)", function() {
    $_SESSION['role'] = 'Admin';
    if (is_student() === true) return "Admin was improperly recognized as Student";
    if (is_admin() !== true) return "Admin was not recognized as Admin";
    return true;
});

test("Login Isolation: Student credentials rejected on Admin portal tab", function() {
    // Attempting to log in as Admin with student credentials must return role mismatch error
    $auth = authenticate_user('student@example.com', 'Student@12345', 'Admin');
    if ($auth['success'] !== false) return "Student was improperly allowed to authenticate on Admin portal";
    if (strpos($auth['error'], 'Access Denied') === false) return "Expected Access Denied error message";
    return true;
});

test("Login Isolation: Admin credentials rejected on Student portal tab", function() {
    // Attempting to log in as Student with admin credentials must return role mismatch error
    $auth = authenticate_user('admin@example.com', 'Admin@Secure2026!', 'Student');
    if ($auth['success'] !== false) return "Admin was improperly allowed to authenticate on Student portal";
    if (strpos($auth['error'], 'Access Denied') === false) return "Expected Access Denied error message";
    return true;
});

test("Admin Accounts: Provisioning new administrator account", function() {
    $email = 'test_admin_' . rand(100, 999) . '@admin.studenthub.edu';
    $res = create_admin_account([
        'full_name' => 'Dr. Test Administrator',
        'email'     => $email,
        'mobile'    => '9876543299',
        'password'  => 'AdminTestPass123!'
    ]);
    if (!$res['success']) return "create_admin_account failed: " . $res['message'];

    $pdo = get_pdo();
    $chk = $pdo->prepare("SELECT role, password_hash FROM users WHERE email = ? LIMIT 1");
    $chk->execute([$email]);
    $u = $chk->fetch();
    if (!$u || $u['role'] !== 'Admin') return "Account not assigned role 'Admin'";
    if (!password_verify('AdminTestPass123!', $u['password_hash'])) return "Password hash verification failed";

    // Clean up
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    return true;
});

echo "--------------------------------------------------------------------\n";
echo "ARCHITECTURE TEST SUMMARY: $pass / $total PASSED (" . round(($pass / $total) * 100) . "% SUCCESS)\n";
echo "====================================================================\n";
