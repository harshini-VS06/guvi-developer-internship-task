$(document).ready(function() {
    // Check if user is logged in
    const sessionToken = localStorage.getItem('sessionToken');
    const userId = localStorage.getItem('userId');
    
    if (!sessionToken || !userId) {
        window.location.href = 'login.html';
        return;
    }
    
    // Verify session with backend
    verifySession();
    
    // Load user data
    loadUserData();
    
    // Handle profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Handle logout
    $('#logoutBtn').on('click', function() {
        logout();
    });
    
    function verifySession() {
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'verify',
                sessionToken: sessionToken,
                userId: userId
            }),
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    logout();
                }
            },
            error: function() {
                logout();
            }
        });
    }
    
    function loadUserData() {
        // Set username and email from localStorage
        $('#username').val(localStorage.getItem('username'));
        $('#email').val(localStorage.getItem('email'));
        
        // Fetch additional profile data from MongoDB
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'get',
                sessionToken: sessionToken,
                userId: userId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success && response.profile) {
                    const profile = response.profile;
                    $('#fullName').val(profile.fullName || '');
                    $('#age').val(profile.age || '');
                    $('#dob').val(profile.dob || '');
                    $('#contact').val(profile.contact || '');
                    $('#address').val(profile.address || '');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading profile:', error);
            }
        });
    }
    
    function updateProfile() {
        // Clear previous messages
        $('#errorMessage').addClass('d-none');
        $('#successMessage').addClass('d-none');
        
        // Get form values
        const fullName = $('#fullName').val().trim();
        const age = $('#age').val();
        const dob = $('#dob').val().trim();
        const contact = $('#contact').val().trim();
        const address = $('#address').val().trim();
        
        // Validation
        if (age && (age < 1 || age > 120)) {
            showError('Please enter a valid age between 1 and 120');
            return;
        }
        
        if (dob && !isValidDate(dob)) {
            showError('Please enter date in YYYY-MM-DD format (e.g., 1999-01-15)');
            return;
        }
        
        if (contact && !isValidPhone(contact)) {
            showError('Please enter a valid contact number');
            return;
        }
        
        // Prepare data
        const profileData = {
            action: 'update',
            sessionToken: sessionToken,
            userId: userId,
            fullName: fullName,
            age: age,
            dob: dob,
            contact: contact,
            address: address
        };
        
        // Send AJAX request
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(profileData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Profile update error:', error);
                showError('An error occurred while updating profile. Please try again.');
            }
        });
    }
    
    function logout() {
        // Send logout request to backend
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'logout',
                sessionToken: sessionToken,
                userId: userId
            }),
            dataType: 'json',
            complete: function() {
                // Clear localStorage
                localStorage.removeItem('sessionToken');
                localStorage.removeItem('userId');
                localStorage.removeItem('username');
                localStorage.removeItem('email');
                
                // Redirect to login
                window.location.href = 'login.html';
            }
        });
    }
    
    function isValidPhone(phone) {
        const phoneRegex = /^[+]?[\d\s\-()]+$/;
        return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }
    
    function isValidDate(dateString) {
        // Check format YYYY-MM-DD
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(dateString)) {
            return false;
        }
        
        // Check if it's a valid date
        const date = new Date(dateString);
        const timestamp = date.getTime();
        
        if (typeof timestamp !== 'number' || Number.isNaN(timestamp)) {
            return false;
        }
        
        // Verify the date string matches the parsed date
        return date.toISOString().startsWith(dateString);
    }
    
    function showError(message) {
        $('#errorMessage').text(message).removeClass('d-none');
        setTimeout(function() {
            $('#errorMessage').addClass('d-none');
        }, 5000);
    }
    
    function showSuccess(message) {
        $('#successMessage').text(message).removeClass('d-none');
        setTimeout(function() {
            $('#successMessage').addClass('d-none');
        }, 3000);
    }
});
