/**
 * Form Validation Module for ExportBridge
 * Provides client-side validation for all form inputs
 */

const FormValidator = {
    // Validation patterns
    patterns: {
        name: /^[a-zA-Z\s'-]+$/,  // Letters, spaces, hyphens, apostrophes only
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,  // Basic email pattern
        alphanumeric: /^[a-zA-Z0-9\s]+$/,  // Letters and numbers only
        countryCode: /^[A-Z]{2,5}$/,  // 2-5 uppercase letters
        hsCode: /^[\d\.]{4,10}$/,  // HS code pattern (4-10 digits/dots)
        phone: /^[\d\s\+\-\(\)]+$/,  // Phone numbers with +, -, spaces, parentheses
        url: /^https?:\/\/.+/  // URL pattern
    },

    // Validation rules
    rules: {
        name: {
            pattern: 'name',
            message: 'Name must contain only letters, spaces, hyphens, and apostrophes'
        },
        email: {
            pattern: 'email',
            message: 'Please enter a valid email address'
        },
        password: {
            minLength: 8,
            message: 'Password must be at least 8 characters long'
        },
        confirmPassword: {
            message: 'Passwords do not match'
        },
        required: {
            message: 'This field is required'
        },
        countryCode: {
            pattern: 'countryCode',
            message: 'Country code must be 2-5 uppercase letters (e.g., FR, DE, IT)'
        },
        hsCode: {
            pattern: 'hsCode',
            message: 'HS Code must be 4-10 digits (e.g., 6109.10)'
        },
        number: {
            message: 'Please enter a valid number'
        },
        url: {
            pattern: 'url',
            message: 'Please enter a valid URL (must start with http:// or https://)'
        },
        alphanumeric: {
            pattern: 'alphanumeric',
            message: 'This field must contain only letters and numbers'
        },
        phone: {
            pattern: 'phone',
            message: 'Please enter a valid phone number'
        }
    },

    /**
     * Initialize validation for a form
     * @param {HTMLFormElement} form - The form element to validate
     * @param {Object} options - Validation options
     */
    init(form, options = {}) {
        if (!form) return;

        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]), select, textarea');
        
        inputs.forEach(input => {
            this.attachValidation(input, options);
        });

        // Form submit handler
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Add password toggle functionality
        this.initPasswordToggles(form);
    },

    /**
     * Attach validation listeners to an input
     */
    attachValidation(input, options) {
        const validationType = this.detectValidationType(input);
        
        // Blur validation
        input.addEventListener('blur', () => {
            this.validateField(input, validationType);
        });

        // Input validation (real-time, optional)
        if (options.realTime !== false) {
            input.addEventListener('input', () => {
                // Only show errors after first blur
                if (input.dataset.touched) {
                    this.validateField(input, validationType);
                }
            });
        }

        // Mark as touched on blur
        input.addEventListener('blur', () => {
            input.dataset.touched = 'true';
        });
    },

    /**
     * Detect validation type based on input attributes and name
     */
    detectValidationType(input) {
        const name = input.name.toLowerCase();
        const type = input.type.toLowerCase();
        const tagName = input.tagName.toLowerCase();

        // Check for data attribute first
        if (input.dataset.validation) {
            return input.dataset.validation.split(' ');
        }

        const types = [];

        // Required check
        if (input.required) {
            types.push('required');
        }

        // Type-based validation
        if (tagName === 'input') {
            if (type === 'email') types.push('email');
            if (type === 'password') types.push('password');
            if (type === 'number') types.push('number');
            if (type === 'url') types.push('url');
        }

        // Name-based validation
        if (name.includes('name') && !name.includes('company')) {
            types.push('name');
        }
        if (name.includes('country_code')) {
            types.push('countryCode');
        }
        if (name.includes('hs_code')) {
            types.push('hsCode');
        }
        if (name.includes('phone')) {
            types.push('phone');
        }
        if (name.includes('confirm_password')) {
            types.push('confirmPassword');
        }

        return types.length ? types : ['required'];
    },

    /**
     * Validate a single field
     */
    validateField(input, validationTypes) {
        let isValid = true;
        let errorMessage = '';

        // Remove existing error
        this.clearError(input);

        for (const type of validationTypes) {
            const result = this.validate(input, type);
            if (!result.valid) {
                isValid = false;
                errorMessage = result.message;
                break;
            }
        }

        if (!isValid) {
            this.showError(input, errorMessage);
        }

        return isValid;
    },

    /**
     * Validate input against a specific rule
     */
    validate(input, ruleType) {
        const value = input.value.trim();
        const tagName = input.tagName.toLowerCase();

        // Skip validation for empty optional fields
        if (!value && !input.required) {
            return { valid: true };
        }

        // Required validation
        if (ruleType === 'required' && !value) {
            return { valid: false, message: this.rules.required.message };
        }

        // Pattern-based validation
        if (this.rules[ruleType] && this.rules[ruleType].pattern) {
            const pattern = this.patterns[this.rules[ruleType].pattern];
            if (pattern && !pattern.test(value)) {
                return { valid: false, message: this.rules[ruleType].message };
            }
        }

        // Password validation
        if (ruleType === 'password' && value && value.length < this.rules.password.minLength) {
            return { valid: false, message: this.rules.password.message };
        }

        // Confirm password validation
        if (ruleType === 'confirmPassword') {
            const form = input.closest('form');
            const passwordField = form.querySelector('input[name="password"]');
            if (passwordField && value !== passwordField.value) {
                return { valid: false, message: this.rules.confirmPassword.message };
            }
        }

        // Number validation
        if (ruleType === 'number' && value && isNaN(value)) {
            return { valid: false, message: this.rules.number.message };
        }

        return { valid: true };
    },

    /**
     * Validate entire form
     */
    validateForm(form) {
        let isValid = true;
        let firstInvalidField = null;

        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]), select, textarea');
        
        inputs.forEach(input => {
            const validationTypes = this.detectValidationType(input);
            if (!this.validateField(input, validationTypes)) {
                isValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = input;
                }
            }
        });

        // Focus first invalid field
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    },

    /**
     * Show error message below field
     */
    showError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        errorDiv.dataset.validationError = 'true';

        // Insert after input
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    },

    /**
     * Clear error message
     */
    clearError(input) {
        input.classList.remove('is-invalid');
        
        const existingError = input.parentNode.querySelector('[data-validation-error]');
        if (existingError) {
            existingError.remove();
        }
    },

    /**
     * Initialize password reveal toggles
     */
    initPasswordToggles(form) {
        const passwordInputs = form.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(passwordInput => {
            // Check if toggle already exists
            if (passwordInput.parentNode.querySelector('.password-toggle')) return;

            // Create toggle button
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle btn btn-outline-secondary';
            toggleBtn.style.borderTopLeftRadius = '0';
            toggleBtn.style.borderBottomLeftRadius = '0';
            toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
            toggleBtn.setAttribute('aria-label', 'Show password');

            // Wrap in input-group if not already
            if (!passwordInput.parentNode.classList.contains('input-group')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                passwordInput.parentNode.insertBefore(wrapper, passwordInput);
                wrapper.appendChild(passwordInput);
                wrapper.appendChild(toggleBtn);
            } else {
                passwordInput.parentNode.appendChild(toggleBtn);
            }

            // Toggle visibility
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
    },

    /**
     * Show success state for a field
     */
    showSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
};

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        FormValidator.init(form);
    });
});

// Export for manual initialization
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}
