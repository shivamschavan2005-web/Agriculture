<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    header("Location: login.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'ADMIN'");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_panel.php");
exit();
?>