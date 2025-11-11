<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

// --- PHÂN TRANG & TÌM KIẾM ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Điều kiện tìm kiếm
$where = '';
if($search !== ''){
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where = "WHERE username LIKE '%$search_esc%' OR fullname LIKE '%$search_esc%' OR email LIKE '%$search_esc%'";
}

// Lấy tổng số bản ghi
$total_sql = "SELECT COUNT(*) AS count FROM users $where";
$total_result = mysqli_fetch_assoc(mysqli_query($conn, $total_sql));
$total_records = $total_result['count'];
$total_pages = ceil($total_records / $limit);

// Lấy danh sách sinh viên theo trang
$sql = "SELECT * FROM users $where ORDER BY id DESC LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Sinh viên</title>
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
}
.sidebar h4 { text-align: center; margin-bottom: 20px; }
.sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin: 5px 10px; transition: all 0.3s; }
.sidebar a:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }

/* Content */
.content { margin-left: 240px; padding: 30px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

/* Buttons */
.btn-add { background: linear-gradient(45deg,#0d6efd,#6610f2); color: #fff; border-radius: 50px; padding: 8px 20px; margin-bottom: 15px; }
.btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); color: #fff; }
.btn-edit, .btn-delete { border-radius: 50px; }

/* Table */
.table thead { background: #0d6efd; color: #fff; }
.table tbody tr:hover { background: rgba(0,0,0,0.05); }

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
    <h4>📚 Library Admin</h4>
    <a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../books/index.php"><i class="bi bi-book"></i> Quản lý sách</a>
    <a href="index.php"><i class="bi bi-people"></i> Sinh viên</a>
    <a href="../borrows/index.php"><i class="bi bi-journal-check"></i> Mượn/Trả sách</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <div class="header">
        <h2>Quản lý Sinh viên</h2>
        <p>Xin chào, <strong><?php echo $_SESSION['admin_username']; ?></strong> 👋</p>
    </div>

    <!-- Nút thêm và tìm kiếm -->
    <div class="d-flex justify-content-between mb-3">
        <a href="add.php" class="btn btn-add"><i class="bi bi-plus-circle"></i> Thêm Sinh viên</a>
        <form method="GET" class="d-flex">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control me-2" placeholder="Tìm kiếm tên, username, email">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tìm</button>
        </form>
    </div>

    <!-- Bảng sinh viên -->
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên đăng nhập</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=$offset+1; while($row=mysqli_fetch_assoc($result)){ ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success btn-edit"><i class="bi bi-pencil-square"></i></a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Bạn có chắc muốn xóa sinh viên này?');"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- PHÂN TRANG NÂNG CẤP -->
<nav class="mt-3">
  <ul class="pagination justify-content-center flex-wrap">
    <?php
    $adjacents = 2; // số trang hiện xung quanh trang hiện tại
    $prev = $page - 1;
    $next = $page + 1;

    // Nút Trước
    if($page > 1){
      echo '<li class="page-item"><a class="page-link" href="?page='.$prev.'&search='.urlencode($search).'">« Trước</a></li>';
    } else {
      echo '<li class="page-item disabled"><span class="page-link">« Trước</span></li>';
    }

    // Nếu cách trang 1 quá xa, hiển thị 1 và dấu ...
    if($page > $adjacents + 1){
      echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'">1</a></li>';
      if($page > $adjacents + 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Hiển thị các trang xung quanh trang hiện tại
    for($p = max(1, $page - $adjacents); $p <= min($total_pages, $page + $adjacents); $p++){
      $active = ($p == $page) ? ' active' : '';
      echo '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$p.'&search='.urlencode($search).'">'.$p.'</a></li>';
    }

    // Nếu còn trang cuối xa trang hiện tại, hiển thị dấu ... và trang cuối
    if($page < $total_pages - $adjacents){
      if($page < $total_pages - $adjacents -1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
      echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'">'.$total_pages.'</a></li>';
    }

    // Nút Sau
    if($page < $total_pages){
      echo '<li class="page-item"><a class="page-link" href="?page='.$next.'&search='.urlencode($search).'">Sau »</a></li>';
    } else {
      echo '<li class="page-item disabled"><span class="page-link">Sau »</span></li>';
    }
    ?>
  </ul>
</nav>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
