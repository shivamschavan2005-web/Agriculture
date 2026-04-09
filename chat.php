<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION["user_id"];
$current_role = $_SESSION["role"];

$farmer_id = isset($_GET["farmer_id"]) ? (int)$_GET["farmer_id"] : 0;
$product_id = isset($_GET["product_id"]) ? (int)$_GET["product_id"] : 0;

if ($farmer_id <= 0 || $product_id <= 0) {
    die("Invalid chat request.");
}

// Get product details
$product_sql = "SELECT products.*, users.name AS farmer_name, users.phone AS farmer_phone
                FROM products
                JOIN users ON products.farmer_id = users.id
                WHERE products.id = '$product_id' AND products.farmer_id = '$farmer_id'";
$product_result = $conn->query($product_sql);

if (!$product_result || $product_result->num_rows == 0) {
    die("Product or farmer not found.");
}

$product = $product_result->fetch_assoc();

// Determine receiver
if ($current_role === "CUSTOMER") {
    $receiver_id = $farmer_id;
} elseif ($current_role === "FARMER") {
    $receiver_id = isset($_GET["customer_id"]) ? (int)$_GET["customer_id"] : 0;
    if ($receiver_id <= 0) {
        die("Customer not specified.");
    }
} else {
    die("Only farmer or customer can access chat.");
}

// Send message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["message"])) {
    $message = trim($_POST["message"]);

    if ($message !== "") {
        $messageSafe = $conn->real_escape_string($message);

        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, product_id, message)
                       VALUES ('$current_user_id', '$receiver_id', '$product_id', '$messageSafe')";
        $conn->query($insert_sql);
    }

    // Avoid resubmission
    $redirect_url = "chat.php?farmer_id=" . $farmer_id . "&product_id=" . $product_id;
    if ($current_role === "FARMER") {
        $redirect_url .= "&customer_id=" . $receiver_id;
    }
    header("Location: " . $redirect_url);
    exit();
}

// Get other user details
$other_user_id = $receiver_id;
$user_sql = "SELECT name, phone, role FROM users WHERE id = '$other_user_id'";
$user_result = $conn->query($user_sql);
$other_user = $user_result ? $user_result->fetch_assoc() : null;

// Fetch messages
$chat_sql = "SELECT messages.*, users.name AS sender_name
             FROM messages
             JOIN users ON messages.sender_id = users.id
             WHERE product_id = '$product_id'
             AND (
                  (sender_id = '$current_user_id' AND receiver_id = '$other_user_id')
                  OR
                  (sender_id = '$other_user_id' AND receiver_id = '$current_user_id')
             )
             ORDER BY sent_at ASC";
$chat_result = $conn->query($chat_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f8f2;
        }

        .page {
            width: 92%;
            max-width: 1000px;
            margin: 25px auto;
        }

        .top-box,
        .chat-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            padding: 20px;
        }

        .top-box h2,
        .chat-box h2 {
            margin-top: 0;
            color: #1b5e20;
        }

        .product-info p,
        .contact-info p {
            margin: 8px 0;
        }

        .phone-link {
            display: inline-block;
            margin-top: 8px;
            background: #2e7d32;
            color: white;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 6px;
        }

        .phone-link:hover {
            background: #1b5e20;
        }

        .messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #d6e6d5;
            border-radius: 10px;
            padding: 15px;
            background: #f9fff8;
            margin-bottom: 15px;
        }

        .message {
            max-width: 75%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 10px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .sent {
            background: #dcedc8;
            margin-left: auto;
            text-align: right;
        }

        .received {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .meta {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
        }

        .chat-form {
            display: flex;
            gap: 10px;
        }

        .chat-form input[type="text"] {
            flex: 1;
            padding: 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 14px;
        }

        .chat-form button {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .chat-form button:hover {
            background: #1b5e20;
        }

        .nav-links {
            margin-top: 15px;
        }

        .nav-links a {
            display: inline-block;
            margin-right: 10px;
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 10px 14px;
            border-radius: 6px;
        }

        .nav-links a:hover {
            background: #1b5e20;
        }

        @media (max-width: 768px) {
            .chat-form {
                flex-direction: column;
            }

            .message {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="page">

    <div class="top-box">
        <h2>Chat About Product</h2>

        <div class="product-info">
            <p><b>Product:</b> <?= htmlspecialchars($product["crop_name"]) ?></p>
            <p><b>Category:</b> <?= htmlspecialchars($product["category"]) ?></p>
            <p><b>Price:</b> ₹<?= htmlspecialchars($product["price"]) ?> / Kg</p>
            <p><b>Locality:</b> <?= htmlspecialchars($product["locality"]) ?></p>
        </div>

        <div class="contact-info">
            <p><b>Farmer:</b> <?= htmlspecialchars($product["farmer_name"]) ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($product["farmer_phone"]) ?></p>
            <a class="phone-link" href="tel:<?= htmlspecialchars($product["farmer_phone"]) ?>">Call Now</a>
        </div>

        <div class="nav-links">
            <a href="index.php">Back to Products</a>
            <a href="my_chats.php">My Chats</a>
        </div>
    </div>

    <div class="chat-box">
        <h2>Conversation</h2>

        <div class="messages" id="messagesBox">
            <?php if ($chat_result && $chat_result->num_rows > 0) { ?>
                <?php while ($msg = $chat_result->fetch_assoc()) { ?>
                    <div class="message <?= ($msg["sender_id"] == $current_user_id) ? 'sent' : 'received' ?>">
                        <?= nl2br(htmlspecialchars($msg["message"])) ?>
                        <div class="meta">
                            <?= htmlspecialchars($msg["sender_name"]) ?> | <?= htmlspecialchars($msg["sent_at"]) ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No messages yet. Start the conversation.</p>
            <?php } ?>
        </div>

        <form method="post" class="chat-form">
            <input type="text" name="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

</div>

<script>
const messagesBox = document.getElementById("messagesBox");
messagesBox.scrollTop = messagesBox.scrollHeight;
</script>

</body>
</html>