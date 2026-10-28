<?php
/**
 * admin/students.php - Student Records Management & Directory CRUD
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 * 
 * Capabilities:
 * - Add New Student Record (Student ID, Name, Email, Phone, Course, Semester, Status)
 * - Edit Existing Student Record
 * - Soft-Delete Student (Status changed to Inactive)
 * - Reactivate Student (Status changed to Active)
 * - Permanent Delete Option
 * - Live Instant Search & Course / Status Filtering
 * - Excel CSV Export with UTF-8 BOM
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/db.php';

init_secure_session();
$adminUser = require_admin();
$pdo = get_pdo();

// -------------------------------------------------------------
// POST HANDLERS: ADD, UPDATE, SOFT-DELETE, REACTIVATE, DELETE
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }

    if ($action === 'add_student') {
        $studentId = strtoupper(trim($_POST['student_id'] ?? ''));
        $name      = trim($_POST['name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone'] ?? '');
        $course    = trim($_POST['course'] ?? 'Computer Science');
        $semester  = trim($_POST['semester'] ?? '1st Semester');
        $status    = trim($_POST['status'] ?? 'Active');

        if (empty($studentId) || empty($name) || empty($email) || empty($phone)) {
            set_flash('danger', 'Please fill in all required fields (Student ID, Name, Email, Phone).');
            header('Location: /STUDENTPORTAL/admin/students.php');
            exit;
        }

        // Duplicate check
        $dup = $pdo->prepare("SELECT id FROM students WHERE student_id = ? OR email = ? LIMIT 1");
        $dup->execute([$studentId, $email]);
        if ($dup->fetch()) {
            set_flash('danger', "A student with ID '{$studentId}' or email '{$email}' already exists.");
            header('Location: /STUDENTPORTAL/admin/students.php');
            exit;
        }

        try {
            $ins = $pdo->prepare("
                INSERT INTO students (student_id, name, email, phone, course, semester, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$studentId, $name, $email, $phone, $course, $semester, $status]);
            set_flash('success', "Student '{$name}' ({$studentId}) added successfully!");
        } catch (Throwable $e) {
            set_flash('danger', "Error adding student: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }

    if ($action === 'update_student') {
        $id        = (int)($_POST['id'] ?? 0);
        $studentId = strtoupper(trim($_POST['student_id'] ?? ''));
        $name      = trim($_POST['name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone'] ?? '');
        $course    = trim($_POST['course'] ?? 'Computer Science');
        $semester  = trim($_POST['semester'] ?? '1st Semester');
        $status    = trim($_POST['status'] ?? 'Active');

        if ($id <= 0 || empty($name) || empty($email)) {
            set_flash('danger', 'Invalid student record details.');
            header('Location: /STUDENTPORTAL/admin/students.php');
            exit;
        }

        // Duplicate check on other rows
        $dup = $pdo->prepare("SELECT id FROM students WHERE (student_id = ? OR email = ?) AND id != ? LIMIT 1");
        $dup->execute([$studentId, $email, $id]);
        if ($dup->fetch()) {
            set_flash('danger', "Another student already has ID '{$studentId}' or email '{$email}'.");
            header('Location: /STUDENTPORTAL/admin/students.php');
            exit;
        }

        try {
            $upd = $pdo->prepare("
                UPDATE students
                SET student_id = ?, name = ?, email = ?, phone = ?, course = ?, semester = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$studentId, $name, $email, $phone, $course, $semester, $status, $id]);
            set_flash('success', "Student details for '{$name}' updated successfully!");
        } catch (Throwable $e) {
            set_flash('danger', "Failed to update student: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }

    if ($action === 'soft_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE students SET status = 'Inactive', updated_at = NOW() WHERE id = ?")->execute([$id]);
            set_flash('warning', 'Student status marked as Inactive (Soft-deleted).');
        }
        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }

    if ($action === 'reactivate') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE students SET status = 'Active', updated_at = NOW() WHERE id = ?")->execute([$id]);
            set_flash('success', 'Student record reactivated to Active status.');
        }
        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }

    if ($action === 'hard_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
            set_flash('success', 'Student record permanently removed from database.');
        }
        header('Location: /STUDENTPORTAL/admin/students.php');
        exit;
    }
}

// -------------------------------------------------------------
// CSV EXPORT ACTION
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="students_roster_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Microsoft Excel compatibility
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['ID', 'Student Code', 'Full Name', 'Email Address', 'Phone Number', 'Course / Branch', 'Semester', 'Status', 'Registered Date']);

    $rows = $pdo->query("SELECT id, student_id, name, email, phone, course, semester, status, created_at FROM students ORDER BY id DESC")->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['student_id'], $r['name'], $r['email'], $r['phone'], $r['course'], $r['semester'], $r['status'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Fetch all students
$students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll();

$pageTitle = 'Student Records (CRUD)';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Student Academic Directory</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Manage student profiles, enrollment status, soft-delete states, and export official rosters.
    </p>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="/STUDENTPORTAL/admin/students.php?action=export_csv" class="btn btn-outline">
      <i class="fas fa-file-excel" style="color: #10b981;"></i> Export CSV (Excel)
    </a>
    <button type="button" class="btn btn-primary" onclick="openModal('addStudentModal')">
      <i class="fas fa-user-plus"></i> Add New Student
    </button>
  </div>
</div>

<!-- Main Table Card -->
<div class="portal-card">
  <div class="card-body">
    <!-- Toolbar with Search & Filter -->
    <div class="portal-toolbar">
      <div class="toolbar-filters">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="studentSearchInput" class="search-input" placeholder="Search by name, ID, email, phone..." autocomplete="off">
        </div>

        <select id="studentCourseFilter" class="select-filter" aria-label="Filter students by course">
          <option value="">All Branches</option>
          <option value="Computer Science">Computer Science</option>
          <option value="Information Tech">Information Tech</option>
          <option value="Mechanical">Mechanical</option>
          <option value="Civil Eng">Civil Eng</option>
          <option value="Electrical">Electrical</option>
        </select>

        <select id="studentStatusFilter" class="select-filter" aria-label="Filter students by status">
          <option value="">All Statuses</option>
          <option value="Active">Active Only</option>
          <option value="Inactive">Inactive (Soft Deleted)</option>
        </select>
      </div>

      <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
        Showing <strong><?php echo count($students); ?></strong> student records
      </div>
    </div>

    <!-- Student Table -->
    <div class="table-responsive">
      <table class="portal-table" id="studentsTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Email Address</th>
            <th>Phone</th>
            <th>Course / Branch</th>
            <th>Semester</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr class="no-filter">
              <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                No student records found in database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($students as $s): ?>
              <tr>
                <td>
                  <span style="font-family: monospace; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; font-size: 0.84rem;">
                    <?php echo e($s['student_id']); ?>
                  </span>
                </td>
                <td style="font-weight: 600; color: var(--text-main);">
                  <?php echo e($s['name']); ?>
                </td>
                <td>
                  <a href="mailto:<?php echo e($s['email']); ?>" style="color: #4f46e5; text-decoration: none;">
                    <?php echo e($s['email']); ?>
                  </a>
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">
                  <?php echo e($s['phone']); ?>
                </td>
                <td>
                  <span style="background: #f1f5f9; color: #334155; padding: 2px 8px; border-radius: 4px; font-size: 0.82rem; font-weight: 500;">
                    <?php echo e($s['course']); ?>
                  </span>
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">
                  <?php echo e($s['semester']); ?>
                </td>
                <td>
                  <span class="badge badge-<?php echo strtolower($s['status']); ?>">
                    <?php echo e($s['status']); ?>
                  </span>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                  <button type="button" class="btn btn-outline btn-sm edit-student-btn"
                          data-id="<?php echo $s['id']; ?>"
                          data-code="<?php echo e($s['student_id']); ?>"
                          data-name="<?php echo e($s['name']); ?>"
                          data-email="<?php echo e($s['email']); ?>"
                          data-phone="<?php echo e($s['phone']); ?>"
                          data-course="<?php echo e($s['course']); ?>"
                          data-semester="<?php echo e($s['semester']); ?>"
                          data-status="<?php echo e($s['status']); ?>">
                    <i class="fas fa-edit" style="color: #4f46e5;"></i> Edit
                  </button>

                  <?php if ($s['status'] === 'Active'): ?>
                    <form action="/STUDENTPORTAL/admin/students.php" method="POST" style="display: inline-block; margin: 0;">
                      <input type="hidden" name="action" value="soft_delete">
                      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                      <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                      <button type="submit" class="btn btn-outline btn-sm" title="Soft delete (toggle to Inactive)" style="color: #f59e0b; border-color: #fde68a;">
                        <i class="fas fa-user-slash"></i> Inactivate
                      </button>
                    </form>
                  <?php else: ?>
                    <form action="/STUDENTPORTAL/admin/students.php" method="POST" style="display: inline-block; margin: 0;">
                      <input type="hidden" name="action" value="reactivate">
                      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                      <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                      <button type="submit" class="btn btn-outline btn-sm" title="Reactivate to Active" style="color: #10b981; border-color: #a7f3d0;">
                        <i class="fas fa-user-check"></i> Reactivate
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <tr class="empty-filter-row" style="display: none;">
            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
              No students found matching your search and filter criteria.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: ADD STUDENT                                            -->
<!-- ============================================================= -->
<div class="modal-overlay" id="addStudentModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-user-plus" style="color: #4f46e5;"></i> Add New Student</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('addStudentModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/students.php" method="POST">
      <input type="hidden" name="action" value="add_student">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="studId">Student ID / Roll No *</label>
            <input type="text" id="studId" name="student_id" class="form-control" placeholder="e.g. 24CS050" required>
          </div>

          <div class="form-group">
            <label for="studStatus">Status *</label>
            <select id="studStatus" name="status" class="form-control" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="studName">Full Name *</label>
            <input type="text" id="studName" name="name" class="form-control" placeholder="e.g. Rohan Verma" required>
          </div>

          <div class="form-group">
            <label for="studEmail">Email Address *</label>
            <input type="email" id="studEmail" name="email" class="form-control" placeholder="rohan.v@studenthub.edu" required>
          </div>

          <div class="form-group">
            <label for="studPhone">Phone Number *</label>
            <input type="tel" id="studPhone" name="phone" class="form-control" placeholder="9876543210" required>
          </div>

          <div class="form-group">
            <label for="studCourse">Branch / Course *</label>
            <select id="studCourse" name="course" class="form-control" required>
              <option value="Computer Science">Computer Science</option>
              <option value="Information Tech">Information Tech</option>
              <option value="Mechanical">Mechanical</option>
              <option value="Civil Eng">Civil Eng</option>
              <option value="Electrical">Electrical</option>
            </select>
          </div>

          <div class="form-group">
            <label for="studSem">Current Semester *</label>
            <select id="studSem" name="semester" class="form-control" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="3rd Semester">3rd Semester</option>
              <option value="4th Semester">4th Semester</option>
              <option value="5th Semester">5th Semester</option>
              <option value="6th Semester">6th Semester</option>
              <option value="7th Semester">7th Semester</option>
              <option value="8th Semester">8th Semester</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addStudentModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Student Record</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDIT STUDENT                                           -->
<!-- ============================================================= -->
<div class="modal-overlay" id="editStudentModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-edit" style="color: #4f46e5;"></i> Edit Student Record</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('editStudentModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/students.php" method="POST">
      <input type="hidden" name="action" value="update_student">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
      <input type="hidden" name="id" id="editStudRecordId">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="editStudId">Student ID *</label>
            <input type="text" id="editStudId" name="student_id" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editStudStatus">Status *</label>
            <select id="editStudStatus" name="status" class="form-control" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div class="form-group full-width">
            <label for="editStudName">Full Name *</label>
            <input type="text" id="editStudName" name="name" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editStudEmail">Email Address *</label>
            <input type="email" id="editStudEmail" name="email" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editStudPhone">Phone Number *</label>
            <input type="tel" id="editStudPhone" name="phone" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editStudCourse">Branch / Course *</label>
            <select id="editStudCourse" name="course" class="form-control" required>
              <option value="Computer Science">Computer Science</option>
              <option value="Information Tech">Information Tech</option>
              <option value="Mechanical">Mechanical</option>
              <option value="Civil Eng">Civil Eng</option>
              <option value="Electrical">Electrical</option>
            </select>
          </div>

          <div class="form-group">
            <label for="editStudSem">Semester *</label>
            <select id="editStudSem" name="semester" class="form-control" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="3rd Semester">3rd Semester</option>
              <option value="4th Semester">4th Semester</option>
              <option value="5th Semester">5th Semester</option>
              <option value="6th Semester">6th Semester</option>
              <option value="7th Semester">7th Semester</option>
              <option value="8th Semester">8th Semester</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editStudentModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Record</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'new') {
    openModal('addStudentModal');
  }

  initTableSearch('studentSearchInput', 'studentsTable');
  initTableFilter('studentCourseFilter', 'studentsTable', 4);
  initTableFilter('studentStatusFilter', 'studentsTable', 6);

  document.querySelectorAll('.edit-student-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editStudRecordId').value = btn.dataset.id;
      document.getElementById('editStudId').value = btn.dataset.code;
      document.getElementById('editStudName').value = btn.dataset.name;
      document.getElementById('editStudEmail').value = btn.dataset.email;
      document.getElementById('editStudPhone').value = btn.dataset.phone;
      document.getElementById('editStudCourse').value = btn.dataset.course;
      document.getElementById('editStudSem').value = btn.dataset.semester;
      document.getElementById('editStudStatus').value = btn.dataset.status;
      openModal('editStudentModal');
    });
  });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
