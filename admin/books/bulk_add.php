<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

$message = "";

if(isset($_POST['submit_csv'])){
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0){
        $file_tmp = $_FILES['csv_file']['tmp_name'];
        $file_name = $_FILES['csv_file']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if(strtolower($ext) != 'csv'){
            $message = "Vui lòng chọn file CSV!";
        } else {
            $handle = fopen($file_tmp, "r");
            if($handle !== FALSE){
                $row_count = 0;
                while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                    // Bỏ dòng đầu nếu có header
                    if($row_count == 0 && preg_match("/title/i", $data[0])){
                        $row_count++;
                        continue;
                    }

                    if(count($data) < 5){
                        $message .= "Dòng ".($row_count+1)." bị thiếu dữ liệu.<br>";
                        $row_count++;
                        continue;
                    }

                    $title = mysqli_real_escape_string($conn, trim($data[0]));
                    $author = mysqli_real_escape_string($conn, trim($data[1]));
                    $category = mysqli_real_escape_string($conn, trim($data[2]));
                    $quantity = intval($data[3]);
                    $image = mysqli_real_escape_string($conn, trim($data[4]));

                    $sql = "INSERT INTO books (title, author, category, quantity, image) 
                            VALUES ('$title', '$author', '$category', $quantity, '$image')";
                    if(!mysqli_query($conn, $sql)){
                        $message .= "Lỗi khi thêm sách: $title<br>";
                    }
                    $row_count++;
                }
                fclose($handle);
                if(empty($message)){
                    $message = "Import CSV thành công $row_count dòng!";
                }
            } else {
                $message = "Không thể mở file CSV.";
            }
        }
    } else {
        $message = "Vui lòng chọn file CSV.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nhập nhanh sách - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f1f3f6; }

/* Sidebar */
.sidebar { height: 100vh; position: fixed; width: 220px; background: linear-gradient(180deg, #0d6efd, #6610f2); color: #fff; padding-top: 20px; }
.sidebar h4 { font-weight: 600; text-align: center; margin-bottom: 20px; }
.sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin: 5px 10px; transition: all 0.3s; }
.sidebar a:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }

/* Content */
.content { margin-left: 240px; padding: 30px; }

/* Buttons */
.btn-back { margin-bottom: 15px; }
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
    <h2>Nhập nhanh sách từ CSV</h2>
    <a href="index.php" class="btn btn-secondary btn-back"><i class="bi bi-arrow-left"></i> Quay lại</a>

    <?php if($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Chọn file CSV</label>
            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
        </div>
        <button type="submit" name="submit_csv" class="btn btn-primary"><i class="bi bi-upload"></i> Nhập sách</button>
    </form>

    <hr>
    <h5>Mẫu CSV:</h5>
    <pre>
title,author,category,quantity,image
Lập Trình PHP,Nguyễn Văn B,CNTT,5,lap_trinh_php.jpg
Cơ Sở Dữ Liệu,Trần Văn C,CNTT,3,co_so_du_lieu.jpg
Kinh Tế Học,Lê Văn D,Kinh tế,10,kinh_te_hoc.jpg
    </pre>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
