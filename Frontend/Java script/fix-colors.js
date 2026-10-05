const fs = require('fs');
const path = require('path');

const dir = 'd:/3rd sem/STUDENTPORTAL/Frontend/html';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    
    content = content.replace(/background:\s*#f1f5f9/g, 'background: var(--bg-light)');
    content = content.replace(/color:\s*#64748b/g, 'color: var(--text-muted)');
    content = content.replace(/border:\s*1px\s+solid\s+#cbd5e1/g, 'border: 1px solid var(--border-color)');
    
    fs.writeFileSync(filePath, content, 'utf8');
}
console.log('Fixed inline colors');
