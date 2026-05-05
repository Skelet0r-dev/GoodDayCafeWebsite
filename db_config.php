<?php
// For XAMPP or live servers like Hostinger, host is usually 'localhost' or '127.0.0.1'
// In Docker-compose, it's often 'mysql'
$host     = (getenv('DB_HOST')) ? getenv('DB_HOST') : "localhost"; 
$dbname   = "goodpman_Good_Day_Cafe";
$username = "goodpman_helloman";
$password = "2N_%C]xfcOQs";

// Fallback for the user's specific Docker setup if localhost fails
if ($host === "localhost" && !@fsockopen("localhost", 3306)) {
    $host = "mysql";
}

// --- Primary Connection (XAMPP / Production) ---
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Primary connection failed: " . $e->getMessage());
}

// --- Secondary Connection (Local Docker Database) ---
// Credentials from docker-compose.yml
$docker_host = "mysql"; 
$docker_db   = "Good_Day_Cafe";
$docker_user = "myuser";
$docker_pass = "mypassword";

try {
    // If we are outside Docker, we might need to connect to 127.0.0.1:3307 
    // (assuming port 3307 is mapped in docker-compose.yml)
    $d_host = (getenv('DB_HOST')) ? $docker_host : "127.0.0.1";
    $d_port = (getenv('DB_HOST')) ? "3306" : "3307";

    $conn_docker = new PDO(
        "mysql:host=$d_host;port=$d_port;dbname=$docker_db;charset=utf8mb4",
        $docker_user,
        $docker_pass
    );
    $conn_docker->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Secondary Docker connection failed: " . $e->getMessage());
}