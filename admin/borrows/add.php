<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách sinh viên
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY fullname ASC");

// Lấy danh sách sách còn tồn
$books = mysqli_query($conn, "SELECT * FROM books WHERE quantity > 0 ORDER BY title ASC");

// Xử lý submit
if($_SERVER['REQUEST_METHOD']=='POST'){
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $date_borrow = $_POST['date_borrow'];
    $due_date = $_POST['due_date'];

    // Thêm phiếu mượn
    mysqli_query($conn, "INSERT INTO borrows(user_id, book_id, date_borrow, due_date, status) 
        VALUES('$user_id','$book_id','$date_borrow','$due_date','Đang mượn')");

    // Giảm số lượng sách
    mysqli_query($conn, "UPDATE books SET quantity = quantity - 1 WHERE id='$book_id'");

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thêm phiếu mượn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.container { max-width: 600px; margin-top: 50px; }
.card { border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); padding: 30px; }
.form-control, .btn-submit { border-radius: 50px; }
.btn-submit { background: linear-gradient(45deg,#0d6efd,#6610f2); color: #fff; font-weight: 500; padding: 12px; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); color: #fff; }
.btn-back { border-radius: 50px; background: #6c757d; color: #fff; font-weight: 500; padding: 12px; margin-top: 10px; }
.btn-back:hover { background: #5a6268; transform: translateY(-2px); }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h3 class="mb-4 text-center">Thêm phiếu mượn</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Sinh viên</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php while($u=mysqli_fetch_assoc($users)): ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['fullname'].' ('.$u['username'].')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Sách</label>
                <select name="book_id" class="form-control" required>
                    <option value="">-- Chọn sách --</option>
                    <?php while($b=mysqli_fetch_assoc($books)): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['title'].' (Còn '.$b['quantity'].')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Ngày mượn</label>
                <input type="date" name="date_borrow" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label>Hạn trả</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
            </div>
            <button type="submit" class="btn btn-submit w-100"><i class="bi bi-check-circle"></i> Thêm phiếu mượn</button>
        </form>
        <a href="index.php" class="btn btn-back w-100 mt-2"><i class="bi bi-arrow-left-circle"></i> Quay lại</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
