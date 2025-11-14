<?php
// C:\xampp\htdocs\todolist\auth_check.php
session_start();

// Yêu cầu: Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
}
?>