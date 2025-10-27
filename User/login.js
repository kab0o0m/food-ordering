// Login Form Validation
const loginForm = document.getElementById('loginForm');

loginForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Clear previous errors
    clearErrors();

    let isValid = true;

    // Validate email
    const email = document.getElementById('email').value.trim();
    if (email === '') {
        showError('emailError', 'Please enter your email address', 'email');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('emailError', 'Please enter a valid email address', 'email');
        isValid = false;
    }

    // Validate password
    const password = document.getElementById('password').value;
    if (password === '') {
        showError('passwordError', 'Please enter your password', 'password');
        isValid = false;
    }

    if (isValid) {
        loginUser(email, password);
    }
});

function loginUser(email, password) {
    // Get stored user data (for demo purposes)
    const storedUser = localStorage.getItem('user');

    if (storedUser) {
        const user = JSON.parse(storedUser);

        // Check credentials
        if (user.email === email && user.password === password) {
            localStorage.setItem('isLoggedIn', 'true');
            alert('Login successful! Welcome back, ' + user.name + '!');
            window.location.href = 'index.html';
        } else {
            showError('passwordError', 'Invalid email or password', 'password');
            showError('emailError', '', 'email');
        }
    } else {
        showError('emailError', 'No account found with this email', 'email');
    }
}

// Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function () {
    const input = document.getElementById('password');

    if (input.type === 'password') {
        input.type = 'text';
        this.textContent = '🙈';
    } else {
        input.type = 'password';
        this.textContent = '👁️';
    }
});

// Social login buttons
document.querySelector('.google-btn').addEventListener('click', function () {
    alert('Google login not implemented yet');
});

document.querySelector('.facebook-btn').addEventListener('click', function () {
    alert('Facebook login not implemented yet');
});

// Forgot password link
document.querySelector('.forgot-link').addEventListener('click', function (e) {
    e.preventDefault();
    alert('Password reset feature not implemented yet');
});

// Helper functions
function showError(errorId, message, inputId) {
    const errorElement = document.getElementById(errorId);
    errorElement.textContent = message;
    errorElement.classList.add('show');

    if (inputId) {
        document.getElementById(inputId).classList.add('error');
    }
}

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(element => {
        element.classList.remove('show');
        element.textContent = '';
    });

    document.querySelectorAll('input.error').forEach(element => {
        element.classList.remove('error');
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
