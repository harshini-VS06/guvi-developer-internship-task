$(document).ready(function() {
    // 1. Initial Identity & Session Check
    const sessionToken = localStorage.getItem('sessionToken');
    const userId = localStorage.getItem('userId');
    
    if (!sessionToken || !userId) {
        window.location.href = 'login.html';
        return;
    }

    // --- State Management: Start in "Read-Only" mode ---
    let isEditMode = false;
    setFieldsDisabled(true);

    // 2. Verify session & Load Data
    verifySession();
    loadUserData();

    // 3. Handle Submit Button (Toggles between Edit and Save)
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!isEditMode) {
            // Switch to Edit Mode
            isEditMode = true;
            setFieldsDisabled(false);
            $('.btn-update').html('<i class="fas fa-check me-2"></i>CONFIRM AND SAVE CHANGES');
            $('.btn-update').addClass('btn-success').removeClass('btn-primary');
        } else {
            // Perform the Update
            updateProfile();
        }
    });

    // 4. Logout Handler
    $('#logoutBtn').on('click', function() {
        logout();
    });

    // --- CORE FUNCTIONS ---

    function loadUserData() {
        // MySQL Data (Static identity)
        $('#username').val(localStorage.getItem('username'));
        $('#email').val(localStorage.getItem('email'));
        
        // MongoDB Data (Dynamic profile)
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'get',
                sessionToken: sessionToken,
                userId: userId
            }),
            success: function(response) {
                if (response.success && response.profile) {
                    const p = response.profile;
                    $('#fullName').val(p.fullName || '');
                    $('#age').val(p.age || '');
                    $('#dob').val(p.dob || '');
                    $('#contact').val(p.contact || '');
                    $('#address').val(p.address || '');
                }
            }
        });
    }

    function updateProfile() {
        const profileData = {
            action: 'update',
            sessionToken: sessionToken,
            userId: userId,
            fullName: $('#fullName').val().trim(),
            age: $('#age').val(),
            dob: $('#dob').val().trim(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim()
        };

        // Reuse your existing validation logic here
        if (profileData.age && (profileData.age < 1 || profileData.age > 120)) {
            showError('Please enter a valid age'); return;
        }

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(profileData),
            success: function(response) {
                if (response.success) {
                    showSuccess("Profile updated permanently in MongoDB!");
                    // Reset to Read-Only mode
                    isEditMode = false;
                    setFieldsDisabled(true);
                    $('.btn-update').html('<i class="fas fa-save me-2"></i>UPDATE PROFILE INFORMATION');
                    $('.btn-update').addClass('btn-primary').removeClass('btn-success');
                } else {
                    showError(response.message);
                }
            }
        });
    }

    // --- UTILS ---

    function setFieldsDisabled(status) {
        // Note: username and email are ALWAYS readonly as they come from MySQL
        $('#fullName, #age, #dob, #contact, #address').prop('disabled', status);
    }

    function verifySession() {
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'verify', sessionToken, userId }),
            success: function(res) { if (!res.success) logout(); },
            error: function() { logout(); }
        });
    }

    function logout() {
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'logout', sessionToken, userId }),
            complete: function() {
                localStorage.clear();
                window.location.href = 'login.html';
            }
        });
    }

    function showError(m) { $('#errorMessage').text(m).removeClass('d-none'); }
    function showSuccess(m) { $('#successMessage').text(m).removeClass('d-none'); }
});
