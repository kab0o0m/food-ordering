// Form Validation
const form = document.getElementById('checkoutForm');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    // Validate fields
    let isValid = true;
    
    // Validate name
    const name = document.getElementById('fullName').value.trim();
    if (name === '') {
        showError('nameError', 'Please enter your full name');
        isValid = false;
    }
    
    // Validate phone
    const phone = document.getElementById('phoneNumber').value.trim();
    if (phone === '') {
        showError('phoneError', 'Please enter your phone number');
        isValid = false;
    } else if (!isValidPhone(phone)) {
        showError('phoneError', 'Please enter a valid phone number');
        isValid = false;
    }
    
    // Validate email
    const email = document.getElementById('email').value.trim();
    if (email === '') {
        showError('emailError', 'Please enter your email address');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('emailError', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate address
    const address = document.getElementById('address').value.trim();
    if (address === '') {
        showError('addressError', 'Please enter your delivery address');
        isValid = false;
    }
    
    // Validate postal code
    const postal = document.getElementById('postalCode').value.trim();
    if (postal === '') {
        showError('postalError', 'Please enter your postal code');
        isValid = false;
    } else if (!isValidPostal(postal)) {
        showError('postalError', 'Please enter a valid 6-digit postal code');
        isValid = false;
    }
    
    // Validate terms
    const terms = document.getElementById('terms').checked;
    if (!terms) {
        showError('termsError', 'Please agree to the terms and conditions');
        isValid = false;
    }
    
    // If all valid, proceed
    if (isValid) {
        submitOrder();
    }
});

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    const inputElement = errorElement.previousElementSibling;
    
    errorElement.textContent = message;
    errorElement.classList.add('show');
    
    if (inputElement && inputElement.tagName === 'INPUT') {
        inputElement.classList.add('error');
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

function isValidPostal(postal) {
    return /^\d{6}$/.test(postal);
}

function submitOrder() {
    // Get form data
    const formData = {
        name: document.getElementById('fullName').value,
        phone: document.getElementById('phoneNumber').value,
        email: document.getElementById('email').value,
        address: document.getElementById('address').value,
        unit: document.getElementById('unitNumber').value,
        postal: document.getElementById('postalCode').value,
        instructions: document.getElementById('instructions').value,
        payment: document.querySelector('input[name="payment"]:checked').value
    };
    
    console.log('Order submitted:', formData);
    
    // Show success message
    alert('Order placed successfully! You will receive a confirmation email shortly.');
    
    // Redirect to confirmation page
    // window.location.href = 'confirmation.html';
}

// Real-time validation
document.getElementById('fullName').addEventListener('blur', function() {
    if (this.value.trim() === '') {
        showError('nameError', 'Please enter your full name');
    } else {
        document.getElementById('nameError').classList.remove('show');
        this.classList.remove('error');
    }
});

document.getElementById('email').addEventListener('blur', function() {
    if (this.value.trim() === '') {
        showError('emailError', 'Please enter your email address');
    } else if (!isValidEmail(this.value)) {
        showError('emailError', 'Please enter a valid email address');
    } else {
        document.getElementById('emailError').classList.remove('show');
        this.classList.remove('error');
    }
});

document.getElementById('phoneNumber').addEventListener('blur', function() {
    if (this.value.trim() === '') {
        showError('phoneError', 'Please enter your phone number');
    } else if (!isValidPhone(this.value)) {
        showError('phoneError', 'Please enter a valid phone number');
    } else {
        document.getElementById('phoneError').classList.remove('show');
        this.classList.remove('error');
    }
});

document.getElementById('postalCode').addEventListener('blur', function() {
    if (this.value.trim() === '') {
        showError('postalError', 'Please enter your postal code');
    } else if (!isValidPostal(this.value)) {
        showError('postalError', 'Please enter a valid 6-digit postal code');
    } else {
        document.getElementById('postalError').classList.remove('show');
        this.classList.remove('error');
    }
});