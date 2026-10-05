const fs = require('fs');
const path = require('path');

const baseDir = 'd:/3rd sem/STUDENTPORTAL/Frontend';
const htmlDir = path.join(baseDir, 'html');

const template = fs.readFileSync(path.join(htmlDir, 'dashboard.html'), 'utf8');

// Function to generate a page
function createPage(filename, title, content) {
    let newPage = template.replace(/<title>.*?<\/title>/, `<title>Student Hub Portal - ${title}</title>`);
    
    // Replace the main content area
    const mainRegex = /<main class="main-content">[\s\S]*?<\/main>/;
    newPage = newPage.replace(mainRegex, `<main class="main-content">\n<header class="top-header" style="background: var(--card-bg); padding: 15px 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: var(--shadow); display: flex; justify-content: space-between; align-items: center;"><h2>${title}</h2></header>\n${content}\n</main>`);
    
    // Write file
    fs.writeFileSync(path.join(htmlDir, filename), newPage);
}

// 1. students.html
createPage('students.html', 'Student Directory', `
    <div class="card" style="padding: 20px;">
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <input type="text" id="studentSearch" placeholder="Search students..." style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);" />
            <select id="studentCourseFilter" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">All Courses</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Information Tech">Information Tech</option>
                <option value="Civil Eng">Civil Eng</option>
                <option value="Mechanical">Mechanical</option>
                <option value="Electrical">Electrical</option>
            </select>
            <select id="studentYearFilter" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">All Years</option>
                <option value="1 Year">1 Year</option>
                <option value="2 Year">2 Year</option>
                <option value="3 Year">3 Year</option>
                <option value="4 Year">4 Year</option>
            </select>
            <select id="studentSort" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">Sort By</option>
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="cgpa-desc">CGPA (High-Low)</option>
                <option value="cgpa-asc">CGPA (Low-High)</option>
                <option value="attendance-desc">Attendance (High-Low)</option>
                <option value="attendance-asc">Attendance (Low-High)</option>
            </select>
        </div>
        <div id="studentInfo" style="margin-bottom: 15px; font-weight: bold; color: var(--secondary);"></div>
        <div id="studentContainer"></div>
        <div id="studentPagination"></div>
    </div>
`);

// 2. events.html
createPage('events.html', 'College Events', `
    <div class="card" style="padding: 20px;">
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <input type="text" id="eventSearch" placeholder="Search events..." style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);" />
            <select id="eventCategoryFilter" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">All Categories</option>
                <option value="Technical">Technical</option>
                <option value="Cultural">Cultural</option>
                <option value="Sports">Sports</option>
                <option value="Workshop">Workshop</option>
                <option value="Seminar">Seminar</option>
            </select>
            <select id="eventSort" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">Sort By</option>
                <option value="title-asc">Title (A-Z)</option>
                <option value="title-desc">Title (Z-A)</option>
                <option value="date-asc">Date (Oldest)</option>
                <option value="date-desc">Date (Newest)</option>
            </select>
        </div>
        <div id="eventInfo" style="margin-bottom: 15px; font-weight: bold; color: var(--secondary);"></div>
        <div id="eventContainer"></div>
        <div id="eventPagination"></div>
    </div>
`);

// 3. faqs.html
createPage('faqs.html', 'Frequently Asked Questions', `
    <div class="card" style="padding: 20px;">
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <input type="text" id="faqSearch" placeholder="Search FAQs..." style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);" />
            <select id="faqCategoryFilter" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <option value="">All Categories</option>
                <option value="General">General</option>
                <option value="Attendance">Attendance</option>
                <option value="Courses">Courses</option>
                <option value="Exams">Exams</option>
                <option value="Fees">Fees</option>
            </select>
        </div>
        <div id="faqInfo" style="margin-bottom: 15px; font-weight: bold; color: var(--secondary);"></div>
        <div id="faqContainer"></div>
        <div id="faqPagination"></div>
    </div>
`);

// 4. Update admin.html
let adminContent = fs.readFileSync(path.join(htmlDir, 'admin.html'), 'utf8');
const adminStatsInjection = `<div style="margin-top: 30px;">
  <h3 style="margin-bottom: 15px;">Live Student Statistics</h3>
  <div id="dynamicAdminStats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;"></div>
</div>`;
adminContent = adminContent.replace('</main>', `${adminStatsInjection}\n</main>`);
fs.writeFileSync(path.join(htmlDir, 'admin.html'), adminContent);

// 5. Update dashboard.html
let dashContent = fs.readFileSync(path.join(htmlDir, 'dashboard.html'), 'utf8');
const dashUpcomingInjection = `<div style="margin-top: 30px;">
  <h3 style="margin-bottom: 15px;">Upcoming Events (Live)</h3>
  <div id="dashboardUpcomingEvents"></div>
</div>`;
dashContent = dashContent.replace('</main>', `${dashUpcomingInjection}\n</main>`);
fs.writeFileSync(path.join(htmlDir, 'dashboard.html'), dashContent);

// 6. Update sample.html
let sampleContent = fs.readFileSync(path.join(htmlDir, 'sample.html'), 'utf8');
const sampleInjection = `
<div class="card" style="margin-top: 30px; padding: 20px;">
  <h2 style="color: var(--primary); margin-bottom: 15px;">JSON & Fetch API Demonstration</h2>
  <ul style="list-style: inside; display: grid; gap: 10px; color: var(--text-color);">
    <li><strong>Student JSON:</strong> 20 records dynamically loaded via Fetch API.</li>
    <li><strong>Events JSON:</strong> 20 records, categorized and sortable.</li>
    <li><strong>FAQ JSON:</strong> 20 records with animated accordions.</li>
    <li><strong>Fetch API:</strong> Handles async requests cleanly in <code>api.js</code>.</li>
    <li><strong>Dynamic Rendering:</strong> Cards and tables generated on the fly.</li>
    <li><strong>Search/Filter/Sorting/Pagination:</strong> Seamlessly integrated!</li>
    <li><strong>Loading/Error:</strong> Professional loading spinners and error states.</li>
  </ul>
  <div style="margin-top: 20px; display: flex; gap: 15px;">
    <a href="students.html" class="btn btn-primary">View Students</a>
    <a href="events.html" class="btn btn-primary">View Events</a>
    <a href="faqs.html" class="btn btn-primary">View FAQs</a>
  </div>
</div>
`;
sampleContent = sampleContent.replace('</main>', `${sampleInjection}\n</main>`);
fs.writeFileSync(path.join(htmlDir, 'sample.html'), sampleContent);

// 7. Replace Script Includes in ALL HTML Files
const scriptsToInject = `
    <script src="../js/api.js?v=1"></script>
    <script src="../js/utils.js?v=1"></script>
    <script src="../js/pagination.js?v=1"></script>
    <script src="../js/students.js?v=1"></script>
    <script src="../js/events.js?v=1"></script>
    <script src="../js/faqs.js?v=1"></script>
    <script src="../js/script.js?v=${Date.now()}"></script>
`;

const files = fs.readdirSync(htmlDir);
for (const file of files) {
    if (file.endsWith('.html')) {
        let content = fs.readFileSync(path.join(htmlDir, file), 'utf8');
        
        // Remove old scripts
        content = content.replace(/<script src="\.\.\/Java script\/script\.js.*?"><\/script>/g, '');
        content = content.replace(/<script src="\.\.\/js\/.*?"><\/script>/g, ''); // in case we run it multiple times
        
        // Inject new scripts right before </body>
        content = content.replace('</body>', `${scriptsToInject}\n  </body>`);
        
        fs.writeFileSync(path.join(htmlDir, file), content);
    }
}

console.log("Generated HTML pages and updated scripts");
