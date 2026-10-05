
let allFaqs = [];
let filteredFaqs = [];
let currentFaqPage = 1;
const faqsPerPage = 5;

async function initFAQsData() {
    const container = document.getElementById('faqContainer');
    if (!container) return;
    
    showLoading('faqContainer', 'Loading FAQs...');
    const data = await fetchFAQs();
    
    if (!data) {
        showError('faqContainer', initFAQsData);
        return;
    }
    
    allFaqs = data;
    filteredFaqs = [...allFaqs];
    setupFaqListeners();
    renderFaqPage();
}

function setupFaqListeners() {
    const search = document.getElementById('faqSearch');
    const catFilter = document.getElementById('faqCategoryFilter');
    
    const applyFilters = () => {
        let res = [...allFaqs];
        if (search && search.value) {
            const q = search.value.toLowerCase();
            res = res.filter(f => f.question.toLowerCase().includes(q) || f.answer.toLowerCase().includes(q));
        }
        if (catFilter && catFilter.value) {
            res = res.filter(f => f.category === catFilter.value);
        }
        filteredFaqs = res;
        currentFaqPage = 1;
        renderFaqPage();
    };

    if(search) search.addEventListener('input', applyFilters);
    if(catFilter) catFilter.addEventListener('change', applyFilters);
}

function renderFaqPage() {
    const container = document.getElementById('faqContainer');
    const info = document.getElementById('faqInfo');
    if (!container) return;
    
    if (filteredFaqs.length === 0) {
        showEmptyMessage('faqContainer');
        if(info) info.textContent = 'Showing 0 records';
        document.getElementById('faqPagination').innerHTML = '';
        return;
    }
    
    const paginated = paginate(filteredFaqs, currentFaqPage, faqsPerPage);
    
    let html = '<div class="faq-list" style="margin-top: 20px; display: flex; flex-direction: column; gap: 15px;">';
    paginated.forEach((f, idx) => {
        html += `<div class="faq-item card" style="padding: 15px; cursor: pointer;">
            <div class="faq-question" style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; color: var(--primary);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                ${escapeHTML(f.question)}
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer" style="display: none; margin-top: 10px; color: var(--text-muted); padding-top: 10px; border-top: 1px solid var(--border-color);">
                ${escapeHTML(f.answer)}
                <div style="margin-top: 5px; font-size: 0.85rem; color: var(--secondary);">Category: ${escapeHTML(f.category)}</div>
            </div>
        </div>`;
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    const start = (currentFaqPage - 1) * faqsPerPage + 1;
    const end = Math.min(start + faqsPerPage - 1, filteredFaqs.length);
    if(info) info.textContent = `Showing ${start}-${end} of ${filteredFaqs.length} FAQs`;
    
    renderPagination(filteredFaqs.length, faqsPerPage, currentFaqPage, 'faqPagination', (newPage) => {
        currentFaqPage = newPage;
        renderFaqPage();
    });
}
