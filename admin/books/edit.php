<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$res = mysqli_query($conn,"SELECT * FROM books WHERE id='$id'");
$book = mysqli_fetch_assoc($res);

if($_SERVER['REQUEST_METHOD']=='POST'){
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $image = trim($_POST['image']); // URL ảnh mới

    $sql = "UPDATE books SET 
                title='$title', 
                author='$author', 
                category='$category', 
                quantity='$quantity', 
                image='$image' 
            WHERE id='$id'";

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
<title>Sửa sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.container { margin-top: 50px; max-width: 600px; }
.card { border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); padding: 30px; }
.btn-submit { border-radius: 50px; background: linear-gradient(45deg,#0d6efd,#6610f2); color: #fff; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
img { max-height: 150px; display: block; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="container">
    <div class="card p-4">
        <h3 class="mb-4">Sửa sách</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Tiêu đề</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($book['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Tác giả</label>
                <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($book['author']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Thể loại</label>
                <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($book['category']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Số lượng</label>
                <input type="number" name="quantity" class="form-control" value="<?php echo $book['quantity']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Link ảnh sách</label>
                <?php if($book['image']): ?>
                    <img src="<?php echo htmlspecialchars($book['image']); ?>" alt="Ảnh sách">
                <?php endif; ?>
                <input type="text" name="image" class="form-control" placeholder="Nhập link ảnh (URL)" value="<?php echo htmlspecialchars($book['image']); ?>">
            </div>
            <button type="submit" class="btn btn-submit w-100">Lưu thay đổi</button>
        </form>
        <a href="index.php" class="btn btn-secondary w-100 mt-2"><i class="bi bi-arrow-left-circle"></i> Quay lại</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
