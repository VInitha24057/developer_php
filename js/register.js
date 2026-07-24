// ==========================================
// DeveloperInternship - Registration Script
// ==========================================

// Wait until the DOM is fully loaded
$(document).ready(function () {
    'use strict';

    // Cache DOM elements for better performance
    const $registerBtn = $('#registerBtn');
    const $name = $('#name');
    const $email = $('#email');
    const $password = $('#password');
    const $confirmPassword = $('#confirmPassword');
    const $form = $('#registerForm');

    // Store original button text to restore later
    const originalBtnText = $registerBtn.text();

    // ------------------------------------------
    // Validation Helpers
    // ------------------------------------------

    /**
     * Validates the registration form fields.
     * @returns {string} Error message if validation fails, empty string if valid.
     */
    function validateForm() {
        const name = $name.val().trim();
        const email = $email.val().trim();
        const password = $password.val();
        const confirmPassword = $confirmPassword.val();

        if (name === '') {
            return 'Please enter your full name.';
        }

        if (email === '') {
            return 'Please enter your email address.';
        }

        // Simple email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return 'Please enter a valid email address.';
        }

        if (password === '') {
            return 'Please enter a password.';
        }

        if (password.length < 6) {
            return 'Password must be at least 6 characters.';
        }

        if (confirmPassword === '') {
            return 'Please confirm your password.';
        }

        if (password !== confirmPassword) {
            return 'Passwords do not match.';
        }

        return '';
    }

    /**
     * Displays an alert message using Bootstrap alert styling.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'danger'.
     */
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $form.prepend(alertHtml);
    }

    /**
     * Enables the register button and restores its original text.
     */
    function resetButtonState() {
        $registerBtn.prop('disabled', false);
        $registerBtn.text(originalBtnText);
    }

    // ------------------------------------------
    // Form Submission Handler
    // ------------------------------------------

    $form.on('submit', function (event) {
        event.preventDefault();

        // Clear any existing alerts
        $('.alert').remove();

        // Run client-side validation
        const validationError = validateForm();
        if (validationError !== '') {
            showAlert(validationError, 'danger');
            return;
        }

        // Prepare data to send to the server
        const formData = {
            name: $name.val().trim(),
            email: $email.val().trim(),
            password: $password.val()
        };

        // Disable button and update text during request
        $registerBtn.prop('disabled', true);
        $registerBtn.text('Registering...');

        // ------------------------------------------
        // AJAX Request
        // ------------------------------------------
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showAlert(response.message || 'Registration successful! Redirecting...', 'success');

                    // Redirect to login page after 2 seconds
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    showAlert(response.message || 'Registration failed. Please try again.', 'danger');
                }
            },
            error: function () {
                showAlert('Server connection failed.', 'danger');
            },
            complete: function () {
                // Always re-enable the button after request finishes
                resetButtonState();
            }
        });
    });
});
