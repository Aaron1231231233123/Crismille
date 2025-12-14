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

// Function to generate random batch code with a random prefix
function generateBatchCode() {
    // List of possible prefixes
    $prefixes = ['AER-', 'INV-', 'STK-', 'BATCH-', 'CODE-'];
    
    // Select a random prefix from the array
    $randomPrefix = $prefixes[array_rand($prefixes)];
    
    // Random number between 100 and 999
    $randomNumber = rand(100, 999); 
    
    // Return the random batch code
    return $randomPrefix . $randomNumber;
}

// Initialize batch_code as null
$batch_code = generateBatchCode(); // Generate batch code when the page loads

// Handle form submission for stock in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date']; // Get expiry date from form
    
    // Use the batch code generated earlier
    $batch_code = generateBatchCode(); 

    // Fetch the item name based on the item_id
    $sql = "SELECT item_name FROM inventory WHERE id = $item_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $item_name = $row['item_name'];

    // Insert stock into products table, including quantity
    $insert_sql = $conn->prepare("INSERT INTO products (item_name, batch_code, expiry_date, quantity) VALUES (?, ?, ?, ?)");
    $insert_sql->bind_param("sssi", $item_name, $batch_code, $expiry_date, $quantity);

    if ($insert_sql->execute()) {
        // Log the action in the stock history (including quantity)
        $action = 'Stock In';
        $log_history_sql = $conn->prepare("INSERT INTO stock_history (item_id, action, quantity, expiry_date, date) VALUES (?, ?, ?, ?, NOW())");
        $log_history_sql->bind_param("isis", $item_id, $action, $quantity, $expiry_date);
        $log_history_sql->execute();

        // Update inventory quantity
        $update_sql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $quantity, $item_id);
        $update_stmt->execute();

        // Success message: Stock added
        echo "<script>window.onload = function() { showSuccessAlert('$batch_code'); }</script>";
    } else {
        echo "Error adding stock: " . $conn->error;
    }
}

// Fetch items from inventory for the form
$sql = "SELECT id, item_name FROM inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="stock_in.css" rel="stylesheet">

    <script>
        // Function to show success alert
        function showSuccessAlert(batchCode) {
            var alertBox = document.getElementById('successAlert');
            alertBox.innerHTML = 'Stock added successfully with batch code: ' + batchCode + ' <span class="close-alert" onclick="document.getElementById(\'successAlert\').style.display=\'none\'">×</span>';
            alertBox.style.display = 'block'; // Display the alert
            setTimeout(function() {
                alertBox.style.display = 'none'; // Hide after 5 seconds
            }, 5000);
        }
    </script>
</head>
<body>

<!-- Success Alert Box -->
<div id="successAlert" class="success-alert"></div>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Stock Management</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
    <a href="stock_in.php"><i class="fas fa-arrow-circle-up icon"></i> Stock In</a>
    <a href="stock_out.php"><i class="fas fa-arrow-circle-down icon"></i> Stock Out</a>
    <a href="products.php"><i class="fas fa-boxes icon"></i> Products</a>
    <a href="history.php"><i class="fas fa-history icon"></i> Stock History</a>
    <a href="expiry_products.php"><i class="fas fa-calendar-alt icon"></i> Products by Expiry</a>
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="dashboard-header">
        <h1>Stock In</h1>
    </div>

    <!-- Stock In Form -->
    <div class="form-container">
        <form action="stock_in.php" method="POST">
            <label for="item">Select Item</label>
            <select name="item_id" required>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['item_name'] . "</option>";
                    }
                }
                ?>
            </select>

            <label for="quantity">Quantity to Add</label>
            <input type="number" name="quantity" min="1" required>

            <label for="expiry_date">Expiry Date</label>
            <input type="date" name="expiry_date" required>

            <label for="batch_code">Batch Code</label>
            <input type="text" id="batch_code" name="batch_code" value="<?php echo $batch_code; ?>" readonly>

            <button type="submit">Add Stock</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
