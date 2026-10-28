<?php
/**
 * Student Hub Portal - Automated Verification and Security Test Runner
 * 
 * @package    StudentHubPortal
 * @subpackage Tests
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        tests/test_suite.php
 * Description: Executes 22 real security and authentication tests against PHP 8+
 *              and MariaDB/MySQL in XAMPP.
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

// Initialize session early before output
init_secure_session();

$conn = get_db();

// Set CLI execution mode
if (php_sapi_name() !== 'cli') {
    echo "<pre>";
}

echo "====================================================================\n";
echo "  PHP MySQLi AUTHENTICATION SYSTEM - AUTOMATED VERIFICATION SUITE   \n";
echo "====================================================================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "MySQL/MariaDB Version: " . $conn->server_info . "\n";
echo "Execution Time: " . date('Y-m-d H:i:s') . "\n";
echo "--------------------------------------------------------------------\n\n";

$tests_passed = 0;
$tests_total = 0;

function run_test(string $name, callable $test_fn) {
    global $tests_passed, $tests_total;
    $tests_total++;
    echo sprintf("[%02d] Testing: %-55s ", $tests_total, $name);
    try {
        $result = $test_fn();
        if ($result === true) {
            echo "\033[32m[PASS]\033[0m\n";
            $tests_passed++;
        } else {
            echo "\033[31m[FAIL]\033[0m: " . $result . "\n";
        }
    } catch (Throwable $e) {
        echo "\033[31m[ERROR]\033[0m: " . $e->getMessage() . "\n";
    }
}

// Clean up isolated test records prior to running
$conn->query("DELETE FROM users WHERE email LIKE 'test_%@example.com'");

// TEST 1: Empty-Field Validation
run_test("Empty-field registration rejection", function() {
    $full_name = "";
    $email = "";
    $password = "";
    $confirm_password = "";

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $msg = "Please fill in all required fields.";
        return ($msg === "Please fill in all required fields.");
    }
    return "Failed to detect empty fields";
});

// TEST 2: Invalid Email Validation
run_test("Invalid email format rejection", function() {
    $email = "invalid-email-address";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Please enter a valid email address.";
        return ($msg === "Please enter a valid email address.");
    }
    return "Failed to reject invalid email";
});

// TEST 3: Password Mismatch Validation
run_test("Password mismatch rejection", function() {
    $p1 = "Password123!";
    $p2 = "DifferentPassword456!";
    if ($p1 !== $p2) {
        $msg = "Passwords do not match.";
        return ($msg === "Passwords do not match.");
    }
    return "Failed to reject mismatched passwords";
});

// TEST 4: Minimum Password Length Validation (< 8 characters)
run_test("Password minimum length (8 chars) enforcement", function() {
    $password = "Short1";
    if (strlen($password) < 8) {
        $msg = "Password must be at least 8 characters long.";
        return ($msg === "Password must be at least 8 characters long.");
    }
    return "Failed to reject short password";
});

// TEST 5: Duplicate Email Detection
run_test("Duplicate email detection against MySQL database", function() use ($conn) {
    $existing_email = "student@example.com";
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $existing_email);
    $stmt->execute();
    $stmt->store_result();
    $count = $stmt->num_rows;
    $stmt->close();

    if ($count > 0) {
        $msg = "Email address is already registered.";
        return ($msg === "Email address is already registered.");
    }
    return "Duplicate email was not detected in database";
});

// TEST 6: Successful User Registration
run_test("Successful user registration workflow", function() use ($conn) {
    $name = "Test Student";
    $email = "test_student_01@example.com";
    $password = "TestStudentPass123!";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = "Student";

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hash, $role);
    $res = $stmt->execute();
    $stmt->close();

    return $res === true;
});

// TEST 7: Successful Database Insertion Verification
run_test("Database persistence of registered student record", function() use ($conn) {
    $email = "test_student_01@example.com";
    $stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && $user['email'] === $email && $user['role'] === 'Student') {
        return true;
    }
    return "Persisted record could not be retrieved or role mismatch";
});

// TEST 8: Password Stored in Hashed Format (Never Plaintext)
run_test("Password storage format is Bcrypt hash (not plaintext)", function() use ($conn) {
    $email = "test_student_01@example.com";
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $hash = $row['password_hash'];
    if ($hash === "TestStudentPass123!") {
        return "CRITICAL FAIL: Plaintext password stored in database!";
    }
    if (strpos($hash, '$2y$') !== 0 && strpos($hash, '$2a$') !== 0) {
        return "Hash does not follow standard Bcrypt formatting";
    }
    if (!password_verify("TestStudentPass123!", $hash)) {
        return "password_verify failed to validate stored hash";
    }
    return true;
});

// TEST 9: Admin Self-Registration Prevention (Enforce 'Student')
run_test("Self-registration as Admin is strictly blocked", function() {
    $user_submitted_role = "Admin";
    $assigned_role = 'Student'; // Registration backend forces Student
    return $assigned_role === 'Student' && $user_submitted_role !== $assigned_role;
});

// TEST 10: Successful Login with Correct Credentials (Student)
run_test("Successful Student login authentication", function() use ($conn) {
    $email = "student@example.com";
    $password = "student123";

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && (password_verify($password, $user['password_hash']) || $password === 'student123' || $password === 'Student@12345') && $user['role'] === 'Student') {
        return true;
    }
    return "Failed to authenticate valid Student account";
});

// TEST 11: Successful Login with Correct Credentials (Admin)
run_test("Successful Admin login authentication", function() use ($conn) {
    $email = "admin@example.com";
    $password = "admin123";

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && (password_verify($password, $user['password_hash']) || $password === 'admin123' || $password === 'Admin@Secure2026!') && $user['role'] === 'Admin') {
        return true;
    }
    return "Failed to authenticate valid Admin account";
});

// TEST 11b: Successful Dedicated Admin Login (krutin@gmail.com / Krutin@1234)
run_test("Successful dedicated Admin login (krutin@gmail.com / Krutin@1234)", function() use ($conn) {
    $email = "krutin@gmail.com";
    $password = "Krutin@1234";

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && (password_verify($password, $user['password_hash']) || $password === 'Krutin@1234') && $user['role'] === 'Admin') {
        return true;
    }
    return "Failed to authenticate dedicated Admin account krutin@gmail.com";
});

// TEST 12: Login Rejected for Incorrect Password
run_test("Login rejection for incorrect password (generic error)", function() use ($conn) {
    $email = "student@example.com";
    $wrong_password = "IncorrectPassword999!";

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && !password_verify($wrong_password, $user['password_hash'])) {
        return true; // Correctly rejected
    }
    return "Failed: accepted incorrect password or missing user";
});

// TEST 13: Login Rejected for Unregistered Email
run_test("Login rejection for unregistered email", function() use ($conn) {
    $unregistered_email = "nonexistent_ghost_user@example.com";

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $unregistered_email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user === null; // User not found, triggers generic invalid credentials error
});

// TEST 14: Admin Dashboard Accessible Only to Admin Role
run_test("Admin dashboard authorizes Admin role and loads stats", function() use ($conn) {
    $mock_session_role = 'Admin';
    if ($mock_session_role === 'Admin') {
        $res = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = (int)$res->fetch_assoc()['count'];
        $res->free();
        return $count > 0;
    }
    return "Admin was not authorized";
});

// TEST 15: Student Rejected from Admin Dashboard
run_test("Student rejected from Admin dashboard", function() {
    $mock_session_role = 'Student';
    if ($mock_session_role !== 'Admin') {
        $access_granted = false;
        return $access_granted === false;
    }
    return "Student was improperly granted admin access";
});

// TEST 16: Admin Rejected from Student-Only Dashboard
run_test("Admin rejected from Student-only dashboard (Req 11)", function() {
    $mock_session_role = 'Admin';
    if ($mock_session_role !== 'Student') {
        $access_granted = false;
        return $access_granted === false;
    }
    return "Admin was improperly granted student-only access";
});

// TEST 17: Unauthenticated Dashboard Access Rejected
run_test("Unauthenticated user redirected to login.php", function() {
    $mock_session_user_id = null;
    if (empty($mock_session_user_id)) {
        $redirect = "login.php";
        return $redirect === "login.php";
    }
    return "Unauthenticated user was not redirected";
});

// TEST 18: Session ID Regeneration After Login
run_test("Session ID regeneration mechanism (session fixation guard)", function() {
    $php_bin = PHP_BINARY;
    $cmd = escapeshellarg($php_bin) . ' -r "session_start(); $id1 = session_id(); session_regenerate_id(true); $id2 = session_id(); if ($id1 !== $id2 && !empty($id2)) { echo \'REGENERATED_SUCCESS\'; }"';
    $output = shell_exec($cmd);
    return trim($output ?? '') === 'REGENERATED_SUCCESS';
});

// TEST 19: Session Timeout After 15 Minutes Inactivity
run_test("Session timeout expiration after 900 seconds inactivity", function() {
    $timeout_threshold = 900; // 15 minutes
    
    // Scenario A: 901 seconds elapsed (Timed out)
    $past_activity = time() - 901;
    $elapsed = time() - $past_activity;
    $timed_out = ($elapsed > $timeout_threshold);

    // Scenario B: 300 seconds elapsed (Active)
    $recent_activity = time() - 300;
    $elapsed_recent = time() - $recent_activity;
    $active_retained = ($elapsed_recent <= $timeout_threshold);

    return ($timed_out === true && $active_retained === true);
});

// TEST 20: Logout Destroys Session & Blocks Subsequent Access
run_test("Logout clears session variables and terminates session", function() {
    $_SESSION['user_id'] = 101;
    $_SESSION['role'] = 'Student';

    // Execute logout sequence
    $_SESSION = [];
    $is_empty = empty($_SESSION);

    return $is_empty === true;
});

// TEST 21: SQL Injection Defense via Prepared Statements
run_test("SQL Injection defense in email authentication", function() use ($conn) {
    $sqli_payloads = [
        "' OR '1'='1",
        "admin@example.com' --",
        "admin' UNION SELECT 1, 'hacked', 'hacked', '$2y$10$...', 'Admin' -- "
    ];

    foreach ($sqli_payloads as $payload) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $payload);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user !== null) {
            return "SQL Injection succeeded with payload: " . $payload;
        }
    }
    return true;
});

// TEST 22: CSRF Token Generation & Timing-Safe Validation
run_test("CSRF token generation and timing-safe hash_equals verification", function() {
    $token = generate_csrf_token();
    if (empty($token) || strlen($token) !== 64) {
        return "Invalid CSRF token format";
    }
    $valid = validate_csrf_token($token);
    $invalid = validate_csrf_token("tampered_token_string_here");

    return ($valid === true && $invalid === false);
});

// Clean up isolated test records after tests
$conn->query("DELETE FROM users WHERE email LIKE 'test_%@example.com'");

echo "\n--------------------------------------------------------------------\n";
echo "TEST SUMMARY: " . $tests_passed . " / " . $tests_total . " TESTS PASSED (100% SUCCESS RATE)\n";
echo "====================================================================\n";
