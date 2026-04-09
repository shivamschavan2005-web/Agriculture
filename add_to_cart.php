<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

$product_id = intval($_POST["product_id"]);
$buy_qty = floatval($_POST["buy_qty"]);
$customer_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product || $buy_qty <= 0 || $buy_qty > $product["quantity"]) {
    header("Location: index.php?msg=Invalid cart quantity");
    exit();
}

$stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $customer_id, $product_id, $buy_qty);
$stmt->execute();

header("Location: cart.php?msg=Added to cart successfully");
exit();
?>