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

// Initialize the search term
$searchTerm = '';

// Handle search request
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $searchTerm = $_GET['search_term'];
}

// Prepare SQL query with search filters
$sql = "SELECT item_name, batch_code, quantity, expiry_date FROM products WHERE 1";

// Add search filter if provided
if (!empty($searchTerm)) {
    $sql .= " AND (item_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
               OR batch_code LIKE '%" . $conn->real_escape_string($searchTerm) . "%')";
}

// Order by expiry date
$sql .= " ORDER BY expiry_date ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Sorted by Expiry</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="expiry_products.css" rel="stylesheet">
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
    <a href="expiry_products.php"><i class="fas fa-calendar-alt icon"></i> Products by Expiry</a> <!-- New Link -->
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="dashboard-header">
        <h1>Products Sorted by Expiry Date</h1>
    </div>

    <!-- Search Form -->
    <div class="search-container">
        <form action="expiry_products.php" method="GET">
            <input type="text" name="search_term" placeholder="Search by Item Name or Batch Code" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" name="search">Search</button>
        </form>
    </div>

    <div class="product-list">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Batch Code</th>
                    <th>Quantity</th>
                    <th>Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['item_name'] . "</td>
                                <td>" . $row['batch_code'] . "</td>
                                <td>" . $row['quantity'] . "</td>
                                <td>" . $row['expiry_date'] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
