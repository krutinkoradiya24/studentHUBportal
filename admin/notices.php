<?php
/**
 * admin/notices.php - Campus Circulars & Announcements Management
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

// POST: Add, Edit, Delete Notice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/notices.php');
        exit;
    }

    if ($action === 'add_notice') {
        $title      = trim($_POST['title'] ?? '');
        $category   = trim($_POST['category'] ?? 'General');
        $priority   = trim($_POST['priority'] ?? 'Normal');
        $targetRole = trim($_POST['target_role'] ?? 'All');
        $content    = trim($_POST['content'] ?? '');
        $postedBy   = trim($_POST['posted_by'] ?? 'Administration Office');

        if (empty($title) || empty($content)) {
            set_flash('danger', 'Please provide both notice title and announcement content.');
            header('Location: /STUDENTPORTAL/admin/notices.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO notices (title, category, priority, target_role, content, posted_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $category, $priority, $targetRole, $content, $postedBy]);
            set_flash('success', "Notice '{$title}' published to noticeboard!");
        } catch (Throwable $e) {
            set_flash('danger', "Failed to post notice: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/notices.php');
        exit;
    }

    if ($action === 'delete_notice') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM notices WHERE id = ?")->execute([$id]);
            set_flash('success', 'Notice removed from noticeboard.');
        }
        header('Location: /STUDENTPORTAL/admin/notices.php');
        exit;
    }
}

// Fetch all notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY FIELD(priority, 'Urgent', 'Important', 'Normal'), id DESC")->fetchAll();

$pageTitle = 'Notices & Circulars';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Campus Notice Board Management</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Publish academic circulars, exam notifications, and urgent campus alerts.
    </p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addNoticeModal')">
    <i class="fas fa-bullhorn"></i> Post New Notice
  </button>
</div>

<!-- Notices Table -->
<div class="portal-card">
  <div class="card-body">
    <div class="portal-toolbar">
      <div class="toolbar-filters">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="noticeSearchInput" class="search-input" placeholder="Search notices by title or content...">
        </div>
      </div>
      <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
        Total: <strong><?php echo count($notices); ?></strong> published notices
      </div>
    </div>

    <div class="table-responsive">
      <table class="portal-table" id="noticesTable">
        <thead>
          <tr>
            <th>Title & Content</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Audience</th>
            <th>Posted By & Date</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($notices)): ?>
            <tr class="no-filter">
              <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">
                No notices published.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($notices as $n): ?>
              <?php 
                $priColor = ($n['priority'] === 'Urgent') ? '#ef4444' : (($n['priority'] === 'Important') ? '#f59e0b' : '#3b82f6');
                $priBg = ($n['priority'] === 'Urgent') ? '#fef2f2' : (($n['priority'] === 'Important') ? '#fffbeb' : '#eff6ff');
              ?>
              <tr>
                <td>
                  <strong style="color: var(--text-main); font-size: 0.95rem;"><?php echo e($n['title']); ?></strong>
                  <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px; max-width: 480px;">
                    <?php echo nl2br(e($n['content'])); ?>
                  </div>
                </td>
                <td>
                  <span class="badge badge-category"><?php echo e($n['category']); ?></span>
                </td>
                <td>
                  <span style="background: <?php echo $priBg; ?>; color: <?php echo $priColor; ?>; font-weight: 700; padding: 3px 8px; border-radius: 12px; font-size: 0.76rem; border: 1px solid <?php echo $priColor; ?>40;">
                    <?php echo e($n['priority']); ?>
                  </span>
                </td>
                <td style="font-size: 0.82rem; color: var(--text-muted);">
                  <i class="fas fa-users"></i> <?php echo e($n['target_role']); ?>
                </td>
                <td style="font-size: 0.82rem; color: var(--text-muted);">
                  <div><strong><?php echo e($n['posted_by']); ?></strong></div>
                  <div><?php echo date('M d, Y', strtotime($n['created_at'])); ?></div>
                </td>
                <td style="text-align: right;">
                  <form action="/STUDENTPORTAL/admin/notices.php" method="POST" onsubmit="return confirm('Delete this notice?');" style="margin: 0;">
                    <input type="hidden" name="action" value="delete_notice">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <input type="hidden" name="id" value="<?php echo $n['id']; ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fecaca;">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: ADD NOTICE                                             -->
<!-- ============================================================= -->
<div class="modal-overlay" id="addNoticeModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-bullhorn" style="color: #4f46e5;"></i> Post Notice</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('addNoticeModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/notices.php" method="POST">
      <input type="hidden" name="action" value="add_notice">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="noticeTitle">Notice Title *</label>
            <input type="text" id="noticeTitle" name="title" class="form-control" placeholder="e.g. Mid-Semester Examination Schedule" required>
          </div>

          <div class="form-group">
            <label for="noticeCat">Category *</label>
            <select id="noticeCat" name="category" class="form-control" required>
              <option value="Academic">Academic</option>
              <option value="Exam">Exam</option>
              <option value="Event">Event</option>
              <option value="General">General</option>
              <option value="Urgent">Urgent Alert</option>
            </select>
          </div>

          <div class="form-group">
            <label for="noticePri">Priority Level *</label>
            <select id="noticePri" name="priority" class="form-control" required>
              <option value="Normal">Normal</option>
              <option value="Important">Important</option>
              <option value="Urgent">Urgent</option>
            </select>
          </div>

          <div class="form-group">
            <label for="noticeTarget">Target Audience *</label>
            <select id="noticeTarget" name="target_role" class="form-control" required>
              <option value="All">All Users (Students & Staff)</option>
              <option value="Student">Students Only</option>
              <option value="Admin">Administrators Only</option>
            </select>
          </div>

          <div class="form-group">
            <label for="noticeBy">Issuing Authority *</label>
            <input type="text" id="noticeBy" name="posted_by" class="form-control" value="Administration Office" required>
          </div>

          <div class="form-group full-width">
            <label for="noticeContent">Notice Message Content *</label>
            <textarea id="noticeContent" name="content" class="form-control" rows="4" placeholder="Detailed circular announcement..." required></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addNoticeModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publish Notice</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'new') {
    openModal('addNoticeModal');
  }
  initTableSearch('noticeSearchInput', 'noticesTable');
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
