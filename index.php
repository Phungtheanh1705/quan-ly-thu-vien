<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý thư viện</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        .overlay {
            background: rgba(0,0,0,0.6);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            text-align: center;
            color: #fff;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 12px rgba(0,0,0,0.7);
        }
        .btn-login {
            font-size: 1.2rem;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            margin: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-login i {
            margin-right: 0.5rem;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        .btn-admin {
            background: #0d6efd;
            border: none;
        }
        .btn-user {
            background: #198754;
            border: none;
        }
        @media(max-width: 576px){
            .hero h1 {
                font-size: 2rem;
            }
            .btn-login {
                font-size: 1rem;
                padding: 0.7rem 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="hero">
        <div>
            <h1>HỆ THỐNG QUẢN LÝ THƯ VIỆN</h1>
            <a href="admin/login.php" class="btn btn-admin btn-login">
                <i class="bi bi-person-badge-fill"></i>Đăng nhập Admin
            </a>
            <a href="user/login.php" class="btn btn-user btn-login">
                <i class="bi bi-person-fill"></i>Đăng nhập Sinh viên
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
