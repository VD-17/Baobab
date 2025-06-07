document.addEventListener('DOMContentLoaded', function() {
    // Form and field references
    const form1 = document.getElementById('signUpform');
    const form2 = document.getElementById('signupForm2');
    const form3 = document.getElementById('loginform');

    // Form 1 fields
    const firstname = document.getElementById('firstname');
    const lastname = document.getElementById('lastname');
    const email = document.getElementById('email');
    const phoneNumber = document.getElementById('phoneNumber');
    const password = document.getElementById('password');
    const terms = document.getElementById('terms');

    // Form 2 fields
    const streetAddress = document.getElementById('streetAddress');
    const suburb = document.getElementById('suburb');
    const postalCode = document.getElementById('postalCode');
    const province = document.getElementById('province');
    const city = document.getElementById('city');
    const profilePicture = document.getElementById('profilePicture');

    // Form 3 fields 
    const loginEmail = document.getElementById('email');
    const loginPassword = document.getElementById('password');

    // Province to city mapping for dynamic dropdown
    const provinceCityMap = {
        'Eastern Cape': ['Port Elizabeth', 'East London'],
        'Free State': ['Bloemfontein'],
        'Gauteng': ['Johannesburg', 'Pretoria'],
        'KwaZulu-Natal': ['Durban'],
        'Limpopo': ['Polokwane'],
        'Mpumalanga': ['Nelspruit'],
        'Northern Cape': ['Kimberley'],
        'North West': ['Mahikeng'],
        'Western Cape': ['Cape Town']
    };

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

    // Check required fields
    function checkRequired(inputArray) {
        let isValid = true;
        inputArray.forEach(input => {
            if (input.value.trim() === '') {
                showError(input, `${getFieldName(input)} is required`);
                isValid = false;
            } else {
                showSuccess(input);
            }
        });
        return isValid;
    }

    // Get field name for error messages
    function getFieldName(input) {
        return input.id.charAt(0).toUpperCase() + input.id.slice(1).replace(/([A-Z])/g, ' $1');
    }

    // Check input length
    function checkLength(input, min, max) {
        const value = input.value.trim();
        if (value.length < min) {
            showError(input, `${getFieldName(input)} must be at least ${min} characters`);
            return false;
        } else if (value.length > max) {
            showError(input, `${getFieldName(input)} must be less than ${max} characters`);
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check name (allows letters, spaces, hyphens, apostrophes)
    function checkName(input) {
        const nameRegex = /^[a-zA-Z\s'-]{2,}$/;
        if (!nameRegex.test(input.value.trim())) {
            showError(input, `${getFieldName(input)} can only contain letters, spaces, hyphens, and apostrophes`);
            return false;
        }
        showSuccess(input);
        return true;
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

    // Check address (streetAddress, suburb)
    function checkAddress(input) {
        const addressRegex = /^[a-zA-Z0-9\s#\-.,/]+$/;
        if (!addressRegex.test(input.value.trim())) {
            showError(input, `${getFieldName(input)} can only contain letters, numbers, spaces, and common punctuation`);
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check province
    function checkProvince(input) {
        if (input.value === '') {
            showError(input, 'Please select a province');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check city
    function checkCity(input) {
        if (input.value === '') {
            showError(input, 'Please select a city');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check postal code
    function checkPostalCode(input) {
        const postalCodeRegex = /^\d{4}$/;
        if (!postalCodeRegex.test(input.value.trim())) {
            showError(input, 'Postal code must be 4 digits');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check phone number
    function checkPhoneNumber(input) {
        const phoneRegex = /^(?:\+27\s?|0)\d{2}\s?\d{3}\s?\d{4}$/;
        const normalizedPhone = input.value.trim().replace(/\s/g, '');
        if (!phoneRegex.test(input.value.trim())) {
            showError(input, 'Please enter a valid South African phone number (e.g., 0821234567 or +27821234567)');
            return false;
        }
        input.value = normalizedPhone;
        showSuccess(input);
        return true;
    }

    // Check profile picture
    function checkProfilePicture(input) {
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (input.files.length === 0) {
            showSuccess(input);
            return true;
        }
        const file = input.files[0];
        if (!allowedTypes.includes(file.type)) {
            showError(input, 'Profile picture must be JPEG or PNG');
            return false;
        }
        if (file.size > 5 * 1024 * 1024) {
            showError(input, 'Profile picture must be less than 5MB');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check terms checkbox
    function checkTerms(input) {
        if (!input.checked) {
            showError(input, 'You must agree to the Terms & Conditions');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Dynamic city dropdown based on province
    if (province && city) {
        province.addEventListener('change', function() {
            city.innerHTML = '<option value="">Select City</option>';
            if (provinceCityMap[this.value]) {
                provinceCityMap[this.value].forEach(cityName => {
                    const option = document.createElement('option');
                    option.value = cityName;
                    option.text = cityName;
                    city.appendChild(option);
                });
            }
            checkCity(city);
        });
    }

    // Real-time validation event listeners
    if (firstname) {
        firstname.addEventListener('blur', () => {
            checkLength(firstname, 2, 30);
            checkName(firstname);
        });
    }
    if (lastname) {
        lastname.addEventListener('blur', () => {
            checkLength(lastname, 2, 30);
            checkName(lastname);
        });
    }
    if (email) {
        email.addEventListener('blur', () => checkEmail(email));
    }
    if (phoneNumber) {
        phoneNumber.addEventListener('blur', () => checkPhoneNumber(phoneNumber));
    }
    if (password) {
        password.addEventListener('blur', () => checkPasswordStrength(password));
    }
    if (terms) {
        terms.addEventListener('change', () => checkTerms(terms));
    }
    if (streetAddress) {
        streetAddress.addEventListener('blur', () => checkAddress(streetAddress));
    }
    if (suburb) {
        suburb.addEventListener('blur', () => {
            if (suburb.value.trim() !== '') checkAddress(suburb); // Suburb is optional
        });
    }
    if (postalCode) {
        postalCode.addEventListener('blur', () => checkPostalCode(postalCode));
    }
    if (province) {
        province.addEventListener('change', () => checkProvince(province));
    }
    if (city) {
        city.addEventListener('change', () => checkCity(city));
    }
    if (profilePicture) {
        profilePicture.addEventListener('change', () => {
            checkProfilePicture(profilePicture);
            const fileName = profilePicture.files[0] ? profilePicture.files[0].name : 'No file chosen';
            const fileLabel = document.querySelector('.file-upload-label span');
            if (fileLabel) fileLabel.textContent = fileName;
        });
    }

    if (form1) {
        form1.addEventListener('submit', function(e) {
            e.preventDefault();
            const isFirstNameValid = checkName(firstname) && checkLength(firstname, 2, 30);
            const isLastNameValid = checkName(lastname) && checkLength(lastname, 2, 30);
            const isEmailValid = checkEmail(email);
            const isPhoneNumberValid = checkPhoneNumber(phoneNumber);
            const isPasswordValid = checkPasswordStrength(password);
            const isTermsChecked = checkTerms(terms);

            if (isFirstNameValid && isLastNameValid && isEmailValid && isPhoneNumberValid && isPasswordValid && isTermsChecked) {
                console.log('Form 1 is valid and ready to submit');
                this.submit();
            }
        });
    }

    if (form2) {
        form2.addEventListener('submit', function(e) {
            e.preventDefault();
            const isStreetAddressValid = checkAddress(streetAddress);
            const isSuburbValid = suburb.value.trim() === '' || checkAddress(suburb); // Suburb is optional
            const isPostalCodeValid = checkPostalCode(postalCode);
            const isProvinceValid = checkProvince(province);
            const isCityValid = checkCity(city);
            const isProfilePictureValid = checkProfilePicture(profilePicture);

            if (isStreetAddressValid && isSuburbValid && isPostalCodeValid && isProvinceValid && isCityValid && isProfilePictureValid) {
                console.log('Form 2 is valid and ready to submit');
                this.submit();
            }
        });
    }
});