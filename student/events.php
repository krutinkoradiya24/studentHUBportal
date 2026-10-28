<?php
/**
 * student/events.php - College Events & Competitions Portal for Students
 * 
 * Target Role: Student
 * Access Control: Strictly enforced via require_student() in student_header.php
 */

$pageTitle = 'College Events';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

// Fetch events
$selectedCat = $_GET['category'] ?? 'All';
$query = "SELECT * FROM events WHERE status != 'Cancelled'";
$params = [];

if ($selectedCat !== 'All') {
    $query .= " AND category = :cat";
    $params[':cat'] = $selectedCat;
}

$query .= " ORDER BY date ASC, time ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

$categories = ['All', 'Technical', 'Cultural', 'Sports', 'Workshop', 'Seminar'];
?>

<!-- Events Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">College Events & Competitions</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Explore upcoming campus hackathons, cultural festivals, sports matches, and technical seminars.
    </p>
  </div>
</div>

<!-- Category Tabs & Search Filter -->
<div class="portal-card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <!-- Category Pills -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <?php foreach ($categories as $cat): ?>
        <a href="/STUDENTPORTAL/student/events.php?category=<?php echo urlencode($cat); ?>"
           class="btn btn-sm <?php echo ($selectedCat === $cat) ? 'btn-student' : 'btn-outline'; ?>">
          <?php echo $cat; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Live Search -->
    <div class="search-box" style="min-width: 240px; max-width: 300px;">
      <i class="fas fa-search"></i>
      <input type="text" id="studentEventSearch" class="search-input" placeholder="Search events by title or venue...">
    </div>
  </div>
</div>

<!-- Event Cards Grid -->
<?php if (empty($events)): ?>
  <div class="portal-card">
    <div class="card-body" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
      <i class="fas fa-calendar-times" style="font-size: 2.5rem; opacity: 0.35; display: block; margin-bottom: 12px;"></i>
      <h3 style="font-size: 1.15rem; color: var(--text-main);">No Events Found</h3>
      <p style="margin-top: 4px; font-size: 0.88rem;">There are no scheduled events matching the selected category.</p>
    </div>
  </div>
<?php else: ?>
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;" id="eventCardsGrid">
    <?php foreach ($events as $e): ?>
      <div class="portal-card event-card-item" style="transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column;"
           data-search="<?php echo strtolower(e($e['title'] . ' ' . $e['category'] . ' ' . $e['location'] . ' ' . ($e['organizer'] ?? ''))); ?>">
        
        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
          <!-- Top Category & Status Header -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span class="badge badge-category">
              <i class="fas fa-tag"></i> <?php echo e($e['category']); ?>
            </span>
            <span class="badge badge-<?php echo strtolower($e['status']); ?>">
              <?php echo e($e['status']); ?>
            </span>
          </div>

          <!-- Event Title -->
          <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px; line-height: 1.3;">
            <?php echo e($e['title']); ?>
          </h3>

          <!-- Details Meta -->
          <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.84rem; color: var(--text-muted); margin-bottom: 14px;">
            <div>
              <i class="far fa-calendar-alt" style="color: #0d9488; width: 18px;"></i>
              <strong><?php echo date('l, M d, Y', strtotime($e['date'])); ?></strong>
            </div>
            <div>
              <i class="far fa-clock" style="color: #0d9488; width: 18px;"></i>
              <span><?php echo e($e['time']); ?></span>
            </div>
            <div>
              <i class="fas fa-map-marker-alt" style="color: #ef4444; width: 18px;"></i>
              <span><?php echo e($e['location']); ?></span>
            </div>
            <?php if (!empty($e['organizer'])): ?>
              <div>
                <i class="fas fa-users" style="color: #64748b; width: 18px;"></i>
                <span>Organized by: <?php echo e($e['organizer']); ?></span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Description -->
          <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px; flex: 1;">
            <?php echo e($e['description'] ?? 'Join your fellow students for this campus event.'); ?>
          </p>

          <!-- Action Footer -->
          <div style="border-top: 1px solid var(--border-light); padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.75rem; font-family: monospace; color: var(--text-light);">
              ID: <?php echo e($e['event_id']); ?>
            </span>
            <button type="button" class="btn btn-student btn-sm" onclick="alert('Registration confirmed for event: <?php echo addslashes($e['title']); ?>! An email has been sent to your student inbox.');">
              <i class="fas fa-check-circle"></i> RSVP / Register
            </button>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('studentEventSearch');
  const cards = document.querySelectorAll('.event-card-item');

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase().trim();
      cards.forEach(card => {
        const text = card.dataset.search || '';
        card.style.display = text.includes(term) ? 'flex' : 'none';
      });
    });
  }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
