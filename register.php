<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $role = trim($_POST["role"]);

    $approved = ($role === "FARMER") ? 0 : 1;

    $farmer_story = null;
    $farming_method = null;
    $experience_years = null;
    $farmer_photo_name = null;

    if ($role === "FARMER") {
        $farmer_story = trim($_POST["farmer_story"] ?? "");
        $farming_method = trim($_POST["farming_method"] ?? "");
        $experience_years = !empty($_POST["experience_years"]) ? intval($_POST["experience_years"]) : null;

        if (isset($_FILES["farmer_photo"]) && $_FILES["farmer_photo"]["error"] == 0) {
            $allowed_types = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
            $file_type = $_FILES["farmer_photo"]["type"];
            $file_size = $_FILES["farmer_photo"]["size"];

            if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) {
                $extension = pathinfo($_FILES["farmer_photo"]["name"], PATHINFO_EXTENSION);
                $farmer_photo_name = "farmer_" . time() . "_" . rand(1000,9999) . "." . $extension;
                move_uploaded_file($_FILES["farmer_photo"]["tmp_name"], "uploads/" . $farmer_photo_name);
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role, approved, farmer_story, farmer_photo, farming_method, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssisssi",
        $name,
        $email,
        $password,
        $phone,
        $address,
        $role,
        $approved,
        $farmer_story,
        $farmer_photo_name,
        $farming_method,
        $experience_years
    );

    if ($stmt->execute()) {
        if ($role === "FARMER") {
            $message = "Farmer registration submitted successfully. Wait for admin approval.";
        } else {
            $message = "Customer registration successful. You can login now.";
        }
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - FarmConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>

  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <a href="index.php#products-section">Products</a>
    <a href="index.php#about-section">About</a>
    <a href="index.php#contact-section">Contact</a>
    <a href="register.php">Register</a>
    <a href="login.php">Login</a>
  </div>
</header>

<div class="container">
  <div class="box" style="max-width: 700px; margin: 30px auto;">
    <h2>User Registration</h2>

    <?php if ($message != "") { ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">
      <label>Full Name</label>
      <input type="text" name="name" placeholder="Enter full name" required>

      <label>Email</label>
      <input type="email" name="email" placeholder="Enter email address" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required>

      <label>Phone Number</label>
      <input type="text" name="phone" placeholder="Enter phone number" required>

      <label>Address</label>
      <input type="text" name="address" placeholder="Enter address" required>

      <label>Select Role</label>
      <select name="role" id="roleSelect" required>
        <option value="">Select Role</option>
        <option value="FARMER">Farmer</option>
        <option value="CUSTOMER">Customer</option>
      </select>

      <div id="farmerExtraFields" style="display:none;">
        <label>Farmer Story</label>
        <textarea name="farmer_story" placeholder="Tell customers about your farm, your journey, and your crops"></textarea>

        <label>Farming Method</label>
        <input type="text" name="farming_method" placeholder="Organic / Traditional / Natural etc.">

        <label>Years of Farming Experience</label>
        <input type="number" name="experience_years" placeholder="Enter farming experience in years">

        <label>Upload Farmer Photo</label>
        <input type="file" name="farmer_photo" accept="image/*">
      </div>

      <button type="submit">Register</button>
    </form>

    <br>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </div>
</div>

<script>
const roleSelect = document.getElementById("roleSelect");
const farmerExtraFields = document.getElementById("farmerExtraFields");

function toggleFarmerFields() {
  if (roleSelect.value === "FARMER") {
    farmerExtraFields.style.display = "block";
  } else {
    farmerExtraFields.style.display = "none";
  }
}

roleSelect.addEventListener("change", toggleFarmerFields);
toggleFarmerFields();
</script>

</body>
</html>