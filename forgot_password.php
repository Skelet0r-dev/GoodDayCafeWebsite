<?php

require_once __DIR__ . '/db_config.php';

if (!$conn) {
    die("<script>alert('Database connection failed'); window.location.href='loginandregis.html';</script>");
}

if (empty($_POST['resetEmail'])) {
    die("<script>alert('Email is required'); window.location.href='loginandregis.html';</script>");
}

$resetEmail = $_POST['resetEmail'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$resetEmail]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    die("<script>
                alert('Email not found');
                window.location.href='loginandregis.html';
              </script>");
}

$token = bin2hex(random_bytes(32));

$sqlInsert = "INSERT INTO password_resets (email, token) VALUES (?, ?)";
$conn->prepare($sqlInsert)->execute([$resetEmail, $token]);

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

