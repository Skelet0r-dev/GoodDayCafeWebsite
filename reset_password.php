<?php

require_once __DIR__ . '/db_config.php';

if (!$conn) {
    die("<script>alert('Database connection failed'); window.location.href='loginandregis.html';</script>");
}

if (empty($_POST['token']) || empty($_POST['newPassword'])) {
    die("<script>alert('Invalid request'); window.location.href='loginandregis.html';</script>");
}

$token       = $_POST['token'];
$newPassword = $_POST['newPassword'];

$stmt = $conn->prepare(
    "SELECT * FROM password_resets WHERE token = ? AND CREATED_AT >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
);
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    die("<script>
                alert('Invalid or expired token');
                window.location.href='loginandregis.html';
              </script>");
}

$email = $row['EMAIL'];

$conn->prepare("UPDATE users SET pass = ? WHERE email = ?")
     ->execute([$newPassword, $email]);

$conn->prepare("DELETE FROM password_resets WHERE token = ?")
     ->execute([$token]);

die("<script>
            alert('Password reset successful');
            window.location.href='loginandregis.html';
          </script>");

