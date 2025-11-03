// Login Form Validation
const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (e) {
  e.preventDefault();

  clearErrors();

  let isValid = true;
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  if (email === "") {
    showError("emailError", "Please enter your email address", "email");
    isValid = false;
  } else if (!isValidEmail(email)) {
    showError("emailError", "Please enter a valid email address", "email");
    isValid = false;
  }

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
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        localStorage.setItem("isLoggedIn", "true");
        localStorage.setItem("user", JSON.stringify(data.user));

        // show success toast
        showToast(`Welcome back, ${data.user.name}!`, "success");

        // redirect after short delay
        setTimeout(() => {
          window.location.href = "../homepage/menu.php";
        }, 2000);
      } else {
        showError("passwordError", data.message || "Invalid email or password", "password");
        showToast("Invalid email or password", "error");
      }
    })
    .catch((err) => {
      console.error("Login error:", err);
      showError("passwordError", "Server error. Please try again later.");
      showToast("Server error. Please try again.", "error");
    });
}

// Toast popup
function showToast(message, type) {
  const toast = document.getElementById("toast");
  const toastMessage = document.getElementById("toastMessage");

  toastMessage.textContent = message;
  toast.classList.add("show");

  if (type === "error") toast.classList.add("error");
  else toast.classList.remove("error");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 2000);
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
  if (!errorElement) return;

  errorElement.textContent = message;
  errorElement.classList.add("show");

  if (inputId) {
    const input = document.getElementById(inputId);
    if (input) input.classList.add("error");
  }
}

function clearErrors() {
  document.querySelectorAll(".error-message").forEach((el) => {
    el.classList.remove("show");
    el.textContent = "";
  });

  document.querySelectorAll("input.error").forEach((el) => {
    el.classList.remove("error");
  });
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}
