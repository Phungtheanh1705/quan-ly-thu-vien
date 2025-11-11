<?php
session_start();
include "../config/db.php";

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])){
    $_SESSION['msg'] = "Bạn cần đăng nhập để mượn sách.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra book_id
if(!isset($_GET['book_id']) || empty($_GET['book_id'])){
    $_SESSION['msg'] = "ID sách không hợp lệ.";
    header("Location: books.php");
    exit;
}

$book_id = intval($_GET['book_id']);

// Kiểm tra số lượng sách
$stmt = $conn->prepare("SELECT quantity FROM books WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    $_SESSION['msg'] = "Sách không tồn tại.";
    header("Location: books.php");
    exit;
}
$row = $result->fetch_assoc();
if($row['quantity'] <= 0){
    $_SESSION['msg'] = "Sách này hiện không còn để mượn.";
    header("Location: books.php");
    exit;
}

// Thêm vào borrows
$date_borrow = date("Y-m-d");
$due_date = date("Y-m-d", strtotime("+7 days")); // Hạn trả 7 ngày

$stmt_insert = $conn->prepare("INSERT INTO borrows (user_id, book_id, date_borrow, due_date, status) VALUES (?, ?, ?, ?, 'Đang mượn')");
$stmt_insert->bind_param("iiss", $user_id, $book_id, $date_borrow, $due_date);
$stmt_insert->execute();

// Cập nhật số lượng sách
$stmt_update = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id=?");
$stmt_update->bind_param("i", $book_id);
$stmt_update->execute();

$_SESSION['msg'] = "Mượn sách thành công!";
header("Location: borrow.php");
exit;
?>
