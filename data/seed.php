<?php
/**
 * data/seed.php - Robust Database Initializer & Migration Runner
 */

require_once dirname(__DIR__) . '/includes/db.php';

echo "=== INITIALIZING DATABASE MIGRATION & SEEDING ===\n";

try {
    $pdo = getHubDb();
    echo "[OK] Connected to database via PDO.\n";

    // 1. Table users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `mobile` VARCHAR(20) DEFAULT '',
            `password_hash` VARCHAR(255) NOT NULL,
            `course` VARCHAR(50) DEFAULT 'Computer Science',
            `year` VARCHAR(20) DEFAULT '3rd Year',
            `gender` VARCHAR(20) DEFAULT 'Not Specified',
            `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_users_role` (`role`),
            KEY `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Table students
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `student_id` VARCHAR(30) NOT NULL UNIQUE,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `phone` VARCHAR(20) NOT NULL,
            `course` VARCHAR(50) NOT NULL,
            `semester` VARCHAR(20) NOT NULL DEFAULT '1st Semester',
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_students_status` (`status`),
            KEY `idx_students_course` (`course`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Table events
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `events` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `event_id` VARCHAR(30) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `category` ENUM('Technical', 'Cultural', 'Sports', 'Workshop', 'Seminar') NOT NULL DEFAULT 'Technical',
            `date` DATE NOT NULL,
            `time` VARCHAR(20) NOT NULL,
            `location` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `organizer` VARCHAR(100) DEFAULT 'Student Council',
            `status` ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Upcoming',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_events_date` (`date`),
            KEY `idx_events_category` (`category`),
            KEY `idx_events_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Table timetables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `timetables` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `course` VARCHAR(50) NOT NULL,
            `semester` VARCHAR(20) NOT NULL DEFAULT '6th Semester',
            `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
            `start_time` VARCHAR(10) NOT NULL,
            `end_time` VARCHAR(10) NOT NULL,
            `subject_name` VARCHAR(150) NOT NULL,
            `subject_code` VARCHAR(20) NOT NULL,
            `faculty_name` VARCHAR(100) NOT NULL,
            `room_no` VARCHAR(50) NOT NULL,
            `class_type` ENUM('Lecture', 'Lab', 'Tutorial') NOT NULL DEFAULT 'Lecture',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_tt_course_sem` (`course`, `semester`),
            KEY `idx_tt_day` (`day_of_week`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Table notices
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notices` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `category` ENUM('Academic', 'Exam', 'Event', 'General', 'Urgent') NOT NULL DEFAULT 'General',
            `priority` ENUM('Normal', 'Important', 'Urgent') NOT NULL DEFAULT 'Normal',
            `target_role` ENUM('All', 'Student', 'Admin') NOT NULL DEFAULT 'All',
            `content` TEXT NOT NULL,
            `posted_by` VARCHAR(100) NOT NULL DEFAULT 'Administration Office',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_notices_priority` (`priority`),
            KEY `idx_notices_target` (`target_role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 6. Table attendance
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `attendance` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `student_id` VARCHAR(30) NOT NULL,
            `date` DATE NOT NULL,
            `course` VARCHAR(50) NOT NULL,
            `semester` VARCHAR(20) NOT NULL DEFAULT '1st Semester',
            `status` ENUM('Present', 'Absent', 'Late') NOT NULL DEFAULT 'Present',
            `remarks` VARCHAR(255) DEFAULT '',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_stud_date` (`student_id`, `date`, `course`),
            KEY `idx_attendance_date` (`date`),
            KEY `idx_attendance_student` (`student_id`),
            KEY `idx_attendance_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "[OK] All 6 tables verified/created.\n";

    // Seed Admin Accounts: krutin@gmail.com / Krutin@1234 and admin@example.com / admin123
    $krutinHash = password_hash('Krutin@1234', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (full_name, email, mobile, password_hash, course, year, gender, role)
        VALUES ('Krutin Koradiya', 'krutin@gmail.com', '9876543200', ?, 'Administration', 'Director', 'Male', 'Admin')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'Admin'
    ")->execute([$krutinHash]);

    $adminHash = password_hash('Admin@Secure2026!', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (full_name, email, mobile, password_hash, course, year, gender, role)
        VALUES ('System Administrator', 'admin@example.com', '9876543210', ?, 'Administration', 'Staff', 'Male', 'Admin')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'Admin'
    ")->execute([$adminHash]);

    // Seed Student Account: student@example.com / Student@12345 (and student123)
    $studentHash = password_hash('Student@12345', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (full_name, email, mobile, password_hash, course, year, gender, role)
        VALUES ('Krutin Koradiya', 'student@example.com', '9876543220', ?, 'Computer Science', '3rd Year', 'Male', 'Student')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'Student'
    ")->execute([$studentHash]);

    // Seed Students
    $students = [
        ['22CS034', 'Krutin Koradiya', 'krutin.k@studenthub.edu', '9876543220', 'Computer Science', '6th Semester', 'Active'],
        ['24CS001', 'Aarav Patel', 'aarav.p@studenthub.edu', '9876543210', 'Computer Science', '4th Semester', 'Active'],
        ['24IT005', 'Priya Sharma', 'priya.s@studenthub.edu', '9876543211', 'Information Tech', '4th Semester', 'Active'],
        ['23ME012', 'Rohan Gupta', 'rohan.g@studenthub.edu', '9876543212', 'Mechanical', '6th Semester', 'Active'],
        ['25CE023', 'Ananya Singh', 'ananya.s@studenthub.edu', '9876543213', 'Civil Eng', '2nd Semester', 'Active'],
        ['26EE008', 'Karan Malhotra', 'karan.m@studenthub.edu', '9876543214', 'Electrical', '1st Semester', 'Active'],
        ['24CS045', 'Neha Verma', 'neha.v@studenthub.edu', '9876543215', 'Computer Science', '4th Semester', 'Active'],
        ['24IT021', 'Harsh Joshi', 'harsh.j@studenthub.edu', '9876543216', 'Information Tech', '4th Semester', 'Active'],
        ['24CS099', 'Tanvi Mehta', 'tanvi.mehta@studenthub.edu', '9876543288', 'Information Tech', '5th Semester', 'Inactive']
    ];
    $stIns = $pdo->prepare("
        INSERT INTO students (student_id, name, email, phone, course, semester, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone), course = VALUES(course), status = VALUES(status)
    ");
    foreach ($students as $s) {
        $stIns->execute($s);
    }

    // Seed Events
    $events = [
        ['EVT-101', 'Annual Hackathon 2026', 'Technical', '2026-11-15', '09:00 AM', 'Main Innovation Lab & Tech Hall', '36-hour hackathon solving real-world challenges with cash prizes up to $5,000.', 'Computer Science Club', 'Upcoming'],
        ['EVT-102', 'Spandan Cultural Fest', 'Cultural', '2026-12-01', '05:00 PM', 'University Amphitheatre', 'Grand musical evening, theatrical performances, dance competitions, and food stalls.', 'Student Cultural Wing', 'Upcoming'],
        ['EVT-103', 'AI & Deep Learning Masterclass', 'Workshop', '2026-11-20', '10:30 AM', 'Seminar Hall 3', 'Hands-on session building Computer Vision and Generative AI models using PyTorch.', 'AI Research Group', 'Upcoming'],
        ['EVT-104', 'Inter-Branch Cricket Championship', 'Sports', '2026-11-28', '08:00 AM', 'Sports Complex Ground A', 'Thrilling cricket tournament across all engineering branches with championship trophy.', 'Sports Committee', 'Upcoming'],
        ['EVT-105', 'Cybersecurity & Ethical Hacking Symposium', 'Seminar', '2026-12-10', '02:00 PM', 'Auditorium Block B', 'Industry experts sharing defensive security techniques, threat vectors, and career paths.', 'Tech Forum', 'Upcoming']
    ];
    $evIns = $pdo->prepare("
        INSERT INTO events (event_id, title, category, date, time, location, description, organizer, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE title = VALUES(title), date = VALUES(date), location = VALUES(location)
    ");
    foreach ($events as $e) {
        $evIns->execute($e);
    }

    // Seed Timetables
    $timetables = [
        ['Computer Science', '6th Semester', 'Monday', '09:00', '10:00', 'Cloud Computing & DevOps', 'CS601', 'Dr. Sarah Jenkins', 'Lab 301', 'Lecture'],
        ['Computer Science', '6th Semester', 'Monday', '10:00', '11:00', 'Advanced Database Systems', 'CS602', 'Prof. David Miller', 'Room 204', 'Lecture'],
        ['Computer Science', '6th Semester', 'Monday', '11:15', '01:15', 'Full Stack Web Development Lab', 'CS603P', 'Er. Krutin Koradiya', 'Lab 102', 'Lab'],
        ['Computer Science', '6th Semester', 'Tuesday', '09:00', '10:00', 'Machine Learning Foundations', 'CS604', 'Dr. Rajesh Patel', 'Room 205', 'Lecture'],
        ['Computer Science', '6th Semester', 'Tuesday', '10:00', '11:00', 'Software Engineering & Agile', 'CS605', 'Prof. Lisa Wong', 'Room 205', 'Lecture'],
        ['Computer Science', '6th Semester', 'Tuesday', '11:30', '12:30', 'Cyber Security & Network Defense', 'CS606', 'Prof. Mark Vance', 'Room 204', 'Lecture'],
        ['Computer Science', '6th Semester', 'Wednesday', '09:00', '11:00', 'Machine Learning Practical Lab', 'CS604P', 'Dr. Rajesh Patel', 'Lab 104', 'Lab'],
        ['Computer Science', '6th Semester', 'Wednesday', '11:15', '12:15', 'Advanced Database Systems', 'CS602', 'Prof. David Miller', 'Room 204', 'Lecture'],
        ['Computer Science', '6th Semester', 'Thursday', '09:00', '10:00', 'Cloud Computing & DevOps', 'CS601', 'Dr. Sarah Jenkins', 'Room 204', 'Lecture'],
        ['Computer Science', '6th Semester', 'Thursday', '10:00', '11:00', 'Software Engineering & Agile', 'CS605', 'Prof. Lisa Wong', 'Room 205', 'Lecture'],
        ['Computer Science', '6th Semester', 'Thursday', '11:30', '01:30', 'DevOps & CI/CD Pipeline Lab', 'CS601P', 'Dr. Sarah Jenkins', 'Lab 301', 'Lab'],
        ['Computer Science', '6th Semester', 'Friday', '09:30', '10:30', 'Machine Learning Foundations', 'CS604', 'Dr. Rajesh Patel', 'Room 205', 'Lecture'],
        ['Computer Science', '6th Semester', 'Friday', '10:30', '11:30', 'Cyber Security & Network Defense', 'CS606', 'Prof. Mark Vance', 'Room 204', 'Lecture'],
        ['Computer Science', '6th Semester', 'Friday', '12:00', '01:00', 'Technical Seminar & Mentorship', 'CS607', 'Department Faculty', 'Seminar Hall 1', 'Tutorial'],
        ['Information Tech', '4th Semester', 'Monday', '09:00', '10:00', 'Data Structures & Algorithms', 'IT401', 'Dr. Kevin Adams', 'Room 101', 'Lecture'],
        ['Information Tech', '4th Semester', 'Monday', '10:15', '12:15', 'DSA Implementation Lab', 'IT401P', 'Dr. Kevin Adams', 'Lab 201', 'Lab'],
        ['Information Tech', '4th Semester', 'Tuesday', '09:00', '10:00', 'Computer Networks', 'IT402', 'Prof. Emily Clark', 'Room 102', 'Lecture'],
        ['Information Tech', '4th Semester', 'Wednesday', '10:00', '11:00', 'Operating Systems', 'IT403', 'Prof. Alan Turing', 'Room 103', 'Lecture']
    ];
    $ttIns = $pdo->prepare("
        INSERT INTO timetables (course, semester, day_of_week, start_time, end_time, subject_name, subject_code, faculty_name, room_no, class_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    // Clear old timetable to avoid duplicate insertions on re-seed
    $pdo->exec("DELETE FROM timetables");
    foreach ($timetables as $t) {
        $ttIns->execute($t);
    }

    // Seed Notices
    $notices = [
        ['Mid-Semester Examination Schedule Announced', 'Exam', 'Urgent', 'All', 'The Mid-Semester Examinations for all B.Tech branches will commence from November 10, 2026. Detailed room seating arrangement is published on the portal notice board.', 'Controller of Examinations'],
        ['Registration Open for Annual Hackathon 2026', 'Event', 'Important', 'Student', 'All engineering students are invited to register teams for the 36-hour Annual Hackathon. Check the Events tab to register before November 5.', 'Department of Computer Science'],
        ['Submission of Project Phase-1 Abstract', 'Academic', 'Important', 'Student', 'Final year and 6th semester students must submit their capstone project phase-1 synopsis through the student portal by Friday.', 'Academic Project Coordinator'],
        ['Campus Network Maintenance Window', 'General', 'Normal', 'All', 'Campus Wi-Fi and portal services will undergo scheduled routine maintenance this Sunday from 02:00 AM to 04:00 AM.', 'IT Infrastructure Team']
    ];
    $pdo->exec("DELETE FROM notices");
    $notIns = $pdo->prepare("
        INSERT INTO notices (title, category, priority, target_role, content, posted_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    foreach ($notices as $n) {
        $notIns->execute($n);
    }

    // Verify Counts
    $userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $eventCount   = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $ttCount      = $pdo->query("SELECT COUNT(*) FROM timetables")->fetchColumn();
    $noticeCount  = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();

    echo "--- SEEDING COMPLETED ---\n";
    echo "Users: $userCount (Admin: admin@example.com / admin123 | Student: student@example.com / student123)\n";
    echo "Students: $studentCount\n";
    echo "Events: $eventCount\n";
    echo "Timetable Slots: $ttCount\n";
    echo "Notices: $noticeCount\n";
    echo "[SUCCESS] All data initialized perfectly.\n";

} catch (Throwable $e) {
    echo "[ERROR] Database seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
