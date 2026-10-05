const fs = require('fs');
const path = require('path');

const stylePath = 'd:/3rd sem/STUDENTPORTAL/Frontend/css/style.css';
let content = fs.readFileSync(stylePath, 'utf8');

// 1. Replace the LIGHT MODE :root completely
const rootRegex = /:root\s*\{[^}]*\}/;
const newRoot = :root {
  --primary: #1E3A8A;
  --primary-light: #2563EB;
  --primary-color: #1E3A8A;
  --secondary-color: #1E3A8A;
  --accent: #38BDF8;
  --accent-color: #38BDF8;
  --background: #F4F7FC;
  --bg-light: #F4F7FC;
  --surface: #FFFFFF;
  --card-bg: #FFFFFF;
  --white: #FFFFFF;
  --text: #172033;
  --text-main: #172033;
  --text-light: #64748B;
  --text-muted: #64748B;
  --border: #E2E8F0;
  --border-color: #E2E8F0;
  --success: #16A34A;
  --warning: #F59E0B;
  --danger: #DC2626;
  --shadow: 0 8px 25px rgba(30, 58, 138, 0.08);
  --shadow-hover: 0 15px 30px rgba(30, 58, 138, 0.14);
  --border-radius: 16px;
  --transition: all 0.25s ease;
};

content = content.replace(rootRegex, newRoot);

// 2. Append all new light mode and global component overrides at the end
const upgrades = 
/* ==================================================
   MASSIVE LIGHT MODE DESIGN UPGRADE
================================================== */

/* BODY & BACKGROUND */
:root:not(.dark-theme) body {
    background: linear-gradient(135deg, #F8FAFF 0%, #EEF4FF 100%);
    background-attachment: fixed;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}

/* HEADER */
:root:not(.dark-theme) .top-header {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid var(--border-color);
}
.header-right .notification-icon,
.header-right .theme-toggle-btn,
.header-right .user-profile,
.search-bar {
    transition: var(--transition);
}
.header-right .notification-icon:hover,
.header-right .theme-toggle-btn:hover {
    transform: scale(1.1);
    color: var(--primary-light);
}
.user-profile:hover {
    background: #EFF6FF;
    border-radius: 20px;
    padding: 0 10px;
}
.search-bar:focus-within {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    border-color: var(--primary-light);
}

/* SIDEBAR */
:root:not(.dark-theme) .sidebar {
    background: var(--card-bg);
    border-right: 1px solid var(--border-color);
    box-shadow: 2px 0 15px rgba(0,0,0,0.03);
    color: var(--text-main);
}
:root:not(.dark-theme) .sidebar-header {
    border-bottom: 1px solid var(--border-color);
}
:root:not(.dark-theme) .sidebar-nav a {
    color: var(--text-muted);
    border-radius: 12px;
    margin: 4px 15px;
    border: none;
    position: relative;
    overflow: hidden;
}
:root:not(.dark-theme) .sidebar-nav a:hover {
    background: #EFF6FF;
    color: var(--primary-color);
    opacity: 1;
}
:root:not(.dark-theme) .sidebar-nav a.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    opacity: 1;
    border: none;
}
/* Animated indicator for active item */
:root:not(.dark-theme) .sidebar-nav a.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 60%;
    background: var(--accent);
    border-radius: 0 4px 4px 0;
    animation: slideIn 0.3s ease forwards;
}
@keyframes slideIn {
    from { transform: translateY(-50%) scaleY(0); }
    to { transform: translateY(-50%) scaleY(1); }
}

/* CARDS */
.card, .auth-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}
.card:hover, .auth-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

/* STAT CARDS */
.stat-card {
    border-radius: 16px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

/* BUTTONS */
.btn-primary, button[type="submit"], .btn-action {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    transition: var(--transition);
}
.btn-primary:hover, button[type="submit"]:hover, .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
    color: white;
}
.btn-secondary {
    background: #EFF6FF;
    color: var(--primary);
    border-radius: 10px;
}
.btn-secondary:hover {
    background: #DBEAFE;
    transform: translateY(-2px);
}

/* TABLES */
:root:not(.dark-theme) table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
:root:not(.dark-theme) th {
    background: #F1F5F9;
    color: #334155;
    border-bottom: 1px solid var(--border-color);
    padding: 12px 15px;
}
:root:not(.dark-theme) td {
    background: white;
    border-bottom: 1px solid var(--border-color);
    transition: var(--transition);
}
:root:not(.dark-theme) tr:hover td {
    background: #F8FAFC;
}

/* FORMS */
input, select, textarea {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 14px;
    transition: var(--transition);
    font-family: 'Inter', sans-serif;
}
input:focus, select:focus, textarea:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    outline: none;
}
label {
    font-weight: 600;
}

/* FOOTER */
:root:not(.dark-theme) .site-footer {
    background: #0F172A;
    color: #CBD5E1;
    border-top: none;
}
:root:not(.dark-theme) .site-footer h3,
:root:not(.dark-theme) .site-footer h4 {
    color: white;
}
:root:not(.dark-theme) .site-footer a {
    color: #CBD5E1;
    transition: var(--transition);
}
:root:not(.dark-theme) .site-footer a:hover {
    color: #60A5FA;
}

/* FAQ CARDS */
.faq-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    margin-bottom: 15px;
    transition: var(--transition);
    overflow: hidden;
}
.faq-card.open {
    border-color: var(--primary-light);
    background: #EFF6FF;
}

/* MODAL */
.modal-overlay {
    background: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.modal-content {
    background: var(--card-bg);
    border-radius: 18px;
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.20);
    transform: scale(0.95);
    opacity: 0;
    transition: var(--transition);
}
.modal-overlay.active .modal-content {
    transform: scale(1);
    opacity: 1;
}
;

content += upgrades;
fs.writeFileSync(stylePath, content, 'utf8');
console.log("Updated style.css");
