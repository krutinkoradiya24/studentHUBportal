<?php
/**
 * admin/index.php - Administrator Master Dashboard Overview
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 */

$pageTitle = 'Dashboard Overview';
require_once dirname(__DIR__) . '/includes/admin_header.php';

$pdo = get_pdo();

// Fetch live counts
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();
$totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$upcomingEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()")->fetchColumn();
$totalTimetableSlots = (int)$pdo->query("SELECT COUNT(*) FROM timetables")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Admin'")->fetchColumn();
$activeNotices = (int)$pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();

// Fetch recent 5 events
$recentEvents = $pdo->query("SELECT * FROM events ORDER BY date ASC, time ASC LIMIT 5")->fetchAll();

// Fetch recent 5 students
$recentStudents = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- Statistics Overview Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon indigo">
      <i class="fas fa-user-graduate"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $totalStudents; ?></h3>
      <p>Total Students (<?php echo $activeStudents; ?> Active)</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <i class="fas fa-calendar-alt"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $totalEvents; ?></h3>
      <p>College Events (<?php echo $upcomingEvents; ?> Upcoming)</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon teal">
      <i class="fas fa-clock"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $totalTimetableSlots; ?></h3>
      <p>Timetable Class Slots</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon rose">
      <i class="fas fa-bullhorn"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $activeNotices; ?></h3>
      <p>Published Notices</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon cyan">
      <i class="fas fa-shield-alt"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $totalAdmins; ?></h3>
      <p>System Administrators</p>
    </div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="portal-card">
  <div class="card-header">
    <div class="card-header-title">
      <i class="fas fa-bolt"></i>
      <h3>Administrative Quick Actions</h3>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-muted);">Common administrative workflows</span>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
      <a href="/STUDENTPORTAL/admin/attendance.php" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start; border-color: rgba(16, 185, 129, 0.4); background: #f0fdf4;">
        <i class="fas fa-clipboard-check" style="color: #10b981; font-size: 1.25rem;"></i>
        <div>
          <div style="font-weight: 700; color: #15803d;">Fill / Mark Attendance</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Record student daily presence</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/events.php?action=new" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-calendar-plus" style="color: #4f46e5; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Add College Event</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Publish new campus event</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/timetable.php?action=new" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-calendar-week" style="color: #0d9488; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Add Timetable Slot</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Schedule lecture/lab timing</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/students.php?action=new" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-user-plus" style="color: #2563eb; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Add Student Record</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Enroll new student</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/notices.php?action=new" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-bullhorn" style="color: #f59e0b; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Post Notice</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Broadcast circular</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/students.php?action=export_csv" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-file-excel" style="color: #10b981; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Export CSV (Excel)</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Download student roster</div>
        </div>
      </a>

      <a href="/STUDENTPORTAL/admin/accounts.php?action=new" class="btn btn-outline" style="padding: 14px; text-align: left; justify-content: flex-start;">
        <i class="fas fa-user-shield" style="color: #7c3aed; font-size: 1.2rem;"></i>
        <div>
          <div style="font-weight: 700; color: var(--text-main);">Admin Accounts</div>
          <div style="font-size: 0.76rem; color: var(--text-muted);">Manage admin credentials</div>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Two-Column Preview: Upcoming Events & Recent Students -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">
  
  <!-- Upcoming Events Section -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-calendar-alt"></i>
        <h3>Campus Events Overview</h3>
      </div>
      <a href="/STUDENTPORTAL/admin/events.php" class="btn btn-outline btn-sm">
        View All Events (<?php echo $totalEvents; ?>)
      </a>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="portal-table">
          <thead>
            <tr>
              <th>Event Title</th>
              <th>Category</th>
              <th>Date & Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentEvents)): ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  No events registered yet. Click "Add College Event" to create one.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentEvents as $ev): ?>
                <tr>
                  <td>
                    <strong style="color: var(--text-main);"><?php echo e($ev['title']); ?></strong>
                    <div style="font-size: 0.78rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo e($ev['location']); ?></div>
                  </td>
                  <td>
                    <span class="badge badge-category"><?php echo e($ev['category']); ?></span>
                  </td>
                  <td style="font-size: 0.82rem; color: var(--text-muted);">
                    <div><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($ev['date'])); ?></div>
                    <div><i class="far fa-clock"></i> <?php echo e($ev['time']); ?></div>
                  </td>
                  <td>
                    <span class="badge badge-<?php echo strtolower($ev['status']); ?>">
                      <?php echo e($ev['status']); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Student Registrations -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-users"></i>
        <h3>Recent Student Registrations</h3>
      </div>
      <a href="/STUDENTPORTAL/admin/students.php" class="btn btn-outline btn-sm">
        Manage Directory
      </a>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="portal-table">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Course / Branch</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentStudents)): ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  No students in directory.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentStudents as $st): ?>
                <tr>
                  <td>
                    <span style="font-family: monospace; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 2px 6px; border-radius: 4px;">
                      <?php echo e($st['student_id']); ?>
                    </span>
                  </td>
                  <td>
                    <strong><?php echo e($st['name']); ?></strong>
                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo e($st['email']); ?></div>
                  </td>
                  <td style="font-size: 0.85rem; color: var(--text-muted);">
                    <?php echo e($st['course']); ?>
                  </td>
                  <td>
                    <span class="badge badge-<?php echo strtolower($st['status']); ?>">
                      <?php echo e($st['status']); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
