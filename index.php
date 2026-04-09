<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$category = isset($_GET["category"]) ? trim($_GET["category"]) : "";
$locality = isset($_GET["locality"]) ? trim($_GET["locality"]) : "";
$sort = isset($_GET["sort"]) ? trim($_GET["sort"]) : "";

$sql = "SELECT products.*, 
               users.id AS farmer_user_id,
               users.name AS farmer_name, 
               users.phone AS farmer_phone,
               users.approved AS farmer_approved
        FROM products
        JOIN users ON products.farmer_id = users.id
        WHERE 1=1";

if ($search != "") {
    $searchSafe = $conn->real_escape_string($search);
    $sql .= " AND (
                products.crop_name LIKE '%$searchSafe%' 
                OR products.description LIKE '%$searchSafe%' 
                OR users.name LIKE '%$searchSafe%'
             )";
}

if ($category != "") {
    $categorySafe = $conn->real_escape_string($category);
    $sql .= " AND products.category = '$categorySafe'";
}

if ($locality != "") {
    $localitySafe = $conn->real_escape_string($locality);
    $sql .= " AND products.locality LIKE '%$localitySafe%'";
}

if ($sort == "low") {
    $sql .= " ORDER BY products.price ASC";
} elseif ($sort == "high") {
    $sql .= " ORDER BY products.price DESC";
} else {
    $sql .= " ORDER BY products.id DESC";
}

$result = $conn->query($sql);
$msg = isset($_GET["msg"]) ? $_GET["msg"] : "";

/* Demand Dashboard Queries */
$demand_total_sql = "SELECT COUNT(*) AS total_products FROM products";
$demand_total_result = $conn->query($demand_total_sql);
$demand_total = $demand_total_result->fetch_assoc()["total_products"] ?? 0;

$veg_sql = "SELECT COUNT(*) AS total FROM products WHERE category='Vegetables'";
$veg_result = $conn->query($veg_sql);
$veg_count = $veg_result->fetch_assoc()["total"] ?? 0;

$fruit_sql = "SELECT COUNT(*) AS total FROM products WHERE category='Fruits'";
$fruit_result = $conn->query($fruit_sql);
$fruit_count = $fruit_result->fetch_assoc()["total"] ?? 0;

$grain_sql = "SELECT COUNT(*) AS total FROM products WHERE category='Grains'";
$grain_result = $conn->query($grain_sql);
$grain_count = $grain_result->fetch_assoc()["total"] ?? 0;

$low_stock_sql = "SELECT COUNT(*) AS total FROM products WHERE quantity <= 10";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock_count = $low_stock_result->fetch_assoc()["total"] ?? 0;

$top_demand_sql = "SELECT crop_name, category, quantity, price 
                   FROM products 
                   ORDER BY quantity ASC, id DESC 
                   LIMIT 5";
$top_demand_result = $conn->query($top_demand_sql);

/* Website Review Queries */
$website_review_sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM website_reviews";
$website_review_result = $conn->query($website_review_sql);
$website_review_data = $website_review_result ? $website_review_result->fetch_assoc() : null;

$website_avg_rating = ($website_review_data && $website_review_data["avg_rating"]) ? number_format($website_review_data["avg_rating"], 1) : "0.0";
$website_total_reviews = ($website_review_data && $website_review_data["total_reviews"]) ? $website_review_data["total_reviews"] : 0;

$latest_reviews_sql = "SELECT 
                          website_reviews.rating,
                          website_reviews.review_text,
                          website_reviews.review_after,
                          users.name,
                          users.role,
                          (
                            SELECT COUNT(*) 
                            FROM orders 
                            WHERE orders.customer_id = website_reviews.user_id
                          ) AS total_user_orders
                       FROM website_reviews
                       JOIN users ON website_reviews.user_id = users.id
                       ORDER BY website_reviews.id DESC
                       LIMIT 5";
$latest_reviews_result = $conn->query($latest_reviews_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-logo">FarmConnect</div>

  <div class="topbar-menu">
    <a href="index.php">Home</a>
    <a href="#demand-dashboard">Demand Dashboard</a>
    <a href="#website-review-section">Website Reviews</a>
    <a href="#products-section">Products</a>
    <a href="#about-section">About</a>
    <a href="#contact-section">Contact</a>

    <?php if (!isset($_SESSION["user_id"])) { ?>
      <a href="register.php">Register</a>
      <a href="login.php">Login</a>
    <?php } else { ?>

      <?php if ($_SESSION["role"] === "ADMIN") { ?>
        <a href="admin_panel.php">Admin</a>
      <?php } ?>

      <?php if ($_SESSION["role"] === "FARMER") { ?>
        <a href="add_product.php">Add Product</a>
        <a href="farmer_orders.php">Orders</a>
        <a href="my_chats.php">Chats</a>
        <a href="website_review.php?type=selling">Review Website</a>
      <?php } ?>

      <?php if ($_SESSION["role"] === "CUSTOMER") { ?>
        <a href="cart.php">Cart</a>
        <a href="my_orders.php">My Orders</a>
        <a href="my_chats.php">Chats</a>
        <a href="website_review.php?type=buying">Review Website</a>
      <?php } ?>

      <a href="logout.php">Logout</a>
    <?php } ?>
  </div>
</header>

<div class="container">

  <div class="box">
    <?php if ($msg != "") { ?>
      <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php } ?>

    <?php if (isset($_SESSION["user_id"])) { ?>
      <div class="msg">
        Logged in as <b><?= htmlspecialchars($_SESSION["name"]) ?></b> (<?= htmlspecialchars($_SESSION["role"]) ?>)
      </div>
    <?php } else { ?>
      <div class="msg">No user logged in.</div>
    <?php } ?>
  </div>

  <div class="box" id="demand-dashboard">
    <h2>Demand Dashboard</h2>

    <div class="summary-grid">
      <div class="summary-card">
        <h3>Total Products</h3>
        <p><?= htmlspecialchars($demand_total) ?></p>
      </div>

      <div class="summary-card">
        <h3>Vegetables</h3>
        <p><?= htmlspecialchars($veg_count) ?></p>
      </div>

      <div class="summary-card">
        <h3>Fruits</h3>
        <p><?= htmlspecialchars($fruit_count) ?></p>
      </div>

      <div class="summary-card">
        <h3>Grains</h3>
        <p><?= htmlspecialchars($grain_count) ?></p>
      </div>

      <div class="summary-card">
        <h3>Low Stock Items</h3>
        <p><?= htmlspecialchars($low_stock_count) ?></p>
      </div>
    </div>

    <div class="summary-box">
      <h3 style="margin-top: 0; color: #1b5e20;">Top Demand Products</h3>
      <table>
        <tr>
          <th>Crop Name</th>
          <th>Category</th>
          <th>Available Qty</th>
          <th>Price / Kg</th>
        </tr>

        <?php if ($top_demand_result && $top_demand_result->num_rows > 0) { ?>
          <?php while ($demand_row = $top_demand_result->fetch_assoc()) { ?>
            <tr>
              <td><?= htmlspecialchars($demand_row["crop_name"]) ?></td>
              <td><?= htmlspecialchars($demand_row["category"]) ?></td>
              <td><?= htmlspecialchars($demand_row["quantity"]) ?> Kg</td>
              <td>₹<?= htmlspecialchars($demand_row["price"]) ?></td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="4">No demand data available.</td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>

  <div class="box" id="website-review-section">
    <h2>Website Reviews</h2>

    <div class="summary-grid">
      <div class="summary-card">
        <h3>Average Rating</h3>
        <p>⭐ <?= $website_avg_rating ?>/5</p>
      </div>

      <div class="summary-card">
        <h3>Total Reviews</h3>
        <p><?= $website_total_reviews ?></p>
      </div>
    </div>

    <div class="summary-box">
      <h3 style="margin-top:0; color:#1b5e20;">Latest Website Reviews</h3>

      <?php if ($latest_reviews_result && $latest_reviews_result->num_rows > 0) { ?>
        <?php while ($review = $latest_reviews_result->fetch_assoc()) { ?>
          <p style="margin:12px 0; padding:10px; background:#fff; border-radius:8px;">
            <b><?= htmlspecialchars($review["name"]) ?></b>

            <?php if (
                isset($review["role"]) &&
                $review["role"] === "CUSTOMER" &&
                isset($review["total_user_orders"]) &&
                $review["total_user_orders"] > 0
            ) { ?>
              <span class="verified-buyer-badge">✅ Verified Buyer</span>
            <?php } ?>

            (<?= htmlspecialchars($review["role"]) ?> - After <?= htmlspecialchars($review["review_after"]) ?>)
            - ⭐ <?= htmlspecialchars($review["rating"]) ?>/5<br>
            <?= htmlspecialchars($review["review_text"]) ?>
          </p>
        <?php } ?>
      <?php } else { ?>
        <p>No website reviews yet.</p>
      <?php } ?>
    </div>
  </div>

  <div class="box">
    <h2>Search and Filter Products</h2>

    <form method="get">
      <div class="filter-grid">
        <input type="text" name="search" placeholder="Search crop, description, farmer" value="<?= htmlspecialchars($search) ?>">

        <select name="category">
          <option value="">All Categories</option>
          <option value="Vegetables" <?= $category=="Vegetables" ? "selected" : "" ?>>Vegetables</option>
          <option value="Fruits" <?= $category=="Fruits" ? "selected" : "" ?>>Fruits</option>
          <option value="Grains" <?= $category=="Grains" ? "selected" : "" ?>>Grains</option>
          <option value="Dairy" <?= $category=="Dairy" ? "selected" : "" ?>>Dairy</option>
          <option value="Flowers" <?= $category=="Flowers" ? "selected" : "" ?>>Flowers</option>
        </select>

        <input type="text" name="locality" placeholder="Filter by locality" value="<?= htmlspecialchars($locality) ?>">

        <select name="sort">
          <option value="">Sort by Price</option>
          <option value="low" <?= $sort=="low" ? "selected" : "" ?>>Low to High</option>
          <option value="high" <?= $sort=="high" ? "selected" : "" ?>>High to Low</option>
        </select>
      </div>

      <button type="submit">Apply Filters</button>
    </form>
  </div>

  <div class="box" id="products-section">
    <h2>Available Products</h2>

    <div class="card-grid">
      <?php if ($result && $result->num_rows > 0) { ?>
        <?php while($row = $result->fetch_assoc()) { ?>
          <div class="card">
            <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" alt="<?= htmlspecialchars($row["crop_name"]) ?>">

            <h3><?= htmlspecialchars($row["crop_name"]) ?></h3>
            <p><b>Category:</b> <?= htmlspecialchars($row["category"]) ?></p>
            <p>
              <b>Farmer:</b> <?= htmlspecialchars($row["farmer_name"]) ?>
              <?php if (isset($row["farmer_approved"]) && $row["farmer_approved"] == 1) { ?>
                <span class="verified-seller-badge">✅ Verified Seller</span>
              <?php } ?>
            </p>

            <?php if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "CUSTOMER") { ?>
              <p><b>Farmer Phone:</b> <?= htmlspecialchars($row["farmer_phone"]) ?></p>

              <a class="action-link approve-link"
                 href="chat.php?farmer_id=<?= htmlspecialchars($row["farmer_user_id"]) ?>&product_id=<?= htmlspecialchars($row["id"]) ?>">
                 Message Farmer
              </a>

              <a class="action-link approve-link"
                 href="tel:<?= htmlspecialchars($row["farmer_phone"]) ?>">
                 Call Farmer
              </a>
            <?php } ?>

            <p><b>Description:</b> <?= htmlspecialchars($row["description"]) ?></p>
            <p><b>Price:</b> ₹<?= htmlspecialchars($row["price"]) ?> / Kg</p>
            <p><b>Available:</b> <?= htmlspecialchars($row["quantity"]) ?> Kg</p>
            <p><b>Locality:</b> <?= htmlspecialchars($row["locality"]) ?></p>
            <p><b>Harvest Date:</b> <?= htmlspecialchars($row["harvest_date"]) ?></p>

            <?php if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "CUSTOMER") { ?>
              <form method="post" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($row["id"]) ?>">
                <input
                  type="number"
                  step="0.01"
                  name="buy_qty"
                  min="0.01"
                  max="<?= htmlspecialchars($row["quantity"]) ?>"
                  placeholder="Qty in Kg"
                  required
                >
                <button type="submit">Add to Cart</button>
              </form>
            <?php } ?>

            <?php if (isset($_SESSION["user_id"]) && ($_SESSION["role"] === "ADMIN" || ($_SESSION["role"] === "FARMER" && $_SESSION["user_id"] == $row["farmer_id"]))) { ?>
              <br>
              <a class="action-link delete-link" href="delete_product.php?id=<?= htmlspecialchars($row["id"]) ?>" onclick="return confirm('Delete this product?')">Delete Product</a>
            <?php } ?>
          </div>
        <?php } ?>
      <?php } else { ?>
        <p>No products found.</p>
      <?php } ?>
    </div>
  </div>

  <div class="box" id="about-section">
    <h2>About</h2>
    <p>
      FarmConnect is a farmer-to-customer marketplace where farmers can directly sell fresh crops
      to customers without middlemen. It helps farmers get better profit and customers get quality
      products at fair prices.
    </p>
  </div>

  <div class="box" id="contact-section">
    <h2>Contact</h2>
    <p><b>Email:</b> support@farmconnect.com</p>
    <p><b>Phone:</b> +91 9876543210</p>
    <p><b>Location:</b> Sangamner, Maharashtra</p>
  </div>

</div>

<div class="notification-bell-container">
  <button class="notification-bell-btn" onclick="toggleNotifications()">
    🔔 <span class="notification-count">3</span>
  </button>

  <div class="notification-dropdown" id="notificationDropdown">
    <div class="notification-item">
      <b>New Order</b><br>
      You received a new order.
    </div>
    <div class="notification-item">
      <b>New Message</b><br>
      Customer sent you a message.
    </div>
    <div class="notification-item">
      <b>Low Stock</b><br>
      One product is running low.
    </div>
  </div>
</div>

<div class="chatbot-container">
  <div class="chatbot-header" onclick="toggleChatbot()">AI Chat Bot</div>

  <div class="chatbot-body" id="chatbotBody">
    <div class="chatbot-messages" id="chatMessages">
      <div class="bot-message">Hello! Welcome to FarmConnect. Ask me about registration, login, products, cart, chats, orders, website reviews, notifications, or dashboard features.</div>
    </div>

    <div class="chatbot-input-area">
      <input type="text" id="chatInput" placeholder="Type your message...">
      <button type="button" onclick="sendChatMessage()">Send</button>
    </div>
  </div>
</div>

<script>
function toggleNotifications() {
  const dropdown = document.getElementById("notificationDropdown");
  dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
}

function toggleChatbot() {
  const body = document.getElementById("chatbotBody");
  body.style.display = (body.style.display === "block") ? "none" : "block";
}

function botReply(message) {
  const text = message.toLowerCase().trim();

  if (text.includes("hi") || text.includes("hello") || text.includes("hey")) {
    return "Hello! Welcome to FarmConnect. I can help you with registration, login, products, cart, chats, orders, website reviews, notifications, and farmer features.";
  }

  if (text.includes("review")) {
    return "Users can rate the website from 1 to 5 stars and write a short review after buying or selling a product. Verified buyers are also highlighted in reviews.";
  }

  if (text.includes("verified buyer")) {
    return "Verified Buyer badge is shown for customers who have actually placed at least one order on the website.";
  }

  if (text.includes("verified seller")) {
    return "Verified Seller badge is shown for farmers who are approved by the admin.";
  }

  if (text.includes("notification") || text.includes("bell")) {
    return "The bell icon above the AI chat box shows notifications like new orders, new messages, and low stock alerts.";
  }

  if (text.includes("register") || text.includes("registration")) {
    return "To register, open the Register page from the top menu. Then enter your details and select your role as Farmer or Customer.";
  }

  if (text.includes("login")) {
    return "To login, open the Login page from the top menu and enter your email and password.";
  }

  if (text.includes("product") || text.includes("crop")) {
    return "You can view products in the Products section and use search or filters to find crops easily.";
  }

  if (text.includes("cart")) {
    return "Customers can add products to cart and place orders from the Cart page.";
  }

  if (text.includes("chat")) {
    return "Customers can message farmers inside the website and also contact them directly by phone.";
  }

  if (text.includes("dashboard")) {
    return "The Demand Dashboard shows product counts, category counts, low stock items, and top demand products.";
  }

  return "I can help with registration, login, products, chats, cart, orders, reviews, verified badges, notifications, and website features. Please ask a specific question.";
}

function sendChatMessage() {
  const input = document.getElementById("chatInput");
  const msg = input.value.trim();
  if (msg === "") return;

  const messages = document.getElementById("chatMessages");

  const userDiv = document.createElement("div");
  userDiv.className = "user-message";
  userDiv.textContent = msg;
  messages.appendChild(userDiv);

  const reply = botReply(msg);

  setTimeout(() => {
    const botDiv = document.createElement("div");
    botDiv.className = "bot-message";
    botDiv.textContent = reply;
    messages.appendChild(botDiv);
    messages.scrollTop = messages.scrollHeight;
  }, 300);

  input.value = "";
  messages.scrollTop = messages.scrollHeight;
}

document.getElementById("chatInput").addEventListener("keypress", function(event) {
  if (event.key === "Enter") {
    event.preventDefault();
    sendChatMessage();
  }
});
</script>
</body>
</html>