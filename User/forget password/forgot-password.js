// Forgot Password Form Validation
const forgotPasswordForm = document.getElementById("forgotPasswordForm");

forgotPasswordForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Clear previous errors
    clearErrors();

    let isValid = true;

    // Grab field values
    const email = document.getElementById("email").value.trim();
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Validate email
    if (email === "") {
        showError("emailError", "Please enter your email address", "email");
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError("emailError", "Please enter a valid email address", "email");
        isValid = false;
    }

    // Validate new password
    if (newPassword === "") {
        showError("newPasswordError", "Please enter a new password", "newPassword");
        isValid = false;
    } else if (newPassword.length < 8) {
        showError("newPasswordError", "Password must be at least 8 characters", "newPassword");
        isValid = false;
    }

    // Validate confirm password
    if (confirmPassword === "") {
        showError("confirmPasswordError", "Please confirm your password", "confirmPassword");
        isValid = false;
    } else if (newPassword !== confirmPassword) {
        showError("confirmPasswordError", "Passwords do not match", "confirmPassword");
        isValid = false;
    }

    if (isValid) {
        const resetData = {
            email: email,
            newPassword: newPassword,
        };

        resetPassword(resetData);
    }
});

function resetPassword(resetData) {
    fetch("forgot-password.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(resetData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("Password reset successful! You can now login with your new password.");
                // Redirect to login page
                window.location.href = "../login/login.html";
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("An unexpected error occurred. Please try again.");
        });
}

// Toggle password visibility
document.querySelectorAll(".toggle-password").forEach((button) => {
    button.addEventListener("click", function () {
        const targetId = this.getAttribute("data-target");
        const input = document.getElementById(targetId);

        if (input.type === "password") {
            input.type = "text";
            this.textContent = "🙈";
        } else {
            input.type = "password";
            this.textContent = "👁️";
        }
    });
});

// Helper functions
function showError(errorId, message, inputId) {
    const errorElement = document.getElementById(errorId);
    errorElement.textContent = message;
    errorElement.classList.add("show");

    if (inputId) {
        document.getElementById(inputId).classList.add("error");
    }
}

function clearErrors() {
    document.querySelectorAll(".error-message").forEach((element) => {
        element.classList.remove("show");
        element.textContent = "";
    });

    document.querySelectorAll("input.error").forEach((element) => {
        element.classList.remove("error");
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}