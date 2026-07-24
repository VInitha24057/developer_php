// ==========================================
// DeveloperInternship - Login Script
// ==========================================

$(document).ready(function () {
    'use strict';

    // Cache DOM elements for better performance
    const $loginBtn = $('#loginBtn');
    const $email = $('#email');
    const $password = $('#password');
    const $form = $('#loginForm');

    // Store original button text to restore later
    const originalBtnText = $loginBtn.text();

    // ------------------------------------------
    // Validation Helpers
    // ------------------------------------------

    /**
     * Validates login form fields.
     * @returns {string} Error message if validation fails, empty string if valid.
     */
    function validateForm() {
        const email = $email.val().trim();
        const password = $password.val();

        if (email === '') {
            return 'Please enter your email address.';
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return 'Please enter a valid email address.';
        }

        if (password === '') {
            return 'Please enter your password.';
        }

        if (password.length < 6) {
            return 'Password must be at least 6 characters.';
        }

        return '';
    }

    /**
     * Displays a Bootstrap alert message.
     * @param {string} message - The message to display.
     * @param {string} type - Bootstrap alert type (success, danger, warning, info).
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
     * Resets the login button state.
     */
    function resetButtonState() {
        $loginBtn.prop('disabled', false);
        $loginBtn.text(originalBtnText);
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
            email: $email.val().trim(),
            password: $password.val()
        };

        // Disable button and update text during request
        $loginBtn.prop('disabled', true);
        $loginBtn.text('Logging in...');

        // ------------------------------------------
        // AJAX Request
        // ------------------------------------------
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('user_id', response.user.id);
                    localStorage.setItem('email', response.user.email);

                    showAlert(response.message || 'Login successful! Redirecting...', 'success');

                    // Redirect to profile page after 2 seconds
                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 2000);
                } else {
                    // Show server error message
                    showAlert(response.message || 'Login failed. Please try again.', 'danger');
                }
            },
            error: function () {
                // Handle AJAX/network errors
                showAlert('Unable to connect to server.', 'danger');
            },
            complete: function () {
                // Always re-enable the button after request finishes
                resetButtonState();
            }
        });
    });
});
