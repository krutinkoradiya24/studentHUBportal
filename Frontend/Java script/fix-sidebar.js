const fs = require('fs');
const path = require('path');

const dir = 'd:/3rd sem/STUDENTPORTAL/Frontend/html';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    // 1. Fix Sidebar Header
    const sidebarHeaderRegex = /<div class="sidebar-header">\s*<img\s*src="logo\.png"\s*alt="CHARUSAT Logo"\s*style="([^"]*)"\s*\/>\s*(<button[^>]*>.*?<\/button>)?\s*<\/div>/g;
    
    content = content.replace(sidebarHeaderRegex, (match, style, button) => {
        return \<div class="sidebar-header" style="justify-content: flex-start; gap: 12px; padding: 25px 20px;">
          <img src="logo.png" alt="CHARUSAT Logo" style="\" />
          <span style="font-weight: 700; font-size: 1.3rem; color: var(--primary); letter-spacing: 0.5px;">Student Hub</span>
          \
        </div>\;
    });

    // 2. Add 'active' class to the correct navigation item
    // First, remove any existing active class from sidebar links
    content = content.replace(/<a([^>]+)class="active"([^>]*)>/g, '<a>');
    content = content.replace(/<a([^>]+)class="active\s*([^"]*)"([^>]*)>/g, '<a="">');
    content = content.replace(/<a([^>]+)class='active\s*([^']*)'([^>]*)>/g, '<a="">');

    // Then, add it back to the correct file
    const hrefRegex = new RegExp(\<a([^>]+href=["']\["'][^>]*)>\, 'g');
    content = content.replace(hrefRegex, '<a class="active">');

    fs.writeFileSync(filePath, content, 'utf8');
}
console.log('Fixed Sidebar layout and Active states');
