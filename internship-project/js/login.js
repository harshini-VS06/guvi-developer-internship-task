$(document).ready(function() {
    // Check if user is already logged in
    const sessionToken = localStorage.getItem('sessionToken');
    if (sessionToken) {
        window.location.href = 'profile.html';
    }

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous messages
        $('#errorMessage').addClass('d-none');
        $('#successMessage').addClass('d-none');
        
        // Get form values
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        // Validation
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }
        
        if (password.length === 0) {
            showError('Please enter your password');
            return;
        }
        
        // Prepare data
        const formData = {
            email: email,
            password: password
        };
        
        // Send AJAX request
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    
                    // Store session token in localStorage
                    localStorage.setItem('sessionToken', response.sessionToken);
                    localStorage.setItem('userId', response.userId);
                    localStorage.setItem('username', response.username);
                    localStorage.setItem('email', response.email);
                    
                    // Redirect to profile after 1 second
                    setTimeout(function() {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Login error:', error);
                showError('An error occurred during login. Please try again.');
            }
        });
    });
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showError(message) {
        $('#errorMessage').text(message).removeClass('d-none');
        setTimeout(function() {
            $('#errorMessage').addClass('d-none');
        }, 5000);
    }
    
    function showSuccess(message) {
        $('#successMessage').text(message).removeClass('d-none');
    }
});
