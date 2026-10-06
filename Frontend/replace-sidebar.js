const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'html');
const files = fs.readdirSync(dir);

const targetString = '<a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>';
const replacementString = '<a href="events.html"><i class="fas fa-calendar-alt"></i> Events</a>\n        <a href="faqs.html"><i class="fas fa-question-circle"></i> FAQs</a>\n        <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>';

files.forEach(file => {
    if (file.endsWith('.html')) {
        const filePath = path.join(dir, file);
        let content = fs.readFileSync(filePath, 'utf8');
        if (content.includes(targetString)) {
            content = content.replace(targetString, replacementString);
            fs.writeFileSync(filePath, content, 'utf8');
            console.log('Updated ' + file);
        }
    }
});
