/**
 * Frontend/js/admin.js - Complete Interactive Administrator Portal Controller
 * 
 * Provides interactive workflows for:
 * 1. Marking / Filling Attendance with instant batch-toggle and storage
 * 2. Adding & managing Student records with live search and delete
 * 3. Creating & managing College Events with category badges
 * 4. Scheduling & managing Weekly Timetables
 * 5. Publishing Campus Notices & Circulars
 * 6. Live KPI calculations and persistent localStorage sync
 */

document.addEventListener("DOMContentLoaded", () => {
  initAdminData();
  initAdminTabs();
  initAttendanceSystem();
  renderStudentsTable();
  renderEventsTable();
  renderTimetableMatrix();
  renderNoticesList();
  updateDashboardKPIs();
});

/* ==========================================================
   1. DATA SEEDING & PERSISTENCE HELPERS
========================================================== */
function initAdminData() {
  // 1. Initial Students
  if (!localStorage.getItem("studenthub_students")) {
    const seedStudents = [
      { id: "ST2601", name: "Aryan Patel", email: "aryan@studenthub.edu", phone: "9876543210", course: "B.Tech Computer Science", semester: "6th Semester", status: "Active" },
      { id: "ST2602", name: "Sneha Rao", email: "sneha.rao@studenthub.edu", phone: "9876543211", course: "B.Tech Computer Science", semester: "6th Semester", status: "Active" },
      { id: "ST2603", name: "Rahul Verma", email: "rahul.v@studenthub.edu", phone: "9876543212", course: "B.Tech Information Technology", semester: "4th Semester", status: "Active" },
      { id: "ST2604", name: "Priya Sharma", email: "priya.s@studenthub.edu", phone: "9876543213", course: "BBA Business Analytics", semester: "2nd Semester", status: "Active" },
      { id: "ST2605", name: "Devendra Mehta", email: "devendra@studenthub.edu", phone: "9876543214", course: "MBA Marketing", semester: "2nd Semester", status: "Inactive" }
    ];
    localStorage.setItem("studenthub_students", JSON.stringify(seedStudents));
  }

  // 2. Initial Events
  if (!localStorage.getItem("studenthub_events")) {
    const seedEvents = [
      { id: "EV-101", title: "National Tech Hackathon 2026", category: "Technical", date: "2026-11-15", time: "09:00 AM", location: "Auditorium Hall A", description: "36-hour multi-track code sprint with industry prizes." },
      { id: "EV-102", title: "Annual Cultural Fest - Spandan", category: "Cultural", date: "2026-11-28", time: "05:00 PM", location: "Open Air Amphitheater", description: "Music, dance, fashion show and live bands." },
      { id: "EV-103", title: "Inter-College Cricket Tournament", category: "Sports", date: "2026-12-05", time: "08:30 AM", location: "Sports Complex", description: "Knockout matches between departments." },
      { id: "EV-104", title: "Generative AI & Agentic Coding Workshop", category: "Workshop", date: "2026-11-20", time: "02:00 PM", location: "Lab 402", description: "Hands-on LLM agents and web application orchestration." }
    ];
    localStorage.setItem("studenthub_events", JSON.stringify(seedEvents));
  }

  // 3. Initial Timetable Slots
  if (!localStorage.getItem("studenthub_timetables")) {
    const seedTimetable = [
      { id: "TT-01", day: "Monday", time: "09:00 - 10:00 AM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Cloud Computing & DevOps", faculty: "Prof. S. Joshi", room: "Lab 301" },
      { id: "TT-02", day: "Monday", time: "10:15 - 11:15 AM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Machine Learning Foundations", faculty: "Dr. K. Dave", room: "Room 405" },
      { id: "TT-03", day: "Tuesday", time: "09:00 - 10:00 AM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Web Systems & APIs", faculty: "Prof. R. Patel", room: "Lab 302" },
      { id: "TT-04", day: "Wednesday", time: "11:30 - 01:30 PM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Advanced Database Lab", faculty: "Prof. A. Shah", room: "Lab 305" },
      { id: "TT-05", day: "Thursday", time: "02:00 - 03:00 PM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Information Security & Cryptography", faculty: "Dr. M. Trivedi", room: "Room 402" },
      { id: "TT-06", day: "Friday", time: "10:00 - 11:00 AM", course: "B.Tech Computer Science", semester: "6th Semester", subject: "Enterprise Software Design", faculty: "Prof. N. Vyas", room: "Room 403" }
    ];
    localStorage.setItem("studenthub_timetables", JSON.stringify(seedTimetable));
  }

  // 4. Initial Campus Notices
  if (!localStorage.getItem("studenthub_notices")) {
    const seedNotices = [
      { id: "NT-01", title: "Mid-Semester Examination Schedule Published", category: "Exam", audience: "All Students", date: "2026-10-28", content: "Timetable for Mid-Semester Theory and Practical exams has been released. Check results portal for seat numbers." },
      { id: "NT-02", title: "Library Due Date Extension for Festival Week", category: "Academic", audience: "All Students", date: "2026-10-25", content: "All books issued before Nov 1st can be renewed online without late fee penalty." },
      { id: "NT-03", title: "Industry Internship Submissions Deadline", category: "Urgent", audience: "Final Year", date: "2026-10-22", content: "Upload supervisor sign-off and report by Friday 5:00 PM." }
    ];
    localStorage.setItem("studenthub_notices", JSON.stringify(seedNotices));
  }
}

function getStudents() {
  return JSON.parse(localStorage.getItem("studenthub_students") || "[]");
}

function saveStudents(students) {
  localStorage.setItem("studenthub_students", JSON.stringify(students));
  renderStudentsTable();
  initAttendanceSystem();
  updateDashboardKPIs();
}

function getEvents() {
  return JSON.parse(localStorage.getItem("studenthub_events") || "[]");
}

function saveEvents(events) {
  localStorage.setItem("studenthub_events", JSON.stringify(events));
  renderEventsTable();
  updateDashboardKPIs();
}

function getTimetable() {
  return JSON.parse(localStorage.getItem("studenthub_timetables") || "[]");
}

function saveTimetable(slots) {
  localStorage.setItem("studenthub_timetables", JSON.stringify(slots));
  renderTimetableMatrix();
  updateDashboardKPIs();
}

function getNotices() {
  return JSON.parse(localStorage.getItem("studenthub_notices") || "[]");
}

function saveNotices(notices) {
  localStorage.setItem("studenthub_notices", JSON.stringify(notices));
  renderNoticesList();
  updateDashboardKPIs();
}

/* ==========================================================
   2. TABS CONTROLLER
========================================================== */
function initAdminTabs() {
  const tabBtns = document.querySelectorAll(".admin-tab-btn");
  tabBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const targetTab = btn.getAttribute("data-tab");
      switchAdminTab(targetTab);
    });
  });
}

function switchAdminTab(tabId) {
  document.querySelectorAll(".admin-tab-btn").forEach(b => {
    b.classList.toggle("active", b.getAttribute("data-tab") === tabId);
  });
  document.querySelectorAll(".admin-tab-pane").forEach(pane => {
    pane.classList.toggle("active", pane.id === "tab-" + tabId);
  });
}

/* ==========================================================
   3. MODAL CONTROLLER
========================================================== */
function openAdminModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("open");
    const firstInput = modal.querySelector("input, select");
    if (firstInput) firstInput.focus();
  }
}

function closeAdminModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove("open");
}

// Global modal dismiss on background click
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("admin-modal-overlay")) {
    e.target.classList.remove("open");
  }
});

// Dismiss on Escape key
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    document.querySelectorAll(".admin-modal-overlay.open").forEach(m => m.classList.remove("open"));
  }
});

/* ==========================================================
   4. TOAST NOTIFICATIONS
========================================================== */
function showToast(message, type = "success") {
  let container = document.getElementById("adminToastContainer");
  if (!container) {
    container = document.createElement("div");
    container.id = "adminToastContainer";
    container.className = "admin-toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `admin-toast ${type}`;
  let icon = "check-circle";
  if (type === "danger") icon = "exclamation-circle";
  if (type === "info") icon = "info-circle";

  toast.innerHTML = `<i class="fas fa-${icon}"></i> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(50px)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

/* ==========================================================
   5. ATTENDANCE MANAGEMENT (FILL ATTENDANCE)
========================================================== */
function initAttendanceSystem() {
  const dateInput = document.getElementById("attDateInput");
  const courseSelect = document.getElementById("attCourseSelect");
  const tableBody = document.getElementById("attendanceTableBody");
  if (!dateInput || !tableBody) return;

  // Set default date to today
  if (!dateInput.value) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.value = today;
  }

  renderAttendanceRoster();

  dateInput.onchange = renderAttendanceRoster;
  if (courseSelect) courseSelect.onchange = renderAttendanceRoster;
}

function renderAttendanceRoster() {
  const dateInput = document.getElementById("attDateInput");
  const courseSelect = document.getElementById("attCourseSelect");
  const tableBody = document.getElementById("attendanceTableBody");
  if (!tableBody) return;

  const selectedDate = dateInput ? dateInput.value : "";
  const selectedCourse = courseSelect ? courseSelect.value : "All";

  const allStudents = getStudents();
  let students = allStudents.filter(s => s.status === "Active");
  if (selectedCourse !== "All") {
    students = students.filter(s => s.course.includes(selectedCourse) || selectedCourse.includes(s.course));
  }

  // Load existing records for date
  const storedAttendance = JSON.parse(localStorage.getItem("studenthub_attendance_records") || "{}");
  const dayKey = `${selectedDate}_${selectedCourse}`;
  const dayRecords = storedAttendance[dayKey] || {};

  tableBody.innerHTML = "";

  if (students.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No active students found for course "${selectedCourse}".</td></tr>`;
    updateAttendanceStats();
    return;
  }

  students.forEach((stud, index) => {
    const currentStatus = dayRecords[stud.id] || "Present"; // default present
    const tr = document.createElement("tr");
    tr.setAttribute("data-student-id", stud.id);
    tr.innerHTML = `
      <td><strong>${stud.id}</strong></td>
      <td>
        <div style="display: flex; align-items: center; gap: 10px;">
          <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(stud.name)}&background=random" style="width: 32px; height: 32px; border-radius: 50%;" alt="${stud.name}" />
          <div>
            <div style="font-weight: 600;">${stud.name}</div>
            <div style="font-size: 0.78rem; color: var(--text-muted);">${stud.email}</div>
          </div>
        </div>
      </td>
      <td><span style="font-size: 0.85rem; color: var(--text-muted);">${stud.course} (${stud.semester})</span></td>
      <td>
        <div class="att-status-group" data-student="${stud.id}">
          <button type="button" class="att-status-btn ${currentStatus === 'Present' ? 'selected-p' : ''}" onclick="setStudentAttStatus('${stud.id}', 'Present')">
            <i class="fas fa-check"></i> Present
          </button>
          <button type="button" class="att-status-btn ${currentStatus === 'Absent' ? 'selected-a' : ''}" onclick="setStudentAttStatus('${stud.id}', 'Absent')">
            <i class="fas fa-times"></i> Absent
          </button>
          <button type="button" class="att-status-btn ${currentStatus === 'Late' ? 'selected-l' : ''}" onclick="setStudentAttStatus('${stud.id}', 'Late')">
            <i class="fas fa-clock"></i> Late
          </button>
        </div>
      </td>
      <td>
        <input type="text" class="att-remarks-input" placeholder="Optional remark" value="${dayRecords[stud.id + '_rem'] || ''}" style="width: 100%; max-width: 200px; padding: 6px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;" />
      </td>
    `;
    tableBody.appendChild(tr);
  });

  updateAttendanceStats();
}

function setStudentAttStatus(studentId, status) {
  const group = document.querySelector(`.att-status-group[data-student="${studentId}"]`);
  if (!group) return;

  const btns = group.querySelectorAll(".att-status-btn");
  btns.forEach(btn => {
    btn.classList.remove("selected-p", "selected-a", "selected-l");
    if (status === "Present" && btn.textContent.includes("Present")) btn.classList.add("selected-p");
    if (status === "Absent" && btn.textContent.includes("Absent")) btn.classList.add("selected-a");
    if (status === "Late" && btn.textContent.includes("Late")) btn.classList.add("selected-l");
  });

  updateAttendanceStats();
}

function markAllAttendance(status) {
  const groups = document.querySelectorAll(".att-status-group");
  groups.forEach(g => {
    const studentId = g.getAttribute("data-student");
    setStudentAttStatus(studentId, status);
  });
  updateAttendanceStats();
  showToast(`Marked all displayed students as "${status}"`, "info");
}

function updateAttendanceStats() {
  const rows = document.querySelectorAll("#attendanceTableBody tr[data-student-id]");
  let present = 0;
  let absent = 0;
  let late = 0;

  rows.forEach(r => {
    const pBtn = r.querySelector(".selected-p");
    const aBtn = r.querySelector(".selected-a");
    const lBtn = r.querySelector(".selected-l");
    if (pBtn) present++;
    if (aBtn) absent++;
    if (lBtn) late++;
  });

  const total = rows.length;
  const pct = total > 0 ? Math.round(((present + (late * 0.5)) / total) * 100) : 0;

  const totalEl = document.getElementById("attStatTotal");
  const presentEl = document.getElementById("attStatPresent");
  const absentEl = document.getElementById("attStatAbsent");
  const lateEl = document.getElementById("attStatLate");
  const pctEl = document.getElementById("attStatPct");

  if (totalEl) totalEl.textContent = total;
  if (presentEl) presentEl.textContent = present;
  if (absentEl) absentEl.textContent = absent;
  if (lateEl) lateEl.textContent = late;
  if (pctEl) pctEl.textContent = pct + "%";
}

function saveAttendanceRoster() {
  const dateInput = document.getElementById("attDateInput");
  const courseSelect = document.getElementById("attCourseSelect");
  if (!dateInput) return;

  const selectedDate = dateInput.value;
  const selectedCourse = courseSelect ? courseSelect.value : "All";
  const dayKey = `${selectedDate}_${selectedCourse}`;

  const rows = document.querySelectorAll("#attendanceTableBody tr[data-student-id]");
  const records = {};

  rows.forEach(r => {
    const sId = r.getAttribute("data-student-id");
    let status = "Present";
    if (r.querySelector(".selected-a")) status = "Absent";
    if (r.querySelector(".selected-l")) status = "Late";

    const remInput = r.querySelector(".att-remarks-input");
    const remark = remInput ? remInput.value.trim() : "";

    records[sId] = status;
    if (remark) records[sId + "_rem"] = remark;
  });

  const stored = JSON.parse(localStorage.getItem("studenthub_attendance_records") || "{}");
  stored[dayKey] = records;
  localStorage.setItem("studenthub_attendance_records", JSON.stringify(stored));

  showToast(`Attendance saved successfully for ${rows.length} students on ${selectedDate}!`, "success");
}

/* ==========================================================
   6. STUDENT MANAGEMENT (CRUD)
========================================================= */
function renderStudentsTable(filterText = "") {
  const tbody = document.getElementById("studentsTableBody");
  if (!tbody) return;

  const students = getStudents();
  tbody.innerHTML = "";

  const q = filterText.toLowerCase().trim();
  const filtered = students.filter(s => 
    s.name.toLowerCase().includes(q) ||
    s.id.toLowerCase().includes(q) ||
    s.email.toLowerCase().includes(q) ||
    s.course.toLowerCase().includes(q)
  );

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No matching student records found.</td></tr>`;
    return;
  }

  filtered.forEach(s => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><strong>${s.id}</strong></td>
      <td>
        <div style="display: flex; align-items: center; gap: 10px;">
          <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=random" style="width: 34px; height: 34px; border-radius: 50%;" alt="${s.name}" />
          <div>
            <div style="font-weight: 600;">${s.name}</div>
            <div style="font-size: 0.78rem; color: var(--text-muted);">${s.email}</div>
          </div>
        </div>
      </td>
      <td>${s.phone}</td>
      <td>${s.course}</td>
      <td>${s.semester || '1st Semester'}</td>
      <td>
        <span class="badge ${s.status === 'Active' ? 'badge-active' : 'badge-inactive'}">${s.status}</span>
      </td>
      <td>
        <button class="btn-icon-danger" onclick="deleteStudent('${s.id}')" title="Delete Student Record">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const countBadge = document.getElementById("studentCountBadge");
  if (countBadge) countBadge.textContent = `${students.length} Registered`;
}

function handleAddStudentSubmit(e) {
  e.preventDefault();
  const idInput = document.getElementById("newStudentId");
  const nameInput = document.getElementById("newStudentName");
  const emailInput = document.getElementById("newStudentEmail");
  const phoneInput = document.getElementById("newStudentPhone");
  const courseInput = document.getElementById("newStudentCourse");
  const semInput = document.getElementById("newStudentSem");
  const statusInput = document.getElementById("newStudentStatus");

  const newStudent = {
    id: idInput.value.trim() || ("ST" + Math.floor(1000 + Math.random() * 9000)),
    name: nameInput.value.trim(),
    email: emailInput.value.trim().toLowerCase(),
    phone: phoneInput.value.trim(),
    course: courseInput.value,
    semester: semInput.value,
    status: statusInput.value
  };

  const students = getStudents();
  if (students.some(s => s.id === newStudent.id || s.email === newStudent.email)) {
    alert("A student with this ID or Email already exists!");
    return;
  }

  students.unshift(newStudent);
  saveStudents(students);

  closeAdminModal("addStudentModal");
  e.target.reset();
  showToast(`Student "${newStudent.name}" enrolled successfully!`, "success");
}

function deleteStudent(id) {
  if (!confirm(`Are you sure you want to remove student record ${id}?`)) return;
  let students = getStudents();
  students = students.filter(s => s.id !== id);
  saveStudents(students);
  showToast(`Student ${id} removed from roster.`, "danger");
}

/* ==========================================================
   7. EVENTS MANAGEMENT
========================================================== */
function renderEventsTable() {
  const container = document.getElementById("eventsTableBody");
  if (!container) return;

  const events = getEvents();
  container.innerHTML = "";

  if (events.length === 0) {
    container.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No events scheduled. Click "Add Event" to create one.</td></tr>`;
    return;
  }

  events.forEach(ev => {
    const tr = document.createElement("tr");
    let catBadge = "badge-tech";
    if (ev.category === "Cultural") catBadge = "badge-cultural";
    if (ev.category === "Sports") catBadge = "badge-sports";
    if (ev.category === "Workshop") catBadge = "badge-academic";

    tr.innerHTML = `
      <td><strong>${ev.id}</strong></td>
      <td>
        <div style="font-weight: 600; color: var(--text-main);">${ev.title}</div>
        <div style="font-size: 0.78rem; color: var(--text-muted);">${ev.description || ''}</div>
      </td>
      <td><span class="badge ${catBadge}">${ev.category}</span></td>
      <td><i class="far fa-calendar-alt"></i> ${ev.date} at ${ev.time}</td>
      <td><i class="fas fa-map-marker-alt"></i> ${ev.location}</td>
      <td>
        <button class="btn-icon-danger" onclick="deleteEvent('${ev.id}')" title="Delete Event">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>
    `;
    container.appendChild(tr);
  });
}

function handleAddEventSubmit(e) {
  e.preventDefault();
  const title = document.getElementById("eventTitle").value.trim();
  const category = document.getElementById("eventCategory").value;
  const date = document.getElementById("eventDate").value;
  const time = document.getElementById("eventTime").value.trim();
  const location = document.getElementById("eventLocation").value.trim();
  const description = document.getElementById("eventDesc").value.trim();

  const newEvent = {
    id: "EV-" + Math.floor(100 + Math.random() * 900),
    title,
    category,
    date,
    time,
    location,
    description
  };

  const events = getEvents();
  events.unshift(newEvent);
  saveEvents(events);

  closeAdminModal("addEventModal");
  e.target.reset();
  showToast(`Event "${title}" published successfully!`, "success");
}

function deleteEvent(id) {
  if (!confirm(`Are you sure you want to delete event ${id}?`)) return;
  let events = getEvents();
  events = events.filter(e => e.id !== id);
  saveEvents(events);
  showToast(`Event ${id} removed.`, "danger");
}

/* ==========================================================
   8. TIMETABLE SCHEDULE MANAGEMENT
========================================================== */
function renderTimetableMatrix(filterDay = "All") {
  const tbody = document.getElementById("timetableTableBody");
  if (!tbody) return;

  const timetable = getTimetable();
  tbody.innerHTML = "";

  let filtered = timetable;
  if (filterDay !== "All") {
    filtered = filtered.filter(t => t.day === filterDay);
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No class slots scheduled for ${filterDay}.</td></tr>`;
    return;
  }

  filtered.forEach(slot => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><span class="badge badge-tech" style="font-weight: 700;">${slot.day}</span></td>
      <td><strong>${slot.time}</strong></td>
      <td><span style="font-weight: 600; color: var(--text-main);">${slot.subject}</span></td>
      <td>${slot.course} (${slot.semester})</td>
      <td>${slot.faculty}</td>
      <td><i class="fas fa-door-open"></i> ${slot.room}</td>
      <td>
        <button class="btn-icon-danger" onclick="deleteTimetableSlot('${slot.id}')" title="Delete Class Slot">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function handleAddTimetableSubmit(e) {
  e.preventDefault();
  const day = document.getElementById("ttDay").value;
  const time = document.getElementById("ttTime").value.trim();
  const subject = document.getElementById("ttSubject").value.trim();
  const course = document.getElementById("ttCourse").value;
  const sem = document.getElementById("ttSem").value;
  const faculty = document.getElementById("ttFaculty").value.trim();
  const room = document.getElementById("ttRoom").value.trim();

  const newSlot = {
    id: "TT-" + Math.floor(10 + Math.random() * 90),
    day,
    time,
    subject,
    course,
    semester: sem,
    faculty,
    room
  };

  const slots = getTimetable();
  slots.push(newSlot);
  saveTimetable(slots);

  closeAdminModal("addTimetableModal");
  e.target.reset();
  showToast(`Class slot "${subject}" on ${day} added!`, "success");
}

function deleteTimetableSlot(id) {
  if (!confirm(`Delete class schedule slot ${id}?`)) return;
  let slots = getTimetable();
  slots = slots.filter(s => s.id !== id);
  saveTimetable(slots);
  showToast(`Timetable slot removed.`, "danger");
}

/* ==========================================================
   9. CAMPUS NOTICES MANAGEMENT
========================================================== */
function renderNoticesList() {
  const container = document.getElementById("noticesContainer");
  if (!container) return;

  const notices = getNotices();
  container.innerHTML = "";

  if (notices.length === 0) {
    container.innerHTML = `<div style="text-align: center; color: var(--text-muted); padding: 30px;">No circulars posted yet.</div>`;
    return;
  }

  notices.forEach(n => {
    const card = document.createElement("div");
    card.className = "card";
    card.style.cssText = "margin-bottom: 14px; padding: 18px; border-left: 4px solid var(--primary-light);";

    let catBadge = "badge-tech";
    if (n.category === "Urgent") catBadge = "badge-inactive";
    if (n.category === "Exam") catBadge = "badge-sports";
    if (n.category === "Academic") catBadge = "badge-academic";

    card.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge ${catBadge}">${n.category}</span>
            <span style="font-size: 0.78rem; color: var(--text-muted);"><i class="fas fa-users"></i> Audience: ${n.audience}</span>
            <span style="font-size: 0.78rem; color: var(--text-muted);"><i class="far fa-calendar-alt"></i> ${n.date}</span>
          </div>
          <h4 style="margin: 0 0 6px; font-size: 1.05rem; color: var(--text-main);">${n.title}</h4>
          <p style="margin: 0; font-size: 0.88rem; color: var(--text-muted); line-height: 1.4;">${n.content}</p>
        </div>
        <button class="btn-icon-danger" onclick="deleteNotice('${n.id}')" title="Delete Notice">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;
    container.appendChild(card);
  });
}

function handlePostNoticeSubmit(e) {
  e.preventDefault();
  const title = document.getElementById("noticeTitle").value.trim();
  const category = document.getElementById("noticeCategory").value;
  const audience = document.getElementById("noticeAudience").value;
  const content = document.getElementById("noticeContent").value.trim();

  const newNotice = {
    id: "NT-" + Math.floor(10 + Math.random() * 90),
    title,
    category,
    audience,
    date: new Date().toISOString().split("T")[0],
    content
  };

  const notices = getNotices();
  notices.unshift(newNotice);
  saveNotices(notices);

  closeAdminModal("postNoticeModal");
  e.target.reset();
  showToast(`Campus notice "${title}" published!`, "success");
}

function deleteNotice(id) {
  if (!confirm(`Delete announcement ${id}?`)) return;
  let notices = getNotices();
  notices = notices.filter(n => n.id !== id);
  saveNotices(notices);
  showToast(`Notice removed.`, "danger");
}

/* ==========================================================
   10. LIVE DASHBOARD KPIS & STATS
========================================================== */
function updateDashboardKPIs() {
  const students = getStudents();
  const events = getEvents();
  const timetables = getTimetable();
  const notices = getNotices();

  const kpiStudents = document.getElementById("kpiTotalStudents");
  const kpiEvents = document.getElementById("kpiTotalEvents");
  const kpiTimetable = document.getElementById("kpiTotalSlots");
  const kpiNotices = document.getElementById("kpiTotalNotices");

  if (kpiStudents) kpiStudents.textContent = students.length;
  if (kpiEvents) kpiEvents.textContent = events.length;
  if (kpiTimetable) kpiTimetable.textContent = timetables.length;
  if (kpiNotices) kpiNotices.textContent = notices.length;

  // Render recent preview lists on overview tab
  const recentEventsBox = document.getElementById("recentEventsOverview");
  if (recentEventsBox) {
    recentEventsBox.innerHTML = events.slice(0, 3).map(ev => `
      <div style="padding: 10px 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-weight: 600; font-size: 0.9rem;">${ev.title}</div>
          <div style="font-size: 0.78rem; color: var(--text-muted);"><i class="far fa-calendar"></i> ${ev.date} • ${ev.location}</div>
        </div>
        <span class="badge badge-tech">${ev.category}</span>
      </div>
    `).join("") || '<p style="color:var(--text-muted);">No events scheduled.</p>';
  }

  const recentStudentsBox = document.getElementById("recentStudentsOverview");
  if (recentStudentsBox) {
    recentStudentsBox.innerHTML = students.slice(0, 4).map(s => `
      <div style="padding: 10px 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=random" style="width: 28px; height: 28px; border-radius: 50%;" />
          <div>
            <div style="font-weight: 600; font-size: 0.88rem;">${s.name}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">${s.course}</div>
          </div>
        </div>
        <span class="badge ${s.status === 'Active' ? 'badge-active' : 'badge-inactive'}">${s.status}</span>
      </div>
    `).join("") || '<p style="color:var(--text-muted);">No students enrolled.</p>';
  }
}
