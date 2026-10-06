<?php
$errors = [];
$success = false;

$name = $email = $phone = $subject = $message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must contain at least 2 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    } elseif (strlen($subject) < 3) {
        $errors[] = "Subject must contain at least 3 characters.";
    }

    if (empty($message)) {
        $errors[] = "Message is required.";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message must contain at least 10 characters.";
    }

    if (empty($errors)) {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        
        $dataFile = $dataDir . '/contacts.json';
        $records = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $records = $decoded;
            }
        }
        
        $newRecord = [
            'id' => uniqid(),
            'name' => $safeName,
            'email' => $safeEmail,
            'subject' => $safeSubject,
            'message' => $safeMessage,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        $records[] = $newRecord;
        file_put_contents($dataFile, json_encode($records, JSON_PRETTY_PRINT));
        
        $success = true;
        // Clear fields on success
        $name = $email = $subject = $message = '';
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Hub Portal - Contact</title>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/contact.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <style>
      .success-message {
          background-color: #d4edda;
          color: #155724;
          border: 1px solid #c3e6cb;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
      }
      .error-message {
          background-color: #f8d7da;
          color: #721c24;
          border: 1px solid #f5c6cb;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
      }
      .error-message ul {
          margin: 0;
          padding-left: 20px;
      }
      .btn-secondary {
          background-color: #6c757d;
          color: white;
          border: none;
      }
      .btn-secondary:hover {
          background-color: #5a6268;
      }
      .button-group {
          display: flex;
          gap: 10px;
      }
    </style>
  </head>
  <body class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img
          src="../html/logo.png"
          alt="CHARUSAT Logo"
          style="
            height: 50px;
            background: white;
            padding: 5px;
            border-radius: 50%;
            width: 50px;
            object-fit: contain;
          "
        /> <span style="font-weight: 700; font-size: 1.3rem; color: var(--primary); letter-spacing: 0.5px; margin-left: 10px;">Student Hub</span>
        <button id="close-sidebar"><i class="fas fa-times"></i></button>
      </div>
      <nav class="sidebar-nav">
        <a href="../html/dashboard.html"><i class="fas fa-home"></i> Dashboard</a>
        <a href="../html/course.html"><i class="fas fa-book"></i> Courses</a>
        <a href="../html/assignments.html"><i class="fas fa-tasks"></i> Assignments</a>
        <a href="../html/attendance.html"
          ><i class="fas fa-calendar-check"></i> Attendance</a
        >
        <a href="../html/results.html"><i class="fas fa-chart-bar"></i> Results</a>
        <a href="../html/notice.html"><i class="fas fa-bell"></i> Notices</a>
        <a href="../html/e-library.html"
          ><i class="fas fa-book-reader"></i> E-Library</a
        >
        <a href="../html/fee_receipt.html"
          ><i class="fas fa-receipt"></i> Fee Receipt</a
        >
        <a href="../html/counselling.html"
          ><i class="fas fa-comments"></i> Counselling</a
        >
        <a href="../html/feedback.html"><i class="fas fa-comment-dots"></i> Feedback</a>
        <a href="contact.php" class="active"><i class="fas fa-envelope"></i> Contact</a>
        <a href="../html/profile.html"><i class="fas fa-user"></i> Profile</a>
        <a href="../html/login.html" class="logout-link"
          ><i class="fas fa-sign-out-alt"></i> Logout</a
        >
      </nav>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="header-left">
          <button
            id="hamburger-btn"
            class="hamburger-btn"
            aria-label="Open navigation"
            aria-expanded="false"
          >
            <i class="fas fa-bars"></i>
          </button>
        </div>
        <div class="header-right">
          <button
            id="theme-toggle"
            class="theme-toggle-btn"
            aria-label="Toggle theme"
          >
            <i class="fas fa-moon"></i>
          </button>
          <div class="notification-icon">
            <i class="fas fa-bell"></i>
            <span class="badge">3</span>
          </div>
          <div class="user-profile">
            <img
              src="https://ui-avatars.com/api/?name=Krutin+Koradiya&background=random"
              alt="Profile"
            />
            <span>Krutin Koradiya</span>
          </div>
        </div>
      </header>

      <div class="content-wrapper">
        <div
          style="
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
          "
        >
          <h1 style="color: var(--primary-color); font-size: 1.8rem">
            Contact Us
          </h1>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px">
          <div class="card">
            <h3 style="margin-bottom: 20px">Get in Touch</h3>
            
            <?php if ($success): ?>
            <div class="success-message">
                Your message has been submitted successfully!
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="error-message">
                <p style="margin-top:0; font-weight:bold;">Please fix the following errors:</p>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="contact.php" method="POST">
              <div style="margin-bottom: 15px">
                <label
                  style="display: block; margin-bottom: 5px; font-weight: 500"
                  >Your Name</label
                >
                <input
                  type="text"
                  name="name"
                  value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                  style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    outline: none;
                  "
                  placeholder="Enter your name"
                />
              </div>
              <div style="margin-bottom: 15px">
                <label
                  style="display: block; margin-bottom: 5px; font-weight: 500"
                  >Email Address</label
                >
                <input
                  type="email"
                  name="email"
                  value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                  style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    outline: none;
                  "
                  placeholder="Enter your email"
                />
              </div>
              <div style="margin-bottom: 15px">
                <label
                  style="display: block; margin-bottom: 5px; font-weight: 500"
                  >Subject</label
                >
                <input
                  type="text"
                  name="subject"
                  value="<?php echo htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'); ?>"
                  style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    outline: none;
                  "
                  placeholder="Message subject"
                />
              </div>
              <div style="margin-bottom: 20px">
                <label
                  style="display: block; margin-bottom: 5px; font-weight: 500"
                  >Message</label
                >
                <textarea
                  name="message"
                  rows="4"
                  style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    outline: none;
                    font-family: inherit;
                  "
                  placeholder="Type your message here..."
                ><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-block">
                Send Message
              </button>
            </form>
          </div>
          <div>
            <div
              class="card"
              style="margin-bottom: 20px; text-align: center; padding: 30px"
            >
              <i
                class="fas fa-map-marker-alt"
                style="font-size: 2rem; color: #2563eb; margin-bottom: 15px"
              ></i>
              <h4>University Campus</h4>
              <p style="color: var(--text-muted)">
                123 Education Boulevard, Knowledge City, ST 12345
              </p>
            </div>
            <div
              class="card"
              style="margin-bottom: 20px; text-align: center; padding: 30px"
            >
              <i
                class="fas fa-phone-alt"
                style="font-size: 2rem; color: #22c55e; margin-bottom: 15px"
              ></i>
              <h4>Phone Support</h4>
              <p style="color: var(--text-muted)">
                +1 (800) 123-4567<br />Mon-Fri, 9am - 5pm
              </p>
            </div>
            <div class="card" style="text-align: center; padding: 30px">
              <i
                class="fas fa-envelope"
                style="font-size: 2rem; color: #f59e0b; margin-bottom: 15px"
              ></i>
              <h4>Email Us</h4>
              <p style="color: var(--text-muted)">
                support@studenthub.edu<br />admin@studenthub.edu
              </p>
            </div>
          </div>
        </div>
      </div>

      <footer class="site-footer">
        <div class="footer-container">
          <div class="footer-section">
            <h3>About Student Hub</h3>
            <p>
              Student Hub is a centralized student portal designed to provide
              students with easy access to academic information and college
              services.
            </p>
            <p>
              Students can manage courses, assignments, attendance, results,
              notices, fees, e-library and counselling from one platform.
            </p>
          </div>
          <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
              <li><a href="../html/homepage.html">Home</a></li>
              <li><a href="../html/dashboard.html">Dashboard</a></li>
              <li><a href="../html/course.html">Courses</a></li>
              <li><a href="../html/assignments.html">Assignments</a></li>
              <li><a href="../html/attendance.html">Attendance</a></li>
              <li><a href="../html/results.html">Results</a></li>
              <li><a href="../html/e-library.html">E-Library</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h3>Student Services</h3>
            <ul>
              <li><a href="../html/notice.html">Notices</a></li>
              <li><a href="../html/fee_receipt.html">Fee Receipt</a></li>
              <li><a href="../html/counselling.html">Counselling</a></li>
              <li><a href="../html/feedback.html">Feedback</a></li>
              <li><a href="../html/faculty.html">Faculty</a></li>
              <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h3>Contact</h3>
            <div class="footer-contact-info">
              <p><i class="fas fa-map-marker-alt"></i> College Address</p>
              <p><i class="fas fa-envelope"></i> Email</p>
              <p><i class="fas fa-phone-alt"></i> Phone</p>
              <p><i class="fas fa-clock"></i> Office Hours</p>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2026 Student Hub. All Rights Reserved.</p>
        </div>
      </footer>
    </main>
    
    <script src="../js/api.js"></script>
    <script src="../js/utils.js"></script>
    <script src="../js/pagination.js"></script>
    <script src="../js/students.js"></script>
    <script src="../js/events.js"></script>
    <script src="../js/faqs.js"></script>
    <script src="../js/script.js"></script>

  </body>
</html>
