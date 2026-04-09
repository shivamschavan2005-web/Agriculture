<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "FARMER") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_SESSION["user_id"];
    $crop_name = trim($_POST["crop_name"]);
    $description = trim($_POST["description"]);
    $price = floatval($_POST["price"]);
    $market_price = floatval($_POST["market_price"]);
    $quantity = floatval($_POST["quantity"]);
    $locality = trim($_POST["locality"]);
    $harvest_date = $_POST["harvest_date"];
    $category = trim($_POST["category"]);

    if (
        $crop_name == "" || $description == "" || $price <= 0 || $market_price <= 0 ||
        $quantity <= 0 || $locality == "" || $harvest_date == "" || $category == ""
    ) {
        $message = "Please fill all fields correctly.";
    } else {
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
            $file_type = $_FILES["image"]["type"];
            $file_size = $_FILES["image"]["size"];

            if (!in_array($file_type, $allowed_types)) {
                $message = "Only JPG, JPEG, PNG, and WEBP images are allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $message = "Image size must be less than 5MB.";
            } else {
                $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $image_name = time() . "_" . rand(1000, 9999) . "." . $extension;
                $target = "uploads/" . $image_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
                    $stmt = $conn->prepare("INSERT INTO products (farmer_id, crop_name, description, price, market_price, quantity, locality, harvest_date, image, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "issdddssss",
                        $farmer_id,
                        $crop_name,
                        $description,
                        $price,
                        $market_price,
                        $quantity,
                        $locality,
                        $harvest_date,
                        $image_name,
                        $category
                    );

                    if ($stmt->execute()) {
                        $message = "Product added successfully.";
                    } else {
                        $message = "Database error while saving product.";
                    }
                } else {
                    $message = "Image upload failed.";
                }
            }
        } else {
            $message = "Please select an image.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - FarmConnect</title>
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
    <a href="logout.php">Logout</a>
  </div>
</header>

<div class="container">
  <div class="box" style="max-width: 750px; margin: 30px auto;">
    <h2>Add New Product</h2>

    <?php if ($message != "") { ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">
      <label>Crop Name</label>
      <input type="text" name="crop_name" placeholder="Enter crop name" required>

      <label>Category</label>
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="Vegetables">Vegetables</option>
        <option value="Fruits">Fruits</option>
        <option value="Grains">Grains</option>
        <option value="Dairy">Dairy</option>
        <option value="Flowers">Flowers</option>
      </select>

      <label>Description</label>
      <textarea name="description" placeholder="Enter crop description" required></textarea>

      <label>FarmConnect Price per Kg</label>
      <input type="number" step="0.01" name="price" placeholder="Enter your selling price per Kg" required>

      <label>Market Price per Kg</label>
      <input type="number" step="0.01" name="market_price" placeholder="Enter current market price per Kg" required>

      <label>Quantity Available (Kg)</label>
      <input type="number" step="0.01" name="quantity" placeholder="Enter available quantity" required>

      <label>Locality</label>
      <input type="text" name="locality" placeholder="Enter farm locality" required>

      <label>Harvest Date</label>
      <input type="date" name="harvest_date" required>

      <label>Upload Product Image</label>
      <input type="file" name="image" accept="image/*" required>

      <button type="submit">Add Product</button>
    </form>
  </div>
</div>

</body>
</html>