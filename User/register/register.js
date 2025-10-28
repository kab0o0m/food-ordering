// Register Form Validation
const registerForm = document.getElementById("registerForm");

registerForm.addEventListener("submit", function (e) {
  e.preventDefault();

  // Clear previous errors
  clearErrors();

  let isValid = true;

  // Grab field values once
  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const phone = document.getElementById("phone").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const terms = document.getElementById("terms").checked;

  // Validate name
  if (name === "") {
    showError("nameError", "Please enter your full name", "name");
    isValid = false;
  } else if (name.length < 2) {
    showError("nameError", "Name must be at least 2 characters", "name");
    isValid = false;
  }

  // Validate email
  if (email === "") {
    showError("emailError", "Please enter your email address", "email");
    isValid = false;
  } else if (!isValidEmail(email)) {
    showError("emailError", "Please enter a valid email address", "email");
    isValid = false;
  }

  // Validate phone
  if (phone === "") {
    showError("phoneError", "Please enter your phone number", "phone");
    isValid = false;
  } else if (!isValidPhone(phone)) {
    showError("phoneError", "Please enter a valid phone number", "phone");
    isValid = false;
  }

  // Validate password
  if (password === "") {
    showError("passwordError", "Please enter a password", "password");
    isValid = false;
  } else if (password.length < 8) {
    showError("passwordError", "Password must be at least 8 characters", "password");
    isValid = false;
  }

  // Validate confirm password
  if (confirmPassword === "") {
    showError("confirmPasswordError", "Please confirm your password", "confirmPassword");
    isValid = false;
  } else if (password !== confirmPassword) {
    showError("confirmPasswordError", "Passwords do not match", "confirmPassword");
    isValid = false;
  }

  // Validate terms
  if (!terms) {
    showError("termsError", "Please agree to the terms and conditions");
    isValid = false;
  }

  if (isValid) {
    const userData = {
      name: name,
      email: email,
      phone: phone,
      password: password,
    };

    registerUser(userData);
  }
});

function registerUser(userData) {
  fetch("register.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(userData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // save "session"
        localStorage.setItem("user", JSON.stringify(userData));
        localStorage.setItem("isLoggedIn", "true");

        // go to /User/menu.html
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

function isValidPhone(phone) {
  const phoneRegex = /^[\d\s\+\-\(\)]+$/;
  return phoneRegex.test(phone) && phone.replace(/\D/g, "").length >= 8;
}
