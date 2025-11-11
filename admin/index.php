<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
include "../config/db.php";

// Thống kê số liệu
$total_books = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS count FROM books"))['count'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS count FROM users"))['count'];
$total_borrowed = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS count FROM borrows WHERE date_return IS NULL"))['count'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS count FROM borrows WHERE date_return IS NULL AND due_date < CURDATE()"))['count'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<!-- Bootstrap 5 & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }

/* Sidebar */
.sidebar {
    height: 100vh;
    position: fixed;
    width: 220px;
    background: linear-gradient(180deg, #0d6efd, #6610f2);
    color: #fff;
    padding-top: 20px;
    transition: all 0.3s;
}
.sidebar h4 { font-weight: 600; }
.sidebar a {
    color: #fff;
    text-decoration: none;
    padding: 12px 20px;
    display: block;
    border-radius: 8px;
    margin: 5px 10px;
    transition: all 0.3s;
}
.sidebar a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

/* Content */
.content { margin-left: 240px; padding: 30px; }

/* Header */
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

/* Cards */
.card {
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transition: transform 0.3s;
}
.card:hover { transform: translateY(-5px); }

/* Table */
.table thead { background: #0d6efd; color: #fff; }
.table tbody tr:hover { background: rgba(0,0,0,0.05); }
.table a { text-decoration: none; }

/* Responsive */
@media(max-width: 768px){
    .sidebar { width: 100%; height: auto; position: relative; }
    .content { margin-left: 0; padding: 15px; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">📚 Library Admin</h4>
    <a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="books/index.php"><i class="bi bi-book"></i> Quản lý sách</a>
    <a href="users/index.php"><i class="bi bi-people"></i> Sinh viên</a>
    <a href="borrows/index.php"><i class="bi bi-journal-check"></i> Mượn/Trả sách</a>
    <!-- Báo cáo đã bị xóa -->
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Content -->
<div class="content">
    <div class="header">
        <h2>Dashboard</h2>
        <p>Xin chào, <strong><?php echo $_SESSION['admin_username']; ?></strong> 👋</p>
    </div>

    <!-- Cards thống kê -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Sách</h5>
                <h2><?php echo $total_books; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Sinh viên</h5>
                <h2><?php echo $total_students; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Đang mượn</h5>
                <h2><?php echo $total_borrowed; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Quá hạn</h5>
                <h2><?php echo $total_overdue; ?></h2>
            </div>
        </div>
    </div>

    <!-- Bảng sách mới nhập -->
    <div class="mt-4">
        <h4>Danh sách sách mới nhập</h4>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tiêu đề sách</th>
                    <th>Tác giả</th>
                    <th>Thể loại</th>
                    <th>Số lượng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $books = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC LIMIT 5");
                $i = 1;
                while($row = mysqli_fetch_assoc($books)){
                    echo "<tr>";
                    echo "<td>{$i}</td>";
                    echo "<td>".htmlspecialchars($row['title'])."</td>";
                    echo "<td>".htmlspecialchars($row['author'])."</td>";
                    echo "<td>".htmlspecialchars($row['category'])."</td>";
                    echo "<td>{$row['quantity']}</td>";
                    echo "<td>
                            <a href='books/edit.php?id={$row['id']}' class='btn btn-sm btn-success'><i class='bi bi-pencil-square'></i></a>
                            <a href='books/delete.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Bạn có chắc muốn xóa?\");'><i class='bi bi-trash'></i></a>
                          </td>";
                    echo "</tr>";
                    $i++;
                }
                ?>
            </tbody>
        </table>
        <a href="books/index.php" class="btn btn-primary mt-2"><i class="bi bi-book"></i> Xem tất cả sách</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
