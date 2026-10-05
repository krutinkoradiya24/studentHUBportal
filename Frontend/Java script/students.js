
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
        html += `<tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 10px;">${escapeHTML(s.id)}</td>
            <td style="padding: 10px;">${escapeHTML(s.studentId)}</td>
            <td style="padding: 10px;">${escapeHTML(s.name)}</td>
            <td style="padding: 10px;">${escapeHTML(s.email)}</td>
            <td style="padding: 10px;">${escapeHTML(s.course)}</td>
            <td style="padding: 10px;">${escapeHTML(s.year)}</td>
            <td style="padding: 10px;">${escapeHTML(s.cgpa)}</td>
            <td style="padding: 10px;">${escapeHTML(s.attendance)}%</td>
        </tr>`;
    });
    html += '</tbody></table>';
    
    container.innerHTML = html;
    
    const start = (currentStudentPage - 1) * studentsPerPage + 1;
    const end = Math.min(start + studentsPerPage - 1, filteredStudents.length);
    if(info) info.textContent = `Showing ${start}-${end} of ${filteredStudents.length} students`;
    
    renderPagination(filteredStudents.length, studentsPerPage, currentStudentPage, 'studentPagination', (newPage) => {
        currentStudentPage = newPage;
        renderStudentPage();
    });
}
