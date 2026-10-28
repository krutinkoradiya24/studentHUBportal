<?php
/**
 * Student Hub Portal - Academic Assignments Management
 * 
 * @package    StudentHubPortal
 * @subpackage Student
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        student/assignments.php
 * Role:        Student
 * Access:      Strictly enforced via require_student() in student_header.php
 * Description: Enables enrolled students to track course assignments, view submission
 *              deadlines, upload assignment work, and review evaluated marks.
 */

$pageTitle = 'My Assignments';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();
$flash = get_flash();

// Student details
$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT course, year FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$userProf = $uStmt->fetch();
$studCourse = $userProf['course'] ?? 'Computer Science';

// Default academic assignments for demo & course tracking
$defaultAssignments = [
    [
        'id'          => 1,
        'subject'     => 'Cloud Computing & DevOps (CS601)',
        'title'       => 'Kubernetes Cluster & Helm Chart Deployment',
        'due_date'    => '2026-11-08',
        'max_marks'   => 50,
        'status'      => 'Pending',
        'instructions'=> 'Configure a 3-node Minikube cluster and deploy a containerized multi-tier web application using Helm charts.',
        'faculty'     => 'Dr. Sarah Jenkins'
    ],
    [
        'id'          => 2,
        'subject'     => 'Advanced Database Systems (CS602)',
        'title'       => 'Distributed Query Optimization & Sharding',
        'due_date'    => '2026-11-04',
        'max_marks'   => 25,
        'status'      => 'Submitted',
        'instructions'=> 'Analyze query execution plans on partitioned MySQL tables and implement horizontal sharding strategies.',
        'faculty'     => 'Prof. David Miller'
    ],
    [
        'id'          => 3,
        'subject'     => 'Machine Learning Foundations (CS604)',
        'title'       => 'Deep Convolutional Neural Networks on CIFAR-10',
        'due_date'    => '2026-11-14',
        'max_marks'   => 100,
        'status'      => 'Pending',
        'instructions'=> 'Train a ResNet architecture with data augmentations to achieve over 92% validation accuracy on CIFAR-10.',
        'faculty'     => 'Dr. Rajesh Patel'
    ],
    [
        'id'          => 4,
        'subject'     => 'Cyber Security & Network Defense (CS606)',
        'title'       => 'Wireshark Packet Analysis & Intrusion Detection',
        'due_date'    => '2026-10-24',
        'max_marks'   => 30,
        'status'      => 'Evaluated',
        'marks'       => 28,
        'instructions'=> 'Inspect pcap packet captures to identify SYN flood attacks and ARP spoofing attempts.',
        'faculty'     => 'Prof. Mark Vance'
    ]
];

// Handle assignment submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/student/assignments.php');
        exit;
    }

    if ($action === 'submit_assignment') {
        $assignmentTitle = trim($_POST['assignment_title'] ?? '');
        $submissionText  = trim($_POST['submission_notes'] ?? '');

        set_flash('success', 'Assignment "' . e($assignmentTitle) . '" submitted successfully to your faculty!');
        header('Location: /STUDENTPORTAL/student/assignments.php');
        exit;
    }
}

$filterStatus = $_GET['status'] ?? 'All';
$filteredAssignments = array_filter($defaultAssignments, function($item) use ($filterStatus) {
    if ($filterStatus === 'All') return true;
    return strtolower($item['status']) === strtolower($filterStatus);
});
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Course Assignments & Lab Work</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Active academic assignments for <strong><?php echo e($studCourse); ?></strong> &bull; 6th Semester
    </p>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?php echo e($flash['type']); ?>">
    <i class="fas fa-check-circle"></i>
    <span><?php echo e($flash['message']); ?></span>
  </div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
  <?php foreach (['All', 'Pending', 'Submitted', 'Evaluated'] as $st): ?>
    <a href="/STUDENTPORTAL/student/assignments.php?status=<?php echo $st; ?>"
       class="btn <?php echo ($filterStatus === $st) ? 'btn-primary' : 'btn-outline'; ?>"
       style="font-size: 0.84rem; padding: 6px 14px;">
      <?php echo $st; ?> Assignments
    </a>
  <?php endforeach; ?>
</div>

<!-- Assignments Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
  <?php if (empty($filteredAssignments)): ?>
    <div class="portal-card" style="grid-column: 1 / -1; text-align: center; padding: 50px 20px;">
      <i class="fas fa-tasks" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px;"></i>
      <h3 style="color: var(--text-main);">No Assignments Found</h3>
      <p style="color: var(--text-muted); font-size: 0.9rem;">There are no assignments matching the "<?php echo e($filterStatus); ?>" filter.</p>
    </div>
  <?php else: ?>
    <?php foreach ($filteredAssignments as $as): ?>
      <div class="portal-card" style="display: flex; flex-direction: column;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
          <div>
            <span style="font-size: 0.78rem; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 0.5px;">
              <?php echo e($as['subject']); ?>
            </span>
            <h3 style="font-size: 1.05rem; margin-top: 4px; color: var(--text-main);">
              <?php echo e($as['title']); ?>
            </h3>
          </div>
          <?php
            $badgeColor = '#e2e8f0';
            $badgeText = '#334155';
            if ($as['status'] === 'Pending') { $badgeColor = '#fef3c7'; $badgeText = '#92400e'; }
            elseif ($as['status'] === 'Submitted') { $badgeColor = '#e0f2fe'; $badgeText = '#0369a1'; }
            elseif ($as['status'] === 'Evaluated') { $badgeColor = '#dcfce7'; $badgeText = '#15803d'; }
          ?>
          <span class="badge" style="background: <?php echo $badgeColor; ?>; color: <?php echo $badgeText; ?>; white-space: nowrap;">
            <?php echo e($as['status']); ?>
          </span>
        </div>

        <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
          <p style="font-size: 0.88rem; color: #4b5563; line-height: 1.5; margin-bottom: 16px;">
            <?php echo e($as['instructions']); ?>
          </p>

          <div style="background: #f8fafc; border-radius: var(--radius-sm); padding: 12px; margin-bottom: 16px; font-size: 0.84rem; display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--text-muted);"><i class="fas fa-calendar-alt"></i> Deadline:</span>
              <strong style="color: #dc2626;"><?php echo date('M d, Y', strtotime($as['due_date'])); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--text-muted);"><i class="fas fa-user-tie"></i> Faculty:</span>
              <span><?php echo e($as['faculty']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--text-muted);"><i class="fas fa-award"></i> Total Weightage:</span>
              <strong><?php echo e($as['max_marks']); ?> Marks</strong>
            </div>
            <?php if (isset($as['marks'])): ?>
              <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 6px; margin-top: 4px;">
                <span style="color: #15803d; font-weight: 700;"><i class="fas fa-check-double"></i> Awarded Score:</span>
                <strong style="color: #15803d;"><?php echo e($as['marks']); ?> / <?php echo e($as['max_marks']); ?></strong>
              </div>
            <?php endif; ?>
          </div>

          <div style="margin-top: auto;">
            <?php if ($as['status'] === 'Pending'): ?>
              <button onclick="openSubmitModal('<?php echo e($as['title']); ?>', <?php echo $as['id']; ?>)" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-upload"></i> Submit Solution
              </button>
            <?php elseif ($as['status'] === 'Submitted'): ?>
              <button class="btn btn-outline" style="width: 100%; cursor: default;" disabled>
                <i class="fas fa-check"></i> Work Submitted - Awaiting Review
              </button>
            <?php else: ?>
              <button class="btn btn-outline" style="width: 100%; cursor: default;" disabled>
                <i class="fas fa-star" style="color: #eab308;"></i> Graded & Completed
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Submit Assignment Modal -->
<div class="modal" id="submitModal">
  <div class="modal-content" style="max-width: 520px;">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.15rem; color: var(--text-main);">Submit Assignment Solution</h3>
      <button type="button" class="modal-close" onclick="closeSubmitModal()">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/student/assignments.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo e(generate_csrf_token()); ?>">
      <input type="hidden" name="action" value="submit_assignment">
      <input type="hidden" name="assignment_title" id="submitAssignmentTitle">

      <div class="modal-body">
        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Assignment Title</label>
          <input type="text" id="displayAssignmentTitle" class="form-control" readonly style="background: #f1f5f9; font-weight: 600;">
        </div>

        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">GitHub Repository / Project URL</label>
          <input type="url" name="repo_url" class="form-control" placeholder="https://github.com/username/project" required>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Submission Notes / Summary</label>
          <textarea name="submission_notes" class="form-control" rows="4" placeholder="Brief explanation of your implementation and test results..." required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeSubmitModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Work</button>
      </div>
    </form>
  </div>
</div>

<script>
function openSubmitModal(title, id) {
  document.getElementById('submitAssignmentTitle').value = title;
  document.getElementById('displayAssignmentTitle').value = title;
  document.getElementById('submitModal').classList.add('active');
}
function closeSubmitModal() {
  document.getElementById('submitModal').classList.remove('active');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
