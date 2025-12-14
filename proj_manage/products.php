<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stock_management"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$products_per_page = 10;  // Display 10 products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Sanitize search query to prevent SQL injection
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Get total products count
$total_sql = "SELECT COUNT(id) AS total FROM inventory WHERE item_name LIKE '%$search_query%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $products_per_page);

// Fetch products for the current page based on search query
$sql = "SELECT id, item_name, quantity, price FROM inventory WHERE item_name LIKE '%$search_query%' LIMIT $products_per_page OFFSET $offset";
$result = $conn->query($sql);

// Handle form submission for adding new product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $item_name = $_POST['item_name'];
    $price = $_POST['price']; // Add price for the product

    // Check if the product already exists
    $check_sql = "SELECT id FROM inventory WHERE item_name = '$item_name'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $error_message = "Product with this name already exists.";
    } else {
        // Insert new product into inventory
        $insert_sql = "INSERT INTO inventory (item_name, quantity, price) VALUES ('$item_name', 0, '$price')";
        if ($conn->query($insert_sql) === TRUE) {
            $success_message = "New product added successfully.";
            echo "<script>setTimeout(function(){ location.reload(); }, 500);</script>";

        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Handle product removal
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];

    // Delete product from inventory
    $delete_sql = "DELETE FROM inventory WHERE id = $remove_id";
    if ($conn->query($delete_sql) === TRUE) {
        $success_message = "Product removed successfully.";
        echo "<script>setTimeout(function(){ location.reload(); }, 500);</script>";

    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle product update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $edit_id = $_POST['edit_id'];
    $item_name = $_POST['item_name'];
    $price = $_POST['price']; // Get the updated price

    // Update product in inventory
    $update_sql = "UPDATE inventory SET item_name='$item_name',  price='$price' WHERE id=$edit_id";
    if ($conn->query($update_sql) === TRUE) {
        $success_message = "Product updated successfully.";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="products_php.css" rel="stylesheet">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2 style="font-size: 24px">Stock Management</h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
        <a href="stock_in.php"><i class="fas fa-arrow-circle-up icon"></i> Stock In</a>
        <a href="stock_out.php"><i class="fas fa-arrow-circle-down icon"></i> Stock Out</a>
        <a href="products.php"><i class="fas fa-boxes icon"></i> Products</a>
        <a href="history.php"><i class="fas fa-history icon"></i> Stock History</a>
        <a href="expiry_products.php"><i class="fas fa-calendar-alt icon"></i> Products by Expiry</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Manage Products</h1>

        <!-- Feedback Messages -->
        <?php if (isset($success_message)): ?>
            <p class="success"><?= $success_message; ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="error"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- Add New Product Form -->
        <div class="form-container">
            <h2>Add New Product</h2>
            <form action="products.php" method="POST">
                <label for="item_name">Item Name:</label>
                <input type="text" name="item_name" id="item_name" required>

                <label for="price">Price:</label>
                <input type="number" name="price" id="price" required>

                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="products.php">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query); ?>" placeholder="Search Products...">
            <button type="submit">Search</button>
            <?php if ($search_query): ?>
                <button type="button" onclick="window.location.href='products.php'">Clear</button>
            <?php endif; ?>
        </form>
        <!-- Edit Product Modal -->
        <div class="modal" id="editModal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h2>Edit Product</h2>
                <form action="products.php" method="POST" id="editProductForm">
                    <input type="hidden" name="edit_id" id="edit_id">

                    <div class="form-group">
                        <label for="item_name">Product Name:</label>
                        <input type="text" name="item_name" id="item_name" required placeholder="Enter product name" required>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="text" name="quantity" id="quantity" required placeholder="Enter quantity" disabled>
                    </div>

                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" name="price" id="price" required placeholder="Enter price" required>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" name="edit_product">Save</button>
                        <button type="button" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Table -->
        <table class="product-table">
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['item_name']; ?></td>
                        <td><?= $row['quantity']; ?></td>
                        <td><?= number_format($row['price'], 2); ?></td>
                        <td>
                            <button type="button" onclick="openModal(<?= $row['id']; ?>, '<?= $row['item_name']; ?>', <?= $row['quantity']; ?>, <?= $row['price']; ?>)">Edit</button>
                            <!-- Remove Button -->
                            <a href="products.php?remove_id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Remove</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <p>Page <?= $page; ?> of <?= $total_pages; ?></p>
            <nav>
                <ul>
                    <?php if ($page > 1): ?>
                        <li><a href="?search=<?= urlencode($search_query); ?>&page=1">&laquo; First</a></li>
                        <li><a href="?search=<?= urlencode($search_query); ?>&page=<?= $page - 1; ?>">Prev</a></li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <li><a href="?search=<?= urlencode($search_query); ?>&page=<?= $page + 1; ?>">Next</a></li>
                        <li><a href="?search=<?= urlencode($search_query); ?>&page=<?= $total_pages; ?>">Last &raquo;</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <script>
    function openModal(id, name, quantity, price) {
        document.getElementById('edit_id').value = id;
        document.getElementById('item_name').value = name;
        document.getElementById('quantity').value = quantity;
        document.getElementById('price').value = price;
        // Set placeholders with the current values
        document.getElementById('item_name').setAttribute('placeholder', name);
        document.getElementById('price').setAttribute('placeholder', price);
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    document.getElementById('editProductForm').onsubmit = function() {
        setTimeout(function() {
            location.reload();
        }, 500);
    };
</script>
</body>

</html>

<?php $conn->close(); ?>