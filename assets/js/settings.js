// settings.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialize toggle states
    initializeToggles();
    
    // Add event listeners to all toggle switches
    setupToggleListeners();
    
    // Add event listeners to save buttons
    setupSaveButtons();
    
    // Add password toggle functionality
    setupPasswordToggles();
    
    // Add password form validation
    setupPasswordForm();
});

function initializeToggles() {
    // Get all toggle switches
    const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
    
    toggles.forEach((toggle, index) => {
        // Set default states based on the toggle position
        switch(index) {
            case 0: // New message notification
                toggle.checked = true;
                break;
            case 1: // Show email address
                toggle.checked = true;
                break;
            case 2: // Show phone number
                toggle.checked = false;
                break;
            case 3: // Show location
                toggle.checked = false;
                break;
            default:
                toggle.checked = false;
        }
        
        // Store initial state
        toggle.dataset.initialState = toggle.checked;
    });
    
    // Load saved preferences from localStorage if they exist
    loadSavedPreferences();
}

function setupToggleListeners() {
    const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            // Add visual feedback when toggle changes
            const slider = this.nextElementSibling;
            slider.style.transform = 'scale(1.05)';
            
            setTimeout(() => {
                slider.style.transform = 'scale(1)';
            }, 150);
            
            // You can add additional logic here for immediate actions
            handleToggleChange(this);
        });
    });
}

function handleToggleChange(toggle) {
    const toggleIndex = Array.from(document.querySelectorAll('.switch input[type="checkbox"]')).indexOf(toggle);
    const toggleNames = ['newMessage', 'showEmail', 'showPhone', 'showLocation'];
    const toggleName = toggleNames[toggleIndex];
    
    console.log(`${toggleName} toggled to:`, toggle.checked);
    
    // You can add specific logic for each toggle here
    switch(toggleName) {
        case 'newMessage':
            handleNotificationToggle(toggle.checked);
            break;
        case 'showEmail':
            handleEmailVisibilityToggle(toggle.checked);
            break;
        case 'showPhone':
            handlePhoneVisibilityToggle(toggle.checked);
            break;
        case 'showLocation':
            handleLocationVisibilityToggle(toggle.checked);
            break;
    }
}

function setupPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordField = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

function setupPasswordForm() {
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const currentPassword = document.getElementById('currentPassword').value;
            
            if (newPassword.length < 8) {
                e.preventDefault();
                showErrorMessage('New password must be at least 8 characters long.');
                return;
            }
            
            if (newPassword === currentPassword) {
                e.preventDefault();
                showErrorMessage('New password must be different from current password.');
                return;
            }
            
            if (currentPassword.trim() === '' || newPassword.trim() === '') {
                e.preventDefault();
                showErrorMessage('Both current and new password are required.');
                return;
            }
        });
    }
}

function handleNotificationToggle(isEnabled) {
    if (isEnabled) {
        console.log('Push notifications enabled');
        // Request notification permission if not already granted
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    } else {
        console.log('Push notifications disabled');
    }
}

function handleEmailVisibilityToggle(isVisible) {
    console.log('Email visibility:', isVisible ? 'Public' : 'Private');
}

function handlePhoneVisibilityToggle(isVisible) {
    console.log('Phone visibility:', isVisible ? 'Public' : 'Private');
}

function handleLocationVisibilityToggle(isVisible) {
    console.log('Location visibility:', isVisible ? 'Public' : 'Private');
}

function setupSaveButtons() {
    // Notification settings save button
    const notificationSaveBtn = document.querySelector('#notification button.white');
    if (notificationSaveBtn) {
        notificationSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            saveNotificationSettings();
        });
    }
    
    // Privacy settings save button
    const privacySaveBtn = document.querySelector('#privacy button.white');
    if (privacySaveBtn) {
        privacySaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            savePrivacySettings();
        });
    }
    
    // Delete account button
    const deleteBtn = document.querySelector('#delete button.white');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleDeleteAccount();
        });
    }
}

function saveNotificationSettings() {
    const newMessageToggle = document.querySelectorAll('.switch input[type="checkbox"]')[0];
    
    const settings = {
        newMessage: newMessageToggle.checked
    };
    
    // Save to localStorage
    localStorage.setItem('notificationSettings', JSON.stringify(settings));
    
    // Show success message
    showSuccessMessage('Notification settings saved successfully!');
    
    // Here you would typically send the data to your server
    console.log('Saving notification settings:', settings);
}

function savePrivacySettings() {
    const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
    
    const settings = {
        showEmail: toggles[1].checked,
        showPhone: toggles[2].checked,
        showLocation: toggles[3].checked
    };
    
    // Save to localStorage
    localStorage.setItem('privacySettings', JSON.stringify(settings));
    
    // Show success message
    showSuccessMessage('Privacy settings saved successfully!');
    
    // Here you would typically send the data to your server
    console.log('Saving privacy settings:', settings);
}

function handleDeleteAccount() {
    const confirmation = confirm('Are you sure you want to delete your account? This action cannot be undone.');
    
    if (confirmation) {
        const finalConfirmation = confirm('This will permanently delete all your data. Are you absolutely sure?');
        
        if (finalConfirmation) {
            console.log('Account deletion requested');
            // Here you would typically send the request to your server
            alert('Account deletion request submitted. You will receive a confirmation email.');
        }
    }
}

function loadSavedPreferences() {
    // Load notification settings
    const savedNotifications = localStorage.getItem('notificationSettings');
    if (savedNotifications) {
        const settings = JSON.parse(savedNotifications);
        const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
        if (toggles[0]) toggles[0].checked = settings.newMessage;
    }
    
    // Load privacy settings
    const savedPrivacy = localStorage.getItem('privacySettings');
    if (savedPrivacy) {
        const settings = JSON.parse(savedPrivacy);
        const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
        if (toggles[1]) toggles[1].checked = settings.showEmail;
        if (toggles[2]) toggles[2].checked = settings.showPhone;
        if (toggles[3]) toggles[3].checked = settings.showLocation;
    }
}

function showSuccessMessage(message) {
    // Create and show a temporary success message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'success-message';
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => messageDiv.remove(), 300);
    }, 3000);
}

function showErrorMessage(message) {
    // Create and show a temporary error message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'error-message';
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #dc3545;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => messageDiv.remove(), 300);
    }, 4000);
}

// Add CSS animations for messages
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);