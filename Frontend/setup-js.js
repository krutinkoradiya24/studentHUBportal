const fs = require('fs');
const path = require('path');

const baseDir = 'd:/3rd sem/STUDENTPORTAL/Frontend';
const htmlDir = path.join(baseDir, 'html');
const jsDir = path.join(baseDir, 'Java script');

// We will create the JS files inside jsDir (which is "Java script").
// api.js
fs.writeFileSync(path.join(jsDir, 'api.js'), `
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Failed to load data");
        return await response.json();
    } catch (error) {
        console.error(error);
        return null;
    }
}
async function fetchStudents() { return await fetchData('../data/students.json'); }
async function fetchEvents() { return await fetchData('../data/events.json'); }
async function fetchFAQs() { return await fetchData('../data/faqs.json'); }
`);

// utils.js
fs.writeFileSync(path.join(jsDir, 'utils.js'), `
function showLoading(containerId, message) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = \`<div class="loading-state" aria-live="polite"><i class="fas fa-spinner fa-spin"></i> \${message}</div>\`;
}
function hideLoading(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const loader = container.querySelector('.loading-state');
    if (loader) loader.remove();
}
function showError(containerId, retryCallback) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = \`<div class="error-state" aria-live="polite">Unable to load data. Please try again.<br><button class="btn btn-primary retry-btn" style="margin-top: 10px;">Try Again</button></div>\`;
    container.querySelector('.retry-btn').addEventListener('click', retryCallback);
}
function showEmptyMessage(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = \`<div class="empty-state" aria-live="polite">No records found.</div>\`;
}
function escapeHTML(str) {
    return str.toString().replace(/[&<>'"]/g, tag => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[tag]));
}
`);

// pagination.js
fs.writeFileSync(path.join(jsDir, 'pagination.js'), `
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
    html += \`<button class="btn btn-outline" \${currentPage === 1 ? 'disabled' : ''} data-page="\${currentPage - 1}">Previous</button>\`;
    for (let i = 1; i <= totalPages; i++) {
        html += \`<button class="btn \${i === currentPage ? 'btn-primary' : 'btn-outline'}" \${i === currentPage ? 'aria-current="page"' : ''} data-page="\${i}">\${i}</button>\`;
    }
    html += \`<button class="btn btn-outline" \${currentPage === totalPages ? 'disabled' : ''} data-page="\${currentPage + 1}">Next</button>\`;
    html += '</div>';
    
    container.innerHTML = html;
    container.querySelectorAll('button:not([disabled])').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const newPage = parseInt(e.target.getAttribute('data-page'));
            onPageChange(newPage);
        });
    });
}
`);

// students.js
fs.writeFileSync(path.join(jsDir, 'students.js'), `
let allStudents = [];
let filteredStudents = [];
let currentStudentPage = 1;
const studentsPerPage = 5;

async function initStudents() {
    const container = document.getElementById('studentContainer');
    if (!container) return;
    
    showLoading('studentContainer', 'Loading students...');
    const data = await fetchStudents();
    
    if (!data) {
        showError('studentContainer', initStudents);
        return;
    }
    
    allStudents = data;
    filteredStudents = [...allStudents];
    setupStudentListeners();
    renderStudentPage();
}

function setupStudentListeners() {
    const search = document.getElementById('studentSearch');
    const courseFilter = document.getElementById('studentCourseFilter');
    const yearFilter = document.getElementById('studentYearFilter');
    const sortFilter = document.getElementById('studentSort');
    
    const applyFilters = () => {
        let res = [...allStudents];
        
        // Search
        if (search && search.value) {
            const q = search.value.toLowerCase();
            res = res.filter(s => 
                s.name.toLowerCase().includes(q) || 
                s.studentId.toLowerCase().includes(q) || 
                s.email.toLowerCase().includes(q) || 
                s.course.toLowerCase().includes(q)
            );
        }
        // Filters
        if (courseFilter && courseFilter.value) {
            res = res.filter(s => s.course === courseFilter.value);
        }
        if (yearFilter && yearFilter.value) {
            res = res.filter(s => s.year === yearFilter.value);
        }
        // Sort
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
        
        filteredStudents = res;
        currentStudentPage = 1;
        renderStudentPage();
    };

    if(search) search.addEventListener('input', applyFilters);
    if(courseFilter) courseFilter.addEventListener('change', applyFilters);
    if(yearFilter) yearFilter.addEventListener('change', applyFilters);
    if(sortFilter) sortFilter.addEventListener('change', applyFilters);
}

function renderStudentPage() {
    const container = document.getElementById('studentContainer');
    const info = document.getElementById('studentInfo');
    if (!container) return;
    
    if (filteredStudents.length === 0) {
        showEmptyMessage('studentContainer');
        if(info) info.textContent = 'Showing 0 records';
        document.getElementById('studentPagination').innerHTML = '';
        return;
    }
    
    const paginated = paginate(filteredStudents, currentStudentPage, studentsPerPage);
    
    let html = '<table class="table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
    html += '<thead><tr style="background: var(--primary); color: white;"><th>ID</th><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>CGPA</th><th>Attendance</th></tr></thead><tbody>';
    
    paginated.forEach(s => {
        html += \`<tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 10px;">\${escapeHTML(s.id)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.studentId)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.name)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.email)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.course)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.year)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.cgpa)}</td>
            <td style="padding: 10px;">\${escapeHTML(s.attendance)}%</td>
        </tr>\`;
    });
    html += '</tbody></table>';
    
    container.innerHTML = html;
    
    const start = (currentStudentPage - 1) * studentsPerPage + 1;
    const end = Math.min(start + studentsPerPage - 1, filteredStudents.length);
    if(info) info.textContent = \`Showing \${start}-\${end} of \${filteredStudents.length} students\`;
    
    renderPagination(filteredStudents.length, studentsPerPage, currentStudentPage, 'studentPagination', (newPage) => {
        currentStudentPage = newPage;
        renderStudentPage();
    });
}
`);

// events.js
fs.writeFileSync(path.join(jsDir, 'events.js'), `
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
        html += \`<div class="card" style="padding: 20px;">
            <h3 style="color: var(--primary); margin-bottom: 10px;">\${escapeHTML(e.title)}</h3>
            <p><strong>Category:</strong> \${escapeHTML(e.category)}</p>
            <p><strong>Date:</strong> \${escapeHTML(e.date)} at \${escapeHTML(e.time)}</p>
            <p><strong>Venue:</strong> \${escapeHTML(e.venue)}</p>
            <p><strong>Organizer:</strong> \${escapeHTML(e.organizer)}</p>
            <p style="margin-top: 10px; color: var(--text-muted);">\${escapeHTML(e.description)}</p>
            <span style="display: inline-block; margin-top: 15px; padding: 5px 10px; background: var(--primary-light); color: white; border-radius: 5px; font-size: 0.9rem;">\${escapeHTML(e.status)}</span>
        </div>\`;
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    const start = (currentEventPage - 1) * eventsPerPage + 1;
    const end = Math.min(start + eventsPerPage - 1, filteredEvents.length);
    if(info) info.textContent = \`Showing \${start}-\${end} of \${filteredEvents.length} events\`;
    
    renderPagination(filteredEvents.length, eventsPerPage, currentEventPage, 'eventPagination', (newPage) => {
        currentEventPage = newPage;
        renderEventPage();
    });
}
`);

// faqs.js
fs.writeFileSync(path.join(jsDir, 'faqs.js'), `
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
        html += \`<div class="faq-item card" style="padding: 15px; cursor: pointer;">
            <div class="faq-question" style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; color: var(--primary);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                \${escapeHTML(f.question)}
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer" style="display: none; margin-top: 10px; color: var(--text-muted); padding-top: 10px; border-top: 1px solid var(--border-color);">
                \${escapeHTML(f.answer)}
                <div style="margin-top: 5px; font-size: 0.85rem; color: var(--secondary);">Category: \${escapeHTML(f.category)}</div>
            </div>
        </div>\`;
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    const start = (currentFaqPage - 1) * faqsPerPage + 1;
    const end = Math.min(start + faqsPerPage - 1, filteredFaqs.length);
    if(info) info.textContent = \`Showing \${start}-\${end} of \${filteredFaqs.length} FAQs\`;
    
    renderPagination(filteredFaqs.length, faqsPerPage, currentFaqPage, 'faqPagination', (newPage) => {
        currentFaqPage = newPage;
        renderFaqPage();
    });
}
`);

// Add initialization to the bottom of script.js
let mainScript = fs.readFileSync(path.join(jsDir, 'script.js'), 'utf8');
const initCall = `
document.addEventListener("DOMContentLoaded", () => {
    if (typeof initStudents === 'function') initStudents();
    if (typeof initEvents === 'function') initEvents();
    if (typeof initFAQsData === 'function') initFAQsData();
    if (typeof initDashboardEvents === 'function') initDashboardEvents();
    if (typeof initAdminStats === 'function') initAdminStats();
});

// Admin stats
async function initAdminStats() {
    const adminStats = document.getElementById('dynamicAdminStats');
    if (!adminStats) return;
    const data = await fetchStudents();
    if(data && data.length > 0) {
        const total = data.length;
        const avgCgpa = (data.reduce((sum, s) => sum + s.cgpa, 0) / total).toFixed(2);
        const avgAtt = (data.reduce((sum, s) => sum + s.attendance, 0) / total).toFixed(1);
        adminStats.innerHTML = \`<div class="card"><h3>Total Students</h3><p style="font-size: 2rem; color: var(--primary);">\${total}</p></div>
        <div class="card"><h3>Average CGPA</h3><p style="font-size: 2rem; color: var(--primary);">\${avgCgpa}</p></div>
        <div class="card"><h3>Average Attendance</h3><p style="font-size: 2rem; color: var(--primary);">\${avgAtt}%</p></div>\`;
    }
}

// Dashboard Events
async function initDashboardEvents() {
    const dashEvents = document.getElementById('dashboardUpcomingEvents');
    if (!dashEvents) return;
    const data = await fetchEvents();
    if (data && data.length > 0) {
        const upcoming = data.filter(e => e.status === 'Upcoming').sort((a, b) => new Date(a.date) - new Date(b.date)).slice(0, 3);
        let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
        upcoming.forEach(e => {
            html += \`<div class="card" style="padding: 15px;"><strong>\${escapeHTML(e.title)}</strong> - \${escapeHTML(e.date)}</div>\`;
        });
        html += '</div>';
        dashEvents.innerHTML = html;
    }
}
`;
if (!mainScript.includes('initStudents')) {
    fs.appendFileSync(path.join(jsDir, 'script.js'), initCall);
}
console.log("Created JS modules.");
