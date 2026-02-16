$(document).ready(function() {
    const sessionToken = localStorage.getItem('sessionToken');
    const userId = localStorage.getItem('userId');
    
    if (!sessionToken || !userId) {
        window.location.href = 'login.html';
        return;
    }

    // --- 1. Initial State: Read-Only ---
    setReadOnly(true);

    // --- 2. Load Data from Databases ---
    function loadData() {
    $('#username').val(localStorage.getItem('username'));
    $('#email').val(localStorage.getItem('email'));

    $.ajax({
        url: 'php/profile.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'get', sessionToken, userId }),
        success: function(res) {
            console.log("MongoDB Raw Data:", res); // Check this in your browser console!
            if (res.success && res.profile) {
                const p = res.profile;
                // Handle both MongoDB naming styles
                $('#fullName').val(p.fullName || p.full_name || '');
                $('#age').val(p.age || '');
                $('#dob').val(p.dob || '');
                $('#contact').val(p.contact || '');
                $('#address').val(p.address || '');
                }
            }
        });
    }

    loadData();

    // --- 3. Toggle Edit Mode ---
    $('#editTrigger').on('click', function() {
        setReadOnly(false);
        $(this).addClass('d-none'); // Hide Edit button
        $('#saveBtn').removeClass('d-none'); // Show Save button
    });

    // --- 4. Handle Form Submission (Save) ---
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        const profileData = {
            action: 'update',
            sessionToken: sessionToken,
            userId: parseInt(userId),
            fullName: $('#fullName').val().trim(),
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim()
        };

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(profileData),
            success: function(response) {
                if (response.success) {
                    showSuccess("Profile updated permanently in MongoDB!");
                    // Return to Read-Only Mode
                    setReadOnly(true);
                    $('#saveBtn').addClass('d-none');
                    $('#editTrigger').removeClass('d-none');
                } else {
                    showError(response.message);
                }
            }
        });
    });

    // --- Helper Functions ---
    function setReadOnly(status) {
        // We use readonly so the text remains clear but uneditable
        $('.profile-input').prop('readonly', status);
        // Visual feedback
        if(status) {
            $('.profile-input').css('background-color', '#f8f9fa');
        } else {
            $('.profile-input').css('background-color', '#ffffff').first().focus();
        }
    }

    $('#logoutBtn').on('click', function() {
        localStorage.clear();
        window.location.href = 'login.html';
    });

    function showError(m) { $('#errorMessage').text(m).removeClass('d-none'); setTimeout(() => $('#errorMessage').addClass('d-none'), 3000); }
    function showSuccess(m) { $('#successMessage').text(m).removeClass('d-none'); setTimeout(() => $('#successMessage').addClass('d-none'), 3000); }
});
