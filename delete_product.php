<?php
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET["id"]);

if ($_SESSION["role"] === "ADMIN") {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} elseif ($_SESSION["role"] === "FARMER") {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION["user_id"]);
    $stmt->execute();
}

header("Location: index.php");
exit();
?>