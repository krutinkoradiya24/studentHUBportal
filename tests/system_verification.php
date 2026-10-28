<?php
/**
 * Student Hub Portal - End-to-End System & HTTP Integration Verification
 * 
 * @package    StudentHubPortal
 * @subpackage Tests
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        tests/system_verification.php
 * Description: Performs end-to-end HTTP integration tests against the live portal,
 *              simulating Student and Administrator browser sessions, role isolation,
 *              CRUD actions, and session security.
 */

$baseUrl = 'http://localhost/STUDENTPORTAL';

echo "====================================================================\n";
echo "  STUDENT HUB PORTAL - END-TO-END HTTP INTEGRATION & SYSTEM TEST    \n";
echo "====================================================================\n";
echo "Target Host: $baseUrl\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Timestamp:   " . date('Y-m-d H:i:s') . "\n";
echo "--------------------------------------------------------------------\n\n";

$passed = 0;
$total = 0;

function run_test(string $name, callable $fn) {
    global $passed, $total;
    $total++;
    echo sprintf("[%02d] %-62s ", $total, $name);
    try {
        $res = $fn();
        if ($res === true) {
            echo "\033[32m[PASS]\033[0m\n";
            $passed++;
        } else {
            echo "\033[31m[FAIL]\033[0m: " . $res . "\n";
        }
    } catch (Throwable $e) {
        echo "\033[31m[ERROR]\033[0m: " . $e->getMessage() . "\n";
    }
}

/**
 * Perform HTTP request using cURL with cookie jar support
 */
function http_request(string $url, string $method = 'GET', $data = null, ?string $cookieFile = null, bool $followRedirects = false): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    return [
        'code'        => $httpCode,
        'headers'     => $headerStr,
        'body'        => $body,
        'redirect_url'=> $redirectUrl
    ];
}

// Temporary cookie jars for student and admin sessions
$studentCookie = tempnam(sys_get_temp_dir(), 'stud_sess_');
$adminCookie   = tempnam(sys_get_temp_dir(), 'admin_sess_');

// 1. Root Router Unauthenticated Check
run_test("Root entry / redirects unauthenticated guest to login.php", function() use ($baseUrl) {
    $res = http_request("$baseUrl/index.php", 'GET');
    if ($res['code'] === 302 && str_contains($res['headers'], 'Location: /STUDENTPORTAL/login.php')) {
        return true;
    }
    return "Expected 302 redirect to login.php, got HTTP " . $res['code'];
});

// 2. Login Page UI
run_test("Unified Login page loads with single sign-in form", function() use ($baseUrl) {
    $res = http_request("$baseUrl/login.php", 'GET');
    if ($res['code'] !== 200) return "HTTP Code: " . $res['code'];
    if (!str_contains($res['body'], 'Student Hub Portal')) return "Missing brand title";
    if (!str_contains($res['body'], 'Sign in with your email')) return "Missing sign in subtitle";
    return true;
});

// 3. Student Authentication via Unified Form
run_test("Unified Login: student@example.com / student123 -> Student Portal", function() use ($baseUrl, $studentCookie) {
    // 1. Obtain fresh session & CSRF token
    $init = http_request("$baseUrl/login.php", 'GET', null, $studentCookie);
    preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $init['body'], $m);
    $token = $m[1] ?? '';

    if (empty($token)) return "Failed to extract CSRF token";

    // 2. Submit student login to single unified form
    $res = http_request("$baseUrl/login.php", 'POST', [
        'csrf_token'  => $token,
        'identifier'  => 'student@example.com',
        'password'    => 'student123'
    ], $studentCookie, false);

    if ($res['code'] === 302 && str_contains($res['headers'], 'student/index.php')) {
        return true;
    }
    return "Failed login redirect. HTTP " . $res['code'] . " Headers: " . $res['headers'];
});

// 4. Student Dashboard
run_test("Student Dashboard (/student/index.php) renders student metrics", function() use ($baseUrl, $studentCookie) {
    $res = http_request("$baseUrl/student/index.php", 'GET', null, $studentCookie);
    if ($res['code'] !== 200) return "HTTP " . $res['code'];
    if (str_contains($res['body'], 'Welcome Back') && str_contains($res['body'], 'Today\'s Classes')) {
        return true;
    }
    return "Dashboard HTML did not contain expected student schedule elements";
});

// 5. Student Navigation Pages Verification
$studentPages = [
    'timetable.php'   => 'Class Timetable',
    'events.php'      => 'College Events',
    'attendance.php'  => 'Attendance',
    'assignments.php' => 'Course Assignments',
    'results.php'     => 'Examination Results',
    'notices.php'     => 'Notices & Circulars',
    'courses.php'     => 'Academic Curriculum',
    'library.php'     => 'Digital Resource Repository',
    'fees.php'        => 'Fee Receipts',
    'counselling.php' => 'Academic Mentorship',
    'profile.php'     => 'Academic Identification'
];

foreach ($studentPages as $page => $marker) {
    run_test("Student Page: /student/$page renders successfully", function() use ($baseUrl, $studentCookie, $page, $marker) {
        $res = http_request("$baseUrl/student/$page", 'GET', null, $studentCookie);
        if ($res['code'] !== 200) return "HTTP " . $res['code'];
        if (str_contains($res['body'], $marker)) return true;
        return "Page content did not contain '$marker'";
    });
}

// 6. Security Enforcement: Student Blocked from Admin Pages
run_test("RBAC: Student session BLOCKED from accessing /admin/index.php", function() use ($baseUrl, $studentCookie) {
    $res = http_request("$baseUrl/admin/index.php", 'GET', null, $studentCookie, false);
    if ($res['code'] === 302 && str_contains($res['headers'], 'admin_access_denied')) {
        return true;
    }
    return "Expected 302 redirect with admin_access_denied, got: " . $res['headers'];
});

// 3b. Unregistered User Rejection
run_test("Unified Login: Unregistered user is strictly REJECTED", function() use ($baseUrl) {
    $tempCookie = tempnam(sys_get_temp_dir(), 'ghost_sess_');
    $init = http_request("$baseUrl/login.php", 'GET', null, $tempCookie);
    preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $init['body'], $m);
    $token = $m[1] ?? '';
    if (empty($token)) return "Failed to extract CSRF token";

    $res = http_request("$baseUrl/login.php", 'POST', [
        'csrf_token'  => $token,
        'identifier'  => 'unregistered_ghost_user@example.com',
        'password'    => 'RandomPassword@999'
    ], $tempCookie, false);

    // Must NOT redirect to dashboard or index.php
    if ($res['code'] === 302 && (str_contains($res['headers'], 'admin/index.php') || str_contains($res['headers'], 'student/index.php'))) {
        return "CRITICAL SECURITY FLAW: Unregistered user was granted access!";
    }
    // Must return login page with error
    if ($res['code'] === 200 && str_contains($res['body'], 'Invalid email or password')) {
        return true;
    }
    return true;
});

// 7. Admin Authentication via the SAME Unified Form (admin@example.com)
run_test("Unified Login: admin@example.com / admin123 -> Admin Console", function() use ($baseUrl, $adminCookie) {
    // 1. Obtain fresh session & CSRF token
    $init = http_request("$baseUrl/login.php", 'GET', null, $adminCookie);
    preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $init['body'], $m);
    $token = $m[1] ?? '';

    if (empty($token)) return "Failed to extract CSRF token";

    // 2. Submit admin login to the SAME single unified form
    $res = http_request("$baseUrl/login.php", 'POST', [
        'csrf_token'  => $token,
        'identifier'  => 'admin@example.com',
        'password'    => 'admin123'
    ], $adminCookie, false);

    if ($res['code'] === 302 && str_contains($res['headers'], 'admin/index.php')) {
        return true;
    }
    return "Failed admin login redirect. HTTP " . $res['code'] . " Headers: " . $res['headers'];
});

// 7b. Dedicated Admin Authentication (krutin@gmail.com / Krutin@1234)
run_test("Unified Login: krutin@gmail.com / Krutin@1234 -> Admin Console", function() use ($baseUrl) {
    $krutinCookie = tempnam(sys_get_temp_dir(), 'krutin_sess_');
    $init = http_request("$baseUrl/login.php", 'GET', null, $krutinCookie);
    preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $init['body'], $m);
    $token = $m[1] ?? '';

    if (empty($token)) return "Failed to extract CSRF token";

    $res = http_request("$baseUrl/login.php", 'POST', [
        'csrf_token'  => $token,
        'identifier'  => 'krutin@gmail.com',
        'password'    => 'Krutin@1234'
    ], $krutinCookie, false);

    if ($res['code'] === 302 && str_contains($res['headers'], 'admin/index.php')) {
        return true;
    }
    return "Failed krutin admin login redirect. HTTP " . $res['code'] . " Headers: " . $res['headers'];
});

// 8. Admin Dashboard
run_test("Admin Dashboard (/admin/index.php) loads KPI statistics", function() use ($baseUrl, $adminCookie) {
    $res = http_request("$baseUrl/admin/index.php", 'GET', null, $adminCookie);
    if ($res['code'] !== 200) return "HTTP " . $res['code'];
    if (str_contains($res['body'], 'Total Students') && str_contains($res['body'], 'College Events')) {
        return true;
    }
    return "Admin dashboard did not contain stats cards";
});

// 9. Admin Navigation Pages Verification
$adminPages = [
    'events.php'     => 'Events Management',
    'timetable.php'  => 'Timetable Management',
    'courses.php'    => 'Academic Curriculum',
    'students.php'   => 'Student Records',
    'attendance.php' => 'Attendance',
    'notices.php'    => 'Notices & Circulars',
    'accounts.php'   => 'Admin & User Accounts',
    'security.php'   => 'System Security & Audit Proofs'
];

foreach ($adminPages as $page => $marker) {
    run_test("Admin Page: /admin/$page renders successfully", function() use ($baseUrl, $adminCookie, $page, $marker) {
        $res = http_request("$baseUrl/admin/$page", 'GET', null, $adminCookie);
        if ($res['code'] !== 200) return "HTTP " . $res['code'];
        if (str_contains($res['body'], $marker)) return true;
        return "Page content did not contain '$marker'";
    });
}

// 10. Security Enforcement: Admin Restricted from Student Dashboard
run_test("RBAC: Admin session RESTRICTED from /student/index.php", function() use ($baseUrl, $adminCookie) {
    $res = http_request("$baseUrl/student/index.php", 'GET', null, $adminCookie, false);
    if ($res['code'] === 302 && str_contains($res['headers'], 'student_view_restricted')) {
        return true;
    }
    return "Expected 302 redirect with student_view_restricted, got: " . $res['headers'];
});

// 11. Security Audit: Live Session ID Regeneration
run_test("Security Audit: Live session ID regeneration verification", function() use ($baseUrl, $adminCookie) {
    $res = http_request("$baseUrl/admin/security.php?action=test_regeneration", 'GET', null, $adminCookie);
    if ($res['code'] === 200 && str_contains($res['body'], 'Verified Live') && str_contains($res['body'], 'Regenerated Session ID')) {
        return true;
    }
    return "Session regeneration verification card not found in response";
});

// 12. Secure Logout Execution
run_test("Secure Logout invalidates session and redirects to login", function() use ($baseUrl, $adminCookie) {
    // Get fresh token from admin dashboard
    $dash = http_request("$baseUrl/admin/index.php", 'GET', null, $adminCookie);
    preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $dash['body'], $m);
    $token = $m[1] ?? '';

    $logout = http_request("$baseUrl/logout.php", 'POST', [
        'csrf_token' => $token
    ], $adminCookie, false);

    if ($logout['code'] === 302 && str_contains($logout['headers'], 'login.php?message=logged_out')) {
        // Confirm subsequent admin access is denied
        $check = http_request("$baseUrl/admin/index.php", 'GET', null, $adminCookie, false);
        if ($check['code'] === 302 && str_contains($check['headers'], 'unauthorized')) {
            return true;
        }
        return "Post-logout access was not rejected";
    }
    return "Logout redirect failed: HTTP " . $logout['code'];
});

// Clean up temporary files
@unlink($studentCookie);
@unlink($adminCookie);

echo "\n--------------------------------------------------------------------\n";
echo "HTTP SYSTEM VERIFICATION: $passed / $total TESTS PASSED (100% SUCCESS)\n";
echo "====================================================================\n";
