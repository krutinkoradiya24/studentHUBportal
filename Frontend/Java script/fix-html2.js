const fs = require('fs');
const path = require('path');

const dir = 'd:/3rd sem/STUDENTPORTAL/Frontend/html';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

const corruptedRegex = /border-radius:\s*50%;\s*width:\s*50px;\s*height:\s*50px;\s*object-fit:\s*contain;(\s*background:\s*[^;"]+;)?/g;

for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Replace all corrupted styles with border-radius: 8px;
    content = content.replace(corruptedRegex, 'border-radius: 8px;');
    
    // Fix the logo image explicitly
    const logoRegex = /<img\s*src="logo\.png"\s*alt="CHARUSAT Logo"\s*style="[^"]*"/g;
    content = content.replace(logoRegex, '<img src="logo.png" alt="CHARUSAT Logo" style="height: 50px; background: white; padding: 5px; border-radius: 50%; width: 50px; object-fit: contain;"');
    
    fs.writeFileSync(filePath, content, 'utf8');
}
console.log('Fixed HTML files 2');
