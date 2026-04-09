<?php
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET["id"]);

$sql = "SELECT orders.*, users.name AS customer_name, users.email AS customer_email
        FROM orders
        JOIN users ON orders.customer_id = users.id
        WHERE orders.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$sql2 = "SELECT order_items.*, products.crop_name
         FROM order_items
         JOIN products ON order_items.product_id = products.id
         WHERE order_items.order_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Invoice</title>
  <link rel="stylesheet" href="style.css">
</head>
<body onload="window.print()">

<div class="container">
  <div class="box">
    <h2>Invoice / Bill</h2>

    <p><b>Order ID:</b> <?= $order["id"] ?></p>
    <p><b>Customer:</b> <?= htmlspecialchars($order["customer_name"]) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($order["customer_email"]) ?></p>
    <p><b>Delivery Address:</b> <?= htmlspecialchars($order["delivery_address"]) ?></p>
    <p><b>Delivery Phone:</b> <?= htmlspecialchars($order["delivery_phone"]) ?></p>
    <p><b>Payment Method:</b> <?= htmlspecialchars($order["payment_method"]) ?></p>
    <p><b>Payment Status:</b> <?= htmlspecialchars($order["payment_status"]) ?></p>
    <p><b>Transaction ID:</b> <?= htmlspecialchars($order["transaction_id"]) ?></p>

    <table>
      <tr>
        <th>Crop</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
      </tr>

      <?php while($item = $items->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($item["crop_name"]) ?></td>
        <td><?= $item["quantity"] ?> Kg</td>
        <td>₹<?= $item["price"] ?></td>
        <td>₹<?= $item["total"] ?></td>
      </tr>
      <?php } ?>
    </table>

    <br>
    <p><b>Subtotal:</b> ₹<?= $order["total_amount"] ?></p>
    <p><b>Delivery Charge:</b> ₹<?= $order["delivery_charge"] ?></p>
    <p><b>Grand Total:</b> ₹<?= $order["grand_total"] ?></p>
    <p><b>Order Date:</b> <?= $order["created_at"] ?></p>
  </div>
</div>

</body>
</html>