import { fetchData } from './api.js';
import { displayMessage, clearContainer } from './utils.js';
import { paginate, renderPagination } from './pagination.js';

let allEvents = [];
let filteredEvents = [];
let currentPage = 1;
const itemsPerPage = 5;

export async function initEvents() {
    const container = document.getElementById('eventContainer');
    if (!container) return; // Not on events page
    
    displayMessage('eventContainer', 'Loading events...');
    
    try {
        allEvents = await fetchData('../data/events.json');
        filteredEvents = [...allEvents];
        setupEventListeners();
        updateView();
    } catch (error) {
        displayMessage('eventContainer', 'Unable to load events. Please try again.', true);
    }
}

function setupEventListeners() {
    const searchInput = document.getElementById('eventSearch');
    const categoryFilter = document.getElementById('eventCategoryFilter');
    const sortSelect = document.getElementById('eventSort');
    
    if (searchInput) searchInput.addEventListener('input', handleFilterChange);
    if (categoryFilter) categoryFilter.addEventListener('change', handleFilterChange);
    if (sortSelect) sortSelect.addEventListener('change', handleFilterChange);
}

function handleFilterChange() {
    const searchVal = document.getElementById('eventSearch')?.value.toLowerCase() || '';
    const categoryVal = document.getElementById('eventCategoryFilter')?.value || '';
    const sortVal = document.getElementById('eventSort')?.value || '';
    
    filteredEvents = allEvents.filter(event => {
        const matchesSearch = event.title.toLowerCase().includes(searchVal) || 
                              event.location.toLowerCase().includes(searchVal) ||
                              event.category.toLowerCase().includes(searchVal);
        const matchesCategory = categoryVal === '' || event.category === categoryVal;
        return matchesSearch && matchesCategory;
    });
    
    if (sortVal === 'title-asc') {
        filteredEvents.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortVal === 'title-desc') {
        filteredEvents.sort((a, b) => b.title.localeCompare(a.title));
    } else if (sortVal === 'date-asc') {
        filteredEvents.sort((a, b) => new Date(a.date) - new Date(b.date));
    } else if (sortVal === 'date-desc') {
        filteredEvents.sort((a, b) => new Date(b.date) - new Date(a.date));
    }
    
    currentPage = 1; // Reset to first page
    updateView();
}

function updateView() {
    renderEvents();
    renderPagination('eventPagination', filteredEvents.length, itemsPerPage, currentPage, (newPage) => {
        currentPage = newPage;
        updateView();
    });
    
    const info = document.getElementById('eventInfo');
    if (info) {
        info.textContent = `Showing ${filteredEvents.length} events`;
    }
}

function renderEvents() {
    const container = document.getElementById('eventContainer');
    clearContainer('eventContainer');
    
    const paginatedEvents = paginate(filteredEvents, itemsPerPage, currentPage);
    
    if (paginatedEvents.length === 0) {
        displayMessage('eventContainer', 'No events found matching your criteria.');
        return;
    }
    
    const grid = document.createElement('div');
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
    grid.style.gap = '20px';
    
    paginatedEvents.forEach(event => {
        const card = document.createElement('div');
        card.style.border = '1px solid #e0e0e0';
        card.style.borderRadius = '8px';
        card.style.padding = '15px';
        card.style.background = '#fff';
        card.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        
        card.innerHTML = `
            <h3 style="margin-top:0; color: #1565c0;">${event.title}</h3>
            <p><strong>Date:</strong> ${event.date} | <strong>Time:</strong> ${event.time}</p>
            <p><strong>Location:</strong> ${event.location}</p>
            <p><strong>Category:</strong> <span style="background: #e3f2fd; padding: 3px 8px; border-radius: 12px; font-size: 0.85rem;">${event.category}</span></p>
            <p style="color: #666; font-size: 0.9rem;">${event.description}</p>
        `;
        grid.appendChild(card);
    });
    
    container.appendChild(grid);
}
