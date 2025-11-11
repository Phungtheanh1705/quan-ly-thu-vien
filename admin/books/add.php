<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $image = trim($_POST['image']); // URL ảnh

    mysqli_query($conn,"INSERT INTO books(title,author,category,quantity,image) VALUES('$title','$author','$category','$quantity','$image')");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thêm sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.container { margin-top: 50px; max-width: 600px; }
.card { border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); padding: 30px; }
h3 { font-weight: 600; text-align: center; margin-bottom: 30px; }
.form-control { border-radius: 50px; }
.btn-submit { border-radius: 50px; background: linear-gradient(45deg,#0d6efd,#6610f2); color: #fff; font-weight: 500; padding: 12px; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); color: #fff; }
.btn-back { border-radius: 50px; background: #6c757d; color: #fff; font-weight: 500; padding: 12px; margin-top: 10px; }
.btn-back:hover { background: #5a6268; transform: translateY(-2px); }
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <h3>Thêm sách mới</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Tiêu đề</label>
                <input type="text" name="title" class="form-control" placeholder="Nhập tiêu đề sách" required>
            </div>
            <div class="mb-3">
                <label>Tác giả</label>
                <input type="text" name="author" class="form-control" placeholder="Nhập tác giả" required>
            </div>
            <div class="mb-3">
                <label>Thể loại</label>
                <input type="text" name="category" class="form-control" placeholder="Nhập thể loại" required>
            </div>
            <div class="mb-3">
                <label>Số lượng</label>
                <input type="number" name="quantity" class="form-control" placeholder="Nhập số lượng" required>
            </div>
            <div class="mb-3">
                <label>Link ảnh sách</label>
                <input type="text" name="image" class="form-control" placeholder="Nhập link ảnh (URL)" >
            </div>
            <button type="submit" class="btn btn-submit w-100">Thêm sách</button>
        </form>
        <a href="index.php" class="btn btn-back w-100"><i class="bi bi-arrow-left-circle"></i> Quay lại</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
