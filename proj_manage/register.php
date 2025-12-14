<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default username for XAMPP MySQL
$password = ""; // Default password for XAMPP MySQL
$dbname = "user_system"; // The name of the database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = "Username already exists!";
    } else {
        // Hash the password before saving it to the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Registration successful! You can now <a href='login.php'>Login</a>.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Simple styling for form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        .login-container p {
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Register</h2>
    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Register">
    </form>

    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    <?php if (isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
