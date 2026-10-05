const fs = require('fs');
const path = require('path');

const baseDir = 'd:/3rd sem/STUDENTPORTAL/Frontend';
const dataDir = path.join(baseDir, 'data');
const jsDir = path.join(baseDir, 'js');
const htmlDir = path.join(baseDir, 'html');
const oldJsDir = path.join(baseDir, 'Java script');

// Create directories
if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
if (!fs.existsSync(jsDir)) fs.mkdirSync(jsDir, { recursive: true });

// Move existing script.js to jsDir
if (fs.existsSync(path.join(oldJsDir, 'script.js'))) {
    fs.copyFileSync(path.join(oldJsDir, 'script.js'), path.join(jsDir, 'script.js'));
} else if (!fs.existsSync(path.join(jsDir, 'script.js'))) {
    fs.writeFileSync(path.join(jsDir, 'script.js'), '');
}

// --------------------------------------------------
// 1. GENERATE JSON DATA
// --------------------------------------------------
const students = [];
const courses = ['Computer Science', 'Information Tech', 'Civil Eng', 'Mechanical', 'Electrical'];
for (let i = 1; i <= 20; i++) {
    students.push({
        id: i,
        studentId: `25CS${i.toString().padStart(3, '0')}`,
        name: `Student Name ${i}`,
        email: `student${i}@example.com`,
        mobile: `98765432${(i % 10).toString().padStart(2, '0')}`,
        course: courses[i % courses.length],
        year: `${(i % 4) + 1} Year`,
        semester: (i % 8) + 1,
        gender: i % 3 === 0 ? 'Female' : 'Male',
        cgpa: parseFloat((7 + (i % 3) * 0.4 + (i % 2) * 0.1).toFixed(2)),
        attendance: 75 + (i % 20)
    });
}
fs.writeFileSync(path.join(dataDir, 'students.json'), JSON.stringify(students, null, 2));

const events = [];
const categories = ['Technical', 'Cultural', 'Sports', 'Workshop', 'Seminar'];
for (let i = 1; i <= 20; i++) {
    events.push({
        id: i,
        title: `College Event ${i}`,
        category: categories[i % categories.length],
        date: `2026-10-${(10 + i).toString().padStart(2, '0')}`,
        time: `10:00 AM`,
        venue: `Auditorium ${i % 3 + 1}`,
        organizer: `${categories[i % categories.length]} Dept`,
        description: `This is a great ${categories[i % categories.length]} event for all students to participate and learn.`,
        status: i > 15 ? 'Completed' : 'Upcoming'
    });
}
fs.writeFileSync(path.join(dataDir, 'events.json'), JSON.stringify(events, null, 2));

const faqs = [];
const faqCategories = ['General', 'Attendance', 'Courses', 'Exams', 'Fees'];
for (let i = 1; i <= 20; i++) {
    faqs.push({
        id: i,
        question: `How do I handle FAQ topic ${i}?`,
        answer: `To handle FAQ topic ${i}, you should visit the respective section on the Student Hub dashboard and follow the standard procedure.`,
        category: faqCategories[i % faqCategories.length]
    });
}
fs.writeFileSync(path.join(dataDir, 'faqs.json'), JSON.stringify(faqs, null, 2));

console.log("Created JSON files");
