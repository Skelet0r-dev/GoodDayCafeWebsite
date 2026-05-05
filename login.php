<?php
session_start();
require_once __DIR__ . '/db_config.php';

$email    = $_POST['emailInput'] ?? '';
$password = $_POST['passwordInput'] ?? '';

// Check if email exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$rowname = $stmt->fetch(PDO::FETCH_ASSOC);

if ($rowname === false) {
    die("<script>
                alert('Email Not Found');
                window.location.href='loginandregis.html';
              </script>");
}

// Verify password using password_verify
if (!password_verify($password, $rowname['pass'])) {
    die("<script>
                alert('Wrong Password');
                window.location.href='loginandregis.html';
              </script>");
}

// Set common session variables
$_SESSION['user_id'] = $rowname['user_id'];
$_SESSION['fname']   = $rowname['firstname'];
$_SESSION['lname']   = $rowname['lastname'];
$_SESSION['email']   = $rowname['email'];
$_SESSION['status']  = $rowname['status'];

if (isset($_POST['rememberMe'])) {
    setcookie("rememberEmail", $email, time() + (86400 * 30), "/", "", false, true);
}

if ($rowname['status'] == "STAFF") {
    header("Location: admin/adminpage.php");
    exit;
} else {
    header("Location: menupage.php");
    exit;
}
