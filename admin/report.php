<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$today = date('Y-m-d');

// --- Tổng số sách ---
$total_books = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM books"))['total'];
$total_quantity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM books"))['total'];

// --- Tổng số sinh viên ---
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];

// --- Tổng lượt mượn ---
$total_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrows"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrows WHERE status='Đang mượn'"))['total'];
$total_returned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrows WHERE status='Đã trả'"))['total'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrows WHERE status='Đang mượn' AND due_date < '$today'"))['total'];

// --- Biểu đồ trạng thái mượn/trả ---
$borrow_labels = ['Đang mượn','Đã trả','Quá hạn'];
$borrow_data = [$total_pending, $total_returned, $total_overdue];
$borrow_colors = ['#ffc107','#198754','#dc3545'];

// --- Biểu đồ sinh viên ---
$users_borrowed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM borrows"))['total'];
$users_not_borrowed = $total_users - $users_borrowed;
$student_labels = ['Đã mượn sách','Chưa mượn sách'];
$student_data = [$users_borrowed, $users_not_borrowed];
$student_colors = ['#0d6efd','#ffc107'];

// --- Top 5 sách được mượn nhiều nhất ---
$top_books = mysqli_query($conn, "SELECT b.title, COUNT(br.id) as times_borrowed
                                 FROM borrows br 
                                 JOIN books b ON br.book_id=b.id 
                                 GROUP BY br.book_id 
                                 ORDER BY times_borrowed DESC 
                                 LIMIT 5");
$top_books_labels = [];
$top_books_data = [];
while($row = mysqli_fetch_assoc($top_books)){
    $top_books_labels[] = $row['title'];
    $top_books_data[] = $row['times_borrowed'];
}
$top_books_colors = ['#0d6efd','#6610f2','#ffc107','#198754','#dc3545'];

// --- Top 5 sinh viên mượn nhiều nhất ---
$top_users = mysqli_query($conn, "SELECT u.fullname, u.username, COUNT(br.id) as borrowed_count
                                  FROM borrows br 
                                  JOIN users u ON br.user_id=u.id 
                                  GROUP BY br.user_id 
                                  ORDER BY borrowed_count DESC 
                                  LIMIT 5");
$top_users_labels = [];
$top_users_data = [];
while($row = mysqli_fetch_assoc($top_users)){
    $top_users_labels[] = $row['fullname'].' ('.$row['username'].')';
    $top_users_data[] = $row['borrowed_count'];
}
$top_users_colors = ['#6610f2','#0d6efd','#198754','#ffc107','#dc3545'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Báo cáo tổng hợp</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
.sidebar { height: 100vh; position: fixed; width: 220px; background: linear-gradient(180deg, #0d6efd, #6610f2); color: #fff; padding-top: 20px; }
.sidebar h4 { text-align: center; margin-bottom: 20px; }
.sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin: 5px 10px; transition: all 0.3s; }
.sidebar a:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }
.content { margin-left: 240px; padding: 30px; }

.card-report { border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #fff; }
.bg-primary { background: #0d6efd; }
.bg-warning { background: #ffc107; color: #000; }
.bg-success { background: #198754; }
.bg-danger { background: #dc3545; }
.bg-info { background: #0dcaf0; color: #000; }

.icon-report { font-size: 2.5rem; opacity: 0.7; margin-bottom: 10px; }

.card-chart { border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

.chart-container { display: flex; flex-wrap: wrap; gap: 20px; }
.chart-box { flex: 1 1 300px; min-width: 300px; max-width: 500px; }
</style>
</head>
<body>
<div class="sidebar">
    <h4>📚 Library Admin</h4>
    <a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="books/index.php"><i class="bi bi-book"></i> Quản lý sách</a>
    <a href="users/index.php"><i class="bi bi-people"></i> Sinh viên</a>
    <a href="borrows/index.php"><i class="bi bi-journal-check"></i> Mượn/Trả sách</a>
    <a href="report.php"><i class="bi bi-bar-chart-line"></i> Báo cáo</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <h2 class="mb-4">📊 Báo cáo tổng hợp hệ thống</h2>

    <div class="row g-4">
        <div class="col-md-3"><div class="card-report bg-primary"><i class="bi bi-book icon-report"></i><h5>Tổng sách</h5><h2><?php echo $total_books; ?></h2></div></div>
        <div class="col-md-3"><div class="card-report bg-info"><i class="bi bi-stack icon-report"></i><h5>Tổng tồn</h5><h2><?php echo $total_quantity; ?></h2></div></div>
        <div class="col-md-3"><div class="card-report bg-warning"><i class="bi bi-people icon-report"></i><h5>Tổng sinh viên</h5><h2><?php echo $total_users; ?></h2></div></div>
        <div class="col-md-3"><div class="card-report bg-danger"><i class="bi bi-exclamation-triangle icon-report"></i><h5>Quá hạn</h5><h2><?php echo $total_overdue; ?></h2></div></div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-4"><div class="card-report bg-warning"><i class="bi bi-clock-history icon-report"></i><h5>Đang mượn</h5><h2><?php echo $total_pending; ?></h2></div></div>
        <div class="col-md-4"><div class="card-report bg-success"><i class="bi bi-check2-circle icon-report"></i><h5>Đã trả</h5><h2><?php echo $total_returned; ?></h2></div></div>
        <div class="col-md-4"><div class="card-report bg-primary"><i class="bi bi-journal-check icon-report"></i><h5>Tổng lượt mượn</h5><h2><?php echo $total_borrows; ?></h2></div></div>
    </div>

    <div class="chart-container mt-4">
        <div class="chart-box card-chart">
            <h5 class="mb-3">Trạng thái mượn/trả</h5>
            <canvas id="borrowChart" height="250"></canvas>
        </div>
        <div class="chart-box card-chart">
            <h5 class="mb-3">Sinh viên</h5>
            <canvas id="studentChart" height="250"></canvas>
        </div>
    </div>

    <div class="chart-container mt-4">
        <div class="chart-box card-chart">
            <h5 class="mb-3">Top 5 sách được mượn nhiều nhất</h5>
            <canvas id="topBooksChart" height="250"></canvas>
        </div>
        <div class="chart-box card-chart">
            <h5 class="mb-3">Top 5 sinh viên mượn nhiều sách nhất</h5>
            <canvas id="topUsersChart" height="250"></canvas>
        </div>
    </div>
</div>

<script>
const ctx1 = document.getElementById('borrowChart').getContext('2d');
new Chart(ctx1, { 
    type:'doughnut', 
    data:{
        labels: <?php echo json_encode($borrow_labels); ?>, 
        datasets:[{
            data: <?php echo json_encode($borrow_data); ?>, 
            backgroundColor: <?php echo json_encode($borrow_colors); ?>
        }] 
    }, 
    options:{responsive:true, plugins:{legend:{position:'bottom'}}} 
});

const ctx2 = document.getElementById('studentChart').getContext('2d');
new Chart(ctx2, { 
    type:'doughnut', 
    data:{
        labels: <?php echo json_encode($student_labels); ?>, 
        datasets:[{
            data: <?php echo json_encode($student_data); ?>, 
            backgroundColor: <?php echo json_encode($student_colors); ?>
        }] 
    }, 
    options:{responsive:true, plugins:{legend:{position:'bottom'}}} 
});

const ctx3 = document.getElementById('topBooksChart').getContext('2d');
new Chart(ctx3, { 
    type:'bar', 
    data:{
        labels: <?php echo json_encode($top_books_labels); ?>, 
        datasets:[{
            label:'Số lần mượn', 
            data: <?php echo json_encode($top_books_data); ?>, 
            backgroundColor: <?php echo json_encode($top_books_colors); ?>
        }] 
    }, 
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}} 
});

const ctx4 = document.getElementById('topUsersChart').getContext('2d');
new Chart(ctx4, { 
    type:'bar', 
    data:{
        labels: <?php echo json_encode($top_users_labels); ?>, 
        datasets:[{
            label:'Số sách mượn', 
            data: <?php echo json_encode($top_users_data); ?>, 
            backgroundColor: <?php echo json_encode($top_users_colors); ?>
        }] 
    }, 
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}} 
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
