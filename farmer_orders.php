<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "FARMER") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];

$sql = "SELECT orders.id AS order_id, orders.created_at,
               order_items.quantity, order_items.price, order_items.total,
               products.crop_name, products.image,
               users.name AS customer_name, users.phone AS customer_phone, users.address AS customer_address
        FROM order_items
        JOIN orders ON order_items.order_id = orders.id
        JOIN products ON order_items.product_id = products.id
        JOIN users ON orders.customer_id = users.id
        WHERE order_items.farmer_id = ?
        ORDER BY orders.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders on My Products - FarmConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>
  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <a href="add_product.php">Add Product</a>
    <a href="farmer_orders.php">Orders</a>
    <a href="my_chats.php">Chats</a>
    <a href="website_review.php?type=selling">Review Website</a>
    <a href="logout.php">Logout</a>
  </div>
</header>

<div class="container">
  <div class="box">
    <h2>Customer Orders</h2>

    <?php if ($result && $result->num_rows > 0) { ?>
      <div class="card-grid">
        <?php while($row = $result->fetch_assoc()) { ?>
          <div class="card">
            <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" alt="<?= htmlspecialchars($row["crop_name"]) ?>">
            <h3><?= htmlspecialchars($row["crop_name"]) ?></h3>

            <p><b>Order ID:</b> <?= htmlspecialchars($row["order_id"]) ?></p>
            <p><b>Customer:</b> <?= htmlspecialchars($row["customer_name"]) ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($row["customer_phone"]) ?></p>
            <p><b>Address:</b> <?= htmlspecialchars($row["customer_address"]) ?></p>
            <p><b>Quantity:</b> <?= htmlspecialchars($row["quantity"]) ?> Kg</p>
            <p><b>Price:</b> ₹<?= htmlspecialchars($row["price"]) ?> / Kg</p>
            <p><b>Total:</b> ₹<?= htmlspecialchars($row["total"]) ?></p>
            <p><b>Order Date:</b> <?= htmlspecialchars($row["created_at"]) ?></p>

            <br>
            <a class="action-link approve-link" href="website_review.php?type=selling">Review Website</a>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <p>No orders received yet.</p>
    <?php } ?>
  </div>
</div>

</body>
</html>