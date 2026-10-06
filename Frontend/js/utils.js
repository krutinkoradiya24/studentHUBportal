export function displayMessage(containerId, message, isError = false) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = `<div style="padding: 15px; border-radius: 8px; background: ${isError ? '#ffebee' : '#e3f2fd'}; color: ${isError ? '#c62828' : '#1565c0'}; font-weight: bold; text-align: center;">${message}</div>`;
}

export function clearContainer(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = '';
    }
}
