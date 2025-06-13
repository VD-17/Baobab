// Auto-hide notification after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('success-notification');
    if (notification) {
        setTimeout(() => {
            closeNotification();
        }, 4000);
    }
});

function closeNotification() {
    const notification = document.getElementById('success-notification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}