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
    <style>
        .management-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .management-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .management-header h1 {
            font-size: 2.5rem;
        }

        .add-product-btn {
            padding: 1rem 2rem;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 0.3s;
        }

        .add-product-btn:hover {
            background-color: #45a049;
        }

        .products-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .btn-edit {
            background-color: #0088cc;
            color: white;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .category-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            background-color: #e3f2fd;
            color: #0088cc;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.8rem;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .modal-actions button {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-save {
            background-color: #4caf50;
            color: white;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">FoodHub Admin</div>
        <ul class="nav-links">
            <li><a href="../homepage/menu.php">HOME</a></li>
            <li><a href="../sales-report/sales-report.html">SALES REPORT</a></li>
            <li><a href="/">PRODUCTS</a></li>
        </ul>
        <button class="account-btn" onclick="window.location.href='../homepage/menu.php'">EXIT ADMIN</button>
    </nav>

    <div class="management-container">
        <!-- Header -->
        <div class="management-header">
            <div>
                <h1>🍕 Product Management</h1>
                <p>Manage your menu items</p>
            </div>
            <button class="add-product-btn" onclick="openAddModal()">+ Add New Product</button>
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