<?php
// C:\xampp\htdocs\todolist\login.php
session_start(); // Bắt buộc phải có session_start() ở đầu file
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Dùng Prepared Statement để chống SQL Injection
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. Yêu cầu: Kiểm tra mật khẩu bằng password_verify
    if ($user && password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        // 3. Yêu cầu: Lưu thông tin vào Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Chuyển hướng đến trang chủ
        header("Location: index.php");
        exit;
    } else {
        $message = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng Nhập - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Đăng Nhập</h3>

                        <?php if(!empty($message)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập:</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
                        </form>
                         <p class="text-center mt-3">
                            Chưa có tài khoản? <a href="register.php">Đăng ký</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>