<?php
session_start();
include "../config/db.php";

$error_register = '';
$success_register = '';

if(isset($_POST['action']) && $_POST['action'] == 'register'){
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if($password != $confirm){
        $error_register = "Mật khẩu không khớp!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if(mysqli_num_rows($check) > 0){
            $error_register = "Tên đăng nhập đã tồn tại!";
        } else {
            mysqli_query($conn, "INSERT INTO users (fullname, username, password) VALUES ('$fullname','$username','$password')");
            $success_register = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng ký Sinh viên</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Style giống login.php */
body, html { height:100%; margin:0; font-family:'Poppins',sans-serif; background:url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed; background-size:cover; display:flex; justify-content:center; align-items:center;}
.login-box { background: rgba(255,255,255,0.2); backdrop-filter: blur(15px); padding:40px 30px; border-radius:25px; width:400px; max-width:90%; box-shadow:0 20px 50px rgba(0,0,0,0.25); text-align:center;}
.input-group{margin-bottom:15px;position:relative;}
.input-group input{width:100%;padding:12px 45px 12px 15px;border-radius:50px;border:none;background:rgba(255,255,255,0.3);color:#fff;}
.input-group i{position:absolute; right:15px; top:50%; transform:translateY(-50%); color:#0d6efd;font-size:1.2rem;}
.input-group input:focus{outline:none; background: rgba(255,255,255,0.5);}
.input-group input:focus + i{transform:translateY(-50%) rotate(15deg); color:#6610f2;}
.btn-register{border-radius:50px; padding:14px; font-weight:500; background:linear-gradient(45deg,#0d6efd,#6610f2); color:#fff; border:none; width:100%; margin-bottom:10px;}
.btn-register:hover{transform:scale(1.05);}
.btn-back{margin-top:15px;border-radius:50px;background:rgba(255,255,255,0.3);color:#fff;border:none;width:100%;}
.btn-back:hover{background: rgba(255,255,255,0.5);}
.text-danger{margin-bottom:15px;color:#ff6b6b;}
.text-success{margin-bottom:15px;color:#28a745;}
</style>
</head>
<body>
<div class="login-box">
<h3><i class="bi bi-person-circle"></i> Đăng ký Sinh viên</h3>
<?php 
if($error_register) echo "<p class='text-danger'>$error_register</p>";
if($success_register) echo "<p class='text-success'>$success_register</p>";
?>
<form method="POST">
<input type="hidden" name="action" value="register">
<div class="input-group">
<input type="text" name="fullname" placeholder="Họ và tên" required><i class="bi bi-card-text"></i>
</div>
<div class="input-group">
<input type="text" name="username" placeholder="Tên đăng nhập" required><i class="bi bi-person-fill"></i>
</div>
<div class="input-group">
<input type="password" name="password" placeholder="Mật khẩu" required><i class="bi bi-lock-fill"></i>
</div>
<div class="input-group">
<input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required><i class="bi bi-lock-fill"></i>
</div>
<button type="submit" class="btn-register"><i class="bi bi-pencil-square"></i> Đăng ký</button>
</form>
<a href="login.php" class="btn-back"><i class="bi bi-arrow-left-circle"></i> Quay lại đăng nhập</a>
</div>
</body>
</html>
