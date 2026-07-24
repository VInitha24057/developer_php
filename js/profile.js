// ==========================================
// DeveloperInternship - Profile Script
// ==========================================

$(document).ready(function () {
    'use strict';

    // Cache DOM elements for better performance
    const $name = $('#name');
    const $email = $('#email');
    const $age = $('#age');
    const $dob = $('#dob');
    const $contact = $('#contact');
    const $address = $('#address');
    const $updateBtn = $('#updateBtn');
    const $logoutBtn = $('#logoutBtn');
    const $profileForm = $('#profileForm');

    // Store original button text to restore later
    const originalUpdateBtnText = $updateBtn.text();

    // ------------------------------------------
    // Token Verification
    // ------------------------------------------

    // Retrieve token from localStorage
    const token = localStorage.getItem('token');

    // Retrieve user_id from localStorage
    const user_id = localStorage.getItem('user_id');

    // If token or user_id does not exist, redirect to login page
    if (!token || !user_id) {
        window.location.href = 'login.html';
    }

    // ------------------------------------------
    // Helper Functions
    // ------------------------------------------

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
        $('.card-body').prepend(alertHtml);
    }

    /**
     * Resets the update button state.
     */
    function resetUpdateButton() {
        $updateBtn.prop('disabled', false);
        $updateBtn.text(originalUpdateBtnText);
    }

    // ------------------------------------------
    // Load Profile Data
    // ------------------------------------------

    // Fetch profile data from the server using the token
    console.log("Sending profile request");
    console.log("Token:", token);
    console.log("User ID:", user_id);

    $.ajax({
        url: 'php/profile.php',
        type: 'POST',
        data: { token: token, user_id: user_id },
        dataType: 'json',
        success: function (response) {
            console.log("PROFILE RESPONSE:", response);
            if (response.status === 'success') {
                // Populate form fields with profile data
                $name.val(response.data.name || '');
                $email.val(response.data.email || '');
                $age.val(response.data.age || '');
                $dob.val(response.data.dob || '');
                $contact.val(response.data.contact || '');
                $address.val(response.data.address || '');
            } else {
                // Show error message from server
                showAlert(response.message || 'Failed to load profile.', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.log("Profile AJAX Error:", xhr.status);
            console.log(xhr.responseText);

            showAlert(
              xhr.responseText || "Unable to connect to server.",
              "danger"
            );
        }
    });

    // ------------------------------------------
    // Update Profile Handler
    // ------------------------------------------

    $profileForm.on('submit', function (event) {
        event.preventDefault();

        // Clear any existing alerts
        $('.alert').remove();

        // Collect form data
        const formData = {
            token: token,
            user_id: user_id,
            age: $age.val().trim(),
            dob: $dob.val().trim(),
            contact: $contact.val().trim(),
            address: $address.val().trim()
        };

        // Disable button and update text during request
        $updateBtn.prop('disabled', true);
        $updateBtn.text('Updating...');

        // ------------------------------------------
        // AJAX Request to Update Profile
        // ------------------------------------------
        $.ajax({
            url: 'php/updateProfile.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // Show success message
                    showAlert(response.message || 'Profile updated successfully!', 'success');
                } else {
                    // Show server error message
                    showAlert(response.message || 'Failed to update profile.', 'danger');
                }
            },
            error: function () {
                // Handle network/AJAX errors
                showAlert('Unable to connect to server.', 'danger');
            },
            complete: function () {
                // Always re-enable the button after request finishes
                resetUpdateButton();
            }
        });
    });

    // ------------------------------------------
    // Logout Handler
    // ------------------------------------------

    $logoutBtn.on('click', function () {
        localStorage.removeItem('token');
        localStorage.removeItem('user_id');
        localStorage.removeItem('email');

        // Redirect to login page
        window.location.href = 'login.html';
    });
});
