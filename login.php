<?php
session_start();
require_once __DIR__ . '/db_config.php';

$email    = $_POST['emailInput'] ?? '';
$password = $_POST['passwordInput'] ?? '';

// Check if email exists
$stmt = $conn->prepare("SELECT * FROM users WHERE EMAIL = ?");
$stmt->execute([$email]);
$rowname = $stmt->fetch(PDO::FETCH_ASSOC);

if ($rowname === false) {
    die("<script>
                alert('Email Not Found');
                window.location.href='loginandregis.html';
              </script>");
}

// Verify password
$stmtPass = $conn->prepare("SELECT * FROM users WHERE EMAIL = ? AND PASS = ?");
$stmtPass->execute([$email, $password]);
$rowpass = $stmtPass->fetch(PDO::FETCH_ASSOC);

if ($rowpass === false) {
    die("<script>
                alert('Wrong Password');
                window.location.href='loginandregis.html';
              </script>");
} else if ($rowpass['STATUS'] == "STAFF") {
    if (isset($_POST['rememberMe'])) {
        setcookie("rememberEmail", $email, time() + (86400 * 30), "/", "", false, true);
    }
    $_SESSION['fname'] = $rowpass['FIRSTNAME'];
    $_SESSION['lname'] = $rowpass['LASTNAME'];
    header("Location: admin/adminpage.php");
    exit;
} else {
    if (isset($_POST['rememberMe'])) {
        setcookie("rememberEmail", $email, time() + (86400 * 30), "/", "", false, true);
    }
    $_SESSION['user_id'] = $rowpass['USER_ID'];
    $_SESSION['fname']   = $rowpass['FIRSTNAME'];
    $_SESSION['lname']   = $rowpass['LASTNAME'];
    $_SESSION['email']   = $rowpass['EMAIL'];
    $_SESSION['status']  = $rowpass['STATUS'];
    header("Location: menupage.php");
    exit;
}

