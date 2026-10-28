<?php
/**
 * student/index.php - Student Master Dashboard Overview
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 * Admins are blocked and redirected back to admin console!
 */

$pageTitle = 'My Dashboard';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

// Current student info from session & DB
$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$profile = $uStmt->fetch() ?: $studentUser;

$userCourse = $profile['course'] ?? 'Computer Science';
$userYear = $profile['year'] ?? '3rd Year';
$currentSem = '6th Semester'; // Default for demo

// Get Today's day name (e.g. Monday)
$todayDay = date('l');
// For demo testing on weekends, fall back to Monday if Sunday/Saturday has no classes
if ($todayDay === 'Sunday') {
    $todayDay = 'Monday';
}

// Fetch Today's Classes for student's course
$todayStmt = $pdo->prepare("
    SELECT * FROM timetables
    WHERE course = ? AND day_of_week = ?
    ORDER BY start_time ASC
");
$todayStmt->execute([$userCourse, $todayDay]);
$todayClasses = $todayStmt->fetchAll();

// If no classes on this exact day, fetch Monday's classes as preview
if (empty($todayClasses)) {
    $todayStmt->execute([$userCourse, 'Monday']);
    $todayClasses = $todayStmt->fetchAll();
    $displayDayName = 'Monday (Upcoming)';
} else {
    $displayDayName = $todayDay;
}

// Fetch upcoming 3 events
$events = $pdo->query("SELECT * FROM events WHERE status != 'Cancelled' ORDER BY date ASC, time ASC LIMIT 3")->fetchAll();

// Fetch latest 3 notices for students
$notices = $pdo->query("SELECT * FROM notices WHERE target_role IN ('All', 'Student') ORDER BY FIELD(priority, 'Urgent', 'Important', 'Normal'), id DESC LIMIT 3")->fetchAll();

// Fetch student attendance summary if student record exists
$stRec = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
$stRec->execute([$profile['email']]);
$stRow = $stRec->fetch();
$studentCode = $stRow['student_id'] ?? '22CS034';

$attStmt = $pdo->prepare("
    SELECT 
        COUNT(id) AS total_classes,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_count
    FROM attendance
    WHERE student_id = ?
");
$attStmt->execute([$studentCode]);
$attData = $attStmt->fetch();
$totalCls = (int)($attData['total_classes'] ?? 0);
$presentCls = (int)($attData['present_count'] ?? 0);
$compliancePct = ($totalCls > 0) ? round(($presentCls / $totalCls) * 100, 1) : 85.0; // default benchmark
?>

<!-- Welcome Greeting Hero Card -->
<div class="portal-card" style="background: linear-gradient(135deg, #0d9488 0%, #065f46 100%); color: #fff; border: none; box-shadow: 0 10px 25px rgba(13, 148, 136, 0.25);">
  <div class="card-body" style="padding: 28px 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
      <div>
        <span style="background: rgba(255, 255, 255, 0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">
          Welcome Back
        </span>
        <h2 style="font-size: 1.85rem; font-weight: 800; margin: 8px 0 4px; color: #fff;">
          Hello, <?php echo e($studentUser['full_name']); ?>!
        </h2>
        <p style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem;">
          Enrolled in <strong><?php echo e($userCourse); ?></strong> &bull; <?php echo e($userYear); ?> &bull; Student Roll: <strong><?php echo e($studentCode); ?></strong>
        </p>
      </div>

      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="/STUDENTPORTAL/student/timetable.php" class="btn" style="background: #ffffff; color: #0d9488; font-weight: 700; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
          <i class="fas fa-clock"></i> My Timetable
        </a>
        <a href="/STUDENTPORTAL/student/events.php" class="btn" style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.35);">
          <i class="fas fa-calendar-alt"></i> Campus Events
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Quick Metric Badges -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon teal">
      <i class="fas fa-clock"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo count($todayClasses); ?></h3>
      <p>Today's Scheduled Classes</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon emerald">
      <i class="fas fa-chart-line"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $compliancePct; ?>%</h3>
      <p>Attendance Compliance Rate</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <i class="fas fa-calendar-check"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo count($events); ?></h3>
      <p>Upcoming Campus Events</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon rose">
      <i class="fas fa-bullhorn"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo count($notices); ?></h3>
      <p>New Student Notices</p>
    </div>
  </div>
</div>

<!-- Two-Column Workspace: Today's Classes & Upcoming Events -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(440px, 1fr)); gap: 24px;">

  <!-- Today's Schedule Card -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-calendar-day" style="color: #0d9488;"></i>
        <h3>Today's Classes (<?php echo e($displayDayName); ?>)</h3>
      </div>
      <a href="/STUDENTPORTAL/student/timetable.php" class="btn btn-outline btn-sm">
        Full Weekly View
      </a>
    </div>
    <div class="card-body">
      <?php if (empty($todayClasses)): ?>
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
          <i class="fas fa-coffee" style="font-size: 2rem; display: block; opacity: 0.3; margin-bottom: 8px;"></i>
          No classes scheduled for today. Enjoy your study break!
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($todayClasses as $slot): ?>
            <div class="slot-card" style="border-left-color: #0d9488;">
              <div class="slot-time">
                <i class="far fa-clock" style="color: #0d9488;"></i>
                <span><?php echo e($slot['start_time']); ?> - <?php echo e($slot['end_time']); ?></span>
                <span class="badge badge-type-<?php echo strtolower($slot['class_type']); ?>" style="margin-left: auto;">
                  <?php echo e($slot['class_type']); ?>
                </span>
              </div>
              <div class="slot-subject" style="font-size: 1rem;">
                <?php echo e($slot['subject_name']); ?>
                <?php if (!empty($slot['subject_code'])): ?>
                  <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 500;">(<?php echo e($slot['subject_code']); ?>)</span>
                <?php endif; ?>
              </div>
              <div class="slot-meta" style="margin-top: 6px;">
                <span><i class="fas fa-user-tie"></i> <?php echo e($slot['faculty_name']); ?></span>
                <span class="slot-room"><i class="fas fa-door-open"></i> <?php echo e($slot['room_no']); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upcoming Campus Events Card -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-calendar-alt" style="color: #0d9488;"></i>
        <h3>Campus Events & Competitions</h3>
      </div>
      <a href="/STUDENTPORTAL/student/events.php" class="btn btn-outline btn-sm">
        Explore Events
      </a>
    </div>
    <div class="card-body">
      <?php if (empty($events)): ?>
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
          No upcoming events at the moment.
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 14px;">
          <?php foreach ($events as $ev): ?>
            <div style="padding: 14px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #fff; transition: all 0.2s ease;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                <div>
                  <span class="badge badge-category" style="margin-bottom: 6px;">
                    <?php echo e($ev['category']); ?>
                  </span>
                  <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin: 0;">
                    <?php echo e($ev['title']); ?>
                  </h4>
                </div>
                <span class="badge badge-<?php echo strtolower($ev['status']); ?>">
                  <?php echo e($ev['status']); ?>
                </span>
              </div>

              <div style="font-size: 0.82rem; color: var(--text-muted); margin: 8px 0; display: flex; gap: 14px; flex-wrap: wrap;">
                <span><i class="far fa-calendar-alt" style="color: #0d9488;"></i> <?php echo date('M d, Y', strtotime($ev['date'])); ?> (<?php echo e($ev['time']); ?>)</span>
                <span><i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> <?php echo e($ev['location']); ?></span>
              </div>

              <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                <?php echo e($ev['description'] ?? ''); ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Notice Board Highlights for Student -->
<div class="portal-card">
  <div class="card-header">
    <div class="card-header-title">
      <i class="fas fa-bullhorn" style="color: #0d9488;"></i>
      <h3>Campus Notices & Urgent Circulars</h3>
    </div>
    <a href="/STUDENTPORTAL/student/notices.php" class="btn btn-outline btn-sm">
      View All Notices
    </a>
  </div>
  <div class="card-body">
    <?php if (empty($notices)): ?>
      <div style="text-align: center; color: var(--text-muted); padding: 20px;">
        No active circulars.
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
        <?php foreach ($notices as $n): ?>
          <?php 
            $priColor = ($n['priority'] === 'Urgent') ? '#ef4444' : (($n['priority'] === 'Important') ? '#f59e0b' : '#0d9488');
            $priBg = ($n['priority'] === 'Urgent') ? '#fef2f2' : (($n['priority'] === 'Important') ? '#fffbeb' : '#f0fdfa');
          ?>
          <div style="padding: 16px; border: 1px solid var(--border-color); border-left: 4px solid <?php echo $priColor; ?>; border-radius: var(--radius-md); background: #fff;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <span class="badge badge-category"><?php echo e($n['category']); ?></span>
              <span style="font-size: 0.72rem; font-weight: 700; color: <?php echo $priColor; ?>; background: <?php echo $priBg; ?>; padding: 2px 8px; border-radius: 10px;">
                <?php echo e($n['priority']); ?>
              </span>
            </div>
            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
              <?php echo e($n['title']); ?>
            </h4>
            <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 8px;">
              <?php echo e($n['content']); ?>
            </p>
            <div style="font-size: 0.75rem; color: var(--text-light); display: flex; justify-content: space-between;">
              <span>By: <?php echo e($n['posted_by']); ?></span>
              <span><?php echo date('M d, Y', strtotime($n['created_at'])); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
