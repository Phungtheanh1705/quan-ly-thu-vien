<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

$message = '';

// --- XỬ LÝ XÓA NHIỀU SÁCH ---
if(isset($_POST['delete_selected'])){
    if(!empty($_POST['book_ids'])){
        $ids = implode(',', array_map('intval', $_POST['book_ids']));
        mysqli_query($conn, "DELETE FROM books WHERE id IN ($ids)");
        $message = "Xóa thành công!";
    }
}

// --- TÌM KIẾM VÀ LỌC THEO THỂ LOẠI ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// --- PHÂN TRANG ---
$limit = 10;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- XÂY DỰNG CÂU SQL CHÍNH ---
$where = [];
if($search !== '') $where[] = "(title LIKE '%$search%' OR author LIKE '%$search%')";
if($category_filter !== '') $where[] = "category='$category_filter'";

$where_sql = $where ? "WHERE ".implode(' AND ', $where) : "";

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books $where_sql");
$total_row = mysqli_fetch_assoc($total_result);
$total_books = $total_row['total'];
$total_pages = ceil($total_books / $limit);

$books_result = mysqli_query($conn, "SELECT * FROM books $where_sql ORDER BY id DESC LIMIT $offset, $limit");
$books = [];
while($row = mysqli_fetch_assoc($books_result)){
    $books[] = $row;
}

// --- LẤY DANH SÁCH THỂ LOẠI ---
$categories = [];
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM books");
while($row = mysqli_fetch_assoc($cat_result)){
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
.table img {height:50px; object-fit:cover;}

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
.sidebar a {
    color: #fff;
    text-decoration: none;
    padding: 12px 20px;
    display: block;
    border-radius: 8px;
    margin: 5px 10px;
    transition: all 0.3s;
}
.sidebar a:hover {background: rgba(255,255,255,0.2); transform: translateX(5px);}

.content {margin-left: 240px; padding:30px;}

/* Nút Thêm sách & Thêm nhiều sách */
.btn-add {
    border-radius: 50px;
    padding: 8px 20px;
    background: linear-gradient(45deg,#0d6efd,#6610f2);
    color: #fff;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
    text-decoration: none;
}
.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    color: #fff;
}
</style>
</head>
<body>

<div class="sidebar">
    <h4>📚 Library Admin</h4>
    <a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php"><i class="bi bi-book"></i> Quản lý sách</a>
    <a href="../users/index.php"><i class="bi bi-people"></i> Sinh viên</a>
    <a href="../borrows/index.php"><i class="bi bi-journal-check"></i> Mượn/Trả sách</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <h2>Quản lý sách</h2>
    <?php if($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- NÚT THÊM SÁCH -->
    <div class="mb-3 d-flex gap-2">
        <a href="add.php" class="btn-add"><i class="bi bi-plus-circle"></i> Thêm sách</a>
        <a href="bulk_add.php" class="btn-add"><i class="bi bi-plus-square"></i> Thêm nhiều sách</a>
    </div>

    <!-- THANH TÌM KIẾM & LỌC -->
    <form method="GET" class="d-flex mb-3 justify-content-end">
        <input type="text" name="search" class="form-control me-2" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category" class="form-select me-2">
            <option value="">Tất cả thể loại</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat; ?>" <?php if($cat==$category_filter) echo 'selected'; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-warning" type="submit"><i class="bi bi-search"></i></button>
    </form>

    <!-- DANH SÁCH SÁCH -->
    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa những sách đã chọn?');">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="check_all"></th>
                <th>STT</th>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Tác giả</th>
                <th>Thể loại</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($books as $i=>$b): ?>
            <tr>
                <td><input type="checkbox" name="book_ids[]" value="<?php echo $b['id']; ?>"></td>
                <td><?php echo $offset+$i+1; ?></td>
                <td>
                    <?php
                    // --- Sửa hiển thị ảnh ---
                    if(!empty($b['image'])){
                        if(str_starts_with($b['image'], 'http')){
                            $imgSrc = $b['image']; // link trực tiếp
                        } else {
                            $imgSrc = '../../assets/'.$b['image']; // file nội bộ
                        }
                    } else {
                        $imgSrc = '../../assets/default_book.png';
                    }
                    ?>
                    <img src="<?php echo $imgSrc; ?>" alt="Ảnh sách">
                </td>
                <td><?php echo htmlspecialchars($b['title']); ?></td>
                <td><?php echo htmlspecialchars($b['author']); ?></td>
                <td><?php echo htmlspecialchars($b['category']); ?></td>
                <td><?php echo $b['quantity']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-pencil-square"></i></a>
                    <a href="delete.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa sách này?');"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" name="delete_selected" class="btn btn-danger"><i class="bi bi-trash3"></i> Xóa sách đã chọn</button>
    </form>

    <!-- PHÂN TRANG -->
<nav class="mt-3">
  <ul class="pagination justify-content-center flex-wrap">
    <?php
    $adjacents = 2;
    $prev = $page - 1;
    $next = $page + 1;

    if($page > 1){
      echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&category='.urlencode($category_filter).'">Trang đầu</a></li>';
      echo '<li class="page-item"><a class="page-link" href="?page='.$prev.'&search='.urlencode($search).'&category='.urlencode($category_filter).'">« Trước</a></li>';
    } else {
      echo '<li class="page-item disabled"><span class="page-link">Trang đầu</span></li>';
      echo '<li class="page-item disabled"><span class="page-link">« Trước</span></li>';
    }

    if($page > $adjacents + 1){
      echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&category='.urlencode($category_filter).'">1</a></li>';
      if($page > $adjacents + 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    for($p = max(1, $page - $adjacents); $p <= min($total_pages, $page + $adjacents); $p++){
      $active = ($p == $page) ? ' active' : '';
      echo '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$p.'&search='.urlencode($search).'&category='.urlencode($category_filter).'">'.$p.'</a></li>';
    }

    if($page < $total_pages - $adjacents){
      if($page < $total_pages - $adjacents -1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
      echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&category='.urlencode($category_filter).'">'.$total_pages.'</a></li>';
    }

    if($page < $total_pages){
      echo '<li class="page-item"><a class="page-link" href="?page='.$next.'&search='.urlencode($search).'&category='.urlencode($category_filter).'">Sau »</a></li>';
      echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&category='.urlencode($category_filter).'">Trang cuối</a></li>';
    } else {
      echo '<li class="page-item disabled"><span class="page-link">Sau »</span></li>';
      echo '<li class="page-item disabled"><span class="page-link">Trang cuối</span></li>';
    }
    ?>
  </ul>
</nav>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chọn tất cả checkbox
document.getElementById('check_all').addEventListener('change', function(){
    document.querySelectorAll('input[name="book_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
</body>
</html>
