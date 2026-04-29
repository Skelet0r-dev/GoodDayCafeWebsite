<?php
require_once __DIR__ . '/../db_config.php';

// Get POST data
$product_name        = $_POST['productName'];
$product_price       = $_POST['productPrice'];
$product_description = $_POST['productDescription'];
$product_category    = $_POST['productCategory'];
$image               = $_FILES['productImage'];

// Validate POST data
if (!$product_name || !$product_price || !$product_description || !$product_category || !$image) {
    die("All fields are required.");
}

// Insert product into the database
$insertProductStmt = $conn->prepare(
    "INSERT INTO products (product_name, price, description, product_category)
     VALUES (?, ?, ?, ?)"
);

if (!$insertProductStmt->execute([$product_name, $product_price, $product_description, $product_category])) {
    die("Failed to add product.");
}

// Retrieve the auto-generated product ID
$productId = $conn->lastInsertId();

if (!$productId) {
    die("Failed to retrieve a valid product ID.");
}

// Handle image upload
$destination = __DIR__ . '/../uploads/';
$imageName = basename($image['name']);
$targetImagePath = $destination . $imageName;
$webPath = '/uploads/' . $imageName;
$allowedTypes   = ['png', 'jpg', 'jpeg', 'gif'];
$fileType       = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

// Validate image type
if (!in_array($fileType, $allowedTypes)) {
    die("Unsupported image format. Allowed formats: PNG, JPG, JPEG, GIF.");
}

// Move uploaded image
if (!move_uploaded_file($image['tmp_name'], $targetImagePath)) {
    die("Failed to upload image.");
}

// Insert image data into the database
$insertImageStmt = $conn->prepare(
    "INSERT INTO product_image (image_name, filepath, product_id)
     VALUES (?, ?, ?)"
);

if (!$insertImageStmt->execute([$imageName, $webPath, $productId])) {
    die("Failed to save image data.");
}

// Success message
echo "<script>
    alert('Product added successfully.');
    window.location.href = 'adminpage.php';
</script>";
?>
