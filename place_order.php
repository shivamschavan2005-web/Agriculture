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
    $payment_method = trim($_POST["payment_method"]);
    $transaction_id = isset($_POST["transaction_id"]) ? trim($_POST["transaction_id"]) : "";
    $delivery_address = trim($_POST["delivery_address"]);
    $delivery_phone = trim($_POST["delivery_phone"]);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        die("Product not found.");
    }

    if ($buy_qty <= 0 || $buy_qty > $product["quantity"]) {
        die("Invalid quantity.");
    }

    $total = $buy_qty * $product["price"];

    if ($payment_method === "COD") {
        $payment_status = "Pending";
    } else {
        $payment_status = "Paid";
    }

    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, payment_status, transaction_id, delivery_address, delivery_phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("idsssss", $customer_id, $total, $payment_method, $payment_status, $transaction_id, $delivery_address, $delivery_phone);
        $stmt1->execute();
        $order_id = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, farmer_id, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("iiiddd", $order_id, $product_id, $product["farmer_id"], $buy_qty, $product["price"], $total);
        $stmt2->execute();

        $stmt3 = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $stmt3->bind_param("did", $buy_qty, $product_id, $buy_qty);
        $stmt3->execute();

        if ($stmt3->affected_rows === 0) {
            throw new Exception("Stock update failed.");
        }

        $conn->commit();
        header("Location: my_orders.php?msg=Order placed successfully");
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