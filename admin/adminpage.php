<?php
function fetchSalesSummary($dbConnection, string $granularity = 'daily', ?string $startDate = null, ?string $endDate = null, ?int $productId = null): array {
    $hasStartDate      = !empty($startDate);
    $hasEndDate        = !empty($endDate);
    $endDateExclusive  = $hasEndDate ? date('Y-m-d', strtotime($endDate . ' +1 day')) : null;

    switch (strtolower($granularity)) {
        case 'weekly':
            $periodExpression = "DATE_SUB(DATE(o.order_placed), INTERVAL WEEKDAY(o.order_placed) DAY)";
            break;
        case 'monthly':
            $periodExpression = "DATE_FORMAT(o.order_placed, '%Y-%m-01')";
            break;
        case 'daily':
        default:
            $periodExpression = "DATE(o.order_placed)";
            break;
    }

    /**
 * Handle status update (mark order Completed, etc.)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_position'])) {
    $orderId     = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $newPosition = $_POST['new_position'] ?? '';

    // Allowed positions; adjust to your values
    $allowedPositions = ['Ongoing', 'Completed', 'Cancelled'];

    if ($orderId > 0 && in_array($newPosition, $allowedPositions, true)) {
        $updateSql = "UPDATE `order` SET POSITION = ? WHERE ORDER_ID = ?";
        try {
            $dbConnection->prepare($updateSql)->execute([$newPosition, $orderId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }
}




    $summarySql = "
        SELECT
            $periodExpression AS period,
            SUM(oi.quantity * oi.price) AS revenue,
            SUM(oi.quantity)            AS units,
            COUNT(DISTINCT o.order_id)  AS orders
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        WHERE 1=1
    ";

    $summaryParams = [];
    if ($hasStartDate) {
        $summarySql    .= " AND o.order_placed >= ? ";
        $summaryParams[] = $startDate;
    }
    if ($hasEndDate) {
        $summarySql    .= " AND o.order_placed < ? ";
        $summaryParams[] = $endDateExclusive;
    }
    if (!is_null($productId)) {
        $summarySql    .= " AND oi.product_id = ? ";
        $summaryParams[] = $productId;
    }

    $summarySql .= " GROUP BY $periodExpression ORDER BY period DESC";
    try {
        $summaryStmt = $dbConnection->prepare($summarySql);
        $summaryStmt->execute($summaryParams);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }

    $summaryResults = [];
    while ($summaryRow = $summaryStmt->fetch(PDO::FETCH_ASSOC)) {
        $summaryResults[] = $summaryRow;
    }
    return $summaryResults;
}

// Detailed transactions
function fetchTransactions($dbConnection, ?string $startDate = null, ?string $endDate = null, ?int $productId = null): array {
    if ($dbConnection === false) {
        return [];
    }

    $hasStartDate     = !empty($startDate);
    $hasEndDate       = !empty($endDate);
    $endDateExclusive = $hasEndDate ? date('Y-m-d', strtotime($endDate . ' +1 day')) : null;

    $transactionSql = "
        SELECT
            o.order_id,
            o.user_id,
            o.order_placed,
            o.position,          -- add this
            o.status,         -- keep/remove as needed
            oi.product_id,
            oi.product_name,
            oi.quantity,
            oi.price,
            (oi.quantity * oi.price) AS line_total
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        WHERE 1=1
    ";


    $transactionParams = [];
    if ($hasStartDate) {
        $transactionSql    .= " AND o.order_placed >= ? ";
        $transactionParams[] = $startDate;
    }
    if ($hasEndDate) {
        $transactionSql    .= " AND o.order_placed < ? ";
        $transactionParams[] = $endDateExclusive;
    }
    if (!is_null($productId)) {
        $transactionSql    .= " AND oi.product_id = ? ";
        $transactionParams[] = $productId;
    }

    $transactionSql .= " ORDER BY o.ORDER_PLACED DESC, o.ORDER_ID DESC";
    try {
        $transactionStmt = $dbConnection->prepare($transactionSql);
        $transactionStmt->execute($transactionParams);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }

    $transactionRecords = [];
    while ($transactionRow = $transactionStmt->fetch(PDO::FETCH_ASSOC)) {
        $transactionRecords[] = $transactionRow;
    }
    return $transactionRecords;
}

function normalizeImagePath(?string $filepath): ?string
{
    if (!$filepath) {
        return $filepath;
    }

    $normalized = str_replace('\\', '/', $filepath);
    $uploadsPos = strpos($normalized, '/uploads/');

    return $uploadsPos === false ? $normalized : substr($normalized, $uploadsPos);
}

session_start();
require_once __DIR__ . '/../db_config.php';
$dbConnection = $conn;

if ($dbConnection === false) {
    die("Database connection failed. Please try again later.");
}

// Handle filters
$granularity = $_GET['granularity'] ?? 'daily';
$startDate   = $_GET['startDate'] ?? null;
$endDate     = $_GET['endDate'] ?? null;
$productId   = (isset($_GET['productId']) && $_GET['productId'] !== '') ? (int)$_GET['productId'] : null;

$reportSummary = fetchSalesSummary($dbConnection, $granularity, $startDate, $endDate, $productId);
$reportDetail  = fetchTransactions($dbConnection, $startDate, $endDate, $productId);

$firstName = $_SESSION['fname'] ?? 'Admin';
$lastName  = $_SESSION['lname'] ?? '';

// Fetch products with their images
$productsQuery = "
SELECT
    p.product_id,
    p.product_name,
    p.description,
    p.price,
    p.product_category,
    i.image_name,
    i.filepath
FROM products p
LEFT JOIN product_image i ON p.product_id = i.product_id
";
try {
    $productsStmt = $dbConnection->query($productsQuery);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Failed to retrieve products. Please check your database logs.");
}

$products = [];
while ($productRow = $productsStmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($productRow['filepath'])) {
        $productRow['filepath'] = normalizeImagePath($productRow['filepath']);
    }
    $products[] = $productRow;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/demo/GoodayCafeWebsite-main/admin/adminpage.css">
</head>
<body class="d-flex flex-column align-items-center">

<h1 class="mt-4">Admin Page</h1>
<h4>Welcome, <?php echo htmlspecialchars($firstName . ' ' . $lastName, ENT_QUOTES, 'UTF-8'); ?>!</h4>

<!-- Offcanvas trigger button -->
<div class="mt-2 mb-3">
    <button class="btn btn-outline-secondary btn-sm" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#userProfile" aria-controls="userProfile">
        Profile <i class="bi bi-person-fill"></i>
    </button>
</div>

<div class="container mt-6"> <!-- Main container -->
    <ul class="nav nav-tabs"> <!-- Tabs Navigation -->
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#reports">Reports</a>
            
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#addproduct">Products</a>
        </li>
    </ul>

    <div class="tab-content border border-top-0 p-3">
        <!-- Products Tab -->
        <div class="tab-pane fade" id="addproduct">
            <h4>Products</h4>
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#add-product-modal">
                Add Product
            </button>

            <!-- Category Sections with original container class names -->
            <div class="row g-4 mb-5" id="productIcedDrinkRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Iced Drinks</h1>
                    <p>Check out some of our popular Iced Drinks items!</p>
                  </div>
                  <div class="row g-4 iced-drinks-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productHotDrinkRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Hot Drinks</h1>
                    <p>Check out some of our popular Hot Drinks items!</p>
                  </div>
                  <div class="row g-4 hot-drinks-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productFrappeRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Frappe's</h1>
                    <p>Check out some of our popular Frappe's items!</p>
                  </div>
                  <div class="row g-4 frappe-drinks-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productRefresherRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Refresher's</h1>
                    <p>Check out some of our popular Refresher's items!</p>
                  </div>
                  <div class="row g-4 refresher-drinks-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productPizzaRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Pizza</h1>
                    <p>Check out some of our popular Pizza items!</p>
                  </div>
                  <div class="row g-4 pizza-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productPastaRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Pasta</h1>
                    <p>Check out some of our popular Pasta items!</p>
                  </div>
                  <div class="row g-4 pasta-container"></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-5" id="productPastryRow">
              <div style="padding-top: 20px;">
                <div class="container-fluid">
                  <div class="container text-start">
                    <h1 class="font-playfair fw-bold" style="font-size: 100px;">Pastries</h1>
                    <p>Check out some of our popular Pastries items!</p>
                  </div>
                  <div class="row g-4 pastries-container"></div>
                </div>
              </div>
            </div>
            <!-- End Category Sections -->
        </div>

        <!-- Reports Tab -->
        <div class="tab-pane fade show active" id="reports">
            <h4>Sales Reports</h4>
            <form class="row g-2 mb-3" method="get">
                <div class="col-md-3">
                    <label class="form-label">Granularity</label>
                    <select name="granularity" class="form-select">
                        <option value="daily"   <?= $granularity==='daily'?'selected':''; ?>>Daily</option>
                        <option value="weekly"  <?= $granularity==='weekly'?'selected':''; ?>>Weekly</option>
                        <option value="monthly" <?= $granularity==='monthly'?'selected':''; ?>>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="startDate" class="form-control" value="<?= htmlspecialchars($startDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="endDate" class="form-control" value="<?= htmlspecialchars($endDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product ID (optional)</label>
                    <input type="number" name="productId" class="form-control" value="<?= htmlspecialchars($productId ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary mt-2" type="submit">Filter</button>
                </div>
            </form>

            <h5>Summary (<?= htmlspecialchars($granularity, ENT_QUOTES, 'UTF-8'); ?>)</h5>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Period</th><th>Revenue</th><th>Units</th><th>Orders</th></tr></thead>
                    <tbody>
                    <?php if (empty($reportSummary)): ?>
                        <tr><td colspan="4" class="text-muted">No data</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportSummary as $summaryRow): ?>
                            <tr>
                                <td><?= htmlspecialchars($summaryRow['period'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>₱<?= number_format((float)$summaryRow['revenue'], 2); ?></td>
                                <td><?= (int)$summaryRow['units']; ?></td>
                                <td><?= (int)$summaryRow['orders']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h5>Transactions</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
    <tr>
        <th>Order ID</th><th>User ID</th><th>Date</th><th>Position</th>
        <th>Product ID</th><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th>
        <th>Update</th> <!-- new column -->
    </tr>
</thead>
<tbody>
<?php if (empty($reportDetail)): ?>
    <tr><td colspan="10" class="text-muted">No data</td></tr>
<?php else: ?>
    <?php foreach ($reportDetail as $transactionRow): ?>
        <tr>
            <td><?= (int)$transactionRow['ORDER_ID']; ?></td>
            <td><?= htmlspecialchars($transactionRow['USER_ID'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($transactionRow['ORDER_PLACED'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($transactionRow['POSITION'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= (int)$transactionRow['PRODUCT_ID']; ?></td>
            <td><?= htmlspecialchars($transactionRow['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= (int)$transactionRow['QUANTITY']; ?></td>
            <td>₱<?= number_format((float)$transactionRow['PRICE'], 2); ?></td>
            <td>₱<?= number_format((float)$transactionRow['line_total'], 2); ?></td>
            <td>
                <form method="post" class="d-flex gap-1 align-items-center">
                    <input type="hidden" name="order_id" value="<?= (int)$transactionRow['order_id']; ?>">
                    <select name="new_position" class="form-select form-select-sm">
                        <?php foreach (['Ongoing', 'Completed', 'Cancelled'] as $positionOption): ?>
                            <option value="<?= $positionOption; ?>"
                                <?= ($transactionRow['position'] === $positionOption) ? 'selected' : ''; ?>>
                                <?= $positionOption; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_position" class="btn btn-sm btn-primary">
                        Save
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    

<!-- Add Product Modal -->
<div class="modal fade" id="add-product-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form class="row g-3" method="post" action="add_product.php" enctype="multipart/form-data">
            <div class="col-md-6">
                <label class="form-label">Product Name</label>
                <input type="text" class="form-control" name="productName" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price</label>
                <input type="number" class="form-control" name="productPrice" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="productDescription" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="productCategory" class="form-select" required>
                    <option selected disabled>Choose...</option>
                    <option value="Iced Drink">Iced Drink</option>
                    <option value="Hot Drink">Hot Drink</option>
                    <option value="Frappe">Frappe</option>
                    <option value="Refresher">Refresher</option>
                    <option value="Pastry">Pastry</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Pasta">Pasta</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="productImage" accept="image/*" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- User Profile Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="userProfile" aria-labelledby="userProfileLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="userProfileLabel">User Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="/demo/GoodayCafeWebsite-main/loginandregis.html" class="btn btn-danger mt-3">Logout</a>
    </div>
</div>

<script>
    const products = <?php echo json_encode($products); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="admin.js"></script>
</body>
</html>
