// --- helpers ---
function loadCart() {
  try {
    const raw = localStorage.getItem("cart") || "[]";
    console.log(raw);
    return JSON.parse(raw);
  } catch (e) {
    console.warn("Failed to parse cart from storage", e);
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
}

// format money as $12.34
function money(val) {
  return "$" + Number(val).toFixed(2);
}

// --- helpers ---
function loadCart() {
  try {
    const raw = localStorage.getItem("cart") || "[]";
    console.log("Loaded cart:", raw);
    return JSON.parse(raw);
  } catch (e) {
    console.warn("Failed to parse cart from storage", e);
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
}

// format money as $12.34
function money(val) {
  return "$" + Number(val).toFixed(2);
}

// recalc everything and re-render
function renderCart() {
  const cartContainer = document.getElementById("cart-items");
  const cart = loadCart();

  if (!cartContainer) return;
  cartContainer.innerHTML = "";

  // If cart empty, show message
  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="empty-cart">
        <p>Your cart is empty.</p>
        <button id="go-menu-btn" class="add-more-btn">Browse Menu</button>
      </div>
    `;
    const goMenuBtn = document.getElementById("go-menu-btn");
    if (goMenuBtn) {
      goMenuBtn.addEventListener("click", () => {
        window.location.href = "../homepage/menu.php";
      });
    }
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

    // qty decrease
    row.querySelector(".dec").addEventListener("click", () => {
      const updatedCart = loadCart();
      if (updatedCart[index].qty > 1) {
        updatedCart[index].qty -= 1;
      } else {
        updatedCart.splice(index, 1);
      }
      saveCart(updatedCart);
      renderCart();
    });

    // qty increase
    row.querySelector(".inc").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart[index].qty += 1;
      saveCart(updatedCart);
      renderCart();
    });

    // remove item
    row.querySelector(".remove").addEventListener("click", () => {
      const updatedCart = loadCart();
      updatedCart.splice(index, 1);
      saveCart(updatedCart);
      renderCart();
    });

    // add to container
    cartContainer.appendChild(row);
  });

  // Update totals
  updateTotals(cart);
}

function updateTotals(cart) {
  const originalPriceEl = document.getElementById("original-price");
  const discountEl = document.getElementById("discount-amount");
  const totalEl = document.getElementById("order-total");
  const checkoutItemsText = document.getElementById("checkout-items-text");

  // Subtotal before discount
  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

  // Voucher / discount logic (placeholder = 0 for now)
  const discount = 0;

  // No delivery fee anymore
  const grandTotal = subtotal - discount;

  // Item count
  const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);

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

// --- Add More Buttons ---
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
  // Optional: preload cart items for testing
  // Uncomment below if you want demo data to show up automatically
  /*
  const demoCart = [
    { id: "1", name: "Margherita Pizza", price: 12.9, qty: 18 },
    { id: "2", name: "Pepperoni Pizza", price: 15.5, qty: 3 },
    { id: "3", name: "Hawaiian Pizza", price: 14.2, qty: 1 },
    { id: "44", name: "Cheesy Dip", price: 2.9, qty: 1 },
    { id: "13", name: "Four Cheese Supreme", price: 17.2, qty: 1 }
  ];
  if (!localStorage.getItem("cart")) {
    saveCart(demoCart);
  }
  */

  renderCart();
});
