<?php
/**
 * Student Hub Portal - Course Curriculum Management
 * 
 * @package    StudentHubPortal
 * @subpackage Admin
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        admin/courses.php
 * Role:        Administrator
 * Access:      Strictly enforced via require_admin() in admin_header.php
 * Description: Enables Administrators to manage academic courses, branch departments,
 *              curriculum subjects, and faculty allocations.
 */

$pageTitle = 'Course Curriculum';
require_once dirname(__DIR__) . '/includes/admin_header.php';

$pdo = get_pdo();

// Flash message helper
$flash = get_flash();

// Course list
$courses = ['Computer Science', 'Information Tech', 'Mechanical', 'Civil Eng', 'Electrical'];
$selectedCourse = $_GET['course'] ?? 'Computer Science';

// Fetch distinct subjects registered in timetables for this course
$stmt = $pdo->prepare("
    SELECT DISTINCT subject_code, subject_name, course, semester, faculty_name, class_type, room_no
    FROM timetables
    WHERE course = ?
    ORDER BY semester ASC, subject_code ASC
");
$stmt->execute([$selectedCourse]);
$curriculum = $stmt->fetchAll();

// Group subjects by semester
$bySemester = [];
foreach ($curriculum as $item) {
    $sem = $item['semester'];
    if (!isset($bySemester[$sem])) {
        $bySemester[$sem] = [];
    }
    $bySemester[$sem][] = $item;
}
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Academic Curriculum & Course Modules</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Manage departmental branches, syllabus subjects, and faculty lecture allocations.
    </p>
  </div>
  <div>
    <a href="/STUDENTPORTAL/admin/timetable.php?action=new" class="btn btn-primary">
      <i class="fas fa-plus-circle"></i> Add Subject Slot
    </a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?php echo e($flash['type']); ?>">
    <i class="fas fa-info-circle"></i>
    <span><?php echo e($flash['message']); ?></span>
  </div>
<?php endif; ?>

<!-- Course Department Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
  <?php foreach ($courses as $c): ?>
    <a href="/STUDENTPORTAL/admin/courses.php?course=<?php echo urlencode($c); ?>"
       class="btn <?php echo ($selectedCourse === $c) ? 'btn-primary' : 'btn-outline'; ?>"
       style="font-size: 0.85rem; padding: 7px 16px;">
      <i class="fas fa-graduation-cap"></i> <?php echo e($c); ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- Department Overview Stats -->
<div class="stats-grid" style="margin-bottom: 24px;">
  <div class="stat-card">
    <div class="stat-icon indigo">
      <i class="fas fa-book"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo count($curriculum); ?></h3>
      <p>Subjects in <?php echo e($selectedCourse); ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal">
      <i class="fas fa-layer-group"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo count($bySemester); ?></h3>
      <p>Active Semesters</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber">
      <i class="fas fa-chalkboard-teacher"></i>
    </div>
    <div class="stat-info">
      <h3>
        <?php
          $facultyList = array_unique(array_column($curriculum, 'faculty_name'));
          echo count($facultyList);
        ?>
      </h3>
      <p>Assigned Professors</p>
    </div>
  </div>
</div>

<!-- Subject Tables by Semester -->
<?php if (empty($bySemester)): ?>
  <div class="portal-card">
    <div class="card-body" style="text-align: center; padding: 50px 20px;">
      <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
      <h3 style="color: var(--text-main); margin-bottom: 6px;">No Subjects Registered</h3>
      <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 450px; margin: 0 auto 18px;">
        There are currently no subjects mapped to <strong><?php echo e($selectedCourse); ?></strong>. You can add class slots from the Timetable Management module.
      </p>
      <a href="/STUDENTPORTAL/admin/timetable.php" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Go to Timetable Management
      </a>
    </div>
  </div>
<?php else: ?>
  <?php foreach ($bySemester as $sem => $subs): ?>
    <div class="portal-card" style="margin-bottom: 24px;">
      <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <div class="card-header-title">
          <i class="fas fa-graduation-cap" style="color: var(--color-primary);"></i>
          <h3><?php echo e($sem); ?> Curriculum</h3>
        </div>
        <span class="badge" style="background: #e2e8f0; color: #334155;">
          <?php echo count($subs); ?> Subjects
        </span>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 130px;">Subject Code</th>
                <th>Subject Name</th>
                <th>Assigned Faculty</th>
                <th>Class Type</th>
                <th>Assigned Room</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subs as $s): ?>
                <tr>
                  <td>
                    <span style="font-family: monospace; font-weight: 700; color: var(--color-primary); background: #eef2ff; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem;">
                      <?php echo e($s['subject_code'] ?: 'SUB101'); ?>
                    </span>
                  </td>
                  <td style="font-weight: 600; color: var(--text-main);">
                    <?php echo e($s['subject_name']); ?>
                  </td>
                  <td>
                    <i class="fas fa-user-tie" style="color: #64748b; margin-right: 6px; font-size: 0.85rem;"></i>
                    <?php echo e($s['faculty_name']); ?>
                  </td>
                  <td>
                    <span class="badge badge-type-<?php echo strtolower($s['class_type']); ?>">
                      <?php echo e($s['class_type']); ?>
                    </span>
                  </td>
                  <td>
                    <span style="font-size: 0.85rem; color: #475569;">
                      <i class="fas fa-door-open" style="margin-right: 4px;"></i> <?php echo e($s['room_no']); ?>
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <a href="/STUDENTPORTAL/admin/timetable.php?course=<?php echo urlencode($s['course']); ?>&semester=<?php echo urlencode($s['semester']); ?>"
                       class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8rem;">
                      <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
