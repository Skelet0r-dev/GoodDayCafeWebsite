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

if (empty($_POST['token']) || empty($_POST['newPassword'])) {
    die("<script>alert('Invalid request'); window.location.href='loginandregis.html';</script>");
}

$token = $_POST['token'];
$newPassword = $_POST['newPassword'];

$params = array($token);
$sql = "SELECT * FROM dbo.[PASSWORD_RESETS] WHERE TOKEN = ? AND CREATED_AT >= DATEADD(HOUR, -1, GETDATE())";
$result = sqlsrv_query($conn, $sql, $params);
$row = sqlsrv_fetch_array($result);

if ($row == null) {
    die("<script>
                alert('Invalid or expired token');
                window.location.href='loginandregis.html';
              </script>");
}

$email = $row['EMAIL'];

$updateParams = array($newPassword, $email);
$sqlUpdate = "UPDATE dbo.[USERS] SET PASS = ? WHERE EMAIL = ?";
sqlsrv_query($conn, $sqlUpdate, $updateParams);

$deleteParams = array($token);
$sqlDelete = "DELETE FROM dbo.[PASSWORD_RESETS] WHERE TOKEN = ?";
sqlsrv_query($conn, $sqlDelete, $deleteParams);

die("<script>
            alert('Password reset successful');
            window.location.href='loginandregis.html';
          </script>");

?>
