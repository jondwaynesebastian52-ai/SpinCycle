<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

// Get order ID
if (!isset($_GET['id'])) {
    die("No order specified.");
}
$order_id = intval($_GET['id']);

// Fetch the order
$sql = "SELECT * FROM laundry_orders WHERE id = $order_id";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    die("Order not found.");
}

$order = $result->fetch_assoc();

// Check if user owns the order or is admin
if ($_SESSION['role'] != 'admin') {
    if ($order['user_id'] != $_SESSION['user_id']) {
        die("You are not allowed to view this receipt.");
    }

    // Only show receipt if order is Completed
    if ($order['status'] != 'Completed') {
        die("Receipt is only available for completed orders.");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
    body {
        font-family: "Segoe UI", Roboto, Arial, sans-serif;
        text-align: center;
        background: #f8fafc;
    }
    .receipt {
        width: 320px;
        margin: 20px auto;
        border: 1px dashed #9ca3af;
        padding: 18px;
        background: #ffffff;
    }
    .receipt h3 {
        margin: 0 0 4px;
        color: #2563eb;
    }
    .receipt h2 {
        margin: 8px 0;
        color: #16a34a;
    }
    .receipt hr {
        border: none;
        border-top: 1px dashed #d1d5db;
        margin: 8px 0;
    }
    .receipt p {
        margin: 2px 0;
        font-size: 14px;
    }
</style>
</head>
<body onload="window.print()">

<div class="receipt">
    <h3>SpinCycle Laundry</h3>
    <hr>
    <p>Order ID: <?= htmlspecialchars($order['id']) ?></p>
    <p>Date: <?= htmlspecialchars($order['order_date']) ?></p>

    <?php if ($order['user_id'] === NULL): ?>
        <p>Walk-in Customer: <?= htmlspecialchars($order['walk_in_name']) ?></p>
    <?php endif; ?>

    <p>Service: <?= htmlspecialchars($order['service_type']) ?></p>
    <p>Weight: <?= htmlspecialchars($order['weight']) ?> kg</p>

    <hr>
    <h2>Total: ₱<?= htmlspecialchars($order['total_cost']) ?></h2>

    <p>Status: <?= htmlspecialchars($order['status']) ?></p>
    <hr>
    <p>Thank you for choosing SpinCycle Laundry!</p>
</div>

</body>
</html>
