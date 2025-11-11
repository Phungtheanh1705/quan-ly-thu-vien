<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);

// --- Tính phí trễ hạn ---
$overdue_result = mysqli_query($conn, "SELECT SUM(GREATEST(DATEDIFF(CURDATE(), due_date),0)) AS overdue_days 
                                       FROM borrows WHERE user_id=$user_id AND overdue > 0");
$overdue_data = mysqli_fetch_assoc($overdue_result);
$total_overdue_days = $overdue_data['overdue_days'] ?? 0;
$total_fee = $total_overdue_days * 1000;

// --- Xử lý cập nhật thông tin ---
if(isset($_POST['update_profile'])){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    mysqli_query($conn, "UPDATE users SET fullname='$fullname', email='$email', phone='$phone' WHERE id=$user_id");
    $_SESSION['user_fullname'] = $fullname;
    header("Location: profile.php");
    exit;
}

// --- Xử lý đổi mật khẩu ---
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if($current === $user['password']){
        if($new === $confirm){
            mysqli_query($conn, "UPDATE users SET password='$new' WHERE id=$user_id");
            echo "<script>alert('Đổi mật khẩu thành công!');</script>";
        } else {
            echo "<script>alert('Xác nhận mật khẩu mới không khớp!');</script>";
        }
    } else {
        echo "<script>alert('Mật khẩu hiện tại không đúng!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thông tin tài khoản - Thư viện</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
body { font-family:'Poppins', sans-serif; background:#f0f2f5; }

/* Navbar */
.navbar-nav .nav-link { color: #fff !important; transition: 0.3s; }
.navbar-nav .nav-link:hover { color:#ffc107 !important; transform:translateY(-3px); }

/* Profile Card */
.profile-card {
    max-width: 750px;
    margin: 50px auto;
    background:#fff;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.15);
    overflow:hidden;
    transition: transform 0.3s;
}
.profile-card:hover { transform:translateY(-5px); }

.profile-header {
    background: linear-gradient(90deg, #6610f2, #0d6efd);
    color:#fff;
    padding:30px 20px;
    text-align:center;
    position: relative;
    transition: background 0.5s;
}
.profile-header:hover { background: linear-gradient(90deg, #0d6efd, #6610f2); }

.profile-header img {
    width:120px;
    height:120px;
    border-radius:50%;
    border:4px solid #fff;
    object-fit:cover;
    margin-bottom:15px;
    transition: transform 0.3s;
}
.profile-header img:hover { transform: scale(1.15); }

.profile-body { padding: 20px 30px; }
.profile-body h4 { margin-bottom: 20px; }
.profile-body p { font-size: 1rem; margin-bottom: 12px; }

/* Badge phí trễ hạn */
.badge-overdue {
    background: #dc3545;
    font-size:0.95rem;
    padding:0.5em 0.75em;
    transition: transform 0.3s;
}
.badge-overdue:hover { transform: scale(1.1); }

/* Buttons */
.btn-edit { margin-right:10px; }

/* Progress bar */
.progress { height: 15px; border-radius: 10px; margin-top: 10px; }
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
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">Giới thiệu</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#books">Sách</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php">Mượn/Trả sách</a></li>
        <li class="nav-item"><a class="nav-link active" href="fines.php">Thanh toán / Phí trễ hạn</a></li>
      </ul>
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_fullname']); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item active" href="profile.php">Thông tin tài khoản</a></li>
          <li><a class="dropdown-item text-danger" href="logout.php">Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Profile card -->
<div class="profile-card">
    <div class="profile-header">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=6610f2&color=fff&size=120" alt="Avatar">
        <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
        <span class="badge badge-info">Username: <?php echo htmlspecialchars($user['username']); ?></span>
        <?php if($total_fee>0): ?>
            <span class="badge badge-overdue ms-2">Phí trễ hạn: <?php echo number_format($total_fee); ?>đ</span>
            <div class="progress mt-2">
                <div class="progress-bar bg-danger" role="progressbar" style="width:<?php echo min(100,$total_overdue_days*5); ?>%" aria-valuenow="<?php echo $total_overdue_days; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        <?php else: ?>
            <span class="badge bg-success ms-2">Không có phí trễ hạn</span>
        <?php endif; ?>
    </div>

    <div class="profile-body">
        <h4>Thông tin chi tiết</h4>
        <p><i class="bi bi-person-badge-fill"></i> Họ và tên: <strong><?php echo htmlspecialchars($user['fullname']); ?></strong></p>
        <p><i class="bi bi-at"></i> Username: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
        <p><i class="bi bi-envelope-fill"></i> Email: <strong><?php echo htmlspecialchars($user['email'] ?? '-'); ?></strong></p>
        <p><i class="bi bi-phone-fill"></i> Số điện thoại: <strong><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></strong></p>

        <div class="mt-4">
            <button class="btn btn-primary btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="bi bi-pencil-fill"></i> Chỉnh sửa</button>
            <button class="btn btn-warning btn-edit" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-lock-fill"></i> Đổi mật khẩu</button>
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay về Trang chủ</a>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Chỉnh sửa thông tin</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Họ và tên</label>
            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="update_profile" class="btn btn-primary">Lưu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Đổi mật khẩu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Mật khẩu hiện tại</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu mới</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Xác nhận mật khẩu mới</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="change_password" class="btn btn-warning">Đổi mật khẩu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
      </div>
    </form>
  </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> Thư viện trực tuyến. All rights reserved.
</footer>

</body>
</html>
