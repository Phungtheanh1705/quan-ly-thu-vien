<?php
session_start();
include "../config/db.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giới thiệu - Thư viện trực tuyến</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* --- MENU --- */
.navbar-nav .nav-link {
    position: relative;
    transition: all 0.3s ease;
    color: #fff !important;
}
.navbar-nav .nav-link:hover {
    transform: translateY(-5px);
    color: #ffc107 !important;
}
.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background-color: #ffc107;
    transition: width 0.3s;
}
.navbar-nav .nav-link:hover::after {
    width: 100%;
}
/* Đặc biệt cho "Giới thiệu" */
.navbar-nav .nav-link#gthieu:hover {
    color: #ff6b6b !important;
    transform: translateY(-7px);
}

/* --- GIỚI THIỆU --- */
.content-section {
    padding: 30px 15px; /* nhỏ hơn trước để không bị menu “to” */
    max-width: 900px;
    margin: 60px auto 60px auto;
    text-align: center;
    opacity: 0;
    transform: translateY(50px);
    transition: all 1s ease;
}
.content-section.visible {
    opacity: 1;
    transform: translateY(0);
}
.content-section h1 {
    margin-bottom: 20px;
    font-weight: 700;
}
.content-section p {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 15px;
}

/* --- HIỆU ỨNG HOVER TIÊU ĐỀ --- */
.hover-title {
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: 0.3s;
}
.hover-title::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -5px;
    left: 0;
    background-color: #ff6b6b;
    transition: 0.3s;
}
.hover-title:hover {
    color: #ff6b6b;
    transform: translateY(-5px);
}
.hover-title:hover::after {
    width: 100%;
}

/* --- FOOTER --- */
footer {
    padding: 30px 15px;
    background: #6610f2;
    color: #fff;
    text-align: center;
    font-weight: 500;
}
</style>
</head>
<body>

<!-- NAVBAR GIỐNG INDEX -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3" style="background: linear-gradient(90deg,#6610f2,#0d6efd);">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-journal-bookmark-fill"></i> Thư viện</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="nav-item"><a id="gthieu" class="nav-link active" href="about.php"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#books"><i class="bi bi-book-fill"></i> Sách</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php"><i class="bi bi-arrow-down-up"></i> Mượn/Trả sách</a></li>
      
      </ul>

      <?php if(isset($_SESSION['user_id'])): ?>
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_fullname']; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#">Thông tin tài khoản</a></li>
          <li><a class="dropdown-item text-danger" href="logout.php">Đăng xuất</a></li>
        </ul>
      </div>
      <?php else: ?>
        <a class="btn btn-light" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- NỘI DUNG GIỚI THIỆU -->
<section class="content-section">
    <h1 class="hover-title">Giới thiệu Thư viện trực tuyến</h1>
    <p>Thư viện trực tuyến được xây dựng nhằm hỗ trợ sinh viên và giảng viên tiếp cận nguồn tài liệu phong phú: sách học thuật, sách tham khảo, truyện, tài liệu nghiên cứu khoa học và kỹ năng sống.</p>
    <p>Chúng tôi cung cấp trải nghiệm mượn/trả sách trực tuyến nhanh chóng, dễ dàng, quản lý minh bạch và tiện lợi.</p>
    <p>Với giao diện thân thiện, cập nhật sách mới liên tục và các công cụ tìm kiếm thông minh, thư viện hướng tới trở thành nền tảng học tập hiện đại cho cộng đồng sinh viên Việt Nam.</p>
    <p>Đội ngũ quản lý luôn nâng cao chất lượng phục vụ, tổ chức sự kiện đọc sách, chia sẻ kiến thức và khuyến khích văn hóa đọc trong môi trường học thuật.</p>
</section>

<footer>
  &copy; <?php echo date('Y'); ?> Thư viện trực tuyến. All rights reserved.
</footer>

<script>
function revealOnScroll() {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(sec => {
        const top = sec.getBoundingClientRect().top;
        if(top < window.innerHeight - 100){
            sec.classList.add('visible');
        }
    });
}
window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);
</script>

</body>
</html>
