<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Xử lý mượn sách
if(isset($_GET['action']) && $_GET['action']=='borrow' && isset($_GET['id'])){
    $book_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    mysqli_query($conn, "INSERT INTO borrows (user_id, book_id, date_borrow, due_date, status) VALUES ($user_id, $book_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Đang mượn')");
    header("Location: my_borrows.php");
    exit();
}

// Lấy danh sách phiếu mượn của user
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT br.*, b.title FROM borrows br JOIN books b ON br.book_id=b.id WHERE br.user_id=$user_id");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Phiếu mượn của tôi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
<h3>Phiếu mượn của tôi</h3>
<table class="table table-striped">
<tr><th>#</th><th>Tên sách</th><th>Ngày mượn</th><th>Hạn trả</th><th>Trạng thái</th></tr>
<?php $i=1; while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['date_borrow']; ?></td>
<td><?php echo $row['due_date']; ?></td>
<td><?php echo $row['status']; ?></td>
</tr>
<?php } ?>
</table>
<a href="index.php" class="btn btn-secondary">Quay lại</a>
</div>
</body>
</html>
