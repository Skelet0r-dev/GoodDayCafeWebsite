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

// Verify password
$stmtPass = $conn->prepare("SELECT * FROM users WHERE email = ? AND pass = ?");
$stmtPass->execute([$email, $password]);
$rowpass = $stmtPass->fetch(PDO::FETCH_ASSOC);

if ($rowpass === false) {
    die("<script>
                alert('Wrong Password');
                window.location.href='loginandregis.html';
              </script>");
} else if ($rowpass['status'] == "STAFF") {
    if (isset($_POST['rememberMe'])) {
        setcookie("rememberEmail", $email, time() + (86400 * 30), "/", "", false, true);
    }
    $_SESSION['fname'] = $rowpass['firstname'];
    $_SESSION['lname'] = $rowpass['lastname'];
    header("Location: admin/adminpage.php");
    exit;
} else {
    if (isset($_POST['rememberMe'])) {
        setcookie("rememberEmail", $email, time() + (86400 * 30), "/", "", false, true);
    }
    $_SESSION['user_id'] = $rowpass['user_id'];
    $_SESSION['fname']   = $rowpass['firstname'];
    $_SESSION['lname']   = $rowpass['lastname'];
    $_SESSION['email']   = $rowpass['email'];
    $_SESSION['status']  = $rowpass['status'];
    header("Location: menupage.php");
    exit;
}

