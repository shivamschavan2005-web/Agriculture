<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if ($user["role"] === "FARMER" && $user["approved"] == 0) {
            $message = "Your farmer account is pending admin approval.";
        } else {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            header("Location: index.php");
            exit();
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - FarmConnect</title>
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
  <div class="box" style="max-width: 500px; margin: 30px auto;">
    <h2>Login</h2>

    <?php if ($message != "") { ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <form method="post">
      <label>Email</label>
      <input type="email" name="email" placeholder="Enter your email" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Enter your password" required>

      <button type="submit">Login</button>
    </form>

    <br>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
  </div>
</div>

</body>
</html>