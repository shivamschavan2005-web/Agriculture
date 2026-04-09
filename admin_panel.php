<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    header("Location: login.php");
    exit();
}

$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];
$farmerCount = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='FARMER'")->fetch_assoc()["total"];
$customerCount = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='CUSTOMER'")->fetch_assoc()["total"];
$pendingFarmerCount = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='FARMER' AND approved=0")->fetch_assoc()["total"];
$productCount = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()["total"];
$orderCount = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()["total"];

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

$orders = $conn->query("
    SELECT 
        orders.id AS order_id,
        orders.total_amount,
        orders.payment_method,
        orders.payment_status,
        orders.transaction_id,
        orders.delivery_address,
        orders.delivery_phone,
        orders.delivery_charge,
        orders.grand_total,
        orders.created_at,

        customers.name AS customer_name,
        customers.email AS customer_email,
        customers.phone AS customer_phone,

        products.crop_name,
        products.image,

        order_items.quantity,
        order_items.price,
        order_items.total,

        farmers.name AS farmer_name
    FROM orders
    JOIN users AS customers ON orders.customer_id = customers.id
    JOIN order_items ON orders.id = order_items.order_id
    JOIN products ON order_items.product_id = products.id
    JOIN users AS farmers ON order_items.farmer_id = farmers.id
    ORDER BY orders.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FarmConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>

  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <a href="admin_panel.php">Admin</a>
    <a href="website_review.php">Website Reviews</a>
    <a href="logout.php">Logout</a>
  </div>
</header>

<div class="container">

    <div class="box">
        <h2>Dashboard Summary</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Users</h3>
                <p><?= htmlspecialchars($userCount) ?></p>
            </div>
            <div class="summary-card">
                <h3>Farmers</h3>
                <p><?= htmlspecialchars($farmerCount) ?></p>
            </div>
            <div class="summary-card">
                <h3>Customers</h3>
                <p><?= htmlspecialchars($customerCount) ?></p>
            </div>
            <div class="summary-card">
                <h3>Pending Farmers</h3>
                <p><?= htmlspecialchars($pendingFarmerCount) ?></p>
            </div>
            <div class="summary-card">
                <h3>Products</h3>
                <p><?= htmlspecialchars($productCount) ?></p>
            </div>
            <div class="summary-card">
                <h3>Orders</h3>
                <p><?= htmlspecialchars($orderCount) ?></p>
            </div>
        </div>
    </div>

    <div class="box">
        <h2>All Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Role</th>
                <th>Approved</th>
                <th>Action</th>
            </tr>

            <?php if ($users && $users->num_rows > 0) { ?>
                <?php while ($u = $users->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($u["id"]) ?></td>
                    <td><?= htmlspecialchars($u["name"]) ?></td>
                    <td><?= htmlspecialchars($u["email"]) ?></td>
                    <td><?= htmlspecialchars($u["password"]) ?></td>
                    <td><?= htmlspecialchars($u["phone"]) ?></td>
                    <td><?= htmlspecialchars($u["address"]) ?></td>
                    <td><?= htmlspecialchars($u["role"]) ?></td>
                    <td>
                        <?php if ($u["approved"]) { ?>
                            <span class="status-yes">Yes</span>
                        <?php } else { ?>
                            <span class="status-no">No</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($u["role"] === "FARMER" && !$u["approved"]) { ?>
                            <a class="action-link approve-link" href="approve_farmer.php?id=<?= htmlspecialchars($u["id"]) ?>">Approve</a>
                        <?php } ?>

                        <?php if ($u["role"] !== "ADMIN") { ?>
                            <a class="action-link delete-link" href="delete_user.php?id=<?= htmlspecialchars($u["id"]) ?>" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9">No users found.</td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="box">
        <h2>All Orders</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Crop</th>
                <th>Image</th>
                <th>Farmer</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Item Total</th>
                <th>Order Amount</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Transaction ID</th>
                <th>Delivery Address</th>
                <th>Delivery Phone</th>
                <th>Delivery Charge</th>
                <th>Grand Total</th>
                <th>Date</th>
            </tr>

            <?php if ($orders && $orders->num_rows > 0) { ?>
                <?php while ($o = $orders->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($o["order_id"]) ?></td>
                    <td><?= htmlspecialchars($o["customer_name"]) ?></td>
                    <td><?= htmlspecialchars($o["customer_email"]) ?></td>
                    <td><?= htmlspecialchars($o["customer_phone"]) ?></td>
                    <td><?= htmlspecialchars($o["crop_name"]) ?></td>
                    <td>
                        <img class="order-img" src="uploads/<?= htmlspecialchars($o["image"]) ?>" alt="<?= htmlspecialchars($o["crop_name"]) ?>">
                    </td>
                    <td><?= htmlspecialchars($o["farmer_name"]) ?></td>
                    <td><?= htmlspecialchars($o["quantity"]) ?> Kg</td>
                    <td>₹<?= htmlspecialchars($o["price"]) ?></td>
                    <td>₹<?= htmlspecialchars($o["total"]) ?></td>
                    <td>₹<?= htmlspecialchars($o["total_amount"]) ?></td>
                    <td><?= htmlspecialchars($o["payment_method"]) ?></td>
                    <td><?= htmlspecialchars($o["payment_status"]) ?></td>
                    <td><?= htmlspecialchars($o["transaction_id"]) ?></td>
                    <td><?= htmlspecialchars($o["delivery_address"]) ?></td>
                    <td><?= htmlspecialchars($o["delivery_phone"]) ?></td>
                    <td>₹<?= htmlspecialchars($o["delivery_charge"]) ?></td>
                    <td>₹<?= htmlspecialchars($o["grand_total"]) ?></td>
                    <td><?= htmlspecialchars($o["created_at"]) ?></td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="19">No orders found.</td>
                </tr>
            <?php } ?>
        </table>
    </div>

</div>

</body>
</html>