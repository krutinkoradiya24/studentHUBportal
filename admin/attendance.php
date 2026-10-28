<?php
/**
 * admin/attendance.php - Student Attendance Management & Summary Analytics
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/db.php';

init_secure_session();
$adminUser = require_admin();
$pdo = get_pdo();

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedCourse = $_GET['course'] ?? 'Computer Science';
$selectedSemester = $_GET['semester'] ?? '6th Semester';

// POST: Save attendance batch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/attendance.php');
        exit;
    }

    if ($action === 'save_attendance') {
        $date     = trim($_POST['date'] ?? date('Y-m-d'));
        $course   = trim($_POST['course'] ?? 'Computer Science');
        $semester = trim($_POST['semester'] ?? '1st Semester');
        $statusMap = $_POST['status'] ?? [];
        $remarksMap = $_POST['remarks'] ?? [];

        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, date, course, semester, status, remarks)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks), updated_at = NOW()
        ");

        $count = 0;
        foreach ($statusMap as $sId => $st) {
            $validStatus = in_array($st, ['Present', 'Absent', 'Late'], true) ? $st : 'Present';
            $rem = trim($remarksMap[$sId] ?? '');
            $stmt->execute([$sId, $date, $course, $semester, $validStatus, $rem]);
            $count++;
        }

        set_flash('success', "Attendance saved for {$count} students on {$date} ({$course} - {$semester}).");
        header("Location: /STUDENTPORTAL/admin/attendance.php?date=" . urlencode($date) . "&course=" . urlencode($course) . "&semester=" . urlencode($semester));
        exit;
    }
}

// Fetch active students for selected course
$studStmt = $pdo->prepare("SELECT student_id, name, email, phone FROM students WHERE course = ? AND status = 'Active' ORDER BY student_id ASC");
$studStmt->execute([$selectedCourse]);
$studentsList = $studStmt->fetchAll();

// Fetch existing attendance marks for selected date & course
$attStmt = $pdo->prepare("SELECT student_id, status, remarks FROM attendance WHERE date = ? AND course = ?");
$attStmt->execute([$selectedDate, $selectedCourse]);
$existingMarks = [];
while ($row = $attStmt->fetch()) {
    $existingMarks[$row['student_id']] = $row;
}

// Fetch aggregate attendance percentages
$summary = $pdo->query("
    SELECT 
        s.student_id, 
        s.name, 
        s.course,
        COUNT(a.id) AS total_classes,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
        ROUND((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / GREATEST(COUNT(a.id), 1)) * 100, 1) AS percentage
    FROM students s
    LEFT JOIN attendance a ON s.student_id = a.student_id
    WHERE s.status = 'Active'
    GROUP BY s.student_id, s.name, s.course
    ORDER BY percentage ASC, s.name ASC
    LIMIT 20
")->fetchAll();

$pageTitle = 'Attendance System';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Section Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Attendance Administration</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Mark daily attendance rosters and analyze per-student attendance compliance rates.
    </p>
  </div>
</div>

<!-- Selection Filter Card -->
<div class="portal-card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 16px 20px;">
    <form method="GET" action="/STUDENTPORTAL/admin/attendance.php" id="attendanceFilterForm" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="attDate" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); white-space: nowrap;">
          <i class="far fa-calendar-alt" style="color: #4f46e5;"></i> Date:
        </label>
        <input type="date" id="attDate" name="date" class="form-control" style="width: auto; padding: 7px 12px;" value="<?php echo e($selectedDate); ?>" onchange="document.getElementById('attendanceFilterForm').submit();">
      </div>

      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="attCourse" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); white-space: nowrap;">
          <i class="fas fa-graduation-cap" style="color: #4f46e5;"></i> Department:
        </label>
        <select name="course" id="attCourse" class="select-filter" onchange="document.getElementById('attendanceFilterForm').submit();">
          <?php foreach (['Computer Science', 'Information Tech', 'Mechanical', 'Civil Eng', 'Electrical'] as $c): ?>
            <option value="<?php echo e($c); ?>" <?php echo ($selectedCourse === $c) ? 'selected' : ''; ?>><?php echo e($c); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="attSem" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); white-space: nowrap;">
          <i class="fas fa-layer-group" style="color: #4f46e5;"></i> Semester:
        </label>
        <select name="semester" id="attSem" class="select-filter" onchange="document.getElementById('attendanceFilterForm').submit();">
          <?php foreach (['1st Semester', '2nd Semester', '3rd Semester', '4th Semester', '5th Semester', '6th Semester', '7th Semester', '8th Semester'] as $s): ?>
            <option value="<?php echo e($s); ?>" <?php echo ($selectedSemester === $s) ? 'selected' : ''; ?>><?php echo e($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 24px;">

  <!-- Form: Mark Daily Attendance -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-clipboard-check"></i>
        <h3>Mark Attendance: <?php echo date('M d, Y', strtotime($selectedDate)); ?></h3>
      </div>
      <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
        <?php echo count($studentsList); ?> Students in <?php echo e($selectedCourse); ?>
      </span>
    </div>
    <div class="card-body">
      <?php if (empty($studentsList)): ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
          No active students found in <?php echo e($selectedCourse); ?>.
        </div>
      <?php else: ?>
        <form action="/STUDENTPORTAL/admin/attendance.php" method="POST">
          <input type="hidden" name="action" value="save_attendance">
          <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
          <input type="hidden" name="date" value="<?php echo e($selectedDate); ?>">
          <input type="hidden" name="course" value="<?php echo e($selectedCourse); ?>">
          <input type="hidden" name="semester" value="<?php echo e($selectedSemester); ?>">

          <div class="table-responsive">
            <table class="portal-table">
              <thead>
                <tr>
                  <th>Roll No</th>
                  <th>Student Name</th>
                  <th>Attendance Status</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($studentsList as $st): ?>
                  <?php 
                    $currStatus = $existingMarks[$st['student_id']]['status'] ?? 'Present';
                    $currRem = $existingMarks[$st['student_id']]['remarks'] ?? '';
                  ?>
                  <tr>
                    <td>
                      <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">
                        <?php echo e($st['student_id']); ?>
                      </span>
                    </td>
                    <td>
                      <strong><?php echo e($st['name']); ?></strong>
                    </td>
                    <td>
                      <div style="display: flex; gap: 8px;">
                        <label style="font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; cursor: pointer; color: #059669;">
                          <input type="radio" name="status[<?php echo e($st['student_id']); ?>]" value="Present" <?php echo ($currStatus === 'Present') ? 'checked' : ''; ?>>
                          Present
                        </label>
                        <label style="font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; cursor: pointer; color: #dc2626;">
                          <input type="radio" name="status[<?php echo e($st['student_id']); ?>]" value="Absent" <?php echo ($currStatus === 'Absent') ? 'checked' : ''; ?>>
                          Absent
                        </label>
                        <label style="font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; cursor: pointer; color: #d97706;">
                          <input type="radio" name="status[<?php echo e($st['student_id']); ?>]" value="Late" <?php echo ($currStatus === 'Late') ? 'checked' : ''; ?>>
                          Late
                        </label>
                      </div>
                    </td>
                    <td>
                      <input type="text" name="remarks[<?php echo e($st['student_id']); ?>]" value="<?php echo e($currRem); ?>" placeholder="Optional notes" class="form-control" style="padding: 4px 8px; font-size: 0.8rem; width: 140px;">
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div style="margin-top: 18px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Attendance Records
            </button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Summary Table: Overall Attendance Percentages -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-chart-pie"></i>
        <h3>Student Attendance Compliance</h3>
      </div>
      <span style="font-size: 0.78rem; color: var(--text-muted);">Threshold: 75%</span>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="portal-table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Classes</th>
              <th>Present</th>
              <th>Compliance %</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($summary)): ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  No attendance records recorded yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($summary as $row): ?>
                <?php 
                  $pct = (float)$row['percentage'];
                  $badgeColor = ($pct >= 75) ? '#10b981' : (($pct >= 60) ? '#f59e0b' : '#ef4444');
                  $badgeBg = ($pct >= 75) ? '#ecfdf5' : (($pct >= 60) ? '#fffbeb' : '#fef2f2');
                ?>
                <tr>
                  <td>
                    <strong><?php echo e($row['name']); ?></strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo e($row['student_id']); ?> &bull; <?php echo e($row['course']); ?></div>
                  </td>
                  <td style="font-size: 0.85rem; color: var(--text-muted);">
                    <?php echo $row['total_classes']; ?>
                  </td>
                  <td style="font-size: 0.85rem; font-weight: 600; color: #059669;">
                    <?php echo $row['present_count']; ?>
                  </td>
                  <td>
                    <span style="background: <?php echo $badgeBg; ?>; color: <?php echo $badgeColor; ?>; font-weight: 700; padding: 4px 10px; border-radius: 12px; font-size: 0.82rem; border: 1px solid <?php echo $badgeColor; ?>40;">
                      <?php echo $pct; ?>%
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
