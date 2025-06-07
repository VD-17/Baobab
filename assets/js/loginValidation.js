document.addEventListener('DOMContentLoaded', function() {
    const form3 = document.getElementById('loginform');

    // Form 3 fields 
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    // Error display function
    function showError(input, message) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
        }
        input.classList.add('error-border');
    }

    // Success function
    function showSuccess(input) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = '';
            errorContainer.style.display = 'none';
        }
        input.classList.remove('error-border');
    }

    // Get field name for error messages
    function getFieldName(input) {
        return input.id.charAt(0).toUpperCase() + input.id.slice(1).replace(/([A-Z])/g, ' $1');
    }

    // Check email
    function checkEmail(input) {
        const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        const value = input.value.trim();
        if (!emailRegex.test(value)) {
            showError(input, 'Email is not valid');
            return false;
        }
        if (value.length > 254) {
            showError(input, 'Email is too long');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check password strength
    function checkPasswordStrength(input) {
        const value = input.value.trim();
        const errors = [];
        if (value.length < 8) errors.push('at least 8 characters');
        if (!/[a-z]/.test(value)) errors.push('a lowercase letter');
        if (!/[A-Z]/.test(value)) errors.push('an uppercase letter');
        if (!/\d/.test(value)) errors.push('a number');
        if (!/[@$!%*?&]/.test(value)) errors.push('a special character (@$!%*?&)');
        if (errors.length > 0) {
            showError(input, `Password must include: ${errors.join(', ')}`);
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Real-time validation event listeners
    if (email) {
        email.addEventListener('blur', () => checkEmail(email));
    }
    if (password) {
        password.addEventListener('blur', () => checkPasswordStrength(password));
    }
   

    if (form3) {
        form3.addEventListener('submit', function(e) {
            e.preventDefault();
            const isEmailValid = checkEmail(email);
            const isPasswordValid = checkPasswordStrength(password);

            if (isEmailValid && isPasswordValid) {
                console.log('Form 3 is valid and ready to submit');
                this.submit();
            }
        });
    }
});