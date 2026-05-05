<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'STAFF') {
    header("Location: ../loginandregis.html");
    exit;
}

require_once __DIR__ . '/../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId   = (int)$_POST['productId'];
    $name        = $_POST['productName'] ?? '';
    $price       = (float)$_POST['productPrice'];
    $description = $_POST['productDescription'] ?? '';
    $category    = $_POST['productCategory'] ?? '';

    if ($productId > 0) {
        $sql = "UPDATE products SET product_name = ?, price = ?, description = ?, product_category = ? WHERE product_id = ?";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $description, $category, $productId]);
            header("Location: adminpage.php#addproduct");
            exit;
        } catch (PDOException $e) {
            die("Error updating product: " . $e->getMessage());
        }
    }
}

header("Location: adminpage.php");
exit;
