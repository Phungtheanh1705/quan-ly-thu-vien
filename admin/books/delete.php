<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']); // bảo vệ SQL Injection

    // Lấy thông tin sách
    $res = mysqli_query($conn, "SELECT image FROM books WHERE id='$id'");
    $book = mysqli_fetch_assoc($res);

    if($book){
        $image = $book['image'];

        // Nếu image là file vật lý trong uploads, xóa file
        if($image && file_exists("../../uploads/books/".$image)){
            unlink("../../uploads/books/".$image);
        }

        // Xóa bản ghi khỏi database
        mysqli_query($conn, "DELETE FROM books WHERE id='$id'");
    }
}

header("Location: index.php");
exit();
?>
