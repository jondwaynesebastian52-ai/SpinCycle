<?php
$password = 'YourAdminPassword'; // Replace with your desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed_password . "\n";
?>