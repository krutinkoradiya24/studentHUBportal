
let allEvents = [];
let filteredEvents = [];
let currentEventPage = 1;
const eventsPerPage = 6;

async function initEvents() {
    const container = document.getElementById('eventContainer');
    if (!container) return;
    
    showLoading('eventContainer', 'Loading events...');
    const data = await fetchEvents();
    
    if (!data) {
        showError('eventContainer', initEvents);
        return;
    }
    
    allEvents = data;
    filteredEvents = [...allEvents];
    setupEventListeners();
    renderEventPage();
}

function setupEventListeners() {
    const search = document.getElementById('eventSearch');
    const catFilter = document.getElementById('eventCategoryFilter');
    const sortFilter = document.getElementById('eventSort');
    
    const applyFilters = () => {
        let res = [...allEvents];
        if (search && search.value) {
            const q = search.value.toLowerCase();
            res = res.filter(e => e.title.toLowerCase().includes(q) || e.venue.toLowerCase().includes(q) || e.category.toLowerCase().includes(q));
        }
        if (catFilter && catFilter.value) {
            res = res.filter(e => e.category === catFilter.value);
        }
        if (sortFilter && sortFilter.value) {
            const [key, dir] = sortFilter.value.split('-');
            res.sort((a, b) => {
                let valA = a[key]; let valB = b[key];
                if (typeof valA === 'string') { valA = valA.toLowerCase(); valB = valB.toLowerCase(); }
                if (valA < valB) return dir === 'asc' ? -1 : 1;
                if (valA > valB) return dir === 'asc' ? 1 : -1;
                return 0;
            });
        }
        filteredEvents = res;
        currentEventPage = 1;
        renderEventPage();
    };

    if(search) search.addEventListener('input', applyFilters);
    if(catFilter) catFilter.addEventListener('change', applyFilters);
    if(sortFilter) sortFilter.addEventListener('change', applyFilters);
}

function renderEventPage() {
    const container = document.getElementById('eventContainer');
    const info = document.getElementById('eventInfo');
    if (!container) return;
    
    if (filteredEvents.length === 0) {
        showEmptyMessage('eventContainer');
        if(info) info.textContent = 'Showing 0 records';
        document.getElementById('eventPagination').innerHTML = '';
        return;
    }
    
    const paginated = paginate(filteredEvents, currentEventPage, eventsPerPage);
    
    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';
    paginated.forEach(e => {
        html += `<div class="card" style="padding: 20px;">
            <h3 style="color: var(--primary); margin-bottom: 10px;">${escapeHTML(e.title)}</h3>
            <p><strong>Category:</strong> ${escapeHTML(e.category)}</p>
            <p><strong>Date:</strong> ${escapeHTML(e.date)} at ${escapeHTML(e.time)}</p>
            <p><strong>Venue:</strong> ${escapeHTML(e.venue)}</p>
            <p><strong>Organizer:</strong> ${escapeHTML(e.organizer)}</p>
            <p style="margin-top: 10px; color: var(--text-muted);">${escapeHTML(e.description)}</p>
            <span style="display: inline-block; margin-top: 15px; padding: 5px 10px; background: var(--primary-light); color: white; border-radius: 5px; font-size: 0.9rem;">${escapeHTML(e.status)}</span>
        </div>`;
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    const start = (currentEventPage - 1) * eventsPerPage + 1;
    const end = Math.min(start + eventsPerPage - 1, filteredEvents.length);
    if(info) info.textContent = `Showing ${start}-${end} of ${filteredEvents.length} events`;
    
    renderPagination(filteredEvents.length, eventsPerPage, currentEventPage, 'eventPagination', (newPage) => {
        currentEventPage = newPage;
        renderEventPage();
    });
}
