<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id=$id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];

    $sql = "UPDATE users SET username='$username', password='$password', fullname='$fullname', email='$email' WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sửa Sinh viên</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.container { margin-top: 50px; max-width: 500px; }
.btn-submit { background: linear-gradient(45deg,#0d6efd,#6610f2); color: #fff; border-radius: 50px; padding: 8px 20px; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); color: #fff; }
</style>
</head>
<body>
<div class="container">
    <h2>Sửa Sinh viên</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($user['password']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Họ và tên</label>
            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <button type="submit" class="btn btn-submit w-100"><i class="bi bi-pencil-square"></i> Lưu thay đổi</button>
    </form>
    <a href="index.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left-circle"></i> Quay lại danh sách</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
