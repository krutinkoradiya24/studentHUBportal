<?php
/**
 * Student Hub Portal - Examination Results & Grade Sheet
 * 
 * @package    StudentHubPortal
 * @subpackage Student
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        student/results.php
 * Role:        Student
 * Access:      Strictly enforced via require_student() in student_header.php
 * Description: Displays semester grade transcripts, SGPA/CGPA summaries, credit tallies,
 *              and printable academic marksheets for enrolled students.
 */

$pageTitle = 'Examination Results';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();

// Student details
$userId = (int)$studentUser['id'];
$uStmt = $pdo->prepare("SELECT full_name, email, course, year FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$userId]);
$profile = $uStmt->fetch();

$stStmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
$stStmt->execute([$profile['email']]);
$studRec = $stStmt->fetch();
$rollNo = $studRec['student_id'] ?? '22CS034';

$selectedSem = $_GET['semester'] ?? '5th Semester';

// Academic performance records across semesters
$semesterData = [
    '5th Semester' => [
        'sgpa' => 8.85,
        'cgpa' => 8.62,
        'credits' => 24,
        'status' => 'Distinction',
        'subjects' => [
            ['code' => 'CS501', 'name' => 'Database Management Systems', 'credits' => 4, 'grade' => 'A+', 'points' => 9],
            ['code' => 'CS502', 'name' => 'Design & Analysis of Algorithms', 'credits' => 4, 'grade' => 'A', 'points' => 8],
            ['code' => 'CS503', 'name' => 'Web Technology & Frameworks', 'credits' => 4, 'grade' => 'O', 'points' => 10],
            ['code' => 'CS504', 'name' => 'Theory of Computation', 'credits' => 3, 'grade' => 'A', 'points' => 8],
            ['code' => 'CS505', 'name' => 'Cyber Security Essentials', 'credits' => 3, 'grade' => 'A+', 'points' => 9],
            ['code' => 'CS501P', 'name' => 'DBMS Practical Lab', 'credits' => 2, 'grade' => 'O', 'points' => 10],
            ['code' => 'CS503P', 'name' => 'Web Tech Practical Lab', 'credits' => 2, 'grade' => 'O', 'points' => 10],
            ['code' => 'CS508', 'name' => 'Mini Project Phase-1', 'credits' => 2, 'grade' => 'A+', 'points' => 9]
        ]
    ],
    '4th Semester' => [
        'sgpa' => 8.50,
        'cgpa' => 8.56,
        'credits' => 24,
        'status' => 'Distinction',
        'subjects' => [
            ['code' => 'CS401', 'name' => 'Operating Systems & Concurrency', 'credits' => 4, 'grade' => 'A', 'points' => 8],
            ['code' => 'CS402', 'name' => 'Computer Networks & Protocols', 'credits' => 4, 'grade' => 'A+', 'points' => 9],
            ['code' => 'CS403', 'name' => 'Microprocessors & Embedded Systems', 'credits' => 4, 'grade' => 'B+', 'points' => 7],
            ['code' => 'CS404', 'name' => 'Object-Oriented Programming (Java)', 'credits' => 4, 'grade' => 'O', 'points' => 10],
            ['code' => 'CS405', 'name' => 'Discrete Mathematics', 'credits' => 4, 'grade' => 'A', 'points' => 8],
            ['code' => 'CS401P', 'name' => 'Operating Systems Lab', 'credits' => 2, 'grade' => 'A+', 'points' => 9],
            ['code' => 'CS404P', 'name' => 'Java Programming Lab', 'credits' => 2, 'grade' => 'O', 'points' => 10]
        ]
    ]
];

$activeData = $semesterData[$selectedSem] ?? $semesterData['5th Semester'];
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">Examination Results & Grade Transcript</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Verified university semester marksheets and cumulative GPA records.
    </p>
  </div>
  <div>
    <button onclick="window.print()" class="btn btn-outline">
      <i class="fas fa-print"></i> Print Transcript
    </button>
  </div>
</div>

<!-- Semester Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
  <?php foreach (array_keys($semesterData) as $sem): ?>
    <a href="/STUDENTPORTAL/student/results.php?semester=<?php echo urlencode($sem); ?>"
       class="btn <?php echo ($selectedSem === $sem) ? 'btn-primary' : 'btn-outline'; ?>"
       style="font-size: 0.85rem; padding: 7px 16px;">
      <i class="fas fa-award"></i> <?php echo e($sem); ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- GPA & Performance Metric Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
  <div class="stat-card">
    <div class="stat-icon teal">
      <i class="fas fa-star"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo number_format($activeData['sgpa'], 2); ?></h3>
      <p>SGPA (<?php echo e($selectedSem); ?>)</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon indigo">
      <i class="fas fa-chart-line"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo number_format($activeData['cgpa'], 2); ?></h3>
      <p>Cumulative CGPA</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <i class="fas fa-medal"></i>
    </div>
    <div class="stat-info">
      <h3><?php echo $activeData['credits']; ?></h3>
      <p>Credits Completed</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon teal">
      <i class="fas fa-check-double"></i>
    </div>
    <div class="stat-info">
      <h3 style="font-size: 1.25rem; color: #16a34a;"><?php echo e($activeData['status']); ?></h3>
      <p>Academic Classification</p>
    </div>
  </div>
</div>

<!-- Detailed Marksheet Table -->
<div class="portal-card">
  <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
    <div class="card-header-title">
      <i class="fas fa-file-invoice" style="color: #0d9488;"></i>
      <h3><?php echo e($selectedSem); ?> Official Grade Report</h3>
    </div>
    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700;">
      <i class="fas fa-check"></i> Examination Passed
    </span>
  </div>

  <div class="card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 120px;">Code</th>
            <th>Course / Subject Title</th>
            <th style="text-align: center; width: 100px;">Credits</th>
            <th style="text-align: center; width: 110px;">Grade Point</th>
            <th style="text-align: center; width: 110px;">Letter Grade</th>
            <th style="text-align: right; width: 120px;">Result</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($activeData['subjects'] as $sub): ?>
            <tr>
              <td>
                <span style="font-family: monospace; font-weight: 700; color: #0d9488; background: #f0fdfa; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                  <?php echo e($sub['code']); ?>
                </span>
              </td>
              <td style="font-weight: 600; color: var(--text-main);">
                <?php echo e($sub['name']); ?>
              </td>
              <td style="text-align: center; font-weight: 600;">
                <?php echo e($sub['credits']); ?>
              </td>
              <td style="text-align: center; font-weight: 700; color: #0284c7;">
                <?php echo e($sub['points']); ?> / 10
              </td>
              <td style="text-align: center;">
                <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 800; font-size: 0.9rem;">
                  <?php echo e($sub['grade']); ?>
                </span>
              </td>
              <td style="text-align: right;">
                <span class="badge" style="background: #dcfce7; color: #166534; font-weight: 700;">
                  PASS
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background: #f8fafc; font-weight: 700;">
            <td colspan="2" style="text-align: right;">Semester Total / Weighted Average:</td>
            <td style="text-align: center;"><?php echo $activeData['credits']; ?> Credits</td>
            <td style="text-align: center; color: #0d9488; font-size: 1rem;"><?php echo number_format($activeData['sgpa'], 2); ?> SGPA</td>
            <td colspan="2" style="text-align: right; color: #15803d;">Overall Status: PASS</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
