<?php
/**
 * student/timetable.php - Student Class Timetable Viewer
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 * Admins are blocked and redirected to the Admin Console.
 */

$pageTitle = 'Class Timetable';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

// Enrolled course & semester from profile
$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT course, year FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$userProf = $uStmt->fetch();

$studCourse = $userProf['course'] ?? 'Computer Science';
$selectedSem = $_GET['semester'] ?? '6th Semester';
$selectedDay = $_GET['day'] ?? 'All';

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch slots for this student's branch & semester
$query = "SELECT * FROM timetables WHERE course = :course";
$params = [':course' => $studCourse];

if ($selectedSem !== 'All') {
    $query .= " AND semester = :semester";
    $params[':semester'] = $selectedSem;
}

if ($selectedDay !== 'All' && in_array($selectedDay, $daysOfWeek, true)) {
    $query .= " AND day_of_week = :day";
    $params[':day'] = $selectedDay;
}

$query .= " ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$slots = $stmt->fetchAll();

// Group by day
$slotsByDay = [];
foreach ($daysOfWeek as $d) {
    $slotsByDay[$d] = [];
}
foreach ($slots as $s) {
    $day = $s['day_of_week'];
    if (isset($slotsByDay[$day])) {
        $slotsByDay[$day][] = $s;
    }
}
?>

<!-- Timetable Header Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Academic Timetable</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Weekly class schedule for <strong><?php echo e($studCourse); ?></strong> &bull; <?php echo e($selectedSem); ?>
    </p>
  </div>
  <button type="button" class="btn btn-outline" onclick="window.print()">
    <i class="fas fa-print"></i> Print Schedule
  </button>
</div>

<!-- Day Selector Filter Tabs -->
<div class="portal-card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
      <a href="/STUDENTPORTAL/student/timetable.php?day=All&semester=<?php echo urlencode($selectedSem); ?>"
         class="btn btn-sm <?php echo ($selectedDay === 'All') ? 'btn-student' : 'btn-outline'; ?>">
        Full Week
      </a>
      <?php foreach ($daysOfWeek as $d): ?>
        <a href="/STUDENTPORTAL/student/timetable.php?day=<?php echo urlencode($d); ?>&semester=<?php echo urlencode($selectedSem); ?>"
           class="btn btn-sm <?php echo ($selectedDay === $d) ? 'btn-student' : 'btn-outline'; ?>">
          <?php echo $d; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Semester selector -->
    <form method="GET" action="/STUDENTPORTAL/student/timetable.php" id="semForm" style="display: flex; align-items: center; gap: 8px;">
      <input type="hidden" name="day" value="<?php echo e($selectedDay); ?>">
      <label for="semSelect" style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">Semester:</label>
      <select name="semester" id="semSelect" class="select-filter" style="padding: 6px 12px; font-size: 0.84rem;" onchange="document.getElementById('semForm').submit();">
        <?php foreach (['1st Semester', '2nd Semester', '3rd Semester', '4th Semester', '5th Semester', '6th Semester', '7th Semester', '8th Semester'] as $s): ?>
          <option value="<?php echo e($s); ?>" <?php echo ($selectedSem === $s) ? 'selected' : ''; ?>><?php echo e($s); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</div>

<!-- Timetable Schedule Display -->
<?php if ($selectedDay !== 'All'): ?>
  <!-- Single Day View -->
  <div class="portal-card">
    <div class="card-header" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff;">
      <div class="card-header-title">
        <i class="far fa-calendar-alt" style="color: #fff;"></i>
        <h3 style="color: #fff;"><?php echo e($selectedDay); ?> Schedule</h3>
      </div>
      <span style="font-size: 0.82rem; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px;">
        <?php echo count($slotsByDay[$selectedDay] ?? []); ?> Scheduled Classes
      </span>
    </div>
    <div class="card-body">
      <?php if (empty($slotsByDay[$selectedDay])): ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
          No classes scheduled for <?php echo e($selectedDay); ?>.
        </div>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
          <?php foreach ($slotsByDay[$selectedDay] as $slot): ?>
            <div class="slot-card" style="border-left-color: #0d9488;">
              <div class="slot-time">
                <i class="far fa-clock" style="color: #0d9488;"></i>
                <span><?php echo e($slot['start_time']); ?> - <?php echo e($slot['end_time']); ?></span>
                <span class="badge badge-type-<?php echo strtolower($slot['class_type']); ?>" style="margin-left: auto;">
                  <?php echo e($slot['class_type']); ?>
                </span>
              </div>
              <div class="slot-subject" style="font-size: 1.05rem;">
                <?php echo e($slot['subject_name']); ?>
                <?php if (!empty($slot['subject_code'])): ?>
                  <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 500;">(<?php echo e($slot['subject_code']); ?>)</span>
                <?php endif; ?>
              </div>
              <div class="slot-meta" style="margin-top: 8px;">
                <span><i class="fas fa-user-tie"></i> <?php echo e($slot['faculty_name']); ?></span>
                <span class="slot-room"><i class="fas fa-door-open"></i> <?php echo e($slot['room_no']); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <!-- Full Weekly Grid Matrix -->
  <div class="timetable-grid">
    <?php foreach ($daysOfWeek as $day): ?>
      <div class="day-column">
        <div class="day-header" style="background: linear-gradient(135deg, #0d9488, #0f766e);">
          <span><i class="far fa-calendar-alt"></i> <?php echo $day; ?></span>
          <span style="font-size: 0.75rem; background: rgba(255, 255, 255, 0.2); padding: 2px 8px; border-radius: 12px;">
            <?php echo count($slotsByDay[$day]); ?> Classes
          </span>
        </div>

        <div class="day-slots-list">
          <?php if (empty($slotsByDay[$day])): ?>
            <div style="text-align: center; padding: 28px 10px; color: var(--text-light); font-size: 0.85rem;">
              <i class="fas fa-coffee" style="font-size: 1.5rem; display: block; margin-bottom: 6px; opacity: 0.4;"></i>
              No classes scheduled.
            </div>
          <?php else: ?>
            <?php foreach ($slotsByDay[$day] as $slot): ?>
              <div class="slot-card" style="border-left-color: #0d9488;">
                <div class="slot-time">
                  <i class="far fa-clock" style="color: #0d9488;"></i>
                  <span><?php echo e($slot['start_time']); ?> - <?php echo e($slot['end_time']); ?></span>
                  <span class="badge badge-type-<?php echo strtolower($slot['class_type']); ?>" style="margin-left: auto;">
                    <?php echo e($slot['class_type']); ?>
                  </span>
                </div>

                <div class="slot-subject">
                  <?php echo e($slot['subject_name']); ?>
                  <?php if (!empty($slot['subject_code'])): ?>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">(<?php echo e($slot['subject_code']); ?>)</span>
                  <?php endif; ?>
                </div>

                <div class="slot-meta">
                  <span class="slot-faculty">
                    <i class="fas fa-user-tie" style="color: #64748b;"></i> <?php echo e($slot['faculty_name']); ?>
                  </span>
                  <span class="slot-room">
                    <i class="fas fa-door-open"></i> <?php echo e($slot['room_no']); ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
