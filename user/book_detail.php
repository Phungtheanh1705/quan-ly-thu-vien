<?php
session_start();
include "../config/db.php";

// Kiểm tra ID sách từ GET
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $_SESSION['msg'] = "Sách không tồn tại.";
    header("Location: books.php");
    exit;
}

$book_id = (int)$_GET['id'];

// Lấy thông tin sách
$result = mysqli_query($conn, "SELECT * FROM books WHERE id = $book_id");
$book = mysqli_fetch_assoc($result);
if(!$book){
    $_SESSION['msg'] = "Sách không tồn tại.";
    header("Location: books.php");
    exit;
}

// Lấy danh sách thể loại (cho menu)
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
<title>Chi tiết sách - <?php echo htmlspecialchars($book['title']); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>

/* -------- PAGE TRANSITION -------- */
.page-transition {
    opacity: 0;
    transition: opacity 0.4s ease-in-out;
}
.page-transition.active {
    opacity: 1;
}

/* -------- NAVBAR -------- */
.navbar {
    background: linear-gradient(90deg,#6610f2,#0d6efd);
    box-shadow: 0px 5px 20px rgba(0,0,0,0.2);
}

.navbar-nav .nav-link {
    color: #fff !important;
    transition: 0.3s;
    font-weight: 500;
}
.navbar-nav .nav-link:hover {
    transform: translateY(-5px);
    color: #ffda79 !important;
}

/* -------- BOOK DETAIL CARD -------- */
.detail-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    animation: fadeInUp 0.4s ease-in-out;
}
@keyframes fadeInUp {
    from { opacity:0; transform: translateY(30px); }
    to { opacity:1; transform: translateY(0); }
}

/* TITLE EFFECT */
.hover-title {
    font-weight: bold;
    display: inline-block;
    position: relative;
    color: #3b3f92;
}
.hover-title::after {
    content: '';
    width: 60px;
    height: 5px;
    border-radius: 4px;
    background: linear-gradient(90deg, #ff416c, #ff4b2b);
    position: absolute;
    bottom: -10px;
    left: 0;
}

/* IMAGE ANIMATION */
.detail-img {
    border-radius: 18px;
    transition: transform .35s ease;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.detail-img:hover {
    transform: scale(1.05);
}

/* BUTTON CUSTOM STYLE */
.btn-custom {
    border-radius: 50px;
    padding: 10px 25px;
    font-weight: 600;
    transition: 0.3s ease;
}
.btn-custom:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.25);
}
.btn-gradient {
    background: linear-gradient(135deg,#6610f2,#0d6efd);
    color: white !important;
}

/* FOOTER */
footer {
    padding: 20px;
    background: #6610f2;
    color: #fff;
    text-align: center;
    margin-top: 50px;
    font-size: 15px;
    letter-spacing: 1px;
}

</style>
</head>

<body class="page-transition">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-journal-bookmark"></i> THƯ VIỆN SỐ</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNav">

      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a></li>
        <li class="nav-item"><a class="nav-link" href="books.php"><i class="bi bi-book"></i> Sách</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php"><i class="bi bi-arrow-left-right"></i> Mượn / Trả</a></li>
    
      </ul>

      <?php if(isset($_SESSION['user_id'])): ?>
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_fullname']; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
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


<!-- CHI TIẾT SÁCH -->
<section class="my-5">
  <div class="container detail-card">
    <div class="row align-items-center">

      <div class="col-md-5 text-center">
        <?php
          $imgSrc = !empty($book['image']) ? $book['image'] : '../assets/default_book.png';
          if(!empty($book['image']) && !str_starts_with($book['image'],'http')) $imgSrc = '../assets/'.$book['image'];
        ?>
        <img src="<?php echo $imgSrc; ?>" class="img-fluid detail-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
      </div>

      <div class="col-md-7 mt-4 mt-md-0">
        <h2 class="hover-title mb-4"><?php echo htmlspecialchars($book['title']); ?></h2>
        <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p><strong>Thể loại:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
        <p><strong>Kho còn:</strong> <span class="fw-bold text-success"><?php echo $book['quantity']; ?></span></p>
        <p class="mt-3"><strong>Mô tả:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>

        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($book['quantity'] > 0): ?>
                <a href="borrow_action.php?book_id=<?php echo $book['id']; ?>" class="btn btn-success btn-custom">
                    <i class="bi bi-book"></i> Mượn sách
                </a>
            <?php else: ?>
                <span class="text-danger d-block mb-3">Sách tạm hết</span>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn btn-secondary btn-custom"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập để mượn</a>
        <?php endif; ?>

        <a href="books.php" class="btn btn-gradient btn-custom ms-2"><i class="bi bi-arrow-left"></i> Quay lại</a>
      </div>

    </div>
  </div>
</section>

<footer>
  &copy; <?php echo date('Y'); ?> Thư viện trực tuyến – Designed by YOU.
</footer>

<script>
// PAGE TRANSITION EFFECT
document.addEventListener("DOMContentLoaded", function() {
    document.body.classList.add("active");
});

document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", function(e) {
        const url = this.getAttribute("href");

        if(!url || url.startsWith("#") || url.startsWith("javascript")) return;

        e.preventDefault();
        document.body.classList.remove("active");

        setTimeout(() => {
            window.location.href = url;
        }, 300);
    });
});
</script>

</body>
</html>
