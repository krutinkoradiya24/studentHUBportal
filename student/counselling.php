<?php
/**
 * Student Hub Portal - Academic Counselling & Mentorship Center
 * 
 * @package    StudentHubPortal
 * @subpackage Student
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        student/counselling.php
 * Role:        Student
 * Access:      Strictly enforced via require_student() in student_header.php
 * Description: Enables students to schedule appointments with designated faculty mentors,
 *              academic advisors, and career counselors.
 */

$pageTitle = 'Mentorship & Counselling';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();
$flash = get_flash();

// Student details
$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT full_name, email, course, year FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$profile = $uStmt->fetch();

// Handle appointment booking POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrfToken)) {
        set_flash('danger', 'Security token mismatch. Please try again.');
        header('Location: /STUDENTPORTAL/student/counselling.php');
        exit;
    }

    if ($action === 'book_session') {
        $counsellor = trim($_POST['counsellor'] ?? '');
        $category   = trim($_POST['category'] ?? 'Academic Guidance');
        $date       = trim($_POST['date'] ?? '');
        $timeSlot   = trim($_POST['time_slot'] ?? '');
        $notes      = trim($_POST['notes'] ?? '');

        set_flash('success', 'Your mentorship appointment with ' . e($counsellor) . ' on ' . e($date) . ' at ' . e($timeSlot) . ' has been booked successfully!');
        header('Location: /STUDENTPORTAL/student/counselling.php');
        exit;
    }
}

// Available Counsellors & Mentors
$counsellors = [
    [
        'name'        => 'Dr. Sarah Jenkins',
        'role'        => 'Senior Academic Advisor & Professor',
        'specialty'   => 'Cloud Architecture, Higher Studies & Thesis Guidance',
        'office'      => 'Block C, Room 302',
        'hours'       => 'Mon & Thu: 02:00 PM - 04:00 PM',
        'email'       => 's.jenkins@studenthub.edu'
    ],
    [
        'name'        => 'Prof. David Miller',
        'role'        => 'Career Placement & Industry Mentor',
        'specialty'   => 'Software Engineering Interviews & Resume Review',
        'office'      => 'Innovation Hub, Desk 12',
        'hours'       => 'Tue & Fri: 11:00 AM - 01:00 PM',
        'email'       => 'd.miller@studenthub.edu'
    ],
    [
        'name'        => 'Dr. Sunita Rao',
        'role'        => 'Campus Wellness & Student Psychologist',
        'specialty'   => 'Stress Management, Mindfulness & Academic Life Balance',
        'office'      => 'Student Care Center, Suite A',
        'hours'       => 'Mon to Fri: 10:00 AM - 02:00 PM',
        'email'       => 's.rao@studenthub.edu'
    ]
];
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Academic Mentorship & Student Counselling</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Schedule one-on-one sessions for academic guidance, career direction, and personal wellness.
    </p>
  </div>
  <div>
    <button onclick="document.getElementById('bookModal').classList.add('active')" class="btn btn-primary">
      <i class="fas fa-calendar-plus"></i> Request Session
    </button>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?php echo e($flash['type']); ?>">
    <i class="fas fa-check-circle"></i>
    <span><?php echo e($flash['message']); ?></span>
  </div>
<?php endif; ?>

<!-- Mentors Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
  <?php foreach ($counsellors as $c): ?>
    <div class="portal-card" style="display: flex; flex-direction: column;">
      <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
        <div style="display: flex; gap: 14px; align-items: center;">
          <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0284c7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; font-weight: 700;">
            <?php echo strtoupper(substr($c['name'], 4, 1)); ?>
          </div>
          <div>
            <h3 style="font-size: 1.05rem; margin: 0; color: var(--text-main);"><?php echo e($c['name']); ?></h3>
            <span style="font-size: 0.8rem; color: #0d9488; font-weight: 600;"><?php echo e($c['role']); ?></span>
          </div>
        </div>
      </div>

      <div class="card-body" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="font-size: 0.86rem; color: #475569; display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
          <div><strong style="color: var(--text-main);">Specialization:</strong> <?php echo e($c['specialty']); ?></div>
          <div><i class="fas fa-map-marker-alt" style="color: #64748b; margin-right: 6px;"></i> <?php echo e($c['office']); ?></div>
          <div><i class="fas fa-clock" style="color: #64748b; margin-right: 6px;"></i> <?php echo e($c['hours']); ?></div>
          <div><i class="fas fa-envelope" style="color: #64748b; margin-right: 6px;"></i> <?php echo e($c['email']); ?></div>
        </div>

        <button onclick="openBookingModal('<?php echo e($c['name']); ?>')" class="btn btn-outline" style="width: 100%;">
          <i class="fas fa-calendar-check"></i> Book with <?php echo explode(' ', $c['name'])[1] ?? 'Mentor'; ?>
        </button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Book Appointment Modal -->
<div class="modal" id="bookModal">
  <div class="modal-content" style="max-width: 500px;">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.15rem; color: var(--text-main);">Schedule Mentorship Appointment</h3>
      <button type="button" class="modal-close" onclick="document.getElementById('bookModal').classList.remove('active')">&times;</button>
    </div>
    <form action="/STUDENTPORTAL/student/counselling.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo e(generate_csrf_token()); ?>">
      <input type="hidden" name="action" value="book_session">

      <div class="modal-body">
        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Select Mentor / Counsellor</label>
          <select name="counsellor" id="mentorSelect" class="form-control" required>
            <?php foreach ($counsellors as $c): ?>
              <option value="<?php echo e($c['name']); ?>"><?php echo e($c['name']); ?> (<?php echo e($c['specialty']); ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Discussion Domain</label>
          <select name="category" class="form-control" required>
            <option value="Academic Guidance">Academic Guidance & Subject Support</option>
            <option value="Career & Placement">Career Directions & Placement Preparation</option>
            <option value="Research & Projects">Capstone Research & Project Mentorship</option>
            <option value="Wellness & Balance">Campus Wellness & Stress Relief</option>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
          <div>
            <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Preferred Date</label>
            <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div>
            <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Time Slot</label>
            <select name="time_slot" class="form-control" required>
              <option value="10:00 AM - 10:45 AM">10:00 AM - 10:45 AM</option>
              <option value="11:30 AM - 12:15 PM">11:30 AM - 12:15 PM</option>
              <option value="02:00 PM - 02:45 PM">02:00 PM - 02:45 PM</option>
              <option value="03:30 PM - 04:15 PM">03:30 PM - 04:15 PM</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Discussion Topic / Questions</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Briefly describe what you would like to discuss..." required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('bookModal').classList.remove('active')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Appointment</button>
      </div>
    </form>
  </div>
</div>

<script>
function openBookingModal(mentorName) {
  const sel = document.getElementById('mentorSelect');
  for (let i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value.includes(mentorName)) {
      sel.selectedIndex = i;
      break;
    }
  }
  document.getElementById('bookModal').classList.add('active');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
