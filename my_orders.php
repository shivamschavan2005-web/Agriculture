<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];
$msg = isset($_GET["msg"]) ? $_GET["msg"] : "";

$sql = "SELECT 
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

            order_items.quantity,
            order_items.price,
            order_items.total,

            products.crop_name,
            products.image,

            users.name AS farmer_name,
            users.phone AS farmer_phone
        FROM orders
        JOIN order_items ON orders.id = order_items.order_id
        JOIN products ON order_items.product_id = products.id
        JOIN users ON order_items.farmer_id = users.id
        WHERE orders.customer_id = ?
        ORDER BY orders.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - FarmConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>
  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <a href="cart.php">Cart</a>
    <a href="my_orders.php">My Orders</a>
    <a href="my_chats.php">Chats</a>
    <a href="website_review.php?type=buying">Review Website</a>
    <a href="logout.php">Logout</a>
  </div>
</header>

<div class="container">
  <div class="box">
    <h2>My Orders</h2>

    <?php if ($msg != "") { ?>
      <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php } ?>

    <?php if ($result && $result->num_rows > 0) { ?>
      <div class="card-grid">
        <?php while($row = $result->fetch_assoc()) { ?>
          <div class="card">
            <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" alt="<?= htmlspecialchars($row["crop_name"]) ?>">

            <h3><?= htmlspecialchars($row["crop_name"]) ?></h3>

            <p><b>Order ID:</b> <?= htmlspecialchars($row["order_id"]) ?></p>
            <p><b>Farmer:</b> <?= htmlspecialchars($row["farmer_name"]) ?></p>
            <p><b>Farmer Phone:</b> <?= htmlspecialchars($row["farmer_phone"]) ?></p>

            <p><b>Quantity:</b> <?= htmlspecialchars($row["quantity"]) ?> Kg</p>
            <p><b>Price:</b> ₹<?= htmlspecialchars($row["price"]) ?> / Kg</p>
            <p><b>Item Total:</b> ₹<?= htmlspecialchars($row["total"]) ?></p>

            <p><b>Payment Method:</b> <?= htmlspecialchars($row["payment_method"]) ?></p>
            <p><b>Payment Status:</b> <?= htmlspecialchars($row["payment_status"]) ?></p>
            <p><b>Transaction ID:</b> <?= htmlspecialchars($row["transaction_id"]) ?></p>

            <p><b>Delivery Address:</b> <?= htmlspecialchars($row["delivery_address"]) ?></p>
            <p><b>Delivery Phone:</b> <?= htmlspecialchars($row["delivery_phone"]) ?></p>

            <p><b>Subtotal:</b> ₹<?= htmlspecialchars($row["total_amount"]) ?></p>
            <p><b>Delivery Charge:</b> ₹<?= htmlspecialchars($row["delivery_charge"]) ?></p>
            <p><b>Grand Total:</b> ₹<?= htmlspecialchars($row["grand_total"]) ?></p>
            <p><b>Order Date:</b> <?= htmlspecialchars($row["created_at"]) ?></p>

            <br>
            <a class="invoice-btn" href="invoice.php?id=<?= htmlspecialchars($row["order_id"]) ?>" target="_blank">Download Invoice</a>
            <a class="action-link approve-link" href="website_review.php?type=buying">Review Website</a>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <p>No orders placed yet.</p>
    <?php } ?>
  </div>
</div>

</body>
</html>