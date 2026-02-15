$(document).ready(function() {
    // Check if user is already logged in
    const sessionToken = localStorage.getItem('sessionToken');
    if (sessionToken) {
        window.location.href = 'profile.html';
    }

    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous messages
        $('#errorMessage').addClass('d-none');
        $('#successMessage').addClass('d-none');
        
        // Get form values
        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        
        // Validation
        if (username.length < 3) {
            showError('Username must be at least 3 characters long');
            return;
        }
        
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }
        
        if (password.length < 6) {
            showError('Password must be at least 6 characters long');
            return;
        }
        
        if (password !== confirmPassword) {
            showError('Passwords do not match');
            return;
        }
        
        // Prepare data
        const formData = {
            username: username,
            email: email,
            password: password
        };
        
        // Send AJAX request
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    $('#registerForm')[0].reset();
                    
                    // Redirect to login after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Registration error:', error);
                showError('An error occurred during registration. Please try again.');
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
