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

// --- Lấy danh mục nếu filter ---
$category_filter = isset($_GET['cat']) ? trim($_GET['cat']) : '';
if($category_filter){
    $search_sql = $search_sql ? $search_sql." AND category='$category_filter'" : " WHERE category='$category_filter'";
}

// --- Cấu hình phân trang ---
$limit = 11; // số sách mỗi trang
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// --- Lấy tổng số sách để tính phân trang ---
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books $search_sql");
$total_row = mysqli_fetch_assoc($total_result);
$total_books_count = $total_row['total'];
$total_pages = ceil($total_books_count / $limit);

// --- Lấy sách theo trang ---
$books = [];
$result = mysqli_query($conn, "SELECT * FROM books $search_sql ORDER BY id DESC LIMIT $start, $limit");
while($row = mysqli_fetch_assoc($result)){
    $books[] = $row;
}

// --- Lấy danh sách thể loại ---
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thư viện trực tuyến</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
.hover-title { display: inline-block; position: relative; cursor: pointer; transition: all 0.3s ease; }
.hover-title::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -5px; left: 0; background-color: #ff6b6b; transition: width 0.3s ease; }
.hover-title:hover { transform: translateY(-5px); color: #ff6b6b; }
.hover-title:hover::after { width: 100%; }

.book-card h5, .book-card h4 { transition: all 0.3s ease; cursor: pointer; position: relative; }
.book-card h5::after, .book-card h4::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -3px; left: 0; background-color: #0d6efd; transition: width 0.3s ease; }
.book-card h5:hover, .book-card h4:hover { transform: translateY(-3px); color: #0d6efd; }
.book-card h5:hover::after, .book-card h4:hover::after { width: 100%; }
.book-card { transition: all 0.3s ease; }
.book-card:hover { transform: translateY(-5px) scale(1.03); box-shadow: 0 10px 20px rgba(0,0,0,0.25); }
.book-card img { width: 100%; object-fit: cover; }

.navbar-nav .nav-link { position: relative; transition: all 0.3s ease; color: #fff !important; }
.navbar-nav .nav-link:hover { transform: translateY(-5px); color: #ffc107 !important; }
.navbar-nav .nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -2px; left: 0; background-color: #ffc107; transition: width 0.3s; }
.navbar-nav .nav-link:hover::after { width: 100%; }

section { padding: 50px 15px; opacity: 0; transform: translateY(50px); transition: all 1s ease; }
section.visible { opacity: 1; transform: translateY(0); }

.carousel-item img { width: 100%; height: 450px; object-fit: cover; }

.pagination { justify-content: center; margin-top: 20px; flex-wrap: wrap; }

footer { padding: 30px 15px; background: #6610f2; color: #fff; text-align: center; font-weight: 500; }

.category-btn { text-decoration: none; transition: transform 0.3s, box-shadow 0.3s, background 0.3s; color: #fff; padding: 8px 20px; border-radius: 50px; background: #0d6efd; }
.category-btn:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.3); background: linear-gradient(135deg, #ff416c, #ff4b2b); }
.text-center { text-align: center !important; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3" style="background: linear-gradient(90deg,#6610f2,#0d6efd);">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-journal-bookmark-fill"></i> Thư viện</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a></li>
        <li class="nav-item"><a class="nav-link" href="#books"><i class="bi bi-book-fill"></i> Sách</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php"><i class="bi bi-arrow-down-up"></i> Mượn/Trả sách</a></li>
    
      </ul>

      <form class="d-flex me-3" method="GET" action="index.php">
        <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm sách..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-warning" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <?php if(isset($_SESSION['user_id'])): ?>
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_fullname']; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-fill"></i> Thông tin tài khoản</a></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
        </ul>
      </div>
      <?php else: ?>
        <a class="btn btn-light" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- SLIDE -->
<div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
  <div class="carousel-inner">
    <div class="carousel-item active"><img src="https://media.vov.vn/sites/default/files/styles/large/public/2024-04/rs-stockholm-public-library-alamy.jpg.jpg" class="d-block w-100" alt="Slide 1"></div>
    <div class="carousel-item"><img src="https://png.pngtree.com/background/20250206/original/pngtree-student-reading-book-at-library-photography-one-person-reading-photo-picture-image_14980018.jpg" class="d-block w-100" alt="Slide 2"></div>
    <div class="carousel-item"><img src="https://media.istockphoto.com/id/1503372066/photo/many-books-stacked-with-blurred-background-of-bookstore-full-of-books-photo-with-copyspace.jpg?s=612x612&w=0&k=20&c=FI9RseCP2ygrGafI-J5yQYpmd1JB1XZcy0kVELu8s1c=" class="d-block w-100" alt="Slide 3"></div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</div>

<!-- GIỚI THIỆU -->
<section id="gioithieu">
  <div class="container">
    <h2 class="hover-title">Giới thiệu thư viện</h2>
    <p>Thư viện trực tuyến cung cấp hàng ngàn đầu sách đa dạng từ học thuật đến văn học, khoa học kỹ thuật, kỹ năng sống, truyện thiếu nhi…</p>
    <p>Hệ thống cho phép sinh viên dễ dàng tra cứu, mượn và quản lý sách mọi lúc, mọi nơi. Chúng tôi không ngừng cập nhật tài nguyên mới và cải thiện trải nghiệm người dùng, mang đến môi trường học tập hiện đại và tiện lợi.</p>
    <a href="about.php" class="read-more">Xem thêm...</a>
  </div>
</section>

<!-- THỂ LOẠI -->
<section id="categories">
  <div class="container text-center">
    <h2 class="hover-title mb-4">Thể loại sách</h2>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <?php foreach($categories as $cat): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>" class="btn category-btn text-white px-4 py-2 rounded-pill d-flex align-items-center gap-2">
           <i class="bi bi-book-fill"></i> <?php echo htmlspecialchars($cat); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SÁCH NỔI BẬT -->
<section id="books">
  <div class="container">
    <div class="d-flex justify-content-center mb-4">
      <h2 class="hover-title">Sách nổi bật</h2>
    </div>
    
    <div class="row justify-content-center mb-4">
      <?php for($i=0; $i<3 && $i<count($books); $i++): ?>
      <div class="col-md-4 mb-4">
        <a href="books.php?id=<?php echo $books[$i]['id']; ?>" class="text-decoration-none text-dark">
          <div class="card book-card shadow-lg h-100">
            <?php
            $imgSrc = !empty($books[$i]['image']) ? $books[$i]['image'] : '../assets/default_book.png';
            if(!empty($books[$i]['image']) && !str_starts_with($books[$i]['image'], 'http')) $imgSrc = '../assets/'.$books[$i]['image'];
            ?>
            <img src="<?php echo $imgSrc; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($books[$i]['title']); ?>" style="height:320px; object-fit:cover;">
            <div class="card-body text-center">
              <h4><?php echo htmlspecialchars($books[$i]['title']); ?></h4>
              <p class="card-text"><?php echo htmlspecialchars($books[$i]['author']); ?></p>
              <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($books[$i]['category']); ?></small></p>
            </div>
          </div>
        </a>
      </div>
      <?php endfor; ?>
    </div>

    <div class="row">
      <?php for($i=3; $i<count($books); $i++): ?>
      <div class="col-md-3 mb-4">
        <a href="books.php?id=<?php echo $books[$i]['id']; ?>" class="text-decoration-none text-dark">
          <div class="card book-card h-100">
            <?php
            $imgSrc = !empty($books[$i]['image']) ? $books[$i]['image'] : '../assets/default_book.png';
            if(!empty($books[$i]['image']) && !str_starts_with($books[$i]['image'], 'http')) $imgSrc = '../assets/'.$books[$i]['image'];
            ?>
            <img src="<?php echo $imgSrc; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($books[$i]['title']); ?>" style="height:220px; object-fit:cover;">
            <div class="card-body">
              <h5><?php echo htmlspecialchars($books[$i]['title']); ?></h5>
              <p class="card-text"><?php echo htmlspecialchars($books[$i]['author']); ?></p>
              <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($books[$i]['category']); ?></small></p>
            </div>
          </div>
        </a>
      </div>
      <?php endfor; ?>
    </div>

    <!-- PHÂN TRANG -->
    <?php if($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center flex-wrap">

            <!-- Trang đầu << -->
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="index.php?page=1<?php if($search) echo '&search='.urlencode($search); ?>#books">
                    &laquo; Trang đầu
                </a>
            </li>

            <!-- Previous -->
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="index.php?page=<?php echo $page - 1; ?><?php if($search) echo '&search='.urlencode($search); ?>#books">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>

            <?php
            $range = 2;
            if ($page > 3) {
                echo '<li class="page-item"><a class="page-link" href="index.php?page=1';
                if($search) echo '&search='.urlencode($search);
                echo '#books">1</a></li>';
                if ($page > 4) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
                echo '<li class="page-item '.($i == $page ? 'active' : '').'">
                        <a class="page-link" href="index.php?page='.$i;
                        if($search) echo '&search='.urlencode($search);
                        echo '#books">'.$i.'</a>
                      </li>';
            }
            if ($page < $total_pages - 2) {
                if ($page < $total_pages - 3) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                echo '<li class="page-item"><a class="page-link" href="index.php?page='.$total_pages;
                if($search) echo '&search='.urlencode($search);
                echo '#books">'.$total_pages.'</a></li>';
            }
            ?>

            <!-- Next -->
            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="index.php?page=<?php echo $page + 1; ?><?php if($search) echo '&search='.urlencode($search); ?>#books">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>

            <!-- Trang cuối >> -->
            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="index.php?page=<?php echo $total_pages; ?><?php if($search) echo '&search='.urlencode($search); ?>#books">
                    Trang cuối &raquo;
                </a>
            </li>

        </ul>
    </nav>
    <?php endif; ?>

  </div>
</section>

<footer>
  &copy; 2025 Thư viện trực tuyến. All rights reserved.
</footer>

<script>
function revealOnScroll() {
  const elements = document.querySelectorAll('section');
  elements.forEach(el => {
    const rect = el.getBoundingClientRect();  
    if(rect.top < window.innerHeight - 100){
      el.classList.add('visible');
    }
  });
}
window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);
</script>
</body>
</html>
