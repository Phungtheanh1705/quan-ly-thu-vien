<?php
session_start();
include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username='$username' AND password='$password' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_username'] = $row['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập Admin</title>

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

.input-group {
    margin-bottom: 20px;
    position: relative;
}

.input-group input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border-radius: 50px;
    border: none;
    background: rgba(255,255,255,0.3);
    color: #fff; /* chữ nhập nổi bật */
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.input-group input::placeholder {
    color: #e0e0e0; /* placeholder mờ */
}

.input-group input:focus {
    outline: none;
    background: rgba(255,255,255,0.5);
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}

.input-group i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #0d6efd;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

/* Icon động khi focus input */
.input-group input:focus + i {
    transform: translateY(-50%) rotate(15deg);
    color: #6610f2;
}

.btn-login {
    border-radius: 50px;
    padding: 14px;
    font-weight: 500;
    transition: all 0.4s ease;
    background: linear-gradient(45deg, #0d6efd, #6610f2);
    color: #fff;
    border: none;
}
.btn-login:hover {
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.4);
}

.btn-back {
    margin-top: 15px;
    border-radius: 50px;
    background: rgba(255,255,255,0.3);
    color: #fff;
    border: none;
    transition: all 0.3s ease;
}
.btn-back:hover {
    background: rgba(255,255,255,0.5);
    transform: translateY(-2px);
}

.text-danger {
    margin-bottom: 15px;
    font-weight: 500;
    color: #ff6b6b;
    animation: shake 0.5s;
}

@keyframes shake {
    0% { transform: translateX(0);}
    25% { transform: translateX(-5px);}
    50% { transform: translateX(5px);}
    75% { transform: translateX(-5px);}
    100% { transform: translateX(0);}
}

@media(max-width: 576px){
    .login-box h3 { font-size: 1.5rem; }
    .btn-login, .input-group input { font-size: 1rem; padding: 10px 15px; }
}
</style>
</head>
<body>

<div class="login-box">
    <h3><i class="bi bi-book-half"></i> ADMIN LOGIN</h3>

    <?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

    <form method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <i class="bi bi-lock-fill"></i>
        </div>
        <button type="submit" class="btn btn-login w-100 mb-2">
            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
        </button>
    </form>

    <a href="../index.php" class="btn btn-back w-100"><i class="bi bi-arrow-left-circle"></i> Quay lại trang chủ</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
