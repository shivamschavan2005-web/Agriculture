<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];
$msg = isset($_GET["msg"]) ? $_GET["msg"] : "";

$sql = "SELECT 
            cart.id AS cart_id,
            cart.quantity AS cart_qty,
            products.id AS product_id,
            products.crop_name,
            products.price,
            products.quantity AS stock_qty,
            products.image,
            users.name AS farmer_name,
            users.phone AS farmer_phone
        FROM cart
        JOIN products ON cart.product_id = products.id
        JOIN users ON products.farmer_id = users.id
        WHERE cart.customer_id = ?
        ORDER BY cart.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $row["item_total"] = $row["cart_qty"] * $row["price"];
    $subtotal += $row["item_total"];
    $items[] = $row;
}

$delivery_charge = ($subtotal >= 500) ? 0 : (($subtotal > 0) ? 40 : 0);
$grand_total = $subtotal + $delivery_charge;
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Cart</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function toggleTransactionField() {
      const method = document.getElementById("payment_method").value;
      const box = document.getElementById("transaction_box");
      box.style.display = (method === "UPI" || method === "CARD") ? "block" : "none";
    }
  </script>
</head>
<body>

<header>
  <div class="header-wrap">
    <img src="uploads/logo.png" alt="Logo" class="site-logo">
    <div class="header-text">
      <h1>Farmer to Customer Marketplace</h1>
      <p>Online Marketing Platform</p>
    </div>
  </div>
</header>

<nav>
  <a href="index.php">Home</a>
  <a href="cart.php">Cart</a>
  <a href="my_orders.php">My Orders</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">
  <div class="box">
    <h2>My Cart</h2>

    <?php if ($msg != "") { ?>
      <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php } ?>

    <?php if (count($items) > 0) { ?>
      <table>
        <tr>
          <th>Image</th>
          <th>Crop</th>
          <th>Farmer</th>
          <th>Farmer Phone</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Available Stock</th>
          <th>Total</th>
          <th>Action</th>
        </tr>

        <?php foreach ($items as $item) { ?>
        <tr>
          <td>
            <img src="uploads/<?= htmlspecialchars($item["image"]) ?>"
                 alt="<?= htmlspecialchars($item["crop_name"]) ?>"
                 style="width:70px;height:55px;object-fit:cover;border-radius:6px;">
          </td>
          <td><?= htmlspecialchars($item["crop_name"]) ?></td>
          <td><?= htmlspecialchars($item["farmer_name"]) ?></td>
          <td><?= htmlspecialchars($item["farmer_phone"]) ?></td>
          <td>₹<?= $item["price"] ?> / Kg</td>
          <td><?= $item["cart_qty"] ?> Kg</td>
          <td><?= $item["stock_qty"] ?> Kg</td>
          <td>₹<?= $item["item_total"] ?></td>
          <td>
            <a href="remove_cart.php?id=<?= $item["cart_id"] ?>"
               onclick="return confirm('Remove this item from cart?')">Remove</a>
          </td>
        </tr>
        <?php } ?>
      </table>

      <div class="summary-box">
        <p><b>Subtotal:</b> ₹<?= $subtotal ?></p>
        <p><b>Delivery Charge:</b> ₹<?= $delivery_charge ?></p>
        <p><b>Grand Total:</b> ₹<?= $grand_total ?></p>
        <p><small>Free delivery for orders above ₹500</small></p>
      </div>

      <form method="post" action="checkout_cart.php">
        <label>Delivery Address</label>
        <input type="text" name="delivery_address" placeholder="Enter delivery address" required>

        <label>Delivery Phone Number</label>
        <input type="text" name="delivery_phone" placeholder="Enter phone number" required>

        <label>Payment Method</label>
        <select name="payment_method" id="payment_method" onchange="toggleTransactionField()" required>
          <option value="">Select Payment Method</option>
          <option value="COD">Cash on Delivery</option>
          <option value="UPI">UPI</option>
          <option value="CARD">Card</option>
        </select>

        <div id="transaction_box" style="display:none;">
          <label>Transaction ID</label>
          <input type="text" name="transaction_id" placeholder="Enter transaction ID for UPI/Card">
        </div>

        <button type="submit">Place Order</button>
      </form>
    <?php } else { ?>
      <p>Your cart is empty.</p>
    <?php } ?>
  </div>
</div>

</body>
</html>