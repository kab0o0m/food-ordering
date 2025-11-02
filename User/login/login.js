// Login Form Validation
const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (e) {
  e.preventDefault();

  // Clear previous errors
  clearErrors();

  let isValid = true;

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  // Validate email
  if (email === "") {
    showError("emailError", "Please enter your email address", "email");
    isValid = false;
  } else if (!isValidEmail(email)) {
    showError("emailError", "Please enter a valid email address", "email");
    isValid = false;
  }

  // Validate password
  if (password === "") {
    showError("passwordError", "Please enter your password", "password");
    isValid = false;
  }

  if (isValid) {
    loginUser(email, password);
  }
});

function loginUser(email, password) {
  fetch("login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email: email, password: password }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // store session-ish info
        localStorage.setItem("isLoggedIn", "true");
        localStorage.setItem("user", JSON.stringify(data.user));

        alert("Login successful! Welcome back, " + data.user.name + "!");

        // redirect to site homepage after login
        // login.html is in /User/login/, index.html is in /User/
        window.location.href = "../homepage/menu.php";
      } else {
        // backend said no
        showError("passwordError", data.message || "Invalid email or password", "password");
      }
    })
    .catch((err) => {
      console.error("Login error:", err);
      showError("passwordError", "Server error. Please try again later.", "password");
    });
}

// Toggle password visibility
document.querySelector(".toggle-password").addEventListener("click", function () {
  const input = document.getElementById("password");

  if (input.type === "password") {
    input.type = "text";
    this.textContent = "🙈";
  } else {
    input.type = "password";
    this.textContent = "👁️";
  }
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
