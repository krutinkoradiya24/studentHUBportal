DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(30) NOT NULL,
  `date` date NOT NULL,
  `course` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT '1st Semester',
  `status` enum('Present','Absent','Late') NOT NULL DEFAULT 'Present',
  `remarks` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stud_date` (`student_id`,`date`,`course`),
  KEY `idx_attendance_date` (`date`),
  KEY `idx_attendance_student` (`student_id`),
  KEY `idx_attendance_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `attendance` WRITE;
UNLOCK TABLES;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('Technical','Cultural','Sports','Workshop','Seminar') NOT NULL DEFAULT 'Technical',
  `date` date NOT NULL,
  `time` varchar(20) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `organizer` varchar(100) DEFAULT 'Student Council',
  `status` enum('Upcoming','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id` (`event_id`),
  KEY `idx_events_date` (`date`),
  KEY `idx_events_category` (`category`),
  KEY `idx_events_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `events` WRITE;
INSERT INTO `events` VALUES (1,'EVT-101','Annual Hackathon 2026','Technical','2026-11-15','09:00 AM','Main Innovation Lab & Tech Hall','36-hour hackathon solving real-world challenges with cash prizes up to $5,000.','Computer Science Club','Upcoming','2026-10-28 08:26:08','2026-10-28 08:26:08'),(2,'EVT-102','Spandan Cultural Fest','Cultural','2026-12-01','05:00 PM','University Amphitheatre','Grand musical evening, theatrical performances, dance competitions, and food stalls.','Student Cultural Wing','Upcoming','2026-10-28 08:26:08','2026-10-28 08:26:08'),(3,'EVT-103','AI & Deep Learning Masterclass','Workshop','2026-11-20','10:30 AM','Seminar Hall 3','Hands-on session building Computer Vision and Generative AI models using PyTorch.','AI Research Group','Upcoming','2026-10-28 08:26:08','2026-10-28 08:26:08'),(4,'EVT-104','Inter-Branch Cricket Championship','Sports','2026-11-28','08:00 AM','Sports Complex Ground A','Thrilling cricket tournament across all engineering branches with championship trophy.','Sports Committee','Upcoming','2026-10-28 08:26:08','2026-10-28 08:26:08'),(5,'EVT-105','Cybersecurity & Ethical Hacking Symposium','Seminar','2026-12-10','02:00 PM','Auditorium Block B','Industry experts sharing defensive security techniques, threat vectors, and career paths.','Tech Forum','Upcoming','2026-10-28 08:26:08','2026-10-28 08:26:08');
UNLOCK TABLES;

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` enum('Academic','Exam','Event','General','Urgent') NOT NULL DEFAULT 'General',
  `priority` enum('Normal','Important','Urgent') NOT NULL DEFAULT 'Normal',
  `target_role` enum('All','Student','Admin') NOT NULL DEFAULT 'All',
  `content` text NOT NULL,
  `posted_by` varchar(100) NOT NULL DEFAULT 'Administration Office',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notices_priority` (`priority`),
  KEY `idx_notices_target` (`target_role`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `notices` WRITE;
INSERT INTO `notices` VALUES (17,'Mid-Semester Examination Schedule Announced','Exam','Urgent','All','The Mid-Semester Examinations for all B.Tech branches will commence from November 10, 2026. Detailed room seating arrangement is published on the portal notice board.','Controller of Examinations','2026-10-28 10:11:04','2026-10-28 10:11:04'),(18,'Registration Open for Annual Hackathon 2026','Event','Important','Student','All engineering students are invited to register teams for the 36-hour Annual Hackathon. Check the Events tab to register before November 5.','Department of Computer Science','2026-10-28 10:11:04','2026-10-28 10:11:04'),(19,'Submission of Project Phase-1 Abstract','Academic','Important','Student','Final year and 6th semester students must submit their capstone project phase-1 synopsis through the student portal by Friday.','Academic Project Coordinator','2026-10-28 10:11:04','2026-10-28 10:11:04'),(20,'Campus Network Maintenance Window','General','Normal','All','Campus Wi-Fi and portal services will undergo scheduled routine maintenance this Sunday from 02:00 AM to 04:00 AM.','IT Infrastructure Team','2026-10-28 10:11:04','2026-10-28 10:11:04');
UNLOCK TABLES;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `course` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT 'Semester 1',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `students` WRITE;
INSERT INTO `students` VALUES (1,'22CS034','Krutin Koradiya','krutin.k@studenthub.edu','9876543220','Computer Science','6th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(2,'24CS001','Aarav Patel','aarav.p@studenthub.edu','9876543210','Computer Science','4th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(3,'24IT005','Priya Sharma','priya.s@studenthub.edu','9876543211','Information Tech','4th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(4,'23ME012','Rohan Gupta','rohan.g@studenthub.edu','9876543212','Mechanical','6th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(5,'25CE023','Ananya Singh','ananya.s@studenthub.edu','9876543213','Civil Eng','2nd Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(6,'26EE008','Karan Malhotra','karan.m@studenthub.edu','9876543214','Electrical','1st Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(7,'24CS045','Neha Verma','neha.v@studenthub.edu','9876543215','Computer Science','4th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(8,'24IT021','Harsh Joshi','harsh.j@studenthub.edu','9876543216','Information Tech','4th Semester','Active','2026-10-19 18:13:05','2026-10-19 18:13:05'),(37,'24DCS123','JAY Patel','jay@gmail.com','9876543210','Computer Science','6th Semester','Active','2026-10-21 17:58:08','2026-10-23 15:15:30'),(51,'24CS099','Tanvi Mehta','tanvi.mehta@studenthub.edu','9876543288','Information Tech','5th Semester','Inactive','2026-10-28 08:26:08','2026-10-28 08:26:08');
UNLOCK TABLES;

DROP TABLE IF EXISTS `timetables`;
CREATE TABLE `timetables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT '6th Semester',
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` varchar(10) NOT NULL,
  `end_time` varchar(10) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `room_no` varchar(50) NOT NULL,
  `class_type` enum('Lecture','Lab','Tutorial') NOT NULL DEFAULT 'Lecture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tt_course_sem` (`course`,`semester`),
  KEY `idx_tt_day` (`day_of_week`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `timetables` WRITE;
INSERT INTO `timetables` VALUES (81,'Computer Science','6th Semester','Monday','09:00','10:00','Cloud Computing & DevOps','CS601','Dr. Sarah Jenkins','Lab 301','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(82,'Computer Science','6th Semester','Monday','10:00','11:00','Advanced Database Systems','CS602','Prof. David Miller','Room 204','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(83,'Computer Science','6th Semester','Monday','11:15','01:15','Full Stack Web Development Lab','CS603P','Er. Krutin Koradiya','Lab 102','Lab','2026-10-28 10:11:04','2026-10-28 10:11:04'),(84,'Computer Science','6th Semester','Tuesday','09:00','10:00','Machine Learning Foundations','CS604','Dr. Rajesh Patel','Room 205','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(85,'Computer Science','6th Semester','Tuesday','10:00','11:00','Software Engineering & Agile','CS605','Prof. Lisa Wong','Room 205','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(86,'Computer Science','6th Semester','Tuesday','11:30','12:30','Cyber Security & Network Defense','CS606','Prof. Mark Vance','Room 204','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(87,'Computer Science','6th Semester','Wednesday','09:00','11:00','Machine Learning Practical Lab','CS604P','Dr. Rajesh Patel','Lab 104','Lab','2026-10-28 10:11:04','2026-10-28 10:11:04'),(88,'Computer Science','6th Semester','Wednesday','11:15','12:15','Advanced Database Systems','CS602','Prof. David Miller','Room 204','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(89,'Computer Science','6th Semester','Thursday','09:00','10:00','Cloud Computing & DevOps','CS601','Dr. Sarah Jenkins','Room 204','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(90,'Computer Science','6th Semester','Thursday','10:00','11:00','Software Engineering & Agile','CS605','Prof. Lisa Wong','Room 205','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(91,'Computer Science','6th Semester','Thursday','11:30','01:30','DevOps & CI/CD Pipeline Lab','CS601P','Dr. Sarah Jenkins','Lab 301','Lab','2026-10-28 10:11:04','2026-10-28 10:11:04'),(92,'Computer Science','6th Semester','Friday','09:30','10:30','Machine Learning Foundations','CS604','Dr. Rajesh Patel','Room 205','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(93,'Computer Science','6th Semester','Friday','10:30','11:30','Cyber Security & Network Defense','CS606','Prof. Mark Vance','Room 204','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(94,'Computer Science','6th Semester','Friday','12:00','01:00','Technical Seminar & Mentorship','CS607','Department Faculty','Seminar Hall 1','Tutorial','2026-10-28 10:11:04','2026-10-28 10:11:04'),(95,'Information Tech','4th Semester','Monday','09:00','10:00','Data Structures & Algorithms','IT401','Dr. Kevin Adams','Room 101','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(96,'Information Tech','4th Semester','Monday','10:15','12:15','DSA Implementation Lab','IT401P','Dr. Kevin Adams','Lab 201','Lab','2026-10-28 10:11:04','2026-10-28 10:11:04'),(97,'Information Tech','4th Semester','Tuesday','09:00','10:00','Computer Networks','IT402','Prof. Emily Clark','Room 102','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04'),(98,'Information Tech','4th Semester','Wednesday','10:00','11:00','Operating Systems','IT403','Prof. Alan Turing','Room 103','Lecture','2026-10-28 10:11:04','2026-10-28 10:11:04');
UNLOCK TABLES;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year` varchar(20) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `role` enum('Admin','Student') NOT NULL DEFAULT 'Student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (1,'ram ganesh','ram@gmail.com','8282828292','$2y$10$Qms8xLXXjYzlJu1l/mMBSuysxpvWB/bYVj9xxASCGzJoV6b.L6cKy','BTech','3','Male','Student','2026-10-19 16:58:52','Active'),(2,'System Administrator','admin@example.com','9999999999','$2y$10$bqoDw6aNFMLKNugDjoBZ6e.lRRaCXNq5URGnKN.yPxfSvdRujBM4C','Management','Staff','Other','Admin','2026-10-19 17:00:59','Active'),(4,'John Student','student@example.com','','$2y$10$d0Zi.xPcYb8keO/zmemutuCNQKS26AlgwzKyearpyu5ttRtfztFp2','','','','Student','2026-10-21 17:09:14','Active'),(42,'Krutin Koradiya','krutin@gmail.com','9876543200','$2y$10$Og8mRxg4DE/lKnlbu1K6b.8Q1EwoaBetXWc3ASRuJ2koSuY/0wclW','Administration','Director','Male','Admin','2026-10-28 10:11:04','Active');
UNLOCK TABLES;
