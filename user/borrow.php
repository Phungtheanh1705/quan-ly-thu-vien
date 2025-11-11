<?php
session_start();
include "../config/db.php";

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])){
    $_SESSION['msg'] = "Bạn cần đăng nhập để xem danh sách sách cần trả.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Phân trang ---
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// --- Lấy tổng số sách đang mượn / quá hạn ---
$stmt_total = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM borrows 
    WHERE user_id=? AND (status='Đang mượn' OR status='Quá hạn')
");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$total_row = $stmt_total->get_result()->fetch_assoc();
$total_borrows = $total_row['total'];
$total_pages = ceil($total_borrows / $limit);

// --- Lấy danh sách sách mượn theo user ---
$stmt = $conn->prepare("
    SELECT br.id AS borrow_id, b.title, b.author, b.category, br.date_borrow, br.due_date, br.status
    FROM borrows br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id=? AND (br.status='Đang mượn' OR br.status='Quá hạn')
    ORDER BY br.date_borrow DESC
    LIMIT ?, ?
");
$stmt->bind_param("iii", $user_id, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

$borrows = [];
$reminder_msgs = [];
while($row = $result->fetch_assoc()){
    $borrows[] = $row;

    // Nhắc nhở nếu sách chưa trả
    if($row['status'] != 'Đã trả'){
        $due_date = new DateTime($row['due_date']);
        $today = new DateTime();
        $diff = (int)$today->diff($due_date)->format("%r%a");
        if($diff < 0){
            $reminder_msgs[] = "Sách '{$row['title']}' đã quá hạn trả!";
        } elseif($diff <= 3){
            $reminder_msgs[] = "Sách '{$row['title']}' sắp đến hạn trả (còn $diff ngày).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trả sách</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
.navbar-nav .nav-link { transition: 0.3s; color:#fff !important; }
.navbar-nav .nav-link:hover { color:#ffc107 !important; transform: translateY(-3px); }
.navbar-nav .nav-link.active { color:#ffc107 !important; font-weight:600; }

.table th, .table td { vertical-align: middle; }
.btn-return:hover { transform: translateY(-2px); }
.status-due { color:#dc3545; font-weight:600; } /* quá hạn */
.status-ok { color:#28a745; font-weight:600; } /* đang mượn */
.status-return { color:#198754; font-weight:600; } /* đã trả */
.pagination { justify-content:center; margin-top:20px; }
footer { padding:20px; text-align:center; background:#6610f2; color:#fff; margin-top:50px; }
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
        <li class="nav-item"><a class="nav-link" href="books.php"><i class="bi bi-book-fill"></i> Sách</a></li>
        <li class="nav-item"><a class="nav-link active" href="borrow.php"><i class="bi bi-arrow-return-left"></i> Trả sách</a></li>
        
      </ul>

      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_fullname']; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-fill"></i> Thông tin tài khoản</a></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h2 class="text-center mb-4">Danh sách sách cần trả</h2>

  <!-- Thông báo nhắc nhở -->
  <?php if(!empty($reminder_msgs)): ?>
    <?php foreach($reminder_msgs as $msg): ?>
      <div class="alert alert-warning"><?php echo htmlspecialchars($msg); ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-info"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Tiêu đề</th>
          <th>Tác giả</th>
          <th>Thể loại</th>
          <th>Ngày mượn</th>
          <th>Hạn trả</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php if(count($borrows)==0): ?>
          <tr><td colspan="7" class="text-center">Bạn không có sách cần trả.</td></tr>
        <?php else: ?>
          <?php foreach($borrows as $b): ?>
          <tr>
            <td><?php echo htmlspecialchars($b['title']); ?></td>
            <td><?php echo htmlspecialchars($b['author']); ?></td>
            <td><?php echo htmlspecialchars($b['category']); ?></td>
            <td><?php echo htmlspecialchars($b['date_borrow']); ?></td>
            <td><?php echo htmlspecialchars($b['due_date']); ?></td>
            <td class="<?php 
                echo $b['status']=='Quá hạn'?'status-due':($b['status']=='Đã trả'?'status-return':'status-ok'); 
            ?>"><?php echo $b['status']; ?></td>
            <td>
              <?php if($b['status'] != 'Đã trả'): ?>
              <a href="return_action.php?borrow_id=<?php echo $b['borrow_id']; ?>" class="btn btn-sm btn-success btn-return">
                <i class="bi bi-check-circle"></i> Trả sách
              </a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Phân trang -->
  <?php if($total_pages>1): ?>
  <nav>
    <ul class="pagination">
      <?php for($i=1; $i<=$total_pages; $i++): ?>
        <li class="page-item <?php if($i==$page) echo 'active'; ?>">
          <a class="page-link" href="borrow.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>

</div>

<footer>
  &copy; <?php echo date('Y'); ?> Thư viện trực tuyến. All rights reserved.
</footer>
</body>
</html>
