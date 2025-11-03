// ---------- helpers ----------
function notify(type = "success", title = "", text = "", options = {}) {
  if (typeof Swal !== "undefined") {
    Swal.fire(Object.assign({ icon: type, title, text }, options));
  } else {
    alert(`${title ? title + ": " : ""}${text || ""}`);
  }
}

function showError(errorId, message, inputId) {
  const el = document.getElementById(errorId);
  if (el) {
    el.textContent = message || "";
    el.classList.add("show");
  }
  if (inputId) {
    const input = document.getElementById(inputId);
    if (input) input.classList.add("error");
  }
}

function clearErrors() {
  document.querySelectorAll(".error-message").forEach((e) => {
    e.textContent = "";
    e.classList.remove("show");
  });
  document.querySelectorAll("input.error").forEach((e) => e.classList.remove("error"));
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
  return /^[\d\s+\-()]+$/.test(phone) && phone.replace(/\D/g, "").length >= 8;
}

// ---------- main ----------
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registerForm");
  if (!form) return;

  // Toggle password visibility
  document.querySelectorAll(".toggle-password").forEach((btn) => {
    btn.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      const input = document.getElementById(targetId);
      if (!input) return;
      const isPwd = input.type === "password";
      input.type = isPwd ? "text" : "password";
      this.textContent = isPwd ? "🙈" : "👁️";
    });
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearErrors();

    const name = document.getElementById("name")?.value.trim() || "";
    const email = document.getElementById("email")?.value.trim() || "";
    const phone = document.getElementById("phone")?.value.trim() || "";
    const password = document.getElementById("password")?.value || "";
    const confirmPassword = document.getElementById("confirmPassword")?.value || "";
    const terms = !!document.getElementById("terms")?.checked;

    let ok = true;

    if (name.length < 2) {
      showError("nameError", "Please enter your full name", "name");
      ok = false;
    }
    if (!isValidEmail(email)) {
      showError("emailError", "Please enter a valid email", "email");
      ok = false;
    }
    if (!isValidPhone(phone)) {
      showError("phoneError", "Please enter a valid phone number", "phone");
      ok = false;
    }
    if (password.length < 8) {
      showError("passwordError", "Password must be at least 8 characters", "password");
      ok = false;
    }
    if (password !== confirmPassword) {
      showError("confirmPasswordError", "Passwords do not match", "confirmPassword");
      ok = false;
    }
    if (!terms) {
      showError("termsError", "Please agree to the terms and conditions");
      ok = false;
    }

    if (!ok) {
      notify("error", "Fix the highlighted fields", "Please correct the form and try again.");
      return;
    }

    try {
      const res = await fetch("register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, phone, password }),
      });

      let data;
      try {
        data = await res.json();
      } catch {
        notify("error", "Server Error", "Invalid response from server.");
        return;
      }

      if (res.ok && data?.success) {
        Swal.fire({
          icon: "success",
          title: "Registration Successful!",
          text: "Redirecting to login…",
          showConfirmButton: false,
          timer: 1800,
        }).then(() => {
          window.location.href = "../login/login.html?registered=1";
        });
      } else {
        notify("error", "Oops!", data?.message || "Registration failed.");
      }
    } catch (err) {
      console.error(err);
      notify("error", "Unexpected Error", "Please try again later.");
    }
  });
});
