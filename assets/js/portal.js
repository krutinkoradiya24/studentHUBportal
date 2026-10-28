/**
 * assets/js/portal.js - Shared Client UI Behaviors & Interactive Controls
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Sidebar Toggle
  const toggleBtn = document.getElementById('toggleSidebarBtn');
  const sidebar = document.getElementById('portalSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });

    // Close when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992 && sidebar.classList.contains('show')) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
          sidebar.classList.remove('show');
        }
      }
    });
  }

  // 2. Banner Dismissals
  document.querySelectorAll('.portal-banner-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const banner = btn.closest('.portal-banner');
      if (banner) {
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-10px)';
        banner.style.transition = 'all 0.25s ease';
        setTimeout(() => banner.remove(), 250);
      }
    });
  });

  // 3. Global Modal Esc Key Listener
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay').forEach(modal => {
        if (modal.style.display === 'flex') {
          closeModal(modal.id);
        }
      });
    }
  });
});

/**
 * Open Modal Dialog
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

/**
 * Close Modal Dialog
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

/**
 * Live Table Search Filter
 */
function initTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;

  input.addEventListener('input', () => {
    const term = input.value.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr:not(.no-filter)');
    let visibleCount = 0;

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const match = text.includes(term);
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    const emptyRow = table.querySelector('.empty-filter-row');
    if (emptyRow) {
      emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
  });
}

/**
 * Column Select Filter
 */
function initTableFilter(selectId, tableId, cellIndex) {
  const select = document.getElementById(selectId);
  const table = document.getElementById(tableId);
  if (!select || !table) return;

  select.addEventListener('change', () => {
    const val = select.value.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr:not(.no-filter)');
    let visibleCount = 0;

    rows.forEach(row => {
      const cell = row.children[cellIndex];
      if (!cell) return;
      const cellText = cell.textContent.toLowerCase().trim();
      const match = (!val || cellText.includes(val));
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    const emptyRow = table.querySelector('.empty-filter-row');
    if (emptyRow) {
      emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
  });
}
