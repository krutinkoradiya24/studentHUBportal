<?php
/**
 * admin/timetable.php - Comprehensive Academic Timetable Management
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 * 
 * Capabilities:
 * - Add Class Timetable Slots (Course, Semester, Day, Timings, Faculty, Room, Class Type)
 * - Edit Existing Timetable Slots
 * - Delete Timetable Slots
 * - Interactive Weekly Schedule Matrix Grid (Monday - Saturday)
 * - Filter by Course / Department & Semester
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/db.php';

init_secure_session();
$adminUser = require_admin();
$pdo = get_pdo();

// -------------------------------------------------------------
// POST HANDLERS: ADD, UPDATE, DELETE TIMETABLE SLOT
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/timetable.php');
        exit;
    }

    if ($action === 'add_slot') {
        $course      = trim($_POST['course'] ?? '');
        $semester    = trim($_POST['semester'] ?? '1st Semester');
        $dayOfWeek   = trim($_POST['day_of_week'] ?? 'Monday');
        $startTime   = trim($_POST['start_time'] ?? '');
        $endTime     = trim($_POST['end_time'] ?? '');
        $subjectName = trim($_POST['subject_name'] ?? '');
        $subjectCode = strtoupper(trim($_POST['subject_code'] ?? ''));
        $facultyName = trim($_POST['faculty_name'] ?? '');
        $roomNo      = trim($_POST['room_no'] ?? '');
        $classType   = trim($_POST['class_type'] ?? 'Lecture');

        if (empty($course) || empty($subjectName) || empty($startTime) || empty($endTime) || empty($roomNo)) {
            set_flash('danger', 'Please fill in all required fields (Course, Subject, Times, and Room).');
            header("Location: /STUDENTPORTAL/admin/timetable.php?course=" . urlencode($course) . "&semester=" . urlencode($semester));
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO timetables (course, semester, day_of_week, start_time, end_time, subject_name, subject_code, faculty_name, room_no, class_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$course, $semester, $dayOfWeek, $startTime, $endTime, $subjectName, $subjectCode, $facultyName, $roomNo, $classType]);

            set_flash('success', "Timetable slot for '{$subjectName}' added successfully for {$dayOfWeek}!");
        } catch (Throwable $e) {
            set_flash('danger', "Database error adding timetable slot: " . $e->getMessage());
        }

        header("Location: /STUDENTPORTAL/admin/timetable.php?course=" . urlencode($course) . "&semester=" . urlencode($semester));
        exit;
    }

    if ($action === 'update_slot') {
        $slotId      = (int)($_POST['slot_id'] ?? 0);
        $course      = trim($_POST['course'] ?? '');
        $semester    = trim($_POST['semester'] ?? '1st Semester');
        $dayOfWeek   = trim($_POST['day_of_week'] ?? 'Monday');
        $startTime   = trim($_POST['start_time'] ?? '');
        $endTime     = trim($_POST['end_time'] ?? '');
        $subjectName = trim($_POST['subject_name'] ?? '');
        $subjectCode = strtoupper(trim($_POST['subject_code'] ?? ''));
        $facultyName = trim($_POST['faculty_name'] ?? '');
        $roomNo      = trim($_POST['room_no'] ?? '');
        $classType   = trim($_POST['class_type'] ?? 'Lecture');

        if ($slotId <= 0 || empty($course) || empty($subjectName)) {
            set_flash('danger', 'Invalid timetable slot details provided.');
            header('Location: /STUDENTPORTAL/admin/timetable.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE timetables
                SET course = ?, semester = ?, day_of_week = ?, start_time = ?, end_time = ?, subject_name = ?, subject_code = ?, faculty_name = ?, room_no = ?, class_type = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$course, $semester, $dayOfWeek, $startTime, $endTime, $subjectName, $subjectCode, $facultyName, $roomNo, $classType, $slotId]);

            set_flash('success', "Timetable slot for '{$subjectName}' updated successfully!");
        } catch (Throwable $e) {
            set_flash('danger', "Failed to update timetable slot: " . $e->getMessage());
        }

        header("Location: /STUDENTPORTAL/admin/timetable.php?course=" . urlencode($course) . "&semester=" . urlencode($semester));
        exit;
    }

    if ($action === 'delete_slot') {
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $course = trim($_POST['course'] ?? '');
        $semester = trim($_POST['semester'] ?? '');

        if ($slotId <= 0) {
            set_flash('danger', 'Invalid timetable slot identifier.');
            header('Location: /STUDENTPORTAL/admin/timetable.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM timetables WHERE id = ?");
            $stmt->execute([$slotId]);
            set_flash('success', 'Timetable class slot deleted.');
        } catch (Throwable $e) {
            set_flash('danger', "Failed to delete slot: " . $e->getMessage());
        }

        header("Location: /STUDENTPORTAL/admin/timetable.php?course=" . urlencode($course) . "&semester=" . urlencode($semester));
        exit;
    }
}

// -------------------------------------------------------------
// QUERY & FILTER LOGIC
// -------------------------------------------------------------
$selectedCourse = $_GET['course'] ?? 'Computer Science';
$selectedSemester = $_GET['semester'] ?? '6th Semester';

// Fetch distinct courses and semesters from existing records or defaults
$coursesList = ['Computer Science', 'Information Tech', 'Mechanical', 'Civil Eng', 'Electrical'];
$semestersList = ['1st Semester', '2nd Semester', '3rd Semester', '4th Semester', '5th Semester', '6th Semester', '7th Semester', '8th Semester'];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch slots for selected course and semester
$stmt = $pdo->prepare("
    SELECT * FROM timetables
    WHERE course = ? AND semester = ?
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time ASC
");
$stmt->execute([$selectedCourse, $selectedSemester]);
$allSlots = $stmt->fetchAll();

// Group slots by day of week
$slotsByDay = [];
foreach ($daysOfWeek as $day) {
    $slotsByDay[$day] = [];
}
foreach ($allSlots as $slot) {
    $day = $slot['day_of_week'];
    if (isset($slotsByDay[$day])) {
        $slotsByDay[$day][] = $slot;
    }
}

$pageTitle = 'Timetable Management';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Header Actions & Navigation -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Academic Class Timetable</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Manage weekly lecture schedules, lab sessions, and classroom allocations.
    </p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addSlotModal')">
    <i class="fas fa-plus-circle"></i> Add Class Slot
  </button>
</div>

<!-- Filter Bar: Course & Semester Selectors -->
<div class="portal-card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 16px 20px;">
    <form method="GET" action="/STUDENTPORTAL/admin/timetable.php" id="filterForm" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="courseSelect" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); white-space: nowrap;">
          <i class="fas fa-graduation-cap" style="color: #4f46e5;"></i> Department / Branch:
        </label>
        <select name="course" id="courseSelect" class="select-filter" onchange="document.getElementById('filterForm').submit();">
          <?php foreach ($coursesList as $c): ?>
            <option value="<?php echo e($c); ?>" <?php echo ($selectedCourse === $c) ? 'selected' : ''; ?>>
              <?php echo e($c); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; align-items: center; gap: 8px;">
        <label for="semSelect" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); white-space: nowrap;">
          <i class="fas fa-layer-group" style="color: #4f46e5;"></i> Semester:
        </label>
        <select name="semester" id="semSelect" class="select-filter" onchange="document.getElementById('filterForm').submit();">
          <?php foreach ($semestersList as $s): ?>
            <option value="<?php echo e($s); ?>" <?php echo ($selectedSemester === $s) ? 'selected' : ''; ?>>
              <?php echo e($s); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-left: auto; font-size: 0.85rem; color: var(--text-muted);">
        Schedule for: <strong style="color: #4f46e5;"><?php echo e($selectedCourse); ?> (<?php echo e($selectedSemester); ?>)</strong> &bull; Total: <strong><?php echo count($allSlots); ?> slots</strong>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- WEEKLY SCHEDULE MATRIX GRID (MONDAY - SATURDAY)               -->
<!-- ============================================================= -->
<div class="timetable-grid">
  <?php foreach ($daysOfWeek as $day): ?>
    <div class="day-column">
      <div class="day-header">
        <span><i class="far fa-calendar-alt"></i> <?php echo $day; ?></span>
        <span style="font-size: 0.75rem; background: rgba(255, 255, 255, 0.2); padding: 2px 8px; border-radius: 12px;">
          <?php echo count($slotsByDay[$day]); ?> Classes
        </span>
      </div>

      <div class="day-slots-list">
        <?php if (empty($slotsByDay[$day])): ?>
          <div style="text-align: center; padding: 28px 10px; color: var(--text-light); font-size: 0.85rem;">
            <i class="fas fa-coffee" style="font-size: 1.5rem; display: block; margin-bottom: 6px; opacity: 0.4;"></i>
            No classes scheduled for <?php echo $day; ?>.
          </div>
        <?php else: ?>
          <?php foreach ($slotsByDay[$day] as $slot): ?>
            <div class="slot-card">
              <div class="slot-time">
                <i class="far fa-clock" style="color: #4f46e5;"></i>
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

              <div class="slot-actions">
                <button type="button" class="btn btn-outline btn-sm edit-slot-btn"
                        data-id="<?php echo $slot['id']; ?>"
                        data-course="<?php echo e($slot['course']); ?>"
                        data-semester="<?php echo e($slot['semester']); ?>"
                        data-day="<?php echo e($slot['day_of_week']); ?>"
                        data-start="<?php echo e($slot['start_time']); ?>"
                        data-end="<?php echo e($slot['end_time']); ?>"
                        data-name="<?php echo e($slot['subject_name']); ?>"
                        data-code="<?php echo e($slot['subject_code']); ?>"
                        data-faculty="<?php echo e($slot['faculty_name']); ?>"
                        data-room="<?php echo e($slot['room_no']); ?>"
                        data-type="<?php echo e($slot['class_type']); ?>"
                        style="padding: 3px 8px; font-size: 0.78rem;">
                  <i class="fas fa-edit" style="color: #4f46e5;"></i> Edit
                </button>

                <form action="/STUDENTPORTAL/admin/timetable.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to remove this timetable slot?');">
                  <input type="hidden" name="action" value="delete_slot">
                  <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                  <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                  <input type="hidden" name="course" value="<?php echo e($selectedCourse); ?>">
                  <input type="hidden" name="semester" value="<?php echo e($selectedSemester); ?>">
                  <button type="submit" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem; color: #ef4444; border-color: #fecaca;">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============================================================= -->
<!-- MODAL: ADD TIMETABLE SLOT                                     -->
<!-- ============================================================= -->
<div class="modal-overlay" id="addSlotModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-clock" style="color: #4f46e5;"></i> Add Class Timetable Slot</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('addSlotModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/timetable.php" method="POST">
      <input type="hidden" name="action" value="add_slot">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="slotCourse">Branch / Course *</label>
            <select id="slotCourse" name="course" class="form-control" required>
              <?php foreach ($coursesList as $c): ?>
                <option value="<?php echo e($c); ?>" <?php echo ($selectedCourse === $c) ? 'selected' : ''; ?>>
                  <?php echo e($c); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="slotSemester">Semester *</label>
            <select id="slotSemester" name="semester" class="form-control" required>
              <?php foreach ($semestersList as $s): ?>
                <option value="<?php echo e($s); ?>" <?php echo ($selectedSemester === $s) ? 'selected' : ''; ?>>
                  <?php echo e($s); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="slotDay">Day of Week *</label>
            <select id="slotDay" name="day_of_week" class="form-control" required>
              <?php foreach ($daysOfWeek as $d): ?>
                <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="slotType">Session Type *</label>
            <select id="slotType" name="class_type" class="form-control" required>
              <option value="Lecture">Lecture</option>
              <option value="Lab">Practical Lab</option>
              <option value="Tutorial">Tutorial / Mentorship</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="slotSubject">Subject Title *</label>
            <input type="text" id="slotSubject" name="subject_name" class="form-control" placeholder="e.g. Cloud Computing & DevOps" required>
          </div>

          <div class="form-group">
            <label for="slotCode">Subject Code</label>
            <input type="text" id="slotCode" name="subject_code" class="form-control" placeholder="e.g. CS601">
          </div>

          <div class="form-group">
            <label for="slotFaculty">Faculty / Instructor Name *</label>
            <input type="text" id="slotFaculty" name="faculty_name" class="form-control" placeholder="e.g. Dr. Sarah Jenkins" required>
          </div>

          <div class="form-group">
            <label for="slotStart">Start Time (24h/12h) *</label>
            <input type="text" id="slotStart" name="start_time" class="form-control" placeholder="e.g. 09:00" required>
          </div>

          <div class="form-group">
            <label for="slotEnd">End Time (24h/12h) *</label>
            <input type="text" id="slotEnd" name="end_time" class="form-control" placeholder="e.g. 10:00" required>
          </div>

          <div class="form-group full-width">
            <label for="slotRoom">Classroom / Lab Room No. *</label>
            <input type="text" id="slotRoom" name="room_no" class="form-control" placeholder="e.g. Room 204 or Lab 301" required>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addSlotModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add to Timetable</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDIT TIMETABLE SLOT                                    -->
<!-- ============================================================= -->
<div class="modal-overlay" id="editSlotModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-edit" style="color: #4f46e5;"></i> Edit Timetable Slot</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('editSlotModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/timetable.php" method="POST">
      <input type="hidden" name="action" value="update_slot">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
      <input type="hidden" name="slot_id" id="editSlotId">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="editSlotCourse">Course *</label>
            <select id="editSlotCourse" name="course" class="form-control" required>
              <?php foreach ($coursesList as $c): ?>
                <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="editSlotSemester">Semester *</label>
            <select id="editSlotSemester" name="semester" class="form-control" required>
              <?php foreach ($semestersList as $s): ?>
                <option value="<?php echo e($s); ?>"><?php echo e($s); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="editSlotDay">Day of Week *</label>
            <select id="editSlotDay" name="day_of_week" class="form-control" required>
              <?php foreach ($daysOfWeek as $d): ?>
                <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="editSlotType">Session Type *</label>
            <select id="editSlotType" name="class_type" class="form-control" required>
              <option value="Lecture">Lecture</option>
              <option value="Lab">Practical Lab</option>
              <option value="Tutorial">Tutorial / Mentorship</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="editSlotSubject">Subject Title *</label>
            <input type="text" id="editSlotSubject" name="subject_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editSlotCode">Subject Code</label>
            <input type="text" id="editSlotCode" name="subject_code" class="form-control">
          </div>

          <div class="form-group">
            <label for="editSlotFaculty">Faculty / Instructor Name *</label>
            <input type="text" id="editSlotFaculty" name="faculty_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editSlotStart">Start Time *</label>
            <input type="text" id="editSlotStart" name="start_time" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editSlotEnd">End Time *</label>
            <input type="text" id="editSlotEnd" name="end_time" class="form-control" required>
          </div>

          <div class="form-group full-width">
            <label for="editSlotRoom">Room / Lab No. *</label>
            <input type="text" id="editSlotRoom" name="room_no" class="form-control" required>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editSlotModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // If ?action=new, open add modal
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'new') {
    openModal('addSlotModal');
  }

  // Populate Edit Modal
  document.querySelectorAll('.edit-slot-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editSlotId').value = btn.dataset.id;
      document.getElementById('editSlotCourse').value = btn.dataset.course;
      document.getElementById('editSlotSemester').value = btn.dataset.semester;
      document.getElementById('editSlotDay').value = btn.dataset.day;
      document.getElementById('editSlotType').value = btn.dataset.type;
      document.getElementById('editSlotSubject').value = btn.dataset.name;
      document.getElementById('editSlotCode').value = btn.dataset.code;
      document.getElementById('editSlotFaculty').value = btn.dataset.faculty;
      document.getElementById('editSlotStart').value = btn.dataset.start;
      document.getElementById('editSlotEnd').value = btn.dataset.end;
      document.getElementById('editSlotRoom').value = btn.dataset.room;
      openModal('editSlotModal');
    });
  });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
