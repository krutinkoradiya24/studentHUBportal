import { fetchData } from './api.js';
import { displayMessage, clearContainer } from './utils.js';
import { paginate, renderPagination } from './pagination.js';

let allStudents = [];
let filteredStudents = [];
let currentPage = 1;
const itemsPerPage = 5;

export async function initStudents() {
    const container = document.getElementById('studentContainer');
    if (!container) return;
    
    displayMessage('studentContainer', 'Loading students...');
    
    try {
        allStudents = await fetchData('../data/students.json');
        filteredStudents = [...allStudents];
        setupEventListeners();
        updateView();
    } catch (error) {
        displayMessage('studentContainer', 'Unable to load students. Please try again.', true);
    }
}

function setupEventListeners() {
    const searchInput = document.getElementById('studentSearch');
    const courseFilter = document.getElementById('studentCourseFilter');
    const yearFilter = document.getElementById('studentYearFilter');
    const sortSelect = document.getElementById('studentSort');
    
    if (searchInput) searchInput.addEventListener('input', handleFilterChange);
    if (courseFilter) courseFilter.addEventListener('change', handleFilterChange);
    if (yearFilter) yearFilter.addEventListener('change', handleFilterChange);
    if (sortSelect) sortSelect.addEventListener('change', handleFilterChange);
}

function handleFilterChange() {
    const searchVal = document.getElementById('studentSearch')?.value.toLowerCase() || '';
    const courseVal = document.getElementById('studentCourseFilter')?.value || '';
    const yearVal = document.getElementById('studentYearFilter')?.value || '';
    const sortVal = document.getElementById('studentSort')?.value || '';
    
    filteredStudents = allStudents.filter(student => {
        const matchesSearch = student.name.toLowerCase().includes(searchVal) || 
                              student.enrollmentNo.toLowerCase().includes(searchVal);
        const matchesCourse = courseVal === '' || student.branch === courseVal;
        const matchesYear = yearVal === '' || student.semester === yearVal;
        return matchesSearch && matchesCourse && matchesYear;
    });
    
    if (sortVal === 'name-asc') {
        filteredStudents.sort((a, b) => a.name.localeCompare(b.name));
    } else if (sortVal === 'name-desc') {
        filteredStudents.sort((a, b) => b.name.localeCompare(a.name));
    } else if (sortVal === 'enrollment-asc') {
        filteredStudents.sort((a, b) => a.enrollmentNo.localeCompare(b.enrollmentNo));
    } else if (sortVal === 'enrollment-desc') {
        filteredStudents.sort((a, b) => b.enrollmentNo.localeCompare(a.enrollmentNo));
    } else if (sortVal === 'semester-asc') {
        filteredStudents.sort((a, b) => a.semester.localeCompare(b.semester));
    } else if (sortVal === 'semester-desc') {
        filteredStudents.sort((a, b) => b.semester.localeCompare(a.semester));
    }
    
    currentPage = 1;
    updateView();
}

function updateView() {
    renderStudents();
    renderPagination('studentPagination', filteredStudents.length, itemsPerPage, currentPage, (newPage) => {
        currentPage = newPage;
        updateView();
    });
    
    const info = document.getElementById('studentInfo');
    if (info) {
        info.textContent = `Showing ${filteredStudents.length} students`;
    }
}

function renderStudents() {
    const container = document.getElementById('studentContainer');
    clearContainer('studentContainer');
    
    const paginatedStudents = paginate(filteredStudents, itemsPerPage, currentPage);
    
    if (paginatedStudents.length === 0) {
        displayMessage('studentContainer', 'No students found matching your criteria.');
        return;
    }
    
    const table = document.createElement('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    table.style.marginTop = '15px';
    
    table.innerHTML = `
        <thead>
            <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd; text-align: left;">
                <th style="padding: 12px;">Enrollment No</th>
                <th style="padding: 12px;">Name</th>
                <th style="padding: 12px;">Branch</th>
                <th style="padding: 12px;">Semester</th>
                <th style="padding: 12px;">Email</th>
            </tr>
        </thead>
        <tbody>
            ${paginatedStudents.map(student => `
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px;">${student.enrollmentNo}</td>
                    <td style="padding: 12px; font-weight: bold;">${student.name}</td>
                    <td style="padding: 12px;">${student.branch}</td>
                    <td style="padding: 12px;">${student.semester}</td>
                    <td style="padding: 12px;">${student.email}</td>
                </tr>
            `).join('')}
        </tbody>
    `;
    
    container.appendChild(table);
}
