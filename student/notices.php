<?php
/**
 * student/notices.php - Student Campus Notice Board & Circulars
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 */

$pageTitle = 'Campus Notices';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

// Fetch notices for students & all
$notices = $pdo->query("
    SELECT * FROM notices 
    WHERE target_role IN ('All', 'Student') 
    ORDER BY FIELD(priority, 'Urgent', 'Important', 'Normal'), id DESC
")->fetchAll();
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Official Campus Notices</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Official administrative circulars, examination schedules, and student advisories.
    </p>
  </div>
</div>

<!-- Notice Cards List -->
<?php if (empty($notices)): ?>
  <div class="portal-card">
    <div class="card-body" style="text-align: center; padding: 40px; color: var(--text-muted);">
      No circulars posted at this time.
    </div>
  </div>
<?php else: ?>
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <?php foreach ($notices as $n): ?>
      <?php 
        $priColor = ($n['priority'] === 'Urgent') ? '#ef4444' : (($n['priority'] === 'Important') ? '#f59e0b' : '#0d9488');
        $priBg = ($n['priority'] === 'Urgent') ? '#fef2f2' : (($n['priority'] === 'Important') ? '#fffbeb' : '#f0fdfa');
      ?>
      <div class="portal-card" style="border-left: 5px solid <?php echo $priColor; ?>;">
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="badge badge-category"><?php echo e($n['category']); ?></span>
              <span style="background: <?php echo $priBg; ?>; color: <?php echo $priColor; ?>; font-weight: 700; font-size: 0.76rem; padding: 3px 10px; border-radius: 12px; border: 1px solid <?php echo $priColor; ?>40;">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($n['priority']); ?> Priority
              </span>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
              <i class="far fa-clock"></i> Posted on <?php echo date('M d, Y', strtotime($n['created_at'])); ?>
            </div>
          </div>

          <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">
            <?php echo e($n['title']); ?>
          </h3>

          <div style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 14px;">
            <?php echo nl2br(e($n['content'])); ?>
          </div>

          <div style="font-size: 0.78rem; color: var(--text-light); border-top: 1px solid var(--border-light); padding-top: 10px; display: flex; justify-content: space-between;">
            <span>Issued by: <strong><?php echo e($n['posted_by']); ?></strong></span>
            <span>Target: <?php echo e($n['target_role']); ?> Audience</span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
