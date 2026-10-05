
function showLoading(containerId, message) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = `<div class="loading-state" aria-live="polite"><i class="fas fa-spinner fa-spin"></i> ${message}</div>`;
}
function hideLoading(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const loader = container.querySelector('.loading-state');
    if (loader) loader.remove();
}
function showError(containerId, retryCallback) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = `<div class="error-state" aria-live="polite">Unable to load data. Please try again.<br><button class="btn btn-primary retry-btn" style="margin-top: 10px;">Try Again</button></div>`;
    container.querySelector('.retry-btn').addEventListener('click', retryCallback);
}
function showEmptyMessage(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = `<div class="empty-state" aria-live="polite">No records found.</div>`;
}
function escapeHTML(str) {
    return str.toString().replace(/[&<>'"]/g, tag => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[tag]));
}
