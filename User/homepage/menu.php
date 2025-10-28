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
                    ORDER NOW
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

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
        alert(name + ' added to cart!');
      });
    });
    </script>

</body>
</html>
