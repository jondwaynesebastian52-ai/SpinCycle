<?php
$db_host = "localhost";
$db_user = "root"; // Default XAMPP username
$db_pass = ""; // Default XAMPP password
$db_name = "spincycle_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php
$servername = "sql200.infinityfree.com";
$username = "if0_41136911";
$password = "YOUR_VPANEL_PASSWORD_HERE";
$dbname = "if0_41136911_SpinCycle";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>