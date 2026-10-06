import { initEvents } from './events.js';
import { initStudents } from './students.js';
import { initFaqs } from './faqs.js';

document.addEventListener('DOMContentLoaded', () => {
    // Check which container exists to determine the page
    if (document.getElementById('eventContainer')) {
        initEvents();
    }
    
    if (document.getElementById('studentContainer')) {
        initStudents();
    }
    
    if (document.getElementById('faqContainer')) {
        initFaqs();
    }
});
