/**
 * Client-Side Form Validation
 * Provides real-time validation feedback and form submission validation
 */

/**
 * Show error message for a field
 * @param {HTMLElement} input - The input element
 * @param {string} message - Error message to display
 */
function showFieldError(input, message) {
    // Remove existing error message
    const existingError = input.parentElement.querySelector('.error-text-client');
    if (existingError) {
        existingError.remove();
    }

    // Add error class to input
    input.classList.add('invalid');
    input.classList.remove('valid');

    // Create and insert error message
    const errorSpan = document.createElement('span');
    errorSpan.className = 'error-text error-text-client';
    errorSpan.textContent = message;
    input.parentElement.appendChild(errorSpan);
}

/**
 * Show success state for a field
 * @param {HTMLElement} input - The input element
 */
function showFieldSuccess(input) {
    input.classList.remove('invalid');
    input.classList.add('valid');

    // Remove existing client-side error message
    const existingError = input.parentElement.querySelector('.error-text-client');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Validate name field
 * @param {string} name - Name to validate
 * @returns {string|null} Error message or null if valid
 */
function validateName(name) {
    if (!name || name.trim() === '') {
        return 'Name is required';
    }
    if (name.length > 50) {
        return 'Name must be 50 characters or less';
    }
    if (!/^[a-zA-Z\s'-]+$/.test(name)) {
        return 'Name can only contain letters, spaces, hyphens, and apostrophes';
    }
    return null;
}

/**
 * Validate email field
 * @param {string} email - Email to validate
 * @returns {string|null} Error message or null if valid
 */
function validateEmail(email) {
    if (!email || email.trim() === '') {
        return 'Email is required';
    }
    if (email.length > 50) {
        return 'Email must be 50 characters or less';
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return 'Please enter a valid email address';
    }
    return null;
}

/**
 * Validate phone field
 * @param {string} phone - Phone to validate
 * @returns {string|null} Error message or null if valid
 */
function validatePhone(phone) {
    if (!phone || phone.trim() === '') {
        return 'Phone number is required';
    }
    // Remove spaces, dashes, parentheses for validation
    const cleaned = phone.replace(/[\s\-\(\)]/g, '');
    if (!/^[0-9]{7,15}$/.test(cleaned)) {
        return 'Phone number must be 7-15 digits';
    }
    return null;
}

/**
 * Validate address field
 * @param {string} address - Address to validate
 * @returns {string|null} Error message or null if valid
 */
function validateAddress(address) {
    if (!address || address.trim() === '') {
        return 'Address is required';
    }
    if (address.length > 100) {
        return 'Address must be 100 characters or less';
    }
    return null;
}

/**
 * Validate location field
 * @param {string} location - Location to validate
 * @returns {string|null} Error message or null if valid
 */
function validateLocation(location) {
    if (!location || location.trim() === '') {
        return 'Destination location is required';
    }
    if (location.length > 100) {
        return 'Location must be 100 characters or less';
    }
    return null;
}

/**
 * Validate guests field
 * @param {string} guests - Number of guests
 * @returns {string|null} Error message or null if valid
 */
function validateGuests(guests) {
    if (!guests || guests.trim() === '') {
        return 'Number of guests is required';
    }
    const num = parseInt(guests, 10);
    if (isNaN(num) || num < 1 || num > 50) {
        return 'Number of guests must be between 1 and 50';
    }
    return null;
}

/**
 * Validate date field
 * @param {string} date - Date to validate
 * @param {string} fieldName - Name of the field for error message
 * @returns {string|null} Error message or null if valid
 */
function validateDate(date, fieldName) {
    if (!date || date.trim() === '') {
        return fieldName.charAt(0).toUpperCase() + fieldName.slice(1) + ' date is required';
    }

    // Check if date is valid format
    const dateObj = new Date(date);
    if (isNaN(dateObj.getTime())) {
        return 'Please enter a valid date';
    }

    // Check if date is not in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const inputDate = new Date(date);
    inputDate.setHours(0, 0, 0, 0);

    if (inputDate < today) {
        return fieldName.charAt(0).toUpperCase() + fieldName.slice(1) + ' date cannot be in the past';
    }

    return null;
}

/**
 * Validate date dependency (leaving date must be after arrival date)
 * @param {string} arrivals - Arrival date
 * @param {string} leaving - Leaving date
 * @returns {string|null} Error message or null if valid
 */
function validateDateDependency(arrivals, leaving) {
    if (!arrivals || !leaving) {
        return null; // Let individual date validation handle empty fields
    }

    const arrivalsDate = new Date(arrivals);
    const leavingDate = new Date(leaving);

    if (leavingDate <= arrivalsDate) {
        return 'Leaving date must be after arrival date';
    }

    return null;
}

/**
 * Validate username field
 * @param {string} username - Username to validate
 * @returns {string|null} Error message or null if valid
 */
function validateUsername(username) {
    if (!username || username.trim() === '') {
        return 'Username is required';
    }
    if (username.length > 20) {
        return 'Username must be 20 characters or less';
    }
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        return 'Username can only contain letters, numbers, and underscores';
    }
    return null;
}

/**
 * Validate password field
 * @param {string} password - Password to validate
 * @returns {string|null} Error message or null if valid
 */
function validatePassword(password) {
    if (!password || password === '') {
        return 'Password is required';
    }
    if (password.length < 6) {
        return 'Password must be at least 6 characters';
    }
    return null;
}

/**
 * Validate password confirmation
 * @param {string} password - Password
 * @param {string} confirmPassword - Confirmation password
 * @returns {string|null} Error message or null if valid
 */
function validatePasswordConfirmation(password, confirmPassword) {
    if (!confirmPassword || confirmPassword === '') {
        return 'Please confirm your password';
    }
    if (password !== confirmPassword) {
        return 'Passwords do not match';
    }
    return null;
}

/**
 * Initialize booking form validation
 */
function initBookingFormValidation() {
    const form = document.querySelector('.book-form');
    if (!form) return;

    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('address');
    const locationInput = document.getElementById('location');
    const guestsInput = document.getElementById('guests');
    const arrivalsInput = document.getElementById('arrivals');
    const leavingInput = document.getElementById('leaving');

    // Real-time validation on blur
    if (nameInput) {
        nameInput.addEventListener('blur', () => {
            const error = validateName(nameInput.value);
            if (error) {
                showFieldError(nameInput, error);
            } else {
                showFieldSuccess(nameInput);
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', () => {
            const error = validateEmail(emailInput.value);
            if (error) {
                showFieldError(emailInput, error);
            } else {
                showFieldSuccess(emailInput);
            }
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', () => {
            const error = validatePhone(phoneInput.value);
            if (error) {
                showFieldError(phoneInput, error);
            } else {
                showFieldSuccess(phoneInput);
            }
        });
    }

    if (addressInput) {
        addressInput.addEventListener('blur', () => {
            const error = validateAddress(addressInput.value);
            if (error) {
                showFieldError(addressInput, error);
            } else {
                showFieldSuccess(addressInput);
            }
        });
    }

    if (locationInput) {
        locationInput.addEventListener('blur', () => {
            const error = validateLocation(locationInput.value);
            if (error) {
                showFieldError(locationInput, error);
            } else {
                showFieldSuccess(locationInput);
            }
        });
    }

    if (guestsInput) {
        guestsInput.addEventListener('blur', () => {
            const error = validateGuests(guestsInput.value);
            if (error) {
                showFieldError(guestsInput, error);
            } else {
                showFieldSuccess(guestsInput);
            }
        });
    }

    if (arrivalsInput) {
        arrivalsInput.addEventListener('change', () => {
            const error = validateDate(arrivalsInput.value, 'arrival');
            if (error) {
                showFieldError(arrivalsInput, error);
            } else {
                showFieldSuccess(arrivalsInput);
            }
            // Re-validate leaving date if both dates are set
            if (leavingInput && leavingInput.value) {
                const leavingError = validateDateDependency(arrivalsInput.value, leavingInput.value);
                if (leavingError) {
                    showFieldError(leavingInput, leavingError);
                } else {
                    showFieldSuccess(leavingInput);
                }
            }
        });
    }

    if (leavingInput) {
        leavingInput.addEventListener('change', () => {
            const error = validateDate(leavingInput.value, 'leaving');
            if (error) {
                showFieldError(leavingInput, error);
            } else if (arrivalsInput && arrivalsInput.value) {
                // Check date dependency
                const depError = validateDateDependency(arrivalsInput.value, leavingInput.value);
                if (depError) {
                    showFieldError(leavingInput, depError);
                } else {
                    showFieldSuccess(leavingInput);
                }
            } else {
                showFieldSuccess(leavingInput);
            }
        });
    }

    // Form submission validation
    form.addEventListener('submit', (e) => {
        let hasErrors = false;

        // Validate all fields
        if (nameInput) {
            const error = validateName(nameInput.value);
            if (error) {
                showFieldError(nameInput, error);
                hasErrors = true;
            }
        }

        if (emailInput) {
            const error = validateEmail(emailInput.value);
            if (error) {
                showFieldError(emailInput, error);
                hasErrors = true;
            }
        }

        if (phoneInput) {
            const error = validatePhone(phoneInput.value);
            if (error) {
                showFieldError(phoneInput, error);
                hasErrors = true;
            }
        }

        if (addressInput) {
            const error = validateAddress(addressInput.value);
            if (error) {
                showFieldError(addressInput, error);
                hasErrors = true;
            }
        }

        if (locationInput) {
            const error = validateLocation(locationInput.value);
            if (error) {
                showFieldError(locationInput, error);
                hasErrors = true;
            }
        }

        if (guestsInput) {
            const error = validateGuests(guestsInput.value);
            if (error) {
                showFieldError(guestsInput, error);
                hasErrors = true;
            }
        }

        if (arrivalsInput) {
            const error = validateDate(arrivalsInput.value, 'arrival');
            if (error) {
                showFieldError(arrivalsInput, error);
                hasErrors = true;
            }
        }

        if (leavingInput) {
            const error = validateDate(leavingInput.value, 'leaving');
            if (error) {
                showFieldError(leavingInput, error);
                hasErrors = true;
            } else if (arrivalsInput && arrivalsInput.value) {
                const depError = validateDateDependency(arrivalsInput.value, leavingInput.value);
                if (depError) {
                    showFieldError(leavingInput, depError);
                    hasErrors = true;
                }
            }
        }

        if (hasErrors) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
}

/**
 * Initialize registration form validation
 */
function initRegistrationFormValidation() {
    const form = document.querySelector('.book-form');
    if (!form) return;

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    // Real-time validation on blur
    if (usernameInput) {
        usernameInput.addEventListener('blur', () => {
            const error = validateUsername(usernameInput.value);
            if (error) {
                showFieldError(usernameInput, error);
            } else {
                showFieldSuccess(usernameInput);
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', () => {
            const error = validateEmail(emailInput.value);
            if (error) {
                showFieldError(emailInput, error);
            } else {
                showFieldSuccess(emailInput);
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', () => {
            const error = validatePassword(passwordInput.value);
            if (error) {
                showFieldError(passwordInput, error);
            } else {
                showFieldSuccess(passwordInput);
            }
        });
    }

    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('blur', () => {
            const error = validatePasswordConfirmation(passwordInput.value, confirmPasswordInput.value);
            if (error) {
                showFieldError(confirmPasswordInput, error);
            } else {
                showFieldSuccess(confirmPasswordInput);
            }
        });

        // Also validate confirmation when password changes
        passwordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value) {
                const error = validatePasswordConfirmation(passwordInput.value, confirmPasswordInput.value);
                if (error) {
                    showFieldError(confirmPasswordInput, error);
                } else {
                    showFieldSuccess(confirmPasswordInput);
                }
            }
        });
    }

    // Form submission validation
    form.addEventListener('submit', (e) => {
        let hasErrors = false;

        if (usernameInput) {
            const error = validateUsername(usernameInput.value);
            if (error) {
                showFieldError(usernameInput, error);
                hasErrors = true;
            }
        }

        if (emailInput) {
            const error = validateEmail(emailInput.value);
            if (error) {
                showFieldError(emailInput, error);
                hasErrors = true;
            }
        }

        if (passwordInput) {
            const error = validatePassword(passwordInput.value);
            if (error) {
                showFieldError(passwordInput, error);
                hasErrors = true;
            }
        }

        if (confirmPasswordInput && passwordInput) {
            const error = validatePasswordConfirmation(passwordInput.value, confirmPasswordInput.value);
            if (error) {
                showFieldError(confirmPasswordInput, error);
                hasErrors = true;
            }
        }

        if (hasErrors) {
            e.preventDefault();
            const firstError = form.querySelector('.invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
}

/**
 * Initialize login form validation
 */
function initLoginFormValidation() {
    const form = document.querySelector('.book-form');
    if (!form) return;

    const usernameOrEmailInput = document.getElementById('username_or_email');
    const passwordInput = document.getElementById('password');

    // Real-time validation on blur
    if (usernameOrEmailInput) {
        usernameOrEmailInput.addEventListener('blur', () => {
            if (!usernameOrEmailInput.value || usernameOrEmailInput.value.trim() === '') {
                showFieldError(usernameOrEmailInput, 'Username or email is required');
            } else {
                showFieldSuccess(usernameOrEmailInput);
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', () => {
            if (!passwordInput.value || passwordInput.value === '') {
                showFieldError(passwordInput, 'Password is required');
            } else {
                showFieldSuccess(passwordInput);
            }
        });
    }

    // Form submission validation
    form.addEventListener('submit', (e) => {
        let hasErrors = false;

        if (usernameOrEmailInput) {
            if (!usernameOrEmailInput.value || usernameOrEmailInput.value.trim() === '') {
                showFieldError(usernameOrEmailInput, 'Username or email is required');
                hasErrors = true;
            }
        }

        if (passwordInput) {
            if (!passwordInput.value || passwordInput.value === '') {
                showFieldError(passwordInput, 'Password is required');
                hasErrors = true;
            }
        }

        if (hasErrors) {
            e.preventDefault();
            const firstError = form.querySelector('.invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
}

// Initialize validation when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Detect which form we're on and initialize appropriate validation
        const bookingForm = document.querySelector('.book-form');
        if (bookingForm) {
            // Check if it's booking form (has name, phone, address fields)
            if (document.getElementById('name') && document.getElementById('phone')) {
                initBookingFormValidation();
            }
            // Check if it's registration form (has username, confirm_password fields)
            else if (document.getElementById('username') && document.getElementById('confirm_password')) {
                initRegistrationFormValidation();
            }
            // Check if it's login form (has username_or_email field)
            else if (document.getElementById('username_or_email')) {
                initLoginFormValidation();
            }
        }
    });
} else {
    // DOM already loaded
    const bookingForm = document.querySelector('.book-form');
    if (bookingForm) {
        if (document.getElementById('name') && document.getElementById('phone')) {
            initBookingFormValidation();
        } else if (document.getElementById('username') && document.getElementById('confirm_password')) {
            initRegistrationFormValidation();
        } else if (document.getElementById('username_or_email')) {
            initLoginFormValidation();
        }
    }
}

