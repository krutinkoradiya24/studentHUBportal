export function paginate(data, itemsPerPage, currentPage) {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return data.slice(startIndex, endIndex);
}

export function renderPagination(containerId, totalItems, itemsPerPage, currentPage, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '';
    
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (totalPages <= 1) return; // No pagination needed
    
    const paginationWrapper = document.createElement('div');
    paginationWrapper.style.display = 'flex';
    paginationWrapper.style.justifyContent = 'center';
    paginationWrapper.style.gap = '10px';
    paginationWrapper.style.marginTop = '20px';
    
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = currentPage === 1;
    prevBtn.style.padding = '8px 16px';
    prevBtn.style.cursor = prevBtn.disabled ? 'not-allowed' : 'pointer';
    prevBtn.style.borderRadius = '6px';
    prevBtn.style.border = '1px solid #ccc';
    prevBtn.onclick = () => onPageChange(currentPage - 1);
    
    const pageInfo = document.createElement('span');
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    pageInfo.style.padding = '8px 16px';
    
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.style.padding = '8px 16px';
    nextBtn.style.cursor = nextBtn.disabled ? 'not-allowed' : 'pointer';
    nextBtn.style.borderRadius = '6px';
    nextBtn.style.border = '1px solid #ccc';
    nextBtn.onclick = () => onPageChange(currentPage + 1);
    
    paginationWrapper.appendChild(prevBtn);
    paginationWrapper.appendChild(pageInfo);
    paginationWrapper.appendChild(nextBtn);
    
    container.appendChild(paginationWrapper);
}
