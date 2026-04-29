<?php
require_once __DIR__ . '/db_config.php';

$fname    = $_POST['fname'];
$lname    = $_POST['lname'];
$bday     = $_POST['bday'];
$emailReg = $_POST['emailReg'];
$status   = $_POST['statusReg'];
$passReg  = $_POST['passReg'];

// Check if email is already taken
$checkStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
$checkStmt->execute([$emailReg]);
$emailcheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($emailcheck !== false) {
    echo "<script>
                alert('Email Already Taken');
                window.location.href='loginandregis.html';
              </script>";
    exit;
} else {
    $insertStmt = $conn->prepare(
        "INSERT INTO users (firstname, lastname, dateofbirth, email, pass, status)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $result = $insertStmt->execute([$fname, $lname, $bday, $emailReg, $passReg, $status]);

    if ($result) {
        echo "<script>
                    alert('Registration Successful');
                    window.location.href='loginandregis.html';
                  </script>";
    } else {
        die("Registration failed. Please try again.");
    }
}

