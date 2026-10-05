const fs = require('fs');
const stylePath = 'd:/3rd sem/STUDENTPORTAL/Frontend/css/style.css';
let content = fs.readFileSync(stylePath, 'utf8');

// Replace :root variables
const rootRegex = /:root\s*\{[^}]*\}/;
const newRoot = :root {
    --primary-color: #1E3A8A;
    --primary-light: #2563EB;
    --secondary-color: #2563EB; /* Keeping this for backwards compatibility */
    --accent-color: #38BDF8;
    --accent-hover: #0EA5E9;
    --bg-light: #F4F7FC; /* Fallback */
    --card-bg: #FFFFFF;
    --text-main: #172033;
    --text-muted: #64748B;
    --border-color: #E2E8F0;
    --success: #16A34A;
    --warning: #F59E0B;
    --danger: #DC2626;
    --shadow-soft: 0 8px 25px rgba(30, 58, 138, 0.08);
    --shadow-hover: 0 15px 30px rgba(30, 58, 138, 0.14);
    --border-radius: 16px;
    --transition: all 0.25s ease;
};
content = content.replace(rootRegex, newRoot);

// Update Body Background
content = content.replace(/body\s*\{[^}]*\}/, ody {
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #F8FAFF 0%, #EEF4FF 100%);
    background-attachment: fixed;
    color: var(--text-main);
    transition: background-color 0.3s ease, color 0.3s ease;
    line-height: 1.6;
});

// Update Sidebar Nav
const sidebarNavRegex = /\.sidebar-nav\s*a\s*\{[^}]*\}/;
content = content.replace(sidebarNavRegex, .sidebar-nav a {
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: var(--transition);
    border-radius: 10px;
    margin: 0 15px;
    color: var(--text-muted);
    position: relative;
});

// Add active and hover for sidebar
const sidebarHoverRegex = /\.sidebar-nav\s*a:hover,\s*\.sidebar-nav\s*a\.active\s*\{[^}]*\}/;
content = content.replace(sidebarHoverRegex, .sidebar-nav a:hover {
    background: #EFF6FF;
    color: var(--primary-color);
}
.sidebar-nav a.active {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
}
.sidebar-nav a.active::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 50%;
    transform: translateY(-50%);
    height: 60%;
    width: 4px;
    background: var(--accent-color);
    border-radius: 0 4px 4px 0;
});

// Update Header
const headerRegex = /\.top-header\s*\{[^}]*\}/;
content = content.replace(headerRegex, .top-header {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 100;
});

// Update Cards
const cardRegex = /\.card\s*\{[^}]*\}/g;
let isFirstCard = true;
content = content.replace(cardRegex, (match) => {
    if (isFirstCard) {
        isFirstCard = false;
        return .card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    padding: 25px;
    border: 1px solid var(--border-color);
    transition: var(--transition);
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
};
    }
    return match;
});

// Update Buttons
content = content.replace(/\.btn-primary\s*\{[^}]*\}/, .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    border: none;
});
content = content.replace(/\.btn-primary:hover\s*\{[^}]*\}/, .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
});

// Update Forms
content = content.replace(/\.form-group\s*input\s*\{[^}]*\}/, .form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    outline: none;
    background: var(--card-bg);
    color: var(--text-main);
    transition: var(--transition);
});
content = content.replace(/\.form-group\s*input:focus\s*\{[^}]*\}/, .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
});

fs.writeFileSync(stylePath, content, 'utf8');
console.log("Updated style.css");
