<?php
session_start();
include 'includes/db_connect.php';

$registration_message = ""; // Initialize an empty registration message

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];

    // Check if the username already exists
    $check_sql = "SELECT COUNT(*) FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    $count = $check_result->fetch_row()[0];

    if ($count > 0) {
        // Username already exists
        $registration_message = "<p class='error-message'>Error: Username already exists. Please choose a different username.</p>";
    } else {
        // Username is available, proceed with registration
        $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";

        if ($conn->query($sql) === TRUE) {
            $registration_message = "<p class='success-message'>Registration successful! <a href='login.php'>Login here</a></p>";
        } else {
            $registration_message = "<p class='error-message'>Error: " . $conn->error . "</p>";
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <img src="assets/Untitled.png" alt="SpinCycle Logo" class="logo">
        </div>

        <h2>Register to SpinCycle</h2>
        <?php echo $registration_message; ?>

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
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <button type="submit" name="register">Register</button>
            </div>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>