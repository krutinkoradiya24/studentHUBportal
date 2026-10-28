<?php
/**
 * admin/accounts.php - Administrator & User Accounts Management
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 * 
 * Capabilities:
 * - Provision New Administrator Accounts
 * - Inspect All Registered User Accounts (`users` table)
 * - Filter by Role (Admin vs Student)
 * - Enforce Password Hashing (Bcrypt) and Validation Rules
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth_helpers.php';

init_secure_session();
$adminUser = require_admin();
$pdo = get_pdo();

// POST: Add new Admin account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/accounts.php');
        exit;
    }

    if ($action === 'create_admin') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $mobile   = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';

        $res = create_admin_account([
            'full_name' => $fullName,
            'email'     => $email,
            'mobile'    => $mobile,
            'password'  => $password
        ]);

        if ($res['success']) {
            set_flash('success', $res['message']);
        } else {
            set_flash('danger', $res['message']);
        }

        header('Location: /STUDENTPORTAL/admin/accounts.php');
        exit;
    }
}

// Fetch all user accounts
$users = $pdo->query("SELECT id, full_name, email, mobile, role, course, year, created_at FROM users ORDER BY id DESC")->fetchAll();

$pageTitle = 'Admin & User Accounts';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Account & Credential Administration</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Manage system administrators and review authenticated portal credentials.
    </p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addAdminModal')">
    <i class="fas fa-user-shield"></i> Create New Administrator
  </button>
</div>

<!-- Main Table Card -->
<div class="portal-card">
  <div class="card-body">
    <div class="portal-toolbar">
      <div class="toolbar-filters">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="userSearchInput" class="search-input" placeholder="Search by name, email, role...">
        </div>

        <select id="userRoleFilter" class="select-filter" aria-label="Filter users by role">
          <option value="">All Roles</option>
          <option value="Admin">Administrator Only</option>
          <option value="Student">Student Only</option>
        </select>
      </div>

      <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
        Total: <strong><?php echo count($users); ?></strong> registered accounts
      </div>
    </div>

    <div class="table-responsive">
      <table class="portal-table" id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Account Name</th>
            <th>Email Address</th>
            <th>Mobile</th>
            <th>Role</th>
            <th>Department / Course</th>
            <th>Registered Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td style="font-weight: 700; color: var(--text-muted);">
                #<?php echo $u['id']; ?>
              </td>
              <td>
                <div style="font-weight: 700; color: var(--text-main);">
                  <?php echo e($u['full_name']); ?>
                </div>
              </td>
              <td>
                <a href="mailto:<?php echo e($u['email']); ?>" style="color: #4f46e5; text-decoration: none;">
                  <?php echo e($u['email']); ?>
                </a>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-muted);">
                <?php echo e($u['mobile']); ?>
              </td>
              <td>
                <?php if ($u['role'] === 'Admin'): ?>
                  <span class="badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                    <i class="fas fa-shield-alt"></i> Admin
                  </span>
                <?php else: ?>
                  <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                    <i class="fas fa-user-graduate"></i> Student
                  </span>
                <?php endif; ?>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-muted);">
                <?php echo e($u['course'] ?? 'N/A'); ?>
              </td>
              <td style="font-size: 0.82rem; color: var(--text-muted);">
                <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: CREATE ADMINISTRATOR ACCOUNT                           -->
<!-- ============================================================= -->
<div class="modal-overlay" id="addAdminModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-user-shield" style="color: #4f46e5;"></i> Create Administrator Account</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('addAdminModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/accounts.php" method="POST">
      <input type="hidden" name="action" value="create_admin">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="adminName">Administrator Full Name *</label>
            <input type="text" id="adminName" name="full_name" class="form-control" placeholder="e.g. Prof. Marcus Reed" required>
          </div>

          <div class="form-group">
            <label for="adminEmail">Official Email Address *</label>
            <input type="email" id="adminEmail" name="email" class="form-control" placeholder="marcus.reed@admin.studenthub.edu" required>
          </div>

          <div class="form-group">
            <label for="adminMobile">Contact Mobile Number *</label>
            <input type="tel" id="adminMobile" name="mobile" class="form-control" placeholder="9876543210" required>
          </div>

          <div class="form-group full-width">
            <label for="adminPassword">Administrator Password * (Min 8 Characters)</label>
            <input type="password" id="adminPassword" name="password" class="form-control" placeholder="••••••••" minlength="8" required>
            <small style="color: var(--text-muted); font-size: 0.78rem;">
              Password will be securely hashed with Bcrypt before persistence in MySQL.
            </small>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addAdminModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Provision Admin Account</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'new') {
    openModal('addAdminModal');
  }

  initTableSearch('userSearchInput', 'usersTable');
  initTableFilter('userRoleFilter', 'usersTable', 4);
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
