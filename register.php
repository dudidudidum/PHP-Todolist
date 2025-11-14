<?php
/*
 * Tệp tin: C:\xampp\htdocs\todolist\register.php
 * Chức năng: Đăng ký tài khoản (Đã nâng cấp Validation phía Server)
 */
session_start(); 
require 'db.php';


if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit; 
}

$message = '';
$message_type = ''; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
  
    $username = trim($_POST['username']); 
    $password = $_POST['password']; 
    $email = trim($_POST['email']);
    
    $errors = []; 


    if (empty($username)) {
        $errors[] = "Tên đăng nhập là bắt buộc.";
    }
    
    if (empty($email)) {
        $errors[] = "Email là bắt buộc.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       
        $errors[] = "Email không đúng định dạng.";
    }
    
    if (empty($password)) {
        $errors[] = "Mật khẩu là bắt buộc.";
    } elseif (strlen($password) < 6) {
       
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
  

    
  
    if (empty($errors)) {
        
        
       
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            
            $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $hashed_password, $email])) {
                $message = 'Đăng ký thành công! <a href="login.php" class="alert-link">Đăng nhập ngay</a>';
                $message_type = 'success'; 
            } else {
                $message = 'Đã xảy ra lỗi.';
                $message_type = 'danger';
            }
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) { 
                $message = 'Tên đăng nhập hoặc Email này đã tồn tại!';
            } else {
                $message = 'Lỗi CSDL: ' . $e->getMessage();
            }
            $message_type = 'danger'; 
        }
    } else {
      
        $message = "Đăng ký thất bại:<br>" . implode("<br>", $errors);
        $message_type = 'danger'; 
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng Ký - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Đăng Ký Tài Khoản</h3>
                        
                        <?php if(!empty($message)): ?>
                            <div class="alert <?php echo ($message_type == 'success') ? 'alert-success' : 'alert-danger'; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập:</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng Ký</button>
                        </form>
                        <p class="text-center mt-3">
                            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>