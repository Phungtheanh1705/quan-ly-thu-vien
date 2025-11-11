<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Lấy thông tin phiếu mượn
    $res = mysqli_query($conn, "SELECT * FROM borrows WHERE id='$id'");
    $borrow = mysqli_fetch_assoc($res);

    if($borrow && ($borrow['status']=='Đang mượn' || $borrow['status']=='Quá hạn')){
        $today = date('Y-m-d');
        mysqli_query($conn, "UPDATE borrows 
                             SET date_return='$today', status='Đã trả' 
                             WHERE id='$id'");
    }
}

header("Location: index.php");
exit();
?>
