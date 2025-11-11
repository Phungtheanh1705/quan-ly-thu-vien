<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

// Xóa nhiều sách nếu form gửi
if(isset($_POST['delete_selected'])){
    if(!empty($_POST['selected_books'])){
        $ids = implode(",", array_map('intval', $_POST['selected_books']));
        mysqli_query($conn, "DELETE FROM books WHERE id IN ($ids)");
        header("Location: index.php");
        exit();
    }
}

// Lấy danh sách sách
$sql = "SELECT * FROM books ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.sidebar { height: 100vh; position: fixed; width: 220px; background: linear-gradient(180deg, #0d6efd, #6610f2); color: #fff; padding-top: 20px; }
.sidebar h4 { text-align: center; margin-bottom: 20px; }
.sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin: 5px 10px; transition: all 0.3s; }
.sidebar a:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }
.content { margin-left: 240px; padding: 30px; }
.btn-add, .btn-delete-selected { border-radius: 50px; padding: 8px 20px; margin-bottom: 15px; color: #fff; transition: all 0.3s; }
.btn-add { background: linear-gradient(45deg,#0d6efd,#6610f2); }
.btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
.btn-delete-selected { background: linear-gradient(45deg,#dc3545,#a71d2a); }
.btn-delete-selected:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
.table thead { background: #0d6efd; color: #fff; }
.table tbody tr:hover { background: rgba(0,0,0,0.05); }
</style>
</head>
<body>

<div class="sidebar">
    <h4>📚 Library Admin</h4>
    <a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php"><i class="bi bi-book"></i> Quản lý sách</a>
    <a href="../users/index.php"><i class="bi bi-people"></i> Sinh viên</a>
    <a href="../borrow/index.php"><i class="bi bi-journal-check"></i> Mượn/Trả sách</a>
    <a href="../report.php"><i class="bi bi-bar-chart-line"></i> Báo cáo</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <div class="header">
        <h2>Quản lý Sách</h2>
        <p>Xin chào, <strong><?php echo $_SESSION['admin_username']; ?></strong> 👋</p>
    </div>

    <form method="POST">
        <a href="add.php" class="btn btn-add"><i class="bi bi-plus-circle"></i> Thêm sách</a>
        <button type="submit" name="delete_selected" class="btn btn-delete-selected"><i class="bi bi-trash"></i> Xóa đã chọn</button>

        <table class="table table-striped table-hover mt-3">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Tiêu đề</th>
                    <th>Tác giả</th>
                    <th>Thể loại</th>
                    <th>Số lượng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($row=mysqli_fetch_assoc($result)){ ?>
                <tr>
                    <td><input type="checkbox" name="selected_books[]" value="<?php echo $row['id']; ?>"></td>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-pencil-square"></i></a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa sách này?');"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chọn tất cả checkbox
document.getElementById('checkAll').addEventListener('change', function(){
    let checkboxes = document.querySelectorAll('input[name="selected_books[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
</body>
</html>
