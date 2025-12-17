<?php
$serverName = "ANGELO\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "Good_Day_Cafe",
    "Uid" => "",
    "PWD" => "",
];

// Secure database connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    error_log(print_r(sqlsrv_errors(), true));
    die("Database connection failed. Please try again later.");
}

session_start(); // Start the session to access data

$firstName = isset($_SESSION['fname']) ? $_SESSION['fname'] : 'Guest';
$lastName = isset($_SESSION['lname']) ? $_SESSION['lname'] : 'User';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$status = isset($_SESSION['status']) ? $_SESSION['status'] : '';

// Fetch products with their images
$query = "
SELECT 
    p.PRODUCT_ID,
    p.PRODUCT_NAME,
    p.DESCRIPTION,
    p.PRICE,
    p.PRODUCT_CATEGORY,
    i.IMAGE_NAME,
    i.FILEPATH
FROM PRODUCTS p
LEFT JOIN PRODUCT_IMAGE i ON p.PRODUCT_ID = i.PRODUCT_ID
";
$result = sqlsrv_query($conn, $query);

if ($result === false) {
    error_log(print_r(sqlsrv_errors(), true)); // Log error if the query fails
    die("Failed to retrieve products. Please check your database logs.");
}

$products = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    if (isset($row['FILEPATH'])) {
        $row['FILEPATH'] = str_replace(
            'C:\\xampp\\htdocs\\demo\\GoodayCafeWebsite-main',
            '/demo/GoodayCafeWebsite-main',
            $row['FILEPATH']
        );
    }
    $products[] = $row;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Good Day Cafe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/menupage_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
  </head>

  <header>
    <!-- Carousel: autoplay enabled via data attributes below -->
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000" data-bs-pause="hover" aria-label="Featured images carousel">
      <!-- Indicators -->
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="2" aria-label="Slide 3"></button>
        <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="3" aria-label="Slide 4"></button>
      </div>

      <!-- Slides -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="assets/img/Gemini_Generated_Image_hkvt8ahkvt8ahkvt.png" class="d-block w-100 gooddaylogoccarosel-img" alt="Coffee and pastries — slide 1" style="height:500px; object-fit:cover;">
        </div>

        <div class="carousel-item">
          <img src="assets/img/Gemini_Generated_Image_dw50l4dw50l4dw50.png" class="d-block w-100" alt="Coffee closeup — slide 2" style="height:500px; object-fit:cover; object-position: center;">
          <div class="carousel-caption d-none d-md-block text-start">
            <h3 class="font-playfair fw-bold">Try our Refreshing refreshers!</h3>
            <p>Try to beat the heat with our cool refreshers.</p>
          </div>
        </div>

        <div class="carousel-item">
          <img src="assets/img/Gemini_Generated_Image_dl8qt7dl8qt7dl8q.png" class="d-block w-100" alt="Pastries display — slide 3" style="height:500px; object-fit:cover;">
          <div class="carousel-caption d-none d-md-block text-start">
            <h3 class="font-playfair fw-bold">Biscoff Frappucinos</h3>
            <p>New item! biscoff Frappucinos.</p>
          </div>
        </div>

        <div class="carousel-item">
          <img src="assets/img/Add_a_subheading.png" class="d-block w-100" alt="Pastries display — slide 4" style="height:500px; object-fit:cover;">
          <div class="carousel-caption d-none d-md-block text-start"></div>
        </div>
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev" aria-label="Previous slide">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next" aria-label="Next slide">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </header>

  <div style="padding-top: 30px;">
    <div class="container text-center py-4 mb-1">
      <h1 class="font-playfair fw-bold" style="font-size: 75px;">Our Menu</h1>
      <h4 class="font-playfair fw-bold" style="font-size: 25px;">Welcome, <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>!</h4>
      <p class="lead" style="padding-top: 20px;">Explore our delicious offerings below!</p>
    </div>
  </div>

  <!-- NAVBAR -->
  <div class="container-fluid py-2 mb-2 mt-4 sticky-top" id="navbar">
    <div class="container py-3 px-3" style="background: rgb(92, 78, 59); border-radius:40px;">
      
      <!-- Mobile controls -->
      <div class="d-flex d-md-none justify-content-between align-items-center mb-2">
        <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="offcanvas" data-bs-target="#navbarOffcanvas" aria-controls="navbarOffcanvas">
          Menu <i class="bi bi-list"></i>
        </button>
        <button class="btn btn-light btn-sm rounded-pill px-3 toggle-nav-cart" data-bs-toggle="offcanvas" data-bs-target="#cart" aria-controls="cart">
          Cart <i class="bi bi-cart-fill"></i>
        </button>
      </div>

      <!-- Desktop nav -->
      <div class="d-none d-md-flex justify-content-center gap-2 align-items-center flex-wrap" id="navbar-desktop">

        <div class="d-flex gap-3">
          <!-- User Profile button to trigger offcanvas -->
          <a href="#"
             class="btn btn-sm rounded-pill nav-btn px-3 text-decoration-none toggle-nav"
             data-bs-toggle="offcanvas"
             data-bs-target="#userProfile"
             aria-controls="userProfile">
            <?php echo htmlspecialchars($firstName); ?> <i class="bi bi-person-fill"></i>
          </a>
        </div>

        <!-- Nav Buttons -->
        <a href="#productIcedDrinkRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Iced-Drinks</a>
        <a href="#productHotDrinkRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Hot-Drinks</a>
        <a href="#productFrappeRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Frappes</a>
        <a href="#productRefresherRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Refresher</a>
        <a href="#productPizzaRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Pizzas</a>
        <a href="#productPastaRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Pastas</a>
        <a href="#productPastryRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link toggle-nav">Pastries</a>

        <!-- Cart -->
        <a href="#"
           class="btn btn-sm rounded-pill nav-btn px-3 text-decoration-none toggle-nav-cart"
           data-bs-toggle="offcanvas"
           data-bs-target="#cart"
           aria-controls="cart">
          Cart <i class="bi bi-cart-fill"></i>
        </a>
      </div>
    </div>
  </div>
  <!-- NAVBAR -->

  <!-- Offcanvas Navbar for mobile -->
<div class="offcanvas offcanvas-start d-md-none mobile-nav-shell" tabindex="-1" id="navbarOffcanvas" aria-labelledby="navbarOffcanvasLabel">
  <div class="offcanvas-header mobile-nav-body">
    <h5 class="offcanvas-title text-light" style="color: #f5f0e9;" id="navbarOffcanvasLabel">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column gap-2 mobile-nav-body">
    <a href="#productIcedDrinkRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Iced-Drinks</a>
    <a href="#productHotDrinkRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Hot-Drinks</a>
    <a href="#productFrappeRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Frappes</a>
    <a href="#productRefresherRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Refresher</a>
    <a href="#productPizzaRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Pizzas</a>
    <a href="#productPastaRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Pastas</a>
    <a href="#productPastryRow" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav" data-bs-dismiss="offcanvas">Pastries</a>
    <a href="#" class="btn btn-sm rounded-pill nav-btn px-3 nav-link text-start w-100 toggle-nav-cart" data-bs-toggle="offcanvas" data-bs-target="#cart" data-bs-dismiss="offcanvas">Cart</a>
  </div>
</div>

  <div style="padding-top: 20px;"></div>

  <body style="background:rgb(245, 240, 233);">

    <!-- Clickable Cards -->
    <div class="container py-5" id="card-section-Iced-Coffee">
      <div class="row justify-content-center g-4">

        <!-- Iced Drinks Section -->
        <div class="row g-4" id="productIcedDrinkRow">
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

        <!-- Hot Drinks Section -->
        <div class="row g-4" id="productHotDrinkRow">
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

        <!-- Frappe Section -->
        <div class="row g-4" id="productFrappeRow">
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

        <!-- Refresher Section -->
        <div class="row g-4" id="productRefresherRow">
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

        <!-- Pizza Section -->
        <div class="row g-4" id="productPizzaRow">
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

        <!-- Pasta Section -->
        <div class="row g-4" id="productPastaRow">
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

        <!-- Pastries Section -->
        <div class="row g-4" id="productPastryRow">
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

      </div>
    </div>
    <!-- /Card -->

    <!-- Offcanvas Cart -->
    <div class="offcanvas offcanvas-end cart-offcanvas"
         data-bs-scroll="true"
         data-bs-backdrop="false"
         tabindex="-1"
         id="cart"
         aria-labelledby="offcanvasScrollingLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Your Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <!-- Cart Items -->
        <ul id="cartItems" class="list-group list-group-flush">
          <!-- Cart items will be dynamically added here -->
        </ul>
        <div class="mt-3">
          <!-- Total Price -->
          <strong>Total: <span id="cartTotal">0.00</span></strong>
        </div>

        <div class="offcanvas-footer">
          <!-- Checkout Form -->
          <form id="checkoutForm" action="order.php" method="POST">
            <input type="hidden" name="cartItemsInput" id="cartItemsInput">
            <input type="hidden" name="totalPriceInput" id="totalPriceInput">
            <input type="hidden" name="userIdInput" id="userIdInput" value="<?php echo htmlspecialchars($userId); ?>">
            <input type="hidden" name="userStatus" id="userStatus" value="<?php echo htmlspecialchars($status); ?>">
            <input type="number" name="paymentAmountInput" id="paymentAmountInput" placeholder="Enter Payment Amount" class="form-control mt-3" required>
            <button type="submit" class="btn btn-success w-100 mt-4">Checkout</button>
          </form>
        </div>
      </div>
    </div>
    <!--/Offcanvas Cart -->

    <!-- Offcanvas User Profile -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="userProfile" aria-labelledby="userProfileLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="userProfileLabel">User Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($firstName); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($lastName); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>

        <a href="loginandregis.html" class="btn btn-danger mt-3">Logout</a>
        <!-- Additional user info can be added here -->
      </div>
    </div>

    <script>
      const products = <?php echo json_encode($products); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/demo/GoodayCafeWebsite-main/assets/js/product.js"></script>

  </body>
</html>