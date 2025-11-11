<?php
session_start();
include "../config/db.php";

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])){
    $_SESSION['msg'] = "Bạn cần đăng nhập để trả sách.";
    header("Location: login.php");
    exit;
}

// Kiểm tra borrow_id
if(!isset($_GET['borrow_id']) || empty($_GET['borrow_id'])){
    $_SESSION['msg'] = "ID mượn sách không hợp lệ.";
    header("Location: borrow.php");
    exit;
}

$borrow_id = (int)$_GET['borrow_id'];

// Lấy thông tin borrow
$stmt = $conn->prepare("SELECT book_id, status FROM borrows WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $borrow_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows==0){
    $_SESSION['msg'] = "Không tìm thấy sách mượn này hoặc bạn không có quyền trả.";
    header("Location: borrow.php");
    exit;
}

$row = $result->fetch_assoc();

if($row['status']=='Đã trả'){
    $_SESSION['msg'] = "Sách này đã được trả trước đó.";
    header("Location: borrow.php");
    exit;
}

$book_id = $row['book_id'];

// Bắt đầu transaction để đảm bảo atomic
$conn->begin_transaction();

try {
    // 1. Cập nhật trạng thái mượn thành Đã trả và lưu ngày trả
    $stmt_update = $conn->prepare("UPDATE borrows SET status='Đã trả', date_return=CURDATE() WHERE id=?");
    $stmt_update->bind_param("i", $borrow_id);
    $stmt_update->execute();

    // 2. Tăng lại số lượng sách trong bảng books
    $stmt_inc = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id=?");
    $stmt_inc->bind_param("i", $book_id);
    $stmt_inc->execute();

    // commit
    $conn->commit();

    $_SESSION['msg'] = "Trả sách thành công!";
    header("Location: borrow.php");
    exit;
} catch(Exception $e){
    $conn->rollback();
    $_SESSION['msg'] = "Có lỗi xảy ra khi trả sách: " . $e->getMessage();
    header("Location: borrow.php");
    exit;
}
?>
