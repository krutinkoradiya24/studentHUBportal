<?php
/**
 * Student Hub Portal - Authentication Backend (Frontend/php/auth.php)
 * Handles Student Registration, Student Login, Session Checks, and Database Operations.
 */

// Enable CORS for development environments (Apache port 80 & Live-Server port 8080)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get MySQL PDO Connection with fallback support
 */
function getHubDb(): PDO {
    static $pdoInstance = null;
    if ($pdoInstance !== null) {
        return $pdoInstance;
    }

    $dbHost = '127.0.0.1';
    $dbName = 'user_registration_db';
    $dbUser = 'root';
    $dbPass = '';
    $charset = 'utf8mb4';
    $ports = [3306, 3307];

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $lastErr = null;
    foreach ($ports as $port) {
        try {
            // First connect without DB to ensure DB existence
            $dsnNoDb = "mysql:host=$dbHost;port=$port;charset=$charset";
            $temp = new PDO($dsnNoDb, $dbUser, $dbPass, $options);
            $temp->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET $charset COLLATE utf8mb4_unicode_ci");

            // Connect to studenthub database
            $dsn = "mysql:host=$dbHost;port=$port;dbname=$dbName;charset=$charset";
            $pdoInstance = new PDO($dsn, $dbUser, $dbPass, $options);

            // Ensure users table exists
            $pdoInstance->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `full_name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(150) NOT NULL UNIQUE,
                    `mobile` VARCHAR(20) NOT NULL,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `course` VARCHAR(50) NOT NULL,
                    `year` VARCHAR(20) NOT NULL,
                    `gender` VARCHAR(20) NOT NULL,
                    `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Migration check: Add role column if users table was created previously without it
            try {
                $pdoInstance->exec("ALTER TABLE `users` ADD COLUMN `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student'");
            } catch (Exception $ignored) {}

            // Ensure default Administrator account exists
            try {
                $admStmt = $pdoInstance->prepare("SELECT id FROM `users` WHERE `email` = 'admin@example.com' LIMIT 1");
                $admStmt->execute();
                if (!$admStmt->fetch()) {
                    $adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
                    $insAdm = $pdoInstance->prepare("
                        INSERT INTO `users` (`full_name`, `email`, `mobile`, `password_hash`, `course`, `year`, `gender`, `role`)
                        VALUES ('System Administrator', 'admin@example.com', '9999999999', :pwd, 'Management', 'Staff', 'Other', 'Admin')
                    ");
                    $insAdm->execute([':pwd' => $adminHash]);
                }
            } catch (Exception $ignored) {}

            return $pdoInstance;
        } catch (PDOException $e) {
            $lastErr = $e;
        }
    }

    throw new Exception("Database connection failed: " . ($lastErr ? $lastErr->getMessage() : "Unknown error"));
}

// Retrieve action & payload
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Support JSON body if application/json was sent
$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    if (is_array($jsonData)) {
        $_POST = array_merge($_POST, $jsonData);
        if (empty($action) && isset($jsonData['action'])) {
            $action = $jsonData['action'];
        }
    }
}

try {
    $pdo = getHubDb();

    // -------------------------------------------------------------------------
    // ACTION: REGISTER
    // -------------------------------------------------------------------------
    if ($action === 'register') {
        $fullName = trim($_POST['fullName'] ?? $_POST['full_name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $mobile   = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $course   = trim($_POST['course'] ?? '');
        $year     = trim($_POST['year'] ?? '');
        $gender   = trim($_POST['gender'] ?? 'Not Specified');

        // Validation
        if (empty($fullName) || strlen($fullName) < 2) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid full name (at least 2 characters).']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number.']);
            exit;
        }
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must contain at least 8 characters.']);
            exit;
        }

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This email address is already registered. Please login.']);
            exit;
        }

        // Secure password hashing
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users table
        $insert = $pdo->prepare("
            INSERT INTO users (full_name, email, mobile, password_hash, course, year, gender)
            VALUES (:full_name, :email, :mobile, :password_hash, :course, :year, :gender)
        ");
        $insert->execute([
            ':full_name'     => $fullName,
            ':email'         => $email,
            ':mobile'        => $mobile,
            ':password_hash' => $passwordHash,
            ':course'        => $course,
            ':year'          => $year,
            ':gender'        => $gender
        ]);

        $newUserId = $pdo->lastInsertId();

        // Also add or update into students table if exists
        try {
            $studCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = :email LIMIT 1");
            $studCheck->execute([':email' => $email]);
            if (!$studCheck->fetch()) {
                $studInsert = $pdo->prepare("INSERT INTO students (name, email, course) VALUES (:name, :email, :course)");
                $studInsert->execute([
                    ':name'   => $fullName,
                    ':email'  => $email,
                    ':course' => $course
                ]);
            }
        } catch (Exception $e) {
            // Non-blocking if students table schema differs
        }

        echo json_encode([
            'success' => true,
            'message' => 'Registration completed successfully! You can now log in.',
            'user'    => [
                'id'        => (int)$newUserId,
                'full_name' => $fullName,
                'email'     => $email
            ]
        ]);
        exit;
    }

    // -------------------------------------------------------------------------
    // ACTION: LOGIN
    // -------------------------------------------------------------------------
    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please enter both your email/ID and password.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT id, full_name, email, mobile, password_hash, course, year, gender, role, created_at
            FROM users
            WHERE email = :id1 OR mobile = :id2
            LIMIT 1
        ");
        $stmt->execute([
            ':id1' => strtolower($identifier),
            ':id2' => $identifier
        ]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'No student account found with this email or mobile number.']);
            exit;
        }

        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid password. Please check your password and try again.']);
            exit;
        }

        // Determine authenticated role
        $role = $user['role'] ?? ($user['email'] === 'admin@example.com' ? 'Admin' : 'Student');
        
        // Strict Role Separation: Ensure requested portal matches account role
        $portalRole = trim($_POST['portal_role'] ?? '');
        if ($portalRole === 'Student' && $role === 'Admin') {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied: This is an Administrator account. Please switch to the Administrator tab to log in.'
            ]);
            exit;
        }
        if ($portalRole === 'Admin' && $role !== 'Admin') {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied: This account is a Student account and does not have Administrator privileges.'
            ]);
            exit;
        }

        // Prevent Session Fixation attack: Regenerate Session ID upon login
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();

        $_SESSION['role'] = $role;

        // Authentication successful
        $_SESSION['student_user'] = [
            'id'        => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'mobile'    => $user['mobile'],
            'course'    => $user['course'],
            'year'      => $user['year'],
            'gender'    => $user['gender'],
            'role'      => $role
        ];

        echo json_encode([
            'success'    => true,
            'message'    => 'Login successful! Welcome back, ' . $user['full_name'],
            'session_id' => session_id(),
            'user'       => $_SESSION['student_user']
        ]);
        exit;
    }

    // -------------------------------------------------------------------------
    // ACTION: GET_DATABASE_USERS (Show database data)
    // -------------------------------------------------------------------------
    if ($action === 'get_users') {
        // Enforce Admin authorization: non-admins are strictly forbidden from viewing user list
        $userRole = $_SESSION['role'] ?? ($_SESSION['student_user']['role'] ?? '');
        if ($userRole !== 'Admin') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied: Administrator role required.'
            ]);
            exit;
        }

        $stmt = $pdo->query("SELECT id, full_name, email, mobile, course, year, gender, role, created_at FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
        echo json_encode([
            'success' => true,
            'total'   => count($users),
            'users'   => $users
        ]);
        exit;
    }

    // -------------------------------------------------------------------------
    // ACTION: LOGOUT
    // -------------------------------------------------------------------------
    if ($action === 'logout') {
        unset($_SESSION['student_user']);
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        exit;
    }

    // -------------------------------------------------------------------------
    // ACTION: SESSION_CHECK
    // -------------------------------------------------------------------------
    if ($action === 'check_session') {
        $timeoutLimit = 900; // 15 minutes inactivity timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutLimit)) {
            unset($_SESSION['student_user'], $_SESSION['role']);
            session_destroy();
            echo json_encode(['success' => false, 'authenticated' => false, 'timed_out' => true, 'message' => 'Session timed out due to inactivity.']);
            exit;
        }
        $_SESSION['last_activity'] = time();

        if (!empty($_SESSION['student_user'])) {
            echo json_encode([
                'success'       => true, 
                'authenticated' => true, 
                'session_id'    => session_id(),
                'user'          => $_SESSION['student_user']
            ]);
        } else {
            echo json_encode(['success' => true, 'authenticated' => false, 'session_id' => session_id()]);
        }
        exit;
    }

    // -------------------------------------------------------------------------
    // ACTION: ADMIN_CHECK (Restricts access to Admin Panel)
    // -------------------------------------------------------------------------
    if ($action === 'check_admin') {
        $userRole = $_SESSION['role'] ?? ($_SESSION['student_user']['role'] ?? '');
        $isAdmin = ($userRole === 'Admin');

        if ($isAdmin) {
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'role' => 'Admin',
                'user' => $_SESSION['student_user'] ?? ['full_name' => 'Administrator']
            ]);
        } else {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'role' => $userRole,
                'message' => 'Access Denied: Administrator role required.'
            ]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid or missing action.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server or database error: ' . $e->getMessage()
    ]);
}
