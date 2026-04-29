<?php
$host     = "mysql";
$dbname   = "Good_Day_Cafe";
$username = "myuser";
$password = "mypassword";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected successfully to: " . $dbname;
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();  // shows the REAL error
}