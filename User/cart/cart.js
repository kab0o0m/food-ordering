// --- helpers ---

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

// --- Render Cart and Totals ---
function renderCart() {
  const cartContainer = document.getElementById("cart-items");
  const cart = loadCart();

  if (!cartContainer) return;
  cartContainer.innerHTML = "";

  // If cart is empty, show message
  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="empty-cart">
        <p>Your cart is empty.</p>
      </div>
    `;
    updateTotals(cart);
    return;
  }

  // Build each cart item
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
      } else {
        updatedCart.splice(index, 1); // Remove item if quantity is 0
      }
      saveCart(updatedCart);
      renderCart();
    });

    // Increase quantity
    row.querySelector(".inc").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart[index].qty += 1;
      saveCart(updatedCart);
      renderCart();
    });

    // Remove item
    row.querySelector(".remove").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart.splice(index, 1);
      saveCart(updatedCart);
      renderCart();
    });

    // Add to container
    cartContainer.appendChild(row);
  });

  // Update totals
  updateTotals(cart);
}

// --- Update Cart Totals ---
function updateTotals(cart) {
  const originalPriceEl = document.getElementById("original-price");
  const discountEl = document.getElementById("discount-amount");
  const totalEl = document.getElementById("order-total");
  const checkoutItemsText = document.getElementById("checkout-items-text");

  // Calculate subtotal before discount
  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

  // Placeholder discount logic
  const discount = 0;

  // Total calculation (no delivery fee for now)
  const grandTotal = subtotal - discount;

  // Item count
  const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);

  // Update UI elements
  if (originalPriceEl) originalPriceEl.textContent = money(subtotal);
  if (discountEl) discountEl.textContent = money(discount);
  if (totalEl) totalEl.textContent = money(grandTotal);
  if (checkoutItemsText)
    checkoutItemsText.textContent = `${itemCount} item${itemCount === 1 ? "" : "s"} | ${money(
      grandTotal
    )}`;
}

// --- Navbar Account Button Logic ---
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
        if (u && u.name) {
          username = u.name.split(" ")[0];
        }
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

// --- Add More Items Buttons ---
const addPizzaBtn = document.getElementById("add-pizza-btn");
if (addPizzaBtn) {
  addPizzaBtn.addEventListener("click", () => {
    window.location.href = "../homepage/menu.php";
  });
}

const addDrinkBtn = document.getElementById("add-drink-btn");
if (addDrinkBtn) {
  addDrinkBtn.addEventListener("click", () => {
    window.location.href = "../homepage/menu.php#Add-ons";
  });
}

// --- Initialize on page load ---
document.addEventListener("DOMContentLoaded", () => {
  renderCart();
});

// --- Checkout Button Event Listener ---
const checkoutBtn = document.getElementById("checkout-btn");
if (checkoutBtn) {
  checkoutBtn.addEventListener("click", () => {
    const cart = loadCart();

    if (!cart.length) {
      alert("Your cart is empty.");
      return;
    }

    // Must be logged in
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
    if (!isLoggedIn) {
      alert("Please log in before checking out.");
      window.location.href = "../login/login.html";
      return;
    }

    // Get user info from localStorage
    let userData = {};
    try {
      userData = JSON.parse(localStorage.getItem("user") || "{}");
    } catch (e) {
      userData = {};
    }

    if (!userData.email || !userData.name || !userData.phone) {
      alert("Your profile is incomplete. Please update your profile before ordering.");
      window.location.href = "../account/editprofile.html";
      return;
    }

    // Calculate totals
    const totals = calcTotalsForPayload(cart);

    // Prepare the payload to send to the backend
    const payload = {
      name: userData.name,
      phone: userData.phone,
      email: userData.email,
      cart: cart,
      total: totals.total,
    };

    console.log("Payload to send:", payload); // Log the payload for debugging

    // Send the data to checkout.php
    fetch("../checkout/checkout.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((res) => res.json())
      .then((data) => {
        console.log("Response from server:", data); // Log the response for debugging

        if (!data.success) {
          alert("Checkout failed: " + data.message);
          return;
        }

        // Store the last order id for later reference (thank you / my orders page)
        localStorage.setItem("lastOrderId", data.order_id);

        // Clear the cart
        localStorage.removeItem("cart");

        // Redirect to "my orders" page
        window.location.href = "../checkout/checkout.html";
      })
      .catch((err) => {
        console.error("Checkout Error:", err);
        alert("Unexpected error during checkout.");
      });
  });
}

// --- Calculate totals for payload ---
function calcTotalsForPayload(cart) {
  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const discount = 0; // placeholder
  const total = subtotal - discount;
  return { subtotal, discount, total };
}
