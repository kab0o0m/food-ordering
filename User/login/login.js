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

// ---------- main ----------
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (!loginForm) return;

  // Toggle password visibility
  document.querySelector(".toggle-password").addEventListener("click", function () {
    const input = document.getElementById("password");
    if (!input) return;
    const isPwd = input.type === "password";
    input.type = isPwd ? "text" : "password";
    this.textContent = isPwd ? "🙈" : "👁️";
  });

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearErrors();

    const email = document.getElementById("email")?.value.trim() || "";
    const password = document.getElementById("password")?.value || "";
    let ok = true;

    if (!email) {
      showError("emailError", "Please enter your email", "email");
      ok = false;
    } else if (!isValidEmail(email)) {
      showError("emailError", "Please enter a valid email", "email");
      ok = false;
    }
    if (!password) {
      showError("passwordError", "Please enter your password", "password");
      ok = false;
    }

    if (!ok) {
      notify("error", "Fix the highlighted fields", "Please correct the form and try again.");
      return;
    }

    try {
      const res = await fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      let data;
      try {
        data = await res.json();
      } catch {
        notify("error", "Server Error", "Invalid response from server.");
        return;
      }

      if (res.ok && data?.success) {
        localStorage.setItem("isLoggedIn", "true");
        localStorage.setItem("user", JSON.stringify(data.user));

        Swal.fire({
          icon: "success",
          title: `Welcome back, ${data.user.name}!`,
          showConfirmButton: false,
          timer: 2000,
        }).then(() => {
          window.location.href = "../homepage/menu.php";
        });
      } else {
        notify("error", "Login Failed", data?.message || "Invalid email or password");
      }
    } catch (err) {
      console.error(err);
      notify("error", "Unexpected Error", "Please try again later.");
    }
  });
});
