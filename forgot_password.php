<?php

$serverName = "ANGELO\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "Good_Day_Cafe",
    "Uid" => "",
    "PWD" => "",
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("<script>alert('Database connection failed'); window.location.href='loginandregis.html';</script>");
}

if (empty($_POST['resetEmail'])) {
    die("<script>alert('Email is required'); window.location.href='loginandregis.html';</script>");
}

$resetEmail = $_POST['resetEmail'];

$params = array($resetEmail);
$sql = "SELECT * FROM dbo.[USERS] WHERE EMAIL = ?";
$result = sqlsrv_query($conn, $sql, $params);
$row = sqlsrv_fetch_array($result);

if ($row == null) {
    die("<script>
                alert('Email not found');
                window.location.href='loginandregis.html';
              </script>");
}

$token = bin2hex(random_bytes(32));

$insertParams = array($resetEmail, $token);
$sqlInsert = "INSERT INTO dbo.[PASSWORD_RESETS] (EMAIL, TOKEN) VALUES (?, ?)";
sqlsrv_query($conn, $sqlInsert, $insertParams);

$resetLink = "reset_password.html?token=" . $token;

$subject = "Password Reset Request";
$message = "Click the link below to reset your password:\n\n" . $resetLink;
$headers = "From: no-reply@gooddaycafe.com";

$mailSent = mail($resetEmail, $subject, $message, $headers);

if ($mailSent) {
    die("<script>
                alert('A password reset link has been sent to your email.');
                window.location.href='loginandregis.html';
              </script>");
} else {
    die("<script>
                alert('Mail service unavailable. Use this link to reset your password: " . addslashes($resetLink) . "');
                window.location.href='" . addslashes($resetLink) . "';
              </script>");
}

?>
