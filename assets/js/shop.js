// Auto-hide notification after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('cart-notification');
    if (notification) {
        setTimeout(() => {
            closeNotification();
        }, 4000);
    }
});

function closeNotification() {
    const notification = document.getElementById('cart-notification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Enhanced cart button functionality
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function(e) {
        // Add loading state
        const icon = this.querySelector('i');
        const originalClass = icon.className;
        
        // Show loading spinner
        icon.className = 'fa-solid fa-check';
        this.disabled = false;
        
        // If form submission fails, restore original state
        setTimeout(() => {
            if (!this.closest('form').checkValidity()) {
                icon.className = originalClass;
                this.disabled = false;
            }
        }, 100);
    });
});

// Reset success state after a few seconds
setTimeout(() => {
    document.querySelectorAll('.success-icon').forEach(icon => {
        icon.className = 'fa-solid fa-cart-shopping';
        icon.closest('.action-btn').classList.remove('success');
    });
}, 3000);