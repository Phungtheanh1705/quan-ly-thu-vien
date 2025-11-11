<?php
session_start();
include "../config/db.php"; // kết nối DB

// --- Xử lý tìm kiếm ---
$search = "";
$search_sql = "";
if(isset($_GET['search']) && !empty(trim($_GET['search']))){
    $search = trim($_GET['search']);
    $search_sql = " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%'";
}

// --- Lấy danh mục nếu có lọc ---
$category_filter = "";
if(isset($_GET['cat']) && !empty(trim($_GET['cat']))){
    $category_filter = trim($_GET['cat']);
    if($search_sql){
        $search_sql .= " AND category = '$category_filter'";
    } else {
        $search_sql = " WHERE category = '$category_filter'";
    }
}

// --- Cấu hình phân trang ---
$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// --- Lấy tổng số sách ---
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books $search_sql");
$total_row = mysqli_fetch_assoc($total_result);
$total_books_count = $total_row['total'];
$total_pages = ceil($total_books_count / $limit);

// --- Lấy sách ---
$books = [];
$result = mysqli_query($conn, "SELECT * FROM books $search_sql ORDER BY id DESC LIMIT $start, $limit");
while($row = mysqli_fetch_assoc($result)){
    $books[] = $row;
}

// --- Lấy danh mục ---
$categories = [];
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM books");
while($row = mysqli_fetch_assoc($cat_result)){
    $categories[] = $row['category'];
}

// --- Lấy thông tin user để hiển thị dropdown ---
$user_fullname = "";
if(isset($_SESSION['user_id'])){
    $stmt = $conn->prepare("SELECT fullname FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        $user_fullname = $row['fullname'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Danh sách sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* Hiệu ứng chữ hover */
.hover-title { display:inline-block; position:relative; cursor:pointer; transition:all 0.3s ease; }
.hover-title::after { content:''; position:absolute; width:0; height:2px; bottom:-5px; left:50%; background-color:#ff6b6b; transition:all 0.3s ease; transform:translateX(-50%); }
.hover-title:hover { color:#ff6b6b; }
.hover-title:hover::after { width:100%; }

/* Làm chắc chắn tiêu đề nằm giữa */
#books-list h2 {
    text-align: center !important;
    width: 100%;
    display: block;
}

/* Hiệu ứng sách */
.book-card h5 { transition:all 0.3s ease; cursor:pointer; position:relative; }
.book-card h5::after { content:''; position:absolute; width:0; height:2px; bottom:-3px; left:0; background-color:#0d6efd; transition:width 0.3s ease; }
.book-card h5:hover { transform:translateY(-3px); color:#0d6efd; }
.book-card h5:hover::after { width:100%; }
.book-card { transition:all 0.3s ease; }
.book-card:hover { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.2); }
.book-card img { height:220px; object-fit:cover; }

/* Menu */
.navbar-nav .nav-link { position:relative; transition:all 0.3s ease; color:#fff !important; }
.navbar-nav .nav-link:hover { transform:translateY(-5px); color:#ffc107 !important; }
.navbar-nav .nav-link::after { content:''; position:absolute; width:0; height:2px; bottom:-2px; left:0; background-color:#ffc107; transition:width 0.3s; }
.navbar-nav .nav-link:hover::after { width:100%; }

/* Thể loại */
#categories { padding:30px 15px; text-align:center; }
.category-btn { text-decoration:none; transition:0.3s; color:#fff; padding:8px 20px; border-radius:50px; background:#0d6efd; }
.category-btn:hover { transform:translateY(-5px); background:linear-gradient(135deg,#ff416c,#ff4b2b); }

/* Phân trang */
.pagination { justify-content:center; }

/* Footer */
footer { padding:30px 15px; background:#6610f2; color:#fff; text-align:center; font-weight:500; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3" style="background: linear-gradient(90deg,#6610f2,#0d6efd);">
  <div class="container">
    <a class="navbar-brand"><i class="bi bi-journal-bookmark-fill"></i> Thư viện</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a></li>
        <li class="nav-item"><a class="nav-link active" href="books.php"><i class="bi bi-book-fill"></i> Sách</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php"><i class="bi bi-arrow-down-up"></i> Mượn/Trả sách</a></li>
      </ul>

      <form class="d-flex me-3" method="GET" action="books.php">
        <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm sách..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-warning" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <?php if(isset($_SESSION['user_id'])): ?>
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_fullname); ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>"><i class="bi bi-person-fill"></i> Thông tin tài khoản</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- THỂ LOẠI -->
<section id="categories" class="my-4">
  <div class="container">
    <h2 class="hover-title mb-4">Thể loại sách</h2>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="books.php" class="btn category-btn">Tất cả</a>
      <?php foreach($categories as $cat): ?>
        <a href="books.php?cat=<?php echo urlencode($cat); ?>" class="btn category-btn"><?php echo htmlspecialchars($cat); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DANH SÁCH SÁCH -->
<section id="books-list">
  <div class="container my-5">
    <h2 class="hover-title mb-4" style="text-align:center;">Danh sách sách</h2>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-info text-center"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <div class="row">
      <?php if(count($books)==0): ?>
        <p class="text-center">Không tìm thấy sách nào.</p>
      <?php endif; ?>

      <?php foreach($books as $b): ?>
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <a href="book_detail.php?id=<?php echo $b['id']; ?>" style="text-decoration:none; color:inherit;">
          <div class="card book-card h-100">

            <?php
            $imgSrc = !empty($b['image']) ? $b['image'] : '../assets/default_book.png';
            if (!empty($b['image']) && !str_starts_with($b['image'], 'http')) {
                $imgSrc = '../assets/' . $b['image'];
            }
            ?>

            <img src="<?php echo $imgSrc; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($b['title']); ?>">

            <div class="card-body text-center">
              <h5><?php echo htmlspecialchars($b['title']); ?></h5>
              <p><?php echo htmlspecialchars($b['author']); ?></p>
              <p><small class="text-muted"><?php echo htmlspecialchars($b['category']); ?></small></p>
              <p><span class="badge bg-success">Còn: <?php echo $b['quantity']; ?></span></p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PHÂN TRANG -->
<?php if($total_pages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="books.php?page=<?php echo $page - 1; ?><?php if($category_filter) echo '&cat='.urlencode($category_filter); ?><?php if($search) echo '&search='.urlencode($search); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php
        $range = 2;
        if ($page > 3) {
            echo '<li class="page-item"><a class="page-link" href="books.php?page=1">1</a></li>';
            if ($page > 4) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
            echo '<li class="page-item '.($i == $page ? 'active' : '').'">
                    <a class="page-link" href="books.php?page='.$i;
                    if($category_filter) echo '&cat='.urlencode($category_filter);
                    if($search) echo '&search='.urlencode($search);
                    echo '">'.$i.'</a></li>';
        }
        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            echo '<li class="page-item"><a class="page-link" href="books.php?page='.$total_pages;
            if($category_filter) echo '&cat='.urlencode($category_filter);
            if($search) echo '&search='.urlencode($search);
            echo '">'.$total_pages.'</a></li>';
        }
        ?>

        <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
            <a class="page-link" href="books.php?page=<?php echo $page + 1; ?><?php if($category_filter) echo '&cat='.urlencode($category_filter); ?><?php if($search) echo '&search='.urlencode($search); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

  </div>
</section>

<footer>
  &copy; <?php echo date('Y'); ?> Thư viện trực tuyến. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
