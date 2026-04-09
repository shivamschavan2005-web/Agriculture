<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];
$review_after = isset($_GET["type"]) ? trim($_GET["type"]) : "";

if ($review_after !== "buying" && $review_after !== "selling") {
    $review_after = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating = (int)$_POST["rating"];
    $review_text = trim($_POST["review_text"]);
    $review_after = trim($_POST["review_after"]);

    if ($rating < 1 || $rating > 5) {
        $message = "Please select a valid star rating.";
    } elseif ($review_after == "") {
        $message = "Please select review type.";
    } else {
        $stmt = $conn->prepare("INSERT INTO website_reviews (user_id, role, review_after, rating, review_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_id, $role, $review_after, $rating, $review_text);

        if ($stmt->execute()) {
            $message = "Thank you for reviewing the website.";
        } else {
            $message = "Error while saving review.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Website Review - FarmConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>

  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <?php if ($_SESSION["role"] === "FARMER") { ?>
      <a href="add_product.php">Add Product</a>
      <a href="farmer_orders.php">Orders</a>
      <a href="my_chats.php">Chats</a>
    <?php } ?>

    <?php if ($_SESSION["role"] === "CUSTOMER") { ?>
      <a href="cart.php">Cart</a>
      <a href="my_orders.php">My Orders</a>
      <a href="my_chats.php">Chats</a>
    <?php } ?>
    <a href="logout.php">Logout</a>
  </div>
</header>

<div class="container">
  <div class="box" style="max-width:650px; margin:30px auto;">
    <h2>Rate Our Website</h2>

    <?php if ($message != "") { ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <form method="post">
      <label>Review After</label>
      <select name="review_after" required>
        <option value="">Select Type</option>
        <option value="buying" <?= ($review_after == "buying") ? "selected" : "" ?>>After Buying</option>
        <option value="selling" <?= ($review_after == "selling") ? "selected" : "" ?>>After Selling</option>
      </select>

      <label>Stars</label>
      <select name="rating" required>
        <option value="">Select Rating</option>
        <option value="5">5 - Excellent</option>
        <option value="4">4 - Very Good</option>
        <option value="3">3 - Good</option>
        <option value="2">2 - Average</option>
        <option value="1">1 - Poor</option>
      </select>

      <label>Short Review About Website</label>
      <textarea name="review_text" placeholder="Write a short review about the website"></textarea>

      <button type="submit">Submit Review</button>
    </form>
  </div>
</div>

</body>
</html>