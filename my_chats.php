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

// Get latest chat partners for current user
$sql = "SELECT m.product_id,
               p.crop_name,
               p.farmer_id,
               u1.name AS sender_name,
               u2.name AS receiver_name,
               CASE 
                   WHEN m.sender_id = '$current_user_id' THEN m.receiver_id
                   ELSE m.sender_id
               END AS other_user_id,
               MAX(m.sent_at) AS last_time
        FROM messages m
        JOIN products p ON m.product_id = p.id
        JOIN users u1 ON m.sender_id = u1.id
        JOIN users u2 ON m.receiver_id = u2.id
        WHERE m.sender_id = '$current_user_id' OR m.receiver_id = '$current_user_id'
        GROUP BY other_user_id, m.product_id
        ORDER BY last_time DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f8f2;
        }

        .page {
            width: 92%;
            max-width: 1000px;
            margin: 25px auto;
        }

        .box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 20px;
        }

        h2 {
            margin-top: 0;
            color: #1b5e20;
        }

        .chat-item {
            border: 1px solid #d7e9d5;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            background: #fcfffb;
        }

        .chat-item p {
            margin: 6px 0;
        }

        .chat-link {
            display: inline-block;
            margin-top: 8px;
            background: #2e7d32;
            color: white;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 6px;
        }

        .chat-link:hover {
            background: #1b5e20;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="box">
        <h2>My Chats</h2>

        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="chat-item">
                    <p><b>Product:</b> <?= htmlspecialchars($row["crop_name"]) ?></p>
                    <p><b>Last Message Time:</b> <?= htmlspecialchars($row["last_time"]) ?></p>

                    <?php if ($current_role === "CUSTOMER") { ?>
                        <a class="chat-link" href="chat.php?farmer_id=<?= htmlspecialchars($row["farmer_id"]) ?>&product_id=<?= htmlspecialchars($row["product_id"]) ?>">
                            Open Chat
                        </a>
                    <?php } else { ?>
                        <a class="chat-link" href="chat.php?farmer_id=<?= htmlspecialchars($row["farmer_id"]) ?>&product_id=<?= htmlspecialchars($row["product_id"]) ?>&customer_id=<?= htmlspecialchars($row["other_user_id"]) ?>">
                            Open Chat
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No chats found.</p>
        <?php } ?>
    </div>
</div>

</body>
</html>