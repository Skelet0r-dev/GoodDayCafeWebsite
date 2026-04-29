<?php
require_once __DIR__ . '/../db_config.php';

// Get the product ID from POST
$productId = intval($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid product ID.']));
}

// Get the image path
$stmt = $conn->prepare("SELECT filepath FROM product_image WHERE product_id = ?");
$stmt->execute([$productId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$imagePath = $row['FILEPATH'] ?? null;

// Delete image record from database
$conn->prepare("DELETE FROM product_image WHERE product_id = ?")
     ->execute([$productId]);

// Delete product record
try {
    $conn->prepare("DELETE FROM products WHERE product_id = ?")
         ->execute([$productId]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Failed to delete product.']));
}

// Delete the actual image file from the directory
if ($imagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $imagePath)) {
    unlink($_SERVER['DOCUMENT_ROOT'] . "/" . $imagePath);
}

// Return success response
echo json_encode(['success' => true]);
?>