<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    header("Location: login.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("UPDATE users SET approved = 1 WHERE id = ? AND role = 'FARMER'");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_panel.php");
exit();
?>