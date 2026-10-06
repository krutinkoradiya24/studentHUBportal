import { fetchData } from './api.js';
import { displayMessage, clearContainer } from './utils.js';
import { paginate, renderPagination } from './pagination.js';

let allFaqs = [];
let filteredFaqs = [];
let currentPage = 1;
const itemsPerPage = 5;

export async function initFaqs() {
    const container = document.getElementById('faqContainer');
    if (!container) return;
    
    displayMessage('faqContainer', 'Loading FAQs...');
    
    try {
        allFaqs = await fetchData('../data/faqs.json');
        filteredFaqs = [...allFaqs];
        setupEventListeners();
        updateView();
    } catch (error) {
        displayMessage('faqContainer', 'Unable to load FAQs. Please try again.', true);
    }
}

function setupEventListeners() {
    const searchInput = document.getElementById('faqSearch');
    const categoryFilter = document.getElementById('faqCategoryFilter');
    
    // Create sort select element dynamically as it wasn't in original HTML
    const controlsContainer = document.querySelector('#faqSearch').parentElement;
    if (controlsContainer && !document.getElementById('faqSort')) {
        const sortSelect = document.createElement('select');
        sortSelect.id = 'faqSort';
        sortSelect.style = "padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);";
        sortSelect.innerHTML = `
            <option value="">Sort By</option>
            <option value="question-asc">Question (A-Z)</option>
            <option value="question-desc">Question (Z-A)</option>
            <option value="category-asc">Category (A-Z)</option>
            <option value="category-desc">Category (Z-A)</option>
        `;
        controlsContainer.appendChild(sortSelect);
        sortSelect.addEventListener('change', handleFilterChange);
    }
    
    if (searchInput) searchInput.addEventListener('input', handleFilterChange);
    if (categoryFilter) categoryFilter.addEventListener('change', handleFilterChange);
}

function handleFilterChange() {
    const searchVal = document.getElementById('faqSearch')?.value.toLowerCase() || '';
    const categoryVal = document.getElementById('faqCategoryFilter')?.value || '';
    const sortVal = document.getElementById('faqSort')?.value || '';
    
    filteredFaqs = allFaqs.filter(faq => {
        const matchesSearch = faq.question.toLowerCase().includes(searchVal) || 
                              faq.answer.toLowerCase().includes(searchVal);
        const matchesCategory = categoryVal === '' || faq.category === categoryVal;
        return matchesSearch && matchesCategory;
    });
    
    if (sortVal === 'question-asc') {
        filteredFaqs.sort((a, b) => a.question.localeCompare(b.question));
    } else if (sortVal === 'question-desc') {
        filteredFaqs.sort((a, b) => b.question.localeCompare(a.question));
    } else if (sortVal === 'category-asc') {
        filteredFaqs.sort((a, b) => a.category.localeCompare(b.category));
    } else if (sortVal === 'category-desc') {
        filteredFaqs.sort((a, b) => b.category.localeCompare(a.category));
    }
    
    currentPage = 1;
    updateView();
}

function updateView() {
    renderFaqs();
    renderPagination('faqPagination', filteredFaqs.length, itemsPerPage, currentPage, (newPage) => {
        currentPage = newPage;
        updateView();
    });
    
    const info = document.getElementById('faqInfo');
    if (info) {
        info.textContent = `Showing ${filteredFaqs.length} FAQs`;
    }
}

function renderFaqs() {
    const container = document.getElementById('faqContainer');
    clearContainer('faqContainer');
    
    const paginatedFaqs = paginate(filteredFaqs, itemsPerPage, currentPage);
    
    if (paginatedFaqs.length === 0) {
        displayMessage('faqContainer', 'No FAQs found matching your criteria.');
        return;
    }
    
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.flexDirection = 'column';
    wrapper.style.gap = '10px';
    
    paginatedFaqs.forEach((faq, index) => {
        const faqItem = document.createElement('div');
        faqItem.style.border = '1px solid #e0e0e0';
        faqItem.style.borderRadius = '8px';
        faqItem.style.overflow = 'hidden';
        
        const questionHeader = document.createElement('div');
        questionHeader.style.padding = '15px';
        questionHeader.style.background = '#f9f9f9';
        questionHeader.style.cursor = 'pointer';
        questionHeader.style.display = 'flex';
        questionHeader.style.justifyContent = 'space-between';
        questionHeader.style.alignItems = 'center';
        questionHeader.style.fontWeight = 'bold';
        
        const questionText = document.createElement('span');
        questionText.textContent = faq.question;
        
        const icon = document.createElement('span');
        icon.innerHTML = '<i class="fas fa-chevron-down"></i>';
        
        questionHeader.appendChild(questionText);
        questionHeader.appendChild(icon);
        
        const answerBody = document.createElement('div');
        answerBody.style.padding = '15px';
        answerBody.style.background = '#fff';
        answerBody.style.borderTop = '1px solid #e0e0e0';
        answerBody.style.display = 'none';
        
        const answerText = document.createElement('p');
        answerText.style.margin = '0 0 10px 0';
        answerText.textContent = faq.answer;
        
        const categoryTag = document.createElement('span');
        categoryTag.style.background = '#e3f2fd';
        categoryTag.style.padding = '3px 8px';
        categoryTag.style.borderRadius = '12px';
        categoryTag.style.fontSize = '0.8rem';
        categoryTag.style.color = '#1565c0';
        categoryTag.textContent = faq.category;
        
        answerBody.appendChild(answerText);
        answerBody.appendChild(categoryTag);
        
        questionHeader.addEventListener('click', () => {
            const isVisible = answerBody.style.display === 'block';
            answerBody.style.display = isVisible ? 'none' : 'block';
            icon.innerHTML = isVisible ? '<i class="fas fa-chevron-down"></i>' : '<i class="fas fa-chevron-up"></i>';
        });
        
        faqItem.appendChild(questionHeader);
        faqItem.appendChild(answerBody);
        wrapper.appendChild(faqItem);
    });
    
    container.appendChild(wrapper);
}
