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

// Query to fetch current stock items
$sql = "SELECT id, item_name, quantity FROM inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #333;
            color: #fff;
            height: 100%;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #fff;
            font-size: 16px;
        }

        .sidebar a:hover {
            background-color: #444;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }




        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 10px 20px;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .stock-table th,
        .stock-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .stock-table th {
            background-color: #4CAF50;
            color: white;
        }

        .stock-table td {
            background-color: #fff;
        }

        .stock-table td button {
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            cursor: pointer;
        }

        .stock-table td button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
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
            <h1>Stock Overview</h1>
        </div>

        <!-- Current Stock Table -->
        <table class="stock-table">
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                // Display each item in the inventory
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . $row['item_name'] . "</td>
                        <td>" . $row['quantity'] . "</td>
                        <td>
                            <button>Out</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No stock available</td></tr>";
            }
            ?>

        </table>
    </div>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>