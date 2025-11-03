// ---------- helpers ----------

// Load cart data from localStorage
function loadCart() {
  try {
    const raw = localStorage.getItem("cart") || "[]";
    return JSON.parse(raw);
  } catch (e) {
    console.warn("Failed to parse cart from storage", e);
    return [];
  }
}

// Save cart data to localStorage
function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
}

// Format money as $12.34
function money(val) {
  return "$" + Number(val).toFixed(2);
}

// Show notification using SweetAlert2
function notify(type = "success", title = "", text = "", options = {}) {
  if (typeof Swal !== "undefined") {
    Swal.fire(Object.assign({ icon: type, title, text }, options));
  } else {
    alert(`${title ? title + ": " : ""}${text || ""}`);
  }
}

// Toast-style notification (top-right, auto-dismiss)
function toastNotify(type = "success", title = "") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      toast: true,
      icon: type,
      title: title,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
    });
  }
}

// ---------- Render Cart and Totals ----------
function renderCart() {
  const cartContainer = document.getElementById("cart-items");
  const cart = loadCart();

  if (!cartContainer) return;
  cartContainer.innerHTML = "";

  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="empty-cart">
        <p>Your cart is empty.</p>
      </div>
    `;
    updateTotals(cart);
    return;
  }

  cart.forEach((item, index) => {
    const lineTotal = item.price * item.qty;
    const row = document.createElement("div");
    row.className = "cart-item";

    row.innerHTML = `
      <div class="cart-item-card">
        <div class="item-info">
          <div class="item-name">${item.name}</div>
          <div class="item-line-price">${money(lineTotal)}</div>
        </div>
        <div class="item-controls">
          <div class="qty-control">
            <button class="qty-btn dec">−</button>
            <span class="item-quantity">${item.qty}</span>
            <button class="qty-btn inc">+</button>
          </div>
          <button class="action-btn remove">🗑️</button>
        </div>
      </div>
    `;

    // Decrease quantity
    row.querySelector(".dec").addEventListener("click", () => {
      const updatedCart = loadCart();
      if (updatedCart[index].qty > 1) {
        updatedCart[index].qty -= 1;
        toastNotify("info", "Quantity decreased");
      } else {
        updatedCart.splice(index, 1);
        toastNotify("warning", "Item removed from cart");
      }
      saveCart(updatedCart);
      renderCart();
    });

    // Increase quantity
    row.querySelector(".inc").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart[index].qty += 1;
      saveCart(updatedCart);
      toastNotify("success", "Quantity increased");
      renderCart();
    });

    // Remove item
    row.querySelector(".remove").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart.splice(index, 1);
      saveCart(updatedCart);
      toastNotify("warning", "Item removed from cart");
      renderCart();
    });

    cartContainer.appendChild(row);
  });

  updateTotals(cart);
}

// ---------- Update Cart Totals ----------
function updateTotals(cart) {
  const originalPriceEl = document.getElementById("original-price");
  const discountEl = document.getElementById("discount-amount");
  const totalEl = document.getElementById("order-total");
  const checkoutItemsText = document.getElementById("checkout-items-text");

  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const discount = 0;
  const grandTotal = subtotal - discount;
  const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);

  if (originalPriceEl) originalPriceEl.textContent = money(subtotal);
  if (discountEl) discountEl.textContent = money(discount);
  if (totalEl) totalEl.textContent = money(grandTotal);
  if (checkoutItemsText)
    checkoutItemsText.textContent = `${itemCount} item${itemCount === 1 ? "" : "s"} | ${money(
      grandTotal
    )}`;
}

// ---------- Navbar Account Button ----------
(function initAccountButton() {
  const accountBtn = document.getElementById("accountBtn");
  if (!accountBtn) return;

  const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
  if (isLoggedIn) {
    const rawUser = localStorage.getItem("user");
    let username = "Account";
    if (rawUser) {
      try {
        const u = JSON.parse(rawUser);
        if (u && u.name) username = u.name.split(" ")[0];
      } catch (e) {}
    }
    accountBtn.textContent = username;
    accountBtn.addEventListener("click", () => {
      window.location.href = "../account/account.html";
    });
  } else {
    accountBtn.textContent = "LOGIN";
    accountBtn.addEventListener("click", () => {
      window.location.href = "../login/login.html";
    });
  }
})();

// ---------- Add More Items ----------
document.getElementById("add-pizza-btn")?.addEventListener("click", () => {
  window.location.href = "../homepage/menu.php";
});
document.getElementById("add-drink-btn")?.addEventListener("click", () => {
  window.location.href = "../homepage/menu.php#Add-ons";
});

// ---------- Initialize Cart ----------
document.addEventListener("DOMContentLoaded", renderCart);

// ---------- Checkout ----------
document.getElementById("checkout-btn")?.addEventListener("click", () => {
  const cart = loadCart();

  if (!cart.length) {
    notify("error", "Empty Cart", "Your cart is empty.");
    return;
  }

  const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
  if (!isLoggedIn) {
    notify("error", "Login Required", "Please log in before checking out.");
    window.location.href = "../login/login.html";
    return;
  }

  let userData = {};
  try {
    userData = JSON.parse(localStorage.getItem("user") || "{}");
  } catch (e) {}

  if (!userData.email || !userData.name || !userData.phone) {
    notify("error", "Profile Incomplete", "Please update your profile before ordering.");
    window.location.href = "../account/editprofile.html";
    return;
  }

  const totals = calcTotalsForPayload(cart);
  const payload = {
    name: userData.name,
    phone: userData.phone,
    email: userData.email,
    cart: cart,
    total: totals.total,
  };

  fetch("../checkout/checkout.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        notify("error", "Checkout Failed", data.message || "Try again.");
        return;
      }

      localStorage.setItem("lastOrderId", data.order_id);
      localStorage.removeItem("cart");

      Swal.fire({
        icon: "success",
        title: "Order Placed!",
        text: "Redirecting to My Orders…",
        showConfirmButton: false,
        timer: 2000,
      }).then(() => {
        window.location.href = "../checkout/checkout.html";
      });
    })
    .catch((err) => {
      console.error("Checkout Error:", err);
      notify("error", "Unexpected Error", "Please try again later.");
    });
});

// ---------- Calculate totals for payload ----------
function calcTotalsForPayload(cart) {
  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const discount = 0;
  const total = subtotal - discount;
  return { subtotal, discount, total };
}
