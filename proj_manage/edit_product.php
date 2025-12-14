<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stock_management"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch product data
    $sql = "SELECT * FROM inventory WHERE id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $item_name = $_POST['item_name'];
        $item_description = $_POST['item_description'];

        // Update product data
        $update_sql = "UPDATE inventory SET item_name = '$item_name', item_description = '$item_description' WHERE id = $id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Product updated successfully.";
            header("Location: products.php");
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Same style as the products page */
        /* Add your styling here */
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Stock Management</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
    <a href="stock_in.php"><i class="fas fa-arrow-circle-up icon"></i> Stock In</a>
    <a href="stock_out.php"><i class="fas fa-arrow-circle-down icon"></i> Stock Out</a>
    <a href="products.php"><i class="fas fa-boxes icon"></i> Products</a>
    <a href="history.php"><i class="fas fa-history icon"></i> Stock History</a>
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="dashboard-header">
        <h1>Edit Product</h1>
    </div>

    <!-- Edit Product Form -->
    <div class="form-container">
        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="POST">
            <label for="item_name">Item Name</label>
            <input type="text" name="item_name" value="<?php echo $product['item_name']; ?>" required>

            <label for="item_description">Item Description</label>
            <textarea name="item_descriptdion" required><?php echo $product['item_description']; ?></textarea>

            <button type="submit">Update Product</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
