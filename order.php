<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) && !isset($_SESSION['status'])) {
    header("Location: loginandregis.html");
    exit;
}

// Initialize variables with defaults
$orderId        = 0;
$firstName      = $_SESSION['fname'] ?? 'Guest';
$lastName       = $_SESSION['lname'] ?? 'User';
$sessionStatus  = isset($_SESSION['status']) ? strtoupper(trim($_SESSION['status'])) : '';
$cartItems      = [];
$baseTotal      = 0.0;
$discountAmount = 0.0;
$finalTotal     = 0.0;
$paymentAmount  = 0.0;
$change         = 0.0;
$orderPlaced    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId        = !empty($_POST['userIdInput']) ? (int)$_POST['userIdInput'] : null;
    $postedStatus  = $_POST['userStatus'] ?? '';
    $cartData      = $_POST['cartItemsInput'] ?? '[]';
    $paymentAmount = floatval($_POST['paymentAmountInput'] ?? 0);

    $cartItems     = json_decode($cartData, true) ?: [];

    // Compute totals
    foreach ($cartItems as $item) {
        $price = floatval($item['price'] ?? 0);
        $qty   = intval($item['quantity'] ?? 0);
        $baseTotal += $price * $qty;
    }
    $isDiscounted    = in_array($sessionStatus, ['PWD', 'SENIOR'], true);
    $discountedTotal = $isDiscounted ? $baseTotal * 0.9 : $baseTotal;
    $discountAmount  = $baseTotal - $discountedTotal;
    $finalTotal      = round($discountedTotal, 2);
    $change          = round($paymentAmount - $finalTotal, 2);

    // DB connect
    require_once __DIR__ . '/db_config.php';

    try {
        // Start Transaction
        $conn->beginTransaction();

        // Insert ORDER
        $sqlOrderInsert = "
            INSERT INTO `orders` (user_id, total_price, order_placed, payment, status, position)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmtOrder = $conn->prepare($sqlOrderInsert);
        $stmtOrder->execute([
            $userId,
            $finalTotal,
            date('Y-m-d H:i:s'),
            $paymentAmount,
            $sessionStatus,
            'Ongoing' // Match case used in admin panel
        ]);
        $orderId = $conn->lastInsertId();

        // Insert ORDER_ITEM rows
        $sqlItemInsert = "
            INSERT INTO order_item (order_id, product_id, product_name, quantity, price)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmtItem = $conn->prepare($sqlItemInsert);
        foreach ($cartItems as $item) {
            $stmtItem->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price']
            ]);
        }

        $conn->commit();
        $orderPlaced = true;
    } catch (PDOException $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        die("Order failed: " . $e->getMessage());
    }
} else {
    // If accessed via GET, redirect or show message if no orderId is set
    if ($orderId <= 0) {
        // Optional: redirect to menu if no order was just placed
        // header("Location: menupage.php");
        // exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 24px; }
    .receipt { max-width: 520px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .receipt h1 { margin: 0 0 12px; font-size: 24px; }
    .meta, .totals { margin-bottom: 16px; }
    .meta p, .totals p { margin: 4px 0; }
    .items { margin: 16px 0; }
    .items h3 { margin: 0 0 8px; }
    .items .row { display: flex; justify-content: space-between; margin: 4px 0; }
    .hr { border: 0; border-top: 1px solid #ddd; margin: 16px 0; }
    .strong { font-weight: 700; }
  </style>
</head>
<body>
  <div class="receipt">
    <?php if ($orderPlaced): ?>
      <h1>Receipt</h1>
      <div class="meta">
        <div class="container d-flex justify-content-center">
          <h1 class="strong">Order ID: <?php echo htmlspecialchars($orderId); ?></h1>
        </div>
        <p><span class="strong">Name:</span> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></p>
        <p><span class="strong">Status:</span> <?php echo htmlspecialchars($sessionStatus ?: 'REGULAR'); ?></p>
      </div>

      <div class="items">
        <h3>Items</h3>
        <?php foreach ($cartItems as $item): ?>
          <div class="row">
            <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo intval($item['quantity']); ?></span>
            <span>₱<?php echo number_format($item['price'], 2); ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="hr"></div>

      <div class="totals">
        <p>Base Total: ₱<?php echo number_format($baseTotal, 2); ?></p>
        <p>Discount: -₱<?php echo number_format($discountAmount, 2); ?></p>
        <p class="strong">Final Total: ₱<?php echo number_format($finalTotal, 2); ?></p>
        <p>Payment: ₱<?php echo number_format($paymentAmount, 2); ?></p>
        <p class="strong">Change: ₱<?php echo number_format($change, 2); ?></p>
      </div>

      <div class="hr"></div>
      <p class="strong text-success">Order has been placed successfully!</p>

    <?php else: ?>
      <h1>No Order Found</h1>
      <p>It seems you haven't placed an order yet or your session has expired.</p>
      <a href="menupage.php" class="btn btn-primary mt-3">Go to Menu</a>
    <?php endif; ?>
  </div>

  <!-- Buttons -->
  <?php if ($orderPlaced): ?>
    <div class="d-flex justify-content-center gap-3" style="margin-top: 16px;">
      <button onclick="window.print()" class="btn btn-secondary">Print Receipt</button>
      <button onclick="window.location.href='menupage.php'" class="btn btn-primary">Back to Menu</button>
    </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
