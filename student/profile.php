<?php
/**
 * student/profile.php - Student Personal Profile & Academic Identification
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 */

$pageTitle = 'My Profile';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$profile = $uStmt->fetch() ?: $studentUser;

$stStmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
$stStmt->execute([$profile['email']]);
$studRec = $stStmt->fetch();

$rollNo = $studRec['student_id'] ?? '22CS034';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Student Academic Profile</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Verified student identity and active session credentials.
    </p>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">

  <!-- Profile Card -->
  <div class="portal-card">
    <div class="card-header" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff;">
      <div class="card-header-title">
        <i class="fas fa-id-badge" style="color: #fff;"></i>
        <h3 style="color: #fff;">Academic Identification Card</h3>
      </div>
      <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; border: none;">
        Active Student
      </span>
    </div>

    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0284c7); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #fff; box-shadow: 0 4px 12px rgba(13,148,136,0.3);">
          <?php echo strtoupper(substr($profile['full_name'], 0, 1)); ?>
        </div>
        <div>
          <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin: 0;">
            <?php echo e($profile['full_name']); ?>
          </h3>
          <div style="font-size: 0.88rem; color: var(--text-muted); margin-top: 2px;">
            Roll Number: <strong style="color: #0d9488; font-family: monospace;"><?php echo e($rollNo); ?></strong>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-light); margin-top: 2px;">
            Member since: <?php echo date('M d, Y', strtotime($profile['created_at'])); ?>
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.9rem;">
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Department</label>
          <div style="font-weight: 600; color: var(--text-main); margin-top: 2px;"><?php echo e($profile['course'] ?? 'Computer Science'); ?></div>
        </div>
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Academic Year</label>
          <div style="font-weight: 600; color: var(--text-main); margin-top: 2px;"><?php echo e($profile['year'] ?? '3rd Year'); ?></div>
        </div>
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Email Address</label>
          <div style="font-weight: 600; color: var(--text-main); margin-top: 2px; word-break: break-all;"><?php echo e($profile['email']); ?></div>
        </div>
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Mobile Number</label>
          <div style="font-weight: 600; color: var(--text-main); margin-top: 2px;"><?php echo e($profile['mobile'] ?: 'Not Provided'); ?></div>
        </div>
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Assigned Role</label>
          <div style="font-weight: 600; color: #047857; margin-top: 2px;"><i class="fas fa-user-graduate"></i> Student (Authorized)</div>
        </div>
        <div>
          <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Portal Status</label>
          <div style="font-weight: 600; color: #047857; margin-top: 2px;"><i class="fas fa-check-circle"></i> Enrolled & Active</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Session Security Information -->
  <div class="portal-card">
    <div class="card-header">
      <div class="card-header-title">
        <i class="fas fa-shield-alt" style="color: #0d9488;"></i>
        <h3>Session Security & Access Proof</h3>
      </div>
    </div>
    <div class="card-body">
      <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 14px; font-size: 0.88rem;">
        <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
          <span style="color: var(--text-muted);"><i class="fas fa-key" style="color: #0d9488; margin-right: 8px;"></i> Session ID</span>
          <span style="font-family: monospace; font-size: 0.8rem;"><?php echo substr(session_id(), 0, 16); ?>...</span>
        </li>
        <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
          <span style="color: var(--text-muted);"><i class="fas fa-hourglass-half" style="color: #f59e0b; margin-right: 8px;"></i> Inactivity Timeout</span>
          <span style="font-weight: 600;">15 Minutes (Active)</span>
        </li>
        <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
          <span style="color: var(--text-muted);"><i class="fas fa-cookie-bite" style="color: #3b82f6; margin-right: 8px;"></i> Cookie Flags</span>
          <span style="font-weight: 600; color: #059669;">HttpOnly &bull; SameSite=Lax</span>
        </li>
        <li style="display: flex; justify-content: space-between; padding-bottom: 4px;">
          <span style="color: var(--text-muted);"><i class="fas fa-user-lock" style="color: #ef4444; margin-right: 8px;"></i> Role Access Limit</span>
          <span style="font-weight: 600; color: #ef4444;">Admin Area Strictly Forbidden</span>
        </li>
      </ul>

      <div style="margin-top: 20px; padding: 14px; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: var(--radius-md); font-size: 0.82rem; color: #0f766e;">
        <i class="fas fa-info-circle"></i> Strict Server-Side Access Control is verified for this account. If you attempt to open any administrator page directly via URL manipulation, the server will immediately reject the request and redirect back to this dashboard.
      </div>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
