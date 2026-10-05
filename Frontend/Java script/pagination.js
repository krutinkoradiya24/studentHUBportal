
function paginate(items, page, perPage) {
    const start = (page - 1) * perPage;
    return items.slice(start, start + perPage);
}
function renderPagination(totalItems, perPage, currentPage, containerId, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const totalPages = Math.ceil(totalItems / perPage);
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination-controls" style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">';
    html += `<button class="btn btn-outline" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">Previous</button>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="btn ${i === currentPage ? 'btn-primary' : 'btn-outline'}" ${i === currentPage ? 'aria-current="page"' : ''} data-page="${i}">${i}</button>`;
    }
    html += `<button class="btn btn-outline" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next</button>`;
    html += '</div>';
    
    container.innerHTML = html;
    container.querySelectorAll('button:not([disabled])').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const newPage = parseInt(e.target.getAttribute('data-page'));
            onPageChange(newPage);
        });
    });
}
