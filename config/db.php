<?php
$host = "localhost";
$user = "root";
$pass = "123456789";       // XAMPP mặc định không có mật khẩu
$db   = "ql_thuvien";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
}

// echo "✅ Kết nối database thành công!";
?>
