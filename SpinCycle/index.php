<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Function to generate a CSRF token
function generate_csrf_token() {
    return bin2hex(random_bytes(32));
}

// Function to validate a CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

// Handle Status Update
if (isset($_POST['status']) && isset($_POST['order_id'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $order_id = intval($_POST['order_id']);
    $status = sanitize_input($_POST['status']);

    $sql = "UPDATE laundry_orders SET status = '$status' WHERE id = '$order_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Order status updated successfully!</p>";
    } else {
        echo "<p>Error updating order status: " . $conn->error . "</p>";
    }
}

// Process Laundry Submission Form (Registered Users)
if (isset($_POST['submit_laundry'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $user_id = $_SESSION['user_id'];
    $service_type = sanitize_input($_POST['service_type']);
    $weight = floatval($_POST['weight']);
    $special_instructions = sanitize_input($_POST['special_instructions']);
    $total_cost = floatval($_POST['total_cost']); // Get the total cost from the hidden input

    // Insert the laundry order into the database
    $sql = "INSERT INTO laundry_orders (user_id, service_type, weight, special_instructions, total_cost)
            VALUES ('$user_id', '$service_type', '$weight', '$special_instructions', '$total_cost')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Laundry order submitted successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Process Walk-in Laundry Order (Admin Dashboard)
if (isset($_POST['submit_walkin_laundry'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $walk_in_name = sanitize_input($_POST['walk_in_name']);
    $service_type = sanitize_input($_POST['service_type']);
    $weight = floatval($_POST['weight']);
    $special_instructions = sanitize_input($_POST['special_instructions']);
    $total_cost = floatval($_POST['total_cost']); // Get the total cost from the hidden input

    // Insert the laundry order into the database, setting user_id to NULL
    $sql = "INSERT INTO laundry_orders (walk_in_name, service_type, weight, special_instructions, total_cost, user_id)
            VALUES ('$walk_in_name', '$service_type', '$weight', '$special_instructions', '$total_cost', NULL)";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Walk-in laundry order submitted successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Delete Laundry Order
if (isset($_POST['delete_order'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $order_id = intval($_POST['order_id']);
    $sql = "DELETE FROM laundry_orders WHERE id = '$order_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Laundry order deleted successfully!</p>";
    } else {
        echo "<p>Error deleting laundry order: " . $conn->error . "</p>";
    }
}

// Delete User
if (isset($_POST['delete_user'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $user_id = intval($_POST['user_id']);
    $sql = "DELETE FROM users WHERE id = '$user_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<p>User deleted successfully!</p>";
    } else {
        echo "<p>Error deleting user: " . $conn->error . "</p>";
    }
}

// Handle Edit Laundry Order Form Submission
if (isset($_POST['update_order'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $order_id = intval($_POST['order_id']);
    $service_type = sanitize_input($_POST['service_type']);
    $weight = floatval($_POST['weight']);
    $special_instructions = sanitize_input($_POST['special_instructions']);
    $status = sanitize_input($_POST['status']);
    $total_cost = floatval($_POST['total_cost']); // Get the total cost from the hidden input

    $sql = "UPDATE laundry_orders SET
            service_type = '$service_type',
            weight = '$weight',
            special_instructions = '$special_instructions',
            status = '$status',
            total_cost = '$total_cost'
            WHERE id = '$order_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Laundry order updated successfully!</p>";
    } else {
        echo "<p>Error updating laundry order: " . $conn->error . "</p>";
    }
}

// Handle Edit User Form Submission
if (isset($_POST['update_user'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $user_id = intval($_POST['user_id']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);

    $sql = "UPDATE users SET
            username = '$username',
            email = '$email',
            role = '$role'
            WHERE id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<p>User updated successfully!</p>";
    } else {
        echo "<p>Error updating user: " . $conn->error . "</p>";
    }
}

// Determine current section for sidebar highlighting
$currentPage = 'dashboard';
if (isset($_GET['manage_orders']) || isset($_GET['edit_order'])) {
    $currentPage = 'orders';
} elseif (isset($_GET['manage_users']) || isset($_GET['edit_user'])) {
    $currentPage = 'users';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpinCycle Laundry Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        function calculateTotalCost(formType) {
            console.log("calculateTotalCost called for form: " + formType); // Debugging line
            let serviceTypeSelect;
            let weightInput;
            let totalCostDisplay;
            let totalCostInput;

            if (formType === "walkin") {
                serviceTypeSelect = document.getElementById('walkin_service_type');
                weightInput = document.getElementById('walkin_weight');
                totalCostDisplay = document.getElementById('walkin_total_cost_display');
                totalCostInput = document.getElementById('walkin_total_cost');
            } else if (formType === "user") {
                serviceTypeSelect = document.getElementById('user_service_type');
                weightInput = document.getElementById('user_weight');
                totalCostDisplay = document.getElementById('user_total_cost_display');
                totalCostInput = document.getElementById('user_total_cost');
            } else if (formType === "edit") {
                serviceTypeSelect = document.getElementById('edit_service_type');
                weightInput = document.getElementById('edit_weight');
                totalCostDisplay = document.getElementById('edit_total_cost_display');
                totalCostInput = document.getElementById('edit_total_cost');
            } else {
                console.log("Invalid form type"); // Debugging line
                return; // Invalid form type
            }

            const selectedService = serviceTypeSelect.options[serviceTypeSelect.selectedIndex];
            const minPrice = parseFloat(selectedService.dataset.minPrice);
            const maxPrice = parseFloat(selectedService.dataset.maxPrice);
            const weight = parseFloat(weightInput.value);

            console.log("Selected Service:", selectedService.value); // Debugging line
            console.log("Min Price:", minPrice); // Debugging line
            console.log("Max Price:", maxPrice); // Debugging line
            console.log("Weight:", weight); // Debugging line

            if (isNaN(minPrice) || isNaN(maxPrice) || isNaN(weight)) {
                console.log("One or more values are NaN"); // Debugging line
                totalCostDisplay.textContent = '0.00';
                totalCostInput.value = '0.00';
                return;
            }

            // Calculate the average price within the range
            const averagePrice = (minPrice + maxPrice) / 2;
            const totalCost = weight * averagePrice;

            console.log("Total Cost:", totalCost); // Debugging line

            totalCostDisplay.textContent = totalCost.toFixed(2);
            totalCostInput.value = totalCost.toFixed(2);
        }

        function toggleDarkMode() {
    document.documentElement.classList.toggle("dark-mode");
    document.body.classList.toggle("dark-mode");

    localStorage.setItem(
        "darkMode",
        document.body.classList.contains("dark-mode") ? "on" : "off"
    );
}

window.addEventListener("load", () => {
    if (localStorage.getItem("darkMode") === "on") {
        document.documentElement.classList.add("dark-mode");
        document.body.classList.add("dark-mode");
    }
});


    </script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-logo"><?php echo isset($_SESSION['username']) ? strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 2)) : 'SC'; ?></div>
                <div>
                    <span class="sidebar-title"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'SpinCycle'; ?></span>
                    <span class="sidebar-role"><?php echo isset($_SESSION['role']) ? 'Logged in as ' . htmlspecialchars($_SESSION['role']) : ''; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Dashboard</span>
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="index.php?manage_orders" class="sidebar-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-soap"></i>
                    <span>Orders</span>
                </a>
                <a href="index.php?manage_users" class="sidebar-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="app-main">
            <div class="container">
                <div class="admin-header">
                    <div class="nav-left">
                        <div>
                            <h1 class="header-title">SpinCycle Dashboard</h1>
                            <p class="header-subtitle">Real-time view of your laundry operations.</p>
                        </div>
                    </div>
                    <div class="nav-right">
                        <button id="darkModeToggle" class="dark-toggle">🌙 Dark Mode</button>
                        <a href="logout.php" class="logout-link">Logout</a>
                    </div>
                </div>

                <div class="dashboard-hero">
                    <div class="dashboard-hero-left">
                        <img src="assets/Untitled.png" alt="SpinCycle Logo" class="dashboard-logo">
                        <div>
                            <h2>SpinCycle Laundry Dashboard</h2>
                            <p>Monitor orders, revenue, and customer activity in one clean view.</p>
                        </div>
                    </div>
                </div>

                <?php
        if (isset($_SESSION['user_id'])) {
            // Check if the user is an admin
            if ($_SESSION['role'] == 'admin') {
                // Admin Dashboard
                echo "<h3>Admin Dashboard</h3>";

                // Calculate Dashboard Metrics
                $today = date("Y-m-d");

                // Total Orders Today
                $sql_total_orders_today = "SELECT COUNT(*) AS total FROM laundry_orders WHERE DATE(order_date) = '$today'";
                $result_total_orders_today = $conn->query($sql_total_orders_today);
                $row_total_orders_today = $result_total_orders_today->fetch_assoc();
                $total_orders_today = $row_total_orders_today['total'];

                // Total Revenue
                $sql_total_revenue = "SELECT SUM(total_cost) AS total FROM laundry_orders";
                $result_total_revenue = $conn->query($sql_total_revenue);
                $row_total_revenue = $result_total_revenue->fetch_assoc();
                $total_revenue = $row_total_revenue['total'];

                // Orders by Status
                $sql_orders_by_status = "SELECT status, COUNT(*) AS total FROM laundry_orders GROUP BY status";
                $result_orders_by_status = $conn->query($sql_orders_by_status);
                $orders_by_status = array();
                while ($row = $result_orders_by_status->fetch_assoc()) {
                    $orders_by_status[$row['status']] = $row['total'];
                }

                // Display Dashboard Metrics
                echo "<div class='dashboard-grid'>";
                    echo "<div class='dashboard-card'>";
                        echo "<h4><i class='fa-solid fa-box'></i> Total Orders Today</h4>";
                        echo "<h2>" . htmlspecialchars($total_orders_today) . "</h2>";
                    echo "</div>";

                    echo "<div class='dashboard-card'>";
                        echo "<h4><i class='fa-solid fa-peso-sign'></i> Total Revenue</h4>";
                        echo "<h2>₱" . htmlspecialchars($total_revenue) . "</h2>";
                    echo "</div>";

                    echo "<div class='dashboard-card'>";
                        echo "<h4><i class='fa-solid fa-chart-pie'></i> Orders by Status</h4>";
                        echo "<p>Received: " . htmlspecialchars(isset($orders_by_status['Received']) ? $orders_by_status['Received'] : 0) . "</p>";
                        echo "<p>Washing: " . htmlspecialchars(isset($orders_by_status['Washing']) ? $orders_by_status['Washing'] : 0) . "</p>";
                        echo "<p>Ironing: " . htmlspecialchars(isset($orders_by_status['Ironing']) ? $orders_by_status['Ironing'] : 0) . "</p>";
                        echo "<p>Completed: " . htmlspecialchars(isset($orders_by_status['Completed']) ? $orders_by_status['Completed'] : 0) . "</p>";
                    echo "</div>";
                echo "</div><br><br>";


                // Manage Orders Section
                if (isset($_GET['manage_orders'])) {
                    echo "<h4>Manage Laundry Orders</h4>";

                    // Walk-in Laundry Order Form
                    echo "<h5>Create Walk-in Laundry Order</h5>";
                    echo "<form method='post' action=''>";
                        echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                        echo "<label for='walk_in_name'>Walk-in Customer Name:</label>";
                        echo "<input type='text' name='walk_in_name' required><br><br>";

                        echo "<label for='service_type'>Service Type:</label>";
                        echo "<select name='service_type' id='walkin_service_type' required onchange='calculateTotalCost(\"walkin\")'>";
                            // Fetch service types and their price ranges from the database
                            $sql_services = "SELECT service_name, min_price, max_price FROM services";
                            $result_services = $conn->query($sql_services);

                            if ($result_services->num_rows > 0) {
                                while ($row_service = $result_services->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row_service['service_name']) . "' data-min-price='" . htmlspecialchars($row_service['min_price']) . "' data-max-price='" . htmlspecialchars($row_service['max_price']) . "'>" . htmlspecialchars($row_service['service_name']) . " (₱" . htmlspecialchars($row_service['min_price']) . " - ₱" . htmlspecialchars($row_service['max_price']) . ")</option>";
                                }
                            }
                        echo "</select><br><br>";

                        echo "<label for='weight'>Weight (kg):</label>";
                        echo "<input type='number' name='weight' id='walkin_weight' step='0.01' required onchange='calculateTotalCost(\"walkin\")'><br><br>";

                        echo "<label for='special_instructions'>Special Instructions:</label>";
                        echo "<textarea name='special_instructions'></textarea><br><br>";

                        echo "<label>Total Cost (₱): <span id='walkin_total_cost_display'>0.00</span></label>";

                        echo "<input type='hidden' name='total_cost' id='walkin_total_cost' value='0.00'>";

                        echo "<button type='submit' name='submit_walkin_laundry'>Submit Walk-in Order</button>";
                    echo "</form><br><br>";

                    // Fetch all laundry orders
                    $sql = "SELECT * FROM laundry_orders ORDER BY order_date DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table>";
                            echo "<thead>";
                                echo "<tr>";
                                    echo "<th>Order Date</th>";
                                    echo "<th>User ID</th>";
                                    echo "<th>Walk-in Name</th>";
                                    echo "<th>Service Type</th>";
                                    echo "<th>Weight (kg)</th>";
                                    echo "<th>Special Instructions</th>";
                                    echo "<th>Status</th>";
                                    echo "<th>Total Cost</th>";
                                    echo "<th>Action</th>";
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                                while ($row = $result->fetch_assoc()) {
                                   echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                                        echo "<td>" . (($row['user_id']) ? htmlspecialchars($row['user_id']) : '-') . "</td>";
                                        echo "<td>" . (($row['walk_in_name']) ? htmlspecialchars($row['walk_in_name']) : '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['weight']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['special_instructions']) . "</td>";
                                        echo "<td>";
                                            // Status Update Form
                                            echo "<form method='post' action=''>";
                                                echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                                                echo "<input type='hidden' name='order_id' value='" . htmlspecialchars($row['id']) . "'>";
                                                echo "<select name='status' onchange='this.form.submit()'>";
                                                    echo "<option value='Received'" . ($row['status'] == 'Received' ? ' selected' : '') . ">Received</option>";
                                                    echo "<option value='Washing'" . ($row['status'] == 'Washing' ? ' selected' : '') . ">Washing</option>";
                                                    echo "<option value='Ironing'" . ($row['status'] == 'Ironing' ? ' selected' : '') . ">Ironing</option>";
                                                    echo "<option value='Completed'" . ($row['status'] == 'Completed' ? ' selected' : '') . ">Completed</option>";
                                                echo "</select>";
                                            echo "</form>";
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
                                        echo "<td><a href='?edit_order=" . htmlspecialchars($row['id']) . "'>Edit</a> |";
                                        echo " <a href='receipt.php?id=" . $row['id'] . "' target='_blank'>🧾 Receipt</a> |";
                                            echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this order?\");'>";
                                                echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                                                echo "<input type='hidden' name='order_id' value='" . htmlspecialchars($row['id']) . "'>";
                                                echo "<button type='submit' name='delete_order' class='action-delete'>Delete</button>";
                                            echo "</form></td>";
                                    echo "</tr>";
                                }
                            echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>No laundry orders found.</p>";
                    }
                }

                // Edit Laundry Order Form
                if (isset($_GET['edit_order'])) {
                    $order_id = intval($_GET['edit_order']);
                    $sql = "SELECT * FROM laundry_orders WHERE id = '$order_id'";
                    $result = $conn->query($sql);

                    if ($result->num_rows == 1) {
                        $row = $result->fetch_assoc();
                        echo "<h4>Edit Laundry Order</h4>";
                        echo "<form method='post' action=''>";
                            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                            echo "<input type='hidden' name='order_id' value='" . htmlspecialchars($row['id']) . "'>";

                            echo "<label for='service_type'>Service Type:</label>";
                            echo "<select name='service_type' id='edit_service_type' required onchange='calculateTotalCost(\"edit\")'>";
                                // Fetch service types and their price ranges from the database
                                $sql_services = "SELECT service_name, min_price, max_price FROM services";
                                $result_services = $conn->query($sql_services);

                                if ($result_services->num_rows > 0) {
                                    while ($row_service = $result_services->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row_service['service_name']) . "'" . ($row['service_type'] == $row_service['service_name'] ? ' selected' : '') . " data-min-price='" . htmlspecialchars($row_service['min_price']) . "' data-max-price='" . htmlspecialchars($row_service['max_price']) . "'>" . htmlspecialchars($row_service['service_name']) . " (₱" . htmlspecialchars($row_service['min_price']) . " - ₱" . htmlspecialchars($row_service['max_price']) . ")</option>";
                                    }
                                }
                            echo "</select><br><br>";

                            echo "<label for='weight'>Weight (kg):</label>";
                            echo "<input type='number' name='weight' id='edit_weight' step='0.01' value='" . htmlspecialchars($row['weight']) . "' required onchange='calculateTotalCost(\"edit\")'><br><br>";

                            echo "<label for='special_instructions'>Special Instructions:</label>";
                            echo "<textarea name='special_instructions'>" . htmlspecialchars($row['special_instructions']) . "</textarea><br><br>";

                            echo "<label for='status'>Status:</label>";
                            echo "<select name='status' required>";
                                echo "<option value='Received'" . ($row['status'] == 'Received' ? ' selected' : '') . ">Received</option>";
                                echo "<option value='Washing'" . ($row['status'] == 'Washing' ? ' selected' : '') . ">Washing</option>";
                                echo "<option value='Ironing'" . ($row['status'] == 'Ironing' ? ' selected' : '') . ">Ironing</option>";
                                echo "<option value='Completed'" . ($row['status'] == 'Completed' ? ' selected' : '') . ">Completed</option>";
                            echo "</select><br><br>";

                            echo "<label>Total Cost (₱): <span id='edit_total_cost_display'>0.00</span></label>";
                            echo "<input type='hidden' name='total_cost' id='edit_total_cost' value='" . htmlspecialchars($row['total_cost']) . "'>";

                            echo "<button type='submit' name='update_order'>Update Order</button>";
                        echo "</form>";
                    } else {
                        echo "<p>Order not found.</p>";
                    }
                }

                // Manage Users Section
                if (isset($_GET['manage_users'])) {
                    echo "<h4>Manage Users</h4>";
                    // Fetch all users
                    $sql = "SELECT * FROM users ORDER BY created_at DESC";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table>";
                            echo "<thead>";
                                echo "<tr>";
                                    echo "<th>ID</th>";
                                    echo "<th>Username</th>";
                                    echo "<th>Email</th>";
                                    echo "<th>Role</th>";
                                    echo "<th>Created At</th>";
                                    echo "<th>Action</th>";
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "<td><a href='?edit_user=" . htmlspecialchars($row['id']) . "'>Edit</a> |";
                                            echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>";
                                                echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                                                echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['id']) . "'>";
                                                echo "<button type='submit' name='delete_user' class='action-delete'>Delete</button>";
                                            echo "</form></td>";
                                    echo "</tr>";
                                }
                            echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>No users found.</p>";
                    }
                }

                // Edit User Form
                if (isset($_GET['edit_user'])) { $user_id = intval($_GET['edit_user']);
                    $sql = "SELECT * FROM users WHERE id = '$user_id'";
                    $result = $conn->query($sql);

                    if ($result->num_rows == 1) {
                        $row = $result->fetch_assoc();
                        echo "<h4>Edit User</h4>";
                        echo "<form method='post' action=''>";
                            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                            echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['id']) . "'>";

                            echo "<label for='username'>Username:</label>";
                            echo "<input type='text' name='username' value='" . htmlspecialchars($row['username']) . "' required><br><br>";

                            echo "<label for='email'>Email:</label>";
                            echo "<input type='email' name='email' value='" . htmlspecialchars($row['email']) . "' required><br><br>";

                            echo "<label for='role'>Role:</label>";
                            echo "<select name='role' required>";
                                echo "<option value='admin'" . ($row['role'] == 'admin' ? ' selected' : '') . ">Admin</option>";
                                echo "<option value='user'" . ($row['role'] == 'user' ? ' selected' : '') . ">User</option>";
                            echo "</select><br><br>";

                            echo "<button type='submit' name='update_user'>Update User</button>";
                        echo "</form>";
                    } else {
                        echo "<p>User not found.</p>";
                    }
                }
            } else {
                // Laundry Submission Form and Order Tracking for regular users
                echo "<div class='user-dashboard-center'>";
                echo "<h3 class='user-form-title'>Submit Laundry Order</h3>";
                echo "<form method='post' action='' class='user-laundry-form'>";
                    echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token']) . "'>";
                    echo "<label for='service_type'>Service Type:</label>";
                    echo "<select name='service_type' id='user_service_type' required onchange='calculateTotalCost(\"user\")'>";
                        // Fetch service types and their price ranges from the database
                        $sql_services = "SELECT service_name, min_price, max_price FROM services";
                        $result_services = $conn->query($sql_services);

                        if ($result_services->num_rows > 0) {
                            while ($row_service = $result_services->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row_service['service_name']) . "' data-min-price='" . htmlspecialchars($row_service['min_price']) . "' data-max-price='" . htmlspecialchars($row_service['max_price']) . "'>" . htmlspecialchars($row_service['service_name']) . " (₱" . htmlspecialchars($row_service['min_price']) . " - ₱" . htmlspecialchars($row_service['max_price']) . ")</option>";
                            }
                        }
                    echo "</select><br><br>";

                    echo "<label for='weight'>Weight (kg):</label>";
                    echo "<input type='number' name='weight' id='user_weight' step='0.01' required onchange='calculateTotalCost(\"user\")'><br><br>";

                    echo "<label for='special_instructions'>Special Instructions:</label>";
                    echo "<textarea name='special_instructions'></textarea><br><br>";

                    echo "<label>Total Cost (₱): <span id='user_total_cost_display'>0.00</span></label>";
                    echo "<input type='hidden' name='total_cost' id='user_total_cost' value='0.00'>";

                    echo "<button type='submit' name='submit_laundry'>Submit</button>";
                echo "</form>";

                // Fetch Laundry Orders
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM laundry_orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
                $result = $conn->query($sql);

                echo "<h3 class='user-orders-title'>Your Laundry Orders</h3>";

                if ($result->num_rows > 0) {
                    echo "<table>";
                        echo "<thead>";
                            echo "<tr>";
                                echo "<th>Order Date</th>";
                                echo "<th>Service Type</th>";
                                echo "<th>Weight (kg)</th>";
                                echo "<th>Special Instructions</th>";
                                echo "<th>Status</th>";
                                echo "<th>Total Cost</th>";
                            echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['weight']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['special_instructions']) . "</td>";
                                    $status = strtolower($row['status']);
                                    echo "<td><span class='status-badge status-$status'>" . htmlspecialchars($row['status']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
                                echo "</tr>";
                            }
                        echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "<p>No laundry orders found.</p>";
                }
                echo "</div>";
            }
        }
        ?>
            </div>
        </main>
    </div>
<script>
    const darkModeToggle = document.getElementById('darkModeToggle');
    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
    });
</script>
</body>
</html>