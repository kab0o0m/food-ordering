<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "127.0.0.1";
$port = 3307;
$username = "kab0o0m";
$password = "phantoka123";
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY category, id";
$result = mysqli_query($conn, $sql);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoodHub - Product Management</title>
    <link rel="stylesheet" href="../../User/style.css" />
</head>
<body>
    <nav class="navbar">
      <div class="logo">FoodHub Admin</div>
      <ul class="nav-links">
        <li><a href="../admin.html">Admin</a></li>
        <li><a href="../sales-report/sales-report.html">Sales Report</a></li>
        <li><a href="../product-management/product-management.php">Products</a></li>
      </ul>
      <button
        id="exitBtn"
        class="account-btn"
        onclick="window.location.href='../../User/homepage/menu.php'">
        EXIT ADMIN
      </button>
    </nav>

    <div class="management-container">
        <!-- Header -->
        <div class="management-header">
            <div>
                <h1>🍕 Product Management</h1>
                <p>Manage your menu items</p>
            </div>
            <!-- <button class="add-product-btn" onclick="openAddModal()">+ Add New Product</button> -->
        </div>

        <!-- Products Table -->
        <div class="products-table">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <img 
                                    src="../../User/<?php echo htmlspecialchars($row['image_url']); ?>" 
                                    alt="product" 
                                    class="product-image"
                                    
                                >
                            </td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['description'], 0, 60)) . '...'; ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td><span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-edit" onclick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                                    <button class="btn btn-delete" onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit/Add Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Edit Product</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="productForm" class="modal-form" onsubmit="return submitForm(event)">
                <input type="hidden" id="productId" name="id">
                
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="productName" name="name" required>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea id="productDescription" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Price ($) *</label>
                    <input type="number" step="0.01" id="productPrice" name="price" required>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select id="productCategory" name="category" required>
                        <option value="Best Sellers">Best Sellers</option>
                        <option value="Vegetarian">Vegetarian</option>
                        <option value="Meat Lovers">Meat Lovers</option>
                        <option value="Premium">Premium</option>
                        <option value="Add-ons">Add-ons</option>
                    </select>
                </div>

                <div class="form-group">
                    <!-- <label>Image URL *</label>
                    <input type="text" id="productImage" name="image_url" placeholder="assets/images/category/image.png" required> -->
                </div> 

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        let isEditMode = false;

        function openEditModal(product) {
            isEditMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productCategory').value = product.category;
            // document.getElementById('productImage').value = product.image_url;
            document.getElementById('productModal').classList.add('show');
        }

        function openAddModal() {
            isEditMode = false;
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
            document.getElementById('productForm').reset();
        }

        function submitForm(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const action = isEditMode ? 'update-product.php' : 'add-product.php';

            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });

            return false;
        }

        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                fetch('delete-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>