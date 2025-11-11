<?php
session_start();
include "../config/db.php"; // Kết nối MySQLi

// --- Xử lý đăng nhập ---
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_username'] = $row['username'];
        $_SESSION['user_fullname'] = $row['fullname'];
        header("Location: index.php");
        exit();
    } else {
        $error_login = "Sai tài khoản hoặc mật khẩu!";
    }
}

// --- Xử lý đăng ký ---
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);
    $email = trim($_POST['email']); // thêm email

    if ($password != $confirm) {
        $error_register = "Mật khẩu không khớp!";
    } else {
        // Kiểm tra username tồn tại chưa
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error_register = "Tên đăng nhập đã tồn tại!";
        } else {
            // Lưu cả email
            mysqli_query($conn, "INSERT INTO users (fullname, username, password, email) 
                                 VALUES ('$fullname','$username','$password','$email')");
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
<title>Đăng nhập Sinh viên</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body, html {
    height: 100%;
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    position: relative;
    z-index: 2;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    padding: 50px 35px;
    border-radius: 25px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    text-align: center;
    animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
    from {opacity:0; transform: translateY(-30px);}
    to {opacity:1; transform: translateY(0);}
}

.login-box h3 {
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 2rem;
    color: #fff;
    text-shadow: 0 2px 6px rgba(0,0,0,0.5);
}

.input-group { margin-bottom: 20px; position: relative; }
.input-group input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border-radius: 50px;
    border: none;
    background: rgba(255,255,255,0.3);
    color: #fff;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.input-group input::placeholder { color: #e0e0e0; }
.input-group input:focus { outline: none; background: rgba(255,255,255,0.5); }
.input-group i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #0d6efd; font-size: 1.2rem; }
.input-group input:focus + i { transform: translateY(-50%) rotate(15deg); color: #6610f2; }

.btn-login, .btn-register {
    border-radius: 50px;
    padding: 14px;
    font-weight: 500;
    transition: all 0.4s ease;
    background: linear-gradient(45deg, #0d6efd, #6610f2);
    color: #fff;
    border: none;
    width: 100%;
    margin-bottom: 10px;
}
.btn-login:hover, .btn-register:hover { transform: scale(1.05) translateY(-2px); box-shadow: 0 12px 25px rgba(0,0,0,0.4); }

.btn-back {
    margin-top: 10px;
    border-radius: 50px;
    background: rgba(255,255,255,0.3);
    color: #fff;
    border: none;
    width: 100%;
}
.btn-back:hover { background: rgba(255,255,255,0.5); transform: translateY(-2px); }

.text-danger { margin-bottom: 15px; font-weight: 500; color: #ff6b6b; animation: shake 0.5s; }
.text-success { margin-bottom: 15px; font-weight: 500; color: #28a745; }

#registerForm { display: none; }

@keyframes shake {
    0% { transform: translateX(0);}
    25% { transform: translateX(-5px);}
    50% { transform: translateX(5px);}
    75% { transform: translateX(-5px);}
    100% { transform: translateX(0);}
}
</style>
</head>
<body>

<div class="login-box">
    <h3><i class="bi bi-person-circle"></i> Sinh viên</h3>

    <?php 
    if(isset($error_login)) echo "<p class='text-danger'>$error_login</p>";
    if(isset($error_register)) echo "<p class='text-danger'>$error_register</p>";
    if(isset($success_register)) echo "<p class='text-success'>$success_register</p>";
    ?>

    <!-- Form đăng nhập -->
    <form method="POST" id="loginForm">
        <input type="hidden" name="action" value="login">
        <div class="input-group">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <i class="bi bi-lock-fill"></i>
        </div>
        <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</button>
        <p style="color:#fff;">Chưa có tài khoản? <a href="#" id="showRegister" style="color:#ffc107;">Đăng ký</a></p>
    </form>

    <!-- Form đăng ký -->
    <form method="POST" id="registerForm">
        <input type="hidden" name="action" value="register">
        <div class="input-group">
            <input type="text" name="fullname" placeholder="Họ và tên" required>
            <i class="bi bi-card-text"></i>
        </div>
        <div class="input-group">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
            <i class="bi bi-envelope-fill"></i>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <i class="bi bi-lock-fill"></i>
        </div>
        <div class="input-group">
            <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            <i class="bi bi-lock-fill"></i>
        </div>
        <button type="submit" class="btn-register"><i class="bi bi-pencil-square"></i> Đăng ký</button>
        <p style="color:#fff;">Đã có tài khoản? <a href="#" id="showLogin" style="color:#ffc107;">Đăng nhập</a></p>
    </form>

    <a href="../index.php" class="btn-back"><i class="bi bi-arrow-left-circle"></i> Quay lại trang chủ</a>
</div>

<script>
document.getElementById("showRegister").addEventListener("click", function(e){
    e.preventDefault();
    document.getElementById("loginForm").style.display = "none";
    document.getElementById("registerForm").style.display = "block";
});
document.getElementById("showLogin").addEventListener("click", function(e){
    e.preventDefault();
    document.getElementById("registerForm").style.display = "none";
    document.getElementById("loginForm").style.display = "block";
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
