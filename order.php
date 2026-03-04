<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId        = $_POST['userIdInput'] ?? null;
    $postedStatus  = $_POST['userStatus'] ?? '';
    $cartData      = $_POST['cartItemsInput'] ?? '[]';
    $paymentAmount = floatval($_POST['paymentAmountInput'] ?? 0);

    $sessionStatus = isset($_SESSION['status']) ? strtoupper(trim($_SESSION['status'])) : '';
    $firstName     = $_SESSION['fname'] ?? 'Guest';
    $lastName      = $_SESSION['lname'] ?? 'User';
    $cartItems     = json_decode($cartData, true) ?: [];

    // Compute totals
    $baseTotal = 0.0;
    foreach ($cartItems as $item) {
        $price = floatval($item['price'] ?? 0);
        $qty   = intval($item['quantity'] ?? 0);
        $baseTotal += $price * $qty;
    }
    $isDiscounted    = in_array($sessionStatus, ['PWD', 'SENIOR'], true);
    $discountedTotal = $isDiscounted ? $baseTotal * 0.9 : $baseTotal;
    $discountAmount  = $baseTotal - $discountedTotal;
    $finalTotal      = round($discountedTotal, 2);
    $change = round($paymentAmount - $finalTotal, 2);

    // DB connect
    $serverName = "ANGELO\\SQLEXPRESS";
    $connectionOptions = [
        "Database" => "Good_Day_Cafe",
        "Uid"      => "",
        "PWD"      => ""
    ];
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Transaction
    if (!sqlsrv_begin_transaction($conn)) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Insert ORDER + get ORDER_ID
    $sqlOrderInsert = "
        INSERT INTO dbo.[ORDER] ([USER_ID], [TOTAL_PRICE], [ORDER_PLACED], [PAYMENT], [STATUS], [POSITION])
        VALUES (?, ?, ?, ?, ?, ?);
        SELECT CAST(SCOPE_IDENTITY() AS INT) AS ORDER_ID;
    ";
    $paramsOrder = [
        $userId,
        $finalTotal,
        date('Y-m-d H:i:s'),
        $paymentAmount,
        $sessionStatus,
        'ONGOING'
    ];
    $stmtOrder = sqlsrv_query($conn, $sqlOrderInsert, $paramsOrder);
    if ($stmtOrder === false || !sqlsrv_next_result($stmtOrder)) {
        sqlsrv_rollback($conn);
        die(print_r(sqlsrv_errors(), true));
    }
    $row = sqlsrv_fetch_array($stmtOrder, SQLSRV_FETCH_ASSOC);
    $orderId = $row['ORDER_ID'] ?? null;
  
    // Insert ORDER_ITEM rows
    $sqlItemInsert = "
        INSERT INTO dbo.[ORDER_ITEM] ([ORDER_ID], [PRODUCT_ID], [PRODUCT_NAME], [QUANTITY], [PRICE])
        VALUES (?, ?, ?, ?, ?)";
    foreach ($cartItems as $item) {
        $paramsItem = [
            $orderId,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        ];
        $stmtItem = sqlsrv_query($conn, $sqlItemInsert, $paramsItem);
        if ($stmtItem === false) {
            sqlsrv_rollback($conn);
            die(print_r(sqlsrv_errors(), true));
        }
    }

    // Commit
    if (!sqlsrv_commit($conn)) {
        die(print_r(sqlsrv_errors(), true));
    }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
    <p class="strong">Order has been placed successfully!</p>
  </div>

  <!-- Buttons -->
  <div class="d-flex justify-content-center gap-3" style="margin-top: 16px;">
    <button onclick="window.print()" class="btn btn-secondary">Print Receipt</button>
    <button onclick="window.location.href='menupage.php'" class="btn btn-primary">Back to Menu</button>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} // end POST
?>  