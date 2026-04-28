<?php
/**
 * MySQL database connection via PDO.
 * Update $host, $dbname, $username, and $password to match your environment.
 */
$host     = "localhost";
$dbname   = "Good_Day_Cafe";
$username = "";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}
