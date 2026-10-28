<?php
/**
 * admin/events.php - Comprehensive College Events Management Panel
 * 
 * Target Role: Administrator
 * Access Control: Strictly enforced via require_admin() in admin_header.php
 * 
 * Capabilities:
 * - Add New College Events (Technical, Cultural, Sports, Workshop, Seminar)
 * - Edit / Update Existing Events
 * - Delete Events with Confirmation Guard
 * - Category & Status Filtering + Live Search
 */

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/db.php';

init_secure_session();
$adminUser = require_admin();
$pdo = get_pdo();

// -------------------------------------------------------------
// POST HANDLERS: ADD, UPDATE, DELETE EVENT
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/admin/events.php');
        exit;
    }

    if ($action === 'add_event') {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Technical');
        $date        = trim($_POST['date'] ?? '');
        $time        = trim($_POST['time'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $organizer   = trim($_POST['organizer'] ?? 'Student Council');
        $status      = trim($_POST['status'] ?? 'Upcoming');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($date) || empty($time) || empty($location)) {
            set_flash('danger', 'Please fill in all required event details (Title, Date, Time, and Location).');
            header('Location: /STUDENTPORTAL/admin/events.php');
            exit;
        }

        // Generate unique event identifier
        $eventId = 'EVT-' . strtoupper(substr(uniqid(), -5));

        try {
            $stmt = $pdo->prepare("
                INSERT INTO events (event_id, title, category, date, time, location, organizer, status, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$eventId, $title, $category, $date, $time, $location, $organizer, $status, $description]);

            set_flash('success', "Event '{$title}' ({$eventId}) added successfully!");
        } catch (Throwable $e) {
            set_flash('danger', "Database error adding event: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/events.php');
        exit;
    }

    if ($action === 'update_event') {
        $eventId     = trim($_POST['event_id'] ?? '');
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Technical');
        $date        = trim($_POST['date'] ?? '');
        $time        = trim($_POST['time'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $organizer   = trim($_POST['organizer'] ?? 'Student Council');
        $status      = trim($_POST['status'] ?? 'Upcoming');
        $description = trim($_POST['description'] ?? '');

        if (empty($eventId) || empty($title) || empty($date)) {
            set_flash('danger', 'Missing required event fields for update.');
            header('Location: /STUDENTPORTAL/admin/events.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE events
                SET title = ?, category = ?, date = ?, time = ?, location = ?, organizer = ?, status = ?, description = ?, updated_at = NOW()
                WHERE event_id = ?
            ");
            $stmt->execute([$title, $category, $date, $time, $location, $organizer, $status, $description, $eventId]);

            set_flash('success', "Event '{$title}' updated successfully!");
        } catch (Throwable $e) {
            set_flash('danger', "Failed to update event: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/events.php');
        exit;
    }

    if ($action === 'delete_event') {
        $eventId = trim($_POST['event_id'] ?? '');
        if (empty($eventId)) {
            set_flash('danger', 'Invalid event identifier for deletion.');
            header('Location: /STUDENTPORTAL/admin/events.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            set_flash('success', "Event '{$eventId}' permanently deleted.");
        } catch (Throwable $e) {
            set_flash('danger', "Failed to delete event: " . $e->getMessage());
        }

        header('Location: /STUDENTPORTAL/admin/events.php');
        exit;
    }
}

// Fetch all events sorted by date
$events = $pdo->query("SELECT * FROM events ORDER BY date ASC, time ASC")->fetchAll();

$pageTitle = 'Events Management';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<!-- Header Actions & Metric Badges -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">College Events Directory</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Manage campus technical symposiums, cultural festivals, sports meets, and academic workshops.
    </p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addEventModal')">
    <i class="fas fa-plus-circle"></i> Add New Event
  </button>
</div>

<!-- Main Events Card -->
<div class="portal-card">
  <div class="card-body">
    <!-- Toolbar with Search & Category Filters -->
    <div class="portal-toolbar">
      <div class="toolbar-filters">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="eventSearchInput" class="search-input" placeholder="Search events by title, venue, organizer..." autocomplete="off">
        </div>

        <select id="eventCategoryFilter" class="select-filter" aria-label="Filter events by category">
          <option value="">All Categories</option>
          <option value="Technical">Technical</option>
          <option value="Cultural">Cultural</option>
          <option value="Sports">Sports</option>
          <option value="Workshop">Workshop</option>
          <option value="Seminar">Seminar</option>
        </select>

        <select id="eventStatusFilter" class="select-filter" aria-label="Filter events by status">
          <option value="">All Statuses</option>
          <option value="Upcoming">Upcoming</option>
          <option value="Ongoing">Ongoing</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>

      <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
        Total: <strong><?php echo count($events); ?></strong> events listed
      </div>
    </div>

    <!-- Events Data Table -->
    <div class="table-responsive">
      <table class="portal-table" id="eventsTable">
        <thead>
          <tr>
            <th>Event ID</th>
            <th>Title & Location</th>
            <th>Category</th>
            <th>Date & Schedule</th>
            <th>Organizer</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr class="no-filter">
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                <i class="fas fa-calendar-times" style="font-size: 2.2rem; display: block; opacity: 0.3; margin-bottom: 12px;"></i>
                No events currently recorded. Click <strong>"Add New Event"</strong> to publish one.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $e): ?>
              <tr>
                <td>
                  <span style="font-family: monospace; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; font-size: 0.82rem;">
                    <?php echo e($e['event_id']); ?>
                  </span>
                </td>
                <td>
                  <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                    <?php echo e($e['title']); ?>
                  </div>
                  <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> <?php echo e($e['location']); ?>
                  </div>
                  <?php if (!empty($e['description'])): ?>
                    <div style="font-size: 0.78rem; color: var(--text-light); margin-top: 4px; max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <?php echo e($e['description']); ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-category">
                    <?php echo e($e['category']); ?>
                  </span>
                </td>
                <td>
                  <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                    <i class="far fa-calendar-alt" style="color: #4f46e5;"></i> <?php echo date('M d, Y', strtotime($e['date'])); ?>
                  </div>
                  <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    <i class="far fa-clock"></i> <?php echo e($e['time']); ?>
                  </div>
                </td>
                <td style="font-size: 0.85rem; color: var(--text-main);">
                  <?php echo e($e['organizer'] ?? 'Student Council'); ?>
                </td>
                <td>
                  <span class="badge badge-<?php echo strtolower($e['status']); ?>">
                    <?php echo e($e['status']); ?>
                  </span>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                  <button type="button" class="btn btn-outline btn-sm edit-event-btn"
                          data-id="<?php echo e($e['event_id']); ?>"
                          data-title="<?php echo e($e['title']); ?>"
                          data-category="<?php echo e($e['category']); ?>"
                          data-date="<?php echo e($e['date']); ?>"
                          data-time="<?php echo e($e['time']); ?>"
                          data-location="<?php echo e($e['location']); ?>"
                          data-organizer="<?php echo e($e['organizer'] ?? ''); ?>"
                          data-status="<?php echo e($e['status']); ?>"
                          data-desc="<?php echo e($e['description'] ?? ''); ?>">
                    <i class="fas fa-edit" style="color: #4f46e5;"></i> Edit
                  </button>
                  <button type="button" class="btn btn-outline btn-sm delete-event-btn"
                          data-id="<?php echo e($e['event_id']); ?>"
                          data-title="<?php echo e($e['title']); ?>"
                          style="color: #ef4444; border-color: #fecaca;">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Empty search filter row -->
          <tr class="empty-filter-row" style="display: none;">
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
              No events found matching your search and category filter.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: ADD NEW EVENT                                          -->
<!-- ============================================================= -->
<div class="modal-overlay" id="addEventModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-calendar-plus" style="color: #4f46e5;"></i> Add New College Event</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('addEventModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/events.php" method="POST">
      <input type="hidden" name="action" value="add_event">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="eventTitle">Event Title *</label>
            <input type="text" id="eventTitle" name="title" class="form-control" placeholder="e.g. Annual Tech Symposium 2026" required>
          </div>

          <div class="form-group">
            <label for="eventCategory">Category *</label>
            <select id="eventCategory" name="category" class="form-control" required>
              <option value="Technical">Technical</option>
              <option value="Cultural">Cultural</option>
              <option value="Sports">Sports</option>
              <option value="Workshop">Workshop</option>
              <option value="Seminar">Seminar</option>
            </select>
          </div>

          <div class="form-group">
            <label for="eventStatus">Status *</label>
            <select id="eventStatus" name="status" class="form-control" required>
              <option value="Upcoming">Upcoming</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div class="form-group">
            <label for="eventDate">Event Date *</label>
            <input type="date" id="eventDate" name="date" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="eventTime">Time (e.g. 10:00 AM) *</label>
            <input type="text" id="eventTime" name="time" class="form-control" placeholder="e.g. 10:00 AM" required>
          </div>

          <div class="form-group full-width">
            <label for="eventLocation">Venue / Location *</label>
            <input type="text" id="eventLocation" name="location" class="form-control" placeholder="e.g. Main Auditorium / Lab 301" required>
          </div>

          <div class="form-group full-width">
            <label for="eventOrganizer">Organizing Body / Committee</label>
            <input type="text" id="eventOrganizer" name="organizer" class="form-control" placeholder="e.g. Computer Science Club / Sports Wing">
          </div>

          <div class="form-group full-width">
            <label for="eventDesc">Event Description & Agenda</label>
            <textarea id="eventDesc" name="description" class="form-control" rows="3" placeholder="Provide event schedule, registration details, or guidelines..."></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addEventModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Publish Event</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDIT EVENT                                             -->
<!-- ============================================================= -->
<div class="modal-overlay" id="editEventModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3><i class="fas fa-edit" style="color: #4f46e5;"></i> Edit College Event</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('editEventModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/events.php" method="POST">
      <input type="hidden" name="action" value="update_event">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
      <input type="hidden" name="event_id" id="editEventId">

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="editEventTitle">Event Title *</label>
            <input type="text" id="editEventTitle" name="title" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editEventCategory">Category *</label>
            <select id="editEventCategory" name="category" class="form-control" required>
              <option value="Technical">Technical</option>
              <option value="Cultural">Cultural</option>
              <option value="Sports">Sports</option>
              <option value="Workshop">Workshop</option>
              <option value="Seminar">Seminar</option>
            </select>
          </div>

          <div class="form-group">
            <label for="editEventStatus">Status *</label>
            <select id="editEventStatus" name="status" class="form-control" required>
              <option value="Upcoming">Upcoming</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div class="form-group">
            <label for="editEventDate">Date *</label>
            <input type="date" id="editEventDate" name="date" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="editEventTime">Time *</label>
            <input type="text" id="editEventTime" name="time" class="form-control" required>
          </div>

          <div class="form-group full-width">
            <label for="editEventLocation">Venue / Location *</label>
            <input type="text" id="editEventLocation" name="location" class="form-control" required>
          </div>

          <div class="form-group full-width">
            <label for="editEventOrganizer">Organizing Committee</label>
            <input type="text" id="editEventOrganizer" name="organizer" class="form-control">
          </div>

          <div class="form-group full-width">
            <label for="editEventDesc">Description</label>
            <textarea id="editEventDesc" name="description" class="form-control" rows="3"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editEventModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: DELETE CONFIRMATION                                    -->
<!-- ============================================================= -->
<div class="modal-overlay" id="deleteEventModal">
  <div class="modal-container" style="max-width: 440px;">
    <div class="modal-header">
      <h3 style="color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Delete Event</h3>
      <button type="button" class="modal-close-btn" onclick="closeModal('deleteEventModal')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/admin/events.php" method="POST">
      <input type="hidden" name="action" value="delete_event">
      <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
      <input type="hidden" name="event_id" id="deleteEventId">

      <div class="modal-body">
        <p style="font-size: 0.95rem; color: var(--text-main);">
          Are you sure you want to permanently delete event <strong id="deleteEventTitleDisplay" style="color: #ef4444;"></strong>?
        </p>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">
          This action will immediately remove the event from the student portal and cannot be undone.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('deleteEventModal')">Cancel</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Yes, Delete Event</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Check if URL has ?action=new to open Add Modal immediately
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'new') {
    openModal('addEventModal');
  }

  // Setup search and filters
  initTableSearch('eventSearchInput', 'eventsTable');
  initTableFilter('eventCategoryFilter', 'eventsTable', 2);
  initTableFilter('eventStatusFilter', 'eventsTable', 5);

  // Edit Event Button Listeners
  document.querySelectorAll('.edit-event-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editEventId').value = btn.dataset.id;
      document.getElementById('editEventTitle').value = btn.dataset.title;
      document.getElementById('editEventCategory').value = btn.dataset.category;
      document.getElementById('editEventDate').value = btn.dataset.date;
      document.getElementById('editEventTime').value = btn.dataset.time;
      document.getElementById('editEventLocation').value = btn.dataset.location;
      document.getElementById('editEventOrganizer').value = btn.dataset.organizer;
      document.getElementById('editEventStatus').value = btn.dataset.status;
      document.getElementById('editEventDesc').value = btn.dataset.desc;
      openModal('editEventModal');
    });
  });

  // Delete Event Button Listeners
  document.querySelectorAll('.delete-event-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteEventId').value = btn.dataset.id;
      document.getElementById('deleteEventTitleDisplay').textContent = btn.dataset.title + ' (' + btn.dataset.id + ')';
      openModal('deleteEventModal');
    });
  });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
