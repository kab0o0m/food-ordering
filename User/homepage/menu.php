<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- DB CONNECTION ----
$host = "127.0.0.1";
$port = 3307; // SSH tunnel port
$username = "kab0o0m";
$password = "phantoka123";
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

// ---- FETCH PRODUCTS ----
$sql = "SELECT id, name, description, price, category, image_url FROM products ORDER BY category, id";
$result = mysqli_query($conn, $sql);

$categories = []; // e.g. [ "Best Sellers" => [ {id:1,...}, ... ], "Vegetarian" => [...], ... ]

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cat = $row['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = [];
        }
        $categories[$cat][] = $row;
    }
}

// ---- REORDER CATEGORIES ----
// Force Best Sellers to appear first
if (isset($categories['Best Sellers'])) {
    $best = ['Best Sellers' => $categories['Best Sellers']];
    unset($categories['Best Sellers']);
    $categories = $best + $categories; // "Best Sellers" first, others follow
}

mysqli_close($conn);

// helper to make nice section titles if you want to tweak wording
function renderSectionTitle($cat) {
    // You can customize phrasing per category if needed:
    switch ($cat) {
        case 'Best Sellers':
            return 'BEST <span style="font-weight: normal">SELLERS</span>';
        case 'Vegetarian':
            return 'VEGETARIAN <span style="font-weight: normal">PIZZAS</span>';
        case 'Meat Lovers':
            return 'MEAT <span style="font-weight: normal">LOVERS</span>';
        case 'Premium':
            return 'PREMIUM <span style="font-weight: normal">PIZZAS</span>';
        case 'Add-ons':
            return 'SIDES & <span style="font-weight: normal">ADD-ONS</span>';
        default:
            // fallback to just category name
            return htmlspecialchars($cat);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoodHub - Menu</title>

    <!-- adjust path if needed -->
    <link rel="stylesheet" href="../style.css" />
</head>
<body>
    <style>
      /* --- Add-to-cart Toast --- */
      .cart-toast {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        min-width: 260px;
        max-width: 320px;
        background: #1a1a1a;
        color: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        padding: 16px 18px;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px) scale(0.98);
        transition: all 0.25s ease;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Inter", Roboto, sans-serif;
        z-index: 9999;
      }

      .cart-toast.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
      }

      .cart-toast-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
      }

      .cart-toast-text {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
        font-size: 14px;
      }

      .cart-toast-text strong {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
        display: block;
        margin-bottom: 2px;
      }

      .cart-toast-text span {
        font-size: 13px;
        color: #aaa;
      }

      .cart-toast-view {
        margin-left: auto;
        background: #ffcc00;
        color: #000;
        border: 0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 10px;
        cursor: pointer;
        line-height: 1.2;
        white-space: nowrap;
      }

      .cart-toast-view:hover {
        filter: brightness(0.9);
      }

    </style>

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="logo">FoodHub</div>
      <ul class="nav-links">
        <li><a href="menu.php">HOME</a></li>
        <li><a href="../cart/cart.html">MY ORDER</a></li>
      </ul>
      <button class="account-btn" id="accountBtn">LOGIN</button>
    </nav>

    <div class="content-wrapper">

      <?php foreach ($categories as $catName => $items): ?>
        <!-- SECTION HEADER -->
        <div class="section-header">
          <h2><?php echo renderSectionTitle($catName); ?></h2>
        </div>

        <!-- GRID FOR THIS CATEGORY -->
        <div class="menu-grid">
          <?php foreach ($items as $p): ?>
            <div class="menu-item">
              <div class="menu-item-image">
                <!-- You don't have image column yet, so placeholder -->
                <img
                  src="<?php echo '../' . htmlspecialchars($p['image_url']); ?>"
                  alt="pizza"
                  onerror="this.src='../assets/images/best-sellers/pizza-placeholder.png';"
                />


                <?php if ($catName === 'Best Sellers'): ?>
                  <!-- example badge for best sellers, optional -->
                  <div class="discount-badge">
                    ★
                    <br />
                    <small>TOP</small>
                  </div>
                <?php endif; ?>
              </div>

              <div class="menu-item-content">
                <div>
                  <div class="menu-item-title">
                    <?php echo htmlspecialchars($p['name']); ?>
                  </div>

                  <div class="menu-item-description">
                    <?php echo htmlspecialchars($p['description']); ?>
                  </div>
                </div>

                <div class="menu-item-footer">
                  <div class="menu-item-price">
                    $<?php echo number_format((float)$p['price'], 2); ?>
                  </div>

                  <button
                    class="order-now-btn"
                    data-product-id="<?php echo $p['id']; ?>"
                    data-product-name="<?php echo htmlspecialchars($p['name']); ?>"
                    data-product-price="<?php echo htmlspecialchars($p['price']); ?>"
                  >
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

    </div>

        <!-- Toast Notification -->
    <div id="cart-toast" class="cart-toast">
      <div class="cart-toast-content">
        <div class="cart-toast-text">
          <strong id="cart-toast-name">Item added!</strong>
          <span>Added to cart</span>
        </div>
        <button id="cart-toast-view" class="cart-toast-view">View cart</button>
      </div>
    </div>

    <script>
    // --- Navbar button (LOGIN vs username) ---
    (function() {
      const btn = document.getElementById('accountBtn');
      const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
      if (isLoggedIn) {
        const userDataRaw = localStorage.getItem('user');
        let username = 'Account';
        if (userDataRaw) {
          try {
            const userData = JSON.parse(userDataRaw);
            if (userData && userData.name) {
              username = userData.name.split(' ')[0]; // first name only
            }
          } catch (e) {}
        }
        btn.textContent = username;
        btn.onclick = () => {
           window.location.href = '../account/account.html'
        };
      } else {
        btn.textContent = 'LOGIN';
        btn.onclick = () => {
          // adjust path depending on where login.html actually lives
          window.location.href = 'login/login.html';
        };
      }
    })();

    // --- Add to cart ---
    // --- Add to cart ---
    document.querySelectorAll('.order-now-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-product-id');
        const name = btn.getAttribute('data-product-name');
        const price = parseFloat(btn.getAttribute('data-product-price'));

        // load current cart
        let cart = [];
        try {
          cart = JSON.parse(localStorage.getItem('cart') || '[]');
        } catch (e) {
          cart = [];
        }

        // check if already in cart
        const found = cart.find(item => item.id === id);
        if (found) {
          found.qty += 1;
        } else {
          cart.push({
            id,
            name,
            price,
            qty: 1
          });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        console.log(localStorage);

        // NEW: nice toast
        showToast(name);
      });
    });

    // --- Toast behavior ---
    let toastTimeout = null;

    function showToast(productName) {
      const toast = document.getElementById('cart-toast');
      const toastName = document.getElementById('cart-toast-name');
      const toastViewBtn = document.getElementById('cart-toast-view');

      // update text
      toastName.textContent = productName + " added to cart";

      // show
      toast.classList.add('show');

      // clicking "View cart" takes user to cart
      toastViewBtn.onclick = () => {
        window.location.href = '../cart/cart.html';
      };

      // auto-hide after 3 seconds
      if (toastTimeout) {
        clearTimeout(toastTimeout);
      }
      toastTimeout = setTimeout(() => {
        hideToast();
      }, 3000);
    }

    function hideToast() {
      const toast = document.getElementById('cart-toast');
      toast.classList.remove('show');
    }

    </script>

</body>
</html>
