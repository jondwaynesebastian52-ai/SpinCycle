<?php
session_start(); // Start the session
include 'includes/db_connect.php'; // Include the database connection

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to index if already logged in
    exit();
}

$login_error = ""; // Initialize an empty error message

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']); // Escape the username
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result === false) {
        // Query failed
        $login_error = "Database error: " . $conn->error;
    } else {
        echo "<p>Number of rows: " . $result->num_rows . "</p>"; // Debugging line
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                header("Location: index.php");  // Use PHP redirect
                exit();
            } else {
                $login_error = "Incorrect password."; // Set the error message
            }
        } else {
            $login_error = "User not found."; // Set the error message
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to SpinCycle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <img src="assets/Untitled.png" alt="SpinCycle Logo" class="logo">
        </div>

            <h2>Login to SpinCycle</h2>
            <?php echo $login_error; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <button type="submit" name="login">Login</button>
            </div>

        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
