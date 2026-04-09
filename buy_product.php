<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST["product_id"]);
    $buy_qty = floatval($_POST["buy_qty"]);
    $customer_id = $_SESSION["user_id"];

    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        die("Product not found.");
    }

    if ($buy_qty <= 0) {
        die("Invalid quantity.");
    }

    if ($buy_qty > $product["quantity"]) {
        die("Quantity exceeds available stock.");
    }

    $total = $buy_qty * $product["price"];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into orders table
        $stmt1 = $conn->prepare("INSERT INTO orders (customer_id, total_amount) VALUES (?, ?)");
        $stmt1->bind_param("id", $customer_id, $total);
        $stmt1->execute();
        $order_id = $conn->insert_id;

        // Insert into order_items table
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, farmer_id, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("iiiddd", $order_id, $product_id, $product["farmer_id"], $buy_qty, $product["price"], $total);
        $stmt2->execute();

        // Reduce quantity from products table
        $stmt3 = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $stmt3->bind_param("did", $buy_qty, $product_id, $buy_qty);
        $stmt3->execute();

        if ($stmt3->affected_rows === 0) {
            throw new Exception("Stock update failed.");
        }

        $conn->commit();
        header("Location: index.php?msg=Order placed successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Order failed. Please try again.");
    }
} else {
    header("Location: index.php");
    exit();
}
?>