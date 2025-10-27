// Register Form Validation
const registerForm = document.getElementById('registerForm');

registerForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Clear previous errors
    clearErrors();

    let isValid = true;

    // Validate name
    const name = document.getElementById('name').value.trim();
    if (name === '') {
        showError('nameError', 'Please enter your full name', 'name');
        isValid = false;
    } else if (name.length < 2) {
        showError('nameError', 'Name must be at least 2 characters', 'name');
        isValid = false;
    }

    // Validate email
    const email = document.getElementById('email').value.trim();
    if (email === '') {
        showError('emailError', 'Please enter your email address', 'email');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('emailError', 'Please enter a valid email address', 'email');
        isValid = false;
    }

    // Validate phone
    const phone = document.getElementById('phone').value.trim();
    if (phone === '') {
        showError('phoneError', 'Please enter your phone number', 'phone');
        isValid = false;
    } else if (!isValidPhone(phone)) {
        showError('phoneError', 'Please enter a valid phone number', 'phone');
        isValid = false;
    }

    // Validate password
    const password = document.getElementById('password').value;
    if (password === '') {
        showError('passwordError', 'Please enter a password', 'password');
        isValid = false;
    } else if (password.length < 8) {
        showError('passwordError', 'Password must be at least 8 characters', 'password');
        isValid = false;
    }

    // Validate confirm password
    const confirmPassword = document.getElementById('confirmPassword').value;
    if (confirmPassword === '') {
        showError('confirmPasswordError', 'Please confirm your password', 'confirmPassword');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirmPasswordError', 'Passwords do not match', 'confirmPassword');
        isValid = false;
    }

    // Validate terms
    const terms = document.getElementById('terms').checked;
    if (!terms) {
        showError('termsError', 'Please agree to the terms and conditions');
        isValid = false;
    }

    if (isValid) {
        registerUser();
    }
});

function registerUser() {
    const userData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        password: document.getElementById('password').value
    };

    console.log('User registered:', userData);

    // Store user data in localStorage (for demo purposes)
    localStorage.setItem('user', JSON.stringify(userData));
    localStorage.setItem('isLoggedIn', 'true');

    alert('Registration successful! Welcome to FoodHub!');

    // Redirect to homepage
    window.location.href = 'menu.html';
}

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🙈';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });
});

// Social login buttons
document.querySelector('.google-btn').addEventListener('click', function () {
    alert('Google login not implemented yet');
});

document.querySelector('.facebook-btn').addEventListener('click', function () {
    alert('Facebook login not implemented yet');
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

function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\+\-\(\)]+$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 8;
}