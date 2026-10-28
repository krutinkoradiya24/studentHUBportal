<?php
/**
 * student/courses.php - Enrolled Academic Courses & Subjects
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 */

$pageTitle = 'Enrolled Courses';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT course, year FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$userProf = $uStmt->fetch();

$studCourse = $userProf['course'] ?? 'Computer Science';
$currentSem = '6th Semester';

// Fetch distinct subjects from timetables for this course & semester
$stmt = $pdo->prepare("
    SELECT DISTINCT subject_name, subject_code, faculty_name, class_type, room_no
    FROM timetables
    WHERE course = ?
    ORDER BY subject_name ASC
");
$stmt->execute([$studCourse]);
$subjects = $stmt->fetchAll();
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Academic Curriculum & Subjects</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Registered subjects for <strong><?php echo e($studCourse); ?></strong> &bull; <?php echo e($currentSem); ?>
    </p>
  </div>
</div>

<div class="portal-card">
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px;">
      <?php if (empty($subjects)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
          No subject modules registered for this branch.
        </div>
      <?php else: ?>
        <?php foreach ($subjects as $sub): ?>
          <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; background: #fff; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <span style="font-family: monospace; font-weight: 700; color: #0d9488; background: #f0fdfa; padding: 2px 8px; border-radius: 4px; font-size: 0.82rem;">
                <?php echo e($sub['subject_code'] ?: 'SUB101'); ?>
              </span>
              <span class="badge badge-type-<?php echo strtolower($sub['class_type']); ?>">
                <?php echo e($sub['class_type']); ?>
              </span>
            </div>

            <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
              <?php echo e($sub['subject_name']); ?>
            </h4>

            <div style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 12px; flex: 1;">
              <div><i class="fas fa-user-tie" style="color: #64748b; width: 16px;"></i> Faculty: <?php echo e($sub['faculty_name']); ?></div>
              <div><i class="fas fa-door-open" style="color: #64748b; width: 16px;"></i> Room / Lab: <?php echo e($sub['room_no']); ?></div>
            </div>

            <div style="border-top: 1px solid var(--border-light); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: var(--text-light);">
              <span>Credits: 4.0</span>
              <span style="color: #059669; font-weight: 600;"><i class="fas fa-check"></i> Enrolled</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
