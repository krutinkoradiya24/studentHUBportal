
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Failed to load data");
        return await response.json();
    } catch (error) {
        console.error(error);
        return null;
    }
}
async function fetchStudents() { return await fetchData('../data/students.json'); }
async function fetchEvents() { return await fetchData('../data/events.json'); }
async function fetchFAQs() { return await fetchData('../data/faqs.json'); }
