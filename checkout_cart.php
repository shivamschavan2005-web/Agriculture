<?php
include 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "CUSTOMER") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];
$delivery_address = trim($_POST["delivery_address"]);
$delivery_phone = trim($_POST["delivery_phone"]);
$payment_method = trim($_POST["payment_method"]);
$transaction_id = trim($_POST["transaction_id"]);

$sql = "SELECT cart.*, products.price, products.quantity AS stock_qty, products.farmer_id
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    if ($row["quantity"] > $row["stock_qty"]) {
        die("Some cart items exceed available stock.");
    }
    $row["item_total"] = $row["quantity"] * $row["price"];
    $subtotal += $row["item_total"];
    $items[] = $row;
}

if (count($items) == 0) {
    die("Cart is empty.");
}

$delivery_charge = ($subtotal >= 500) ? 0 : 40;
$grand_total = $subtotal + $delivery_charge;
$payment_status = ($payment_method == "COD") ? "Pending" : "Paid";

$conn->begin_transaction();

try {
    $stmt1 = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, payment_status, transaction_id, delivery_address, delivery_phone, delivery_charge, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("idsssssdd", $customer_id, $subtotal, $payment_method, $payment_status, $transaction_id, $delivery_address, $delivery_phone, $delivery_charge, $grand_total);
    $stmt1->execute();
    $order_id = $conn->insert_id;

    foreach ($items as $item) {
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, farmer_id, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("iiiddd", $order_id, $item["product_id"], $item["farmer_id"], $item["quantity"], $item["price"], $item["item_total"]);
        $stmt2->execute();

        $stmt3 = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $stmt3->bind_param("dii", $item["quantity"], $item["product_id"], $item["quantity"]);
        $stmt3->execute();
    }

    $stmt4 = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $stmt4->bind_param("i", $customer_id);
    $stmt4->execute();

    $conn->commit();
    header("Location: my_orders.php?msg=Order placed successfully");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Checkout failed.");
}
?>