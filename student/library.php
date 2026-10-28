<?php
/**
 * Student Hub Portal - Campus E-Library & Digital Resource Center
 * 
 * @package    StudentHubPortal
 * @subpackage Student
 * @author     Student Hub Development Team
 * @version    2.0.0
 * 
 * File:        student/library.php
 * Role:        Student
 * Access:      Strictly enforced via require_student() in student_header.php
 * Description: Digital repository offering academic textbooks, research papers, e-books,
 *              and course reference material with live keyword search.
 */

$pageTitle = 'Digital E-Library';
require_once dirname(__DIR__) . '/includes/student_header.php';

$pdo = get_pdo();
$flash = get_flash();

// Digital library catalog
$books = [
    [
        'id'          => 1,
        'title'       => 'Introduction to Algorithms (4th Edition)',
        'author'      => 'Thomas H. Cormen, Charles E. Leiserson',
        'category'    => 'Computer Science',
        'isbn'        => '978-0262046305',
        'copies'      => 'Available Online',
        'pages'       => '1312 Pages',
        'color'       => '#3b82f6'
    ],
    [
        'id'          => 2,
        'title'       => 'Operating System Concepts (10th Edition)',
        'author'      => 'Abraham Silberschatz, Peter B. Galvin',
        'category'    => 'Operating Systems',
        'isbn'        => '978-1119456339',
        'copies'      => 'Available Online',
        'pages'       => '1008 Pages',
        'color'       => '#0d9488'
    ],
    [
        'id'          => 3,
        'title'       => 'Designing Data-Intensive Applications',
        'author'      => 'Martin Kleppmann',
        'category'    => 'Database Systems',
        'isbn'        => '978-1449373320',
        'copies'      => 'Available Online',
        'pages'       => '616 Pages',
        'color'       => '#8b5cf6'
    ],
    [
        'id'          => 4,
        'title'       => 'Computer Networking: A Top-Down Approach',
        'author'      => 'James F. Kurose, Keith W. Ross',
        'category'    => 'Networks',
        'isbn'        => '978-0136681557',
        'copies'      => 'Available Online',
        'pages'       => '864 Pages',
        'color'       => '#f59e0b'
    ],
    [
        'id'          => 5,
        'title'       => 'Deep Learning with Python (2nd Edition)',
        'author'      => 'François Chollet',
        'category'    => 'Artificial Intelligence',
        'isbn'        => '978-1617296864',
        'copies'      => 'Available Online',
        'pages'       => '504 Pages',
        'color'       => '#ec4899'
    ],
    [
        'id'          => 6,
        'title'       => 'Cloud Native DevOps with Kubernetes',
        'author'      => 'John Arundel, Justin Domingus',
        'category'    => 'Cloud Computing',
        'isbn'        => '978-1492040767',
        'copies'      => 'Available Online',
        'pages'       => '360 Pages',
        'color'       => '#06b6d4'
    ]
];

// Handle search & category filter
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? 'All';

$filteredBooks = array_filter($books, function($b) use ($search, $category) {
    if ($category !== 'All' && $b['category'] !== $category) {
        return false;
    }
    if ($search !== '') {
        $term = strtolower($search);
        return str_contains(strtolower($b['title']), $term) || str_contains(strtolower($b['author']), $term);
    }
    return true;
});

// Categories list
$categories = ['All', 'Computer Science', 'Operating Systems', 'Database Systems', 'Networks', 'Artificial Intelligence', 'Cloud Computing'];
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--text-main); margin: 0;">E-Library & Digital Resource Repository</h2>
    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 3px;">
      Explore university prescribed textbooks, standard reference volumes, and digital research literature.
    </p>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="portal-card" style="margin-bottom: 24px;">
  <div class="card-body">
    <form action="/STUDENTPORTAL/student/library.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
      <div style="flex: 1; min-width: 250px;">
        <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control" placeholder="Search book title, author, or keyword...">
      </div>
      <div style="min-width: 200px;">
        <select name="category" class="form-control">
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo e($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
              <?php echo e($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Search Catalog
      </button>
      <?php if ($search || $category !== 'All'): ?>
        <a href="/STUDENTPORTAL/student/library.php" class="btn btn-outline">Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Digital Books Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
  <?php if (empty($filteredBooks)): ?>
    <div class="portal-card" style="grid-column: 1 / -1; text-align: center; padding: 50px 20px;">
      <i class="fas fa-book-reader" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px;"></i>
      <h3 style="color: var(--text-main);">No Volumes Found</h3>
      <p style="color: var(--text-muted); font-size: 0.9rem;">No titles match your keyword query. Try searching with a broader title or author name.</p>
    </div>
  <?php else: ?>
    <?php foreach ($filteredBooks as $b): ?>
      <div class="portal-card" style="display: flex; flex-direction: column;">
        <div style="padding: 20px; background: linear-gradient(135deg, <?php echo $b['color']; ?>15, <?php echo $b['color']; ?>05); border-bottom: 1px solid var(--border-color); display: flex; gap: 16px; align-items: center;">
          <div style="width: 54px; height: 72px; background: <?php echo $b['color']; ?>; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem;">
            <i class="fas fa-book"></i>
          </div>
          <div style="flex: 1;">
            <span style="font-size: 0.75rem; font-weight: 700; color: <?php echo $b['color']; ?>; text-transform: uppercase;">
              <?php echo e($b['category']); ?>
            </span>
            <h3 style="font-size: 1.05rem; color: var(--text-main); margin: 4px 0 2px; line-height: 1.3;">
              <?php echo e($b['title']); ?>
            </h3>
            <span style="font-size: 0.82rem; color: var(--text-muted);">
              By <?php echo e($b['author']); ?>
            </span>
          </div>
        </div>

        <div class="card-body" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
          <div style="font-size: 0.84rem; color: #64748b; margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px;">
            <div><i class="fas fa-barcode"></i> ISBN: <span style="font-family: monospace;"><?php echo e($b['isbn']); ?></span></div>
            <div><i class="fas fa-file-alt"></i> Length: <?php echo e($b['pages']); ?></div>
            <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Status: <span style="color: #16a34a; font-weight: 600;"><?php echo e($b['copies']); ?></span></div>
          </div>

          <div>
            <a href="javascript:void(0)" onclick="alert('Digital e-Book viewer opened. You have full read and offline annotation access.')" class="btn btn-primary" style="width: 100%; text-align: center;">
              <i class="fas fa-book-open"></i> Read Digital E-Book
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/student_footer.php'; ?>
