<?php
// C:\xampp\htdocs\todolist\edit_task.php
require 'auth_check.php';
require 'db.php';

$user_id = $_SESSION['user_id'];
$message = '';
$task = null;



if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    
    if (!$task) {
      
        header("Location: index.php");
        exit;
    }
} else {
   
    header("Location: index.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $id = $_POST['id']; 

  
    $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    
    if ($stmt->execute([$title, $description, $due_date, $status, $id, $user_id])) {
       
        header("Location: index.php"); 
        exit;
    } else {
        $message = "Có lỗi xảy ra, không thể cập nhật!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chỉnh sửa công việc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Chỉnh sửa công việc</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form action="edit_task.php?id=<?php echo $task['id']; ?>" method="POST">
                            
                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề:</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả:</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày hết hạn:</label>
                                    <input type="date" name="due_date" class="form-control" value="<?php echo $task['due_date']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái:</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php if($task['status'] == 'pending') echo 'selected'; ?>>Đang chờ</option>
                                        <option value="in_progress" <?php if($task['status'] == 'in_progress') echo 'selected'; ?>>Đang làm</option>
                                        <option value="completed" <?php if($task['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Hủy bỏ</a>
                                <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>