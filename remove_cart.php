<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

$id = intval($_GET["id"]);
$customer_id = $_SESSION["user_id"];

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $id, $customer_id);
$stmt->execute();

header("Location: cart.php?msg=Item removed from cart");
exit();
?>