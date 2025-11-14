<?php
// C:\xampp\htdocs\todolist\index.php

// 1. Yêu cầu: Kiểm tra đăng nhập
require 'auth_check.php'; 
// auth_check.php đã có session_start()

require 'db.php'; // Kết nối CSDL

// Lấy user_id từ Session
$user_id = $_SESSION['user_id'];
$message = '';

// === LOGIC XỬ LÝ (CREATE, UPDATE, DELETE) ===

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // C (Create): Thêm công việc mới
    if (isset($_POST['add_task'])) {
        $title = $_POST['title'];
        $description = $_POST['description'] ?? null; // Có thể NULL
        $due_date = $_POST['due_date'] ?? null; // Có thể NULL
        
        // Gán user_id của người đang đăng nhập
        $sql = "INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $title, $description, $due_date]);
        $message = 'Đã thêm công việc mới!';
    }

    // U (Update): Cập nhật trạng thái
    if (isset($_POST['update_status'])) {
        $task_id = $_POST['task_id'];
        $status = $_POST['status'];

        // Kiểm tra xem task này có đúng là của user này không (bảo mật)
        $sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $task_id, $user_id]);
        $message = 'Cập nhật trạng thái thành công!';
    }

    // D (Delete): Xóa công việc
    if (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];

        // Kiểm tra xem task này có đúng là của user này không (bảo mật)
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$task_id, $user_id]);
        $message = 'Đã xóa công việc!';
    }
}

// === LOGIC TRUY VẤN (READ) ===



$filter_status = $_GET['status'] ?? 'all'; // Lấy từ URL
$sort_by = $_GET['sort'] ?? 'due_date_asc'; // Lấy từ URL

// Xây dựng câu lệnh SQL cơ bản
$sql_select = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];

// 1. Thêm điều kiện Lọc (Filter)
if ($filter_status != 'all') {
    $sql_select .= " AND status = ?";
    $params[] = $filter_status;
}

// 2. Thêm điều kiện Sắp xếp (Sort)
switch ($sort_by) {
    case 'due_date_desc':
        $sql_select .= " ORDER BY due_date DESC";
        break;
    case 'created_at_desc':
        $sql_select .= " ORDER BY created_at DESC";
        break;
    case 'status':
        $sql_select .= " ORDER BY status ASC, due_date ASC";
        break;
    case 'due_date_asc':
    default:
        $sql_select .= " ORDER BY due_date ASC";
        break;
}

$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute($params);
$tasks = $stmt_select->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="index.php">To-Do List</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          </ul>
          <span class="navbar-text me-3">
            Chào mừng, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
          </span>
          <a href="logout.php" class="btn btn-outline-light">Đăng Xuất</a>
        </div>
      </div>
    </nav>

    <div class="container mt-4">
        
        <?php if(!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title">Thêm công việc mới</h4>
                <form action="index.php" method="POST">
                    <input type="hidden" name="add_task" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tiêu đề:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">Ngày hết hạn:</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả (tùy chọn):</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Danh sách công việc của bạn</h4>
                
                <form action="index.php" method="GET" class="row g-3 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Lọc theo trạng thái:</label>
                        <select name="status" class="form-select">
                            <option value="all" <?php if($filter_status == 'all') echo 'selected'; ?>>Tất cả</option>
                            <option value="pending" <?php if($filter_status == 'pending') echo 'selected'; ?>>Đang chờ</option>
                            <option value="in_progress" <?php if($filter_status == 'in_progress') echo 'selected'; ?>>Đang làm</option>
                            <option value="completed" <?php if($filter_status == 'completed') echo 'selected'; ?>>Hoàn thành</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sắp xếp theo:</label>
                        <select name="sort" class="form-select">
                            <option value="due_date_asc" <?php if($sort_by == 'due_date_asc') echo 'selected'; ?>>Ngày hết hạn (Gần nhất)</option>
                            <option value="due_date_desc" <?php if($sort_by == 'due_date_desc') echo 'selected'; ?>>Ngày hết hạn (Xa nhất)</option>
                            <option value="created_at_desc" <?php if($sort_by == 'created_at_desc') echo 'selected'; ?>>Ngày tạo (Mới nhất)</option>
                            <option value="status" <?php if($sort_by == 'status') echo 'selected'; ?>>Trạng thái</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-info w-100">Lọc / Sắp xếp</button>
                    </div>
                </form>
                
                <hr>

                <div class="list-group">
                    <?php if (empty($tasks)): ?>
                        <div class="list-group-item">Bạn chưa có công việc nào.</div>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h5>
                                    <small>Hết hạn: <?php echo $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'N/A'; ?></small>
                                </div>
                                <p class="mb-1"><?php echo nl2br(htmlspecialchars($task['description'] ?? '')); ?></p>
                                
                                <div class="d-flex align-items-center mt-2">
    <form action="index.php" method="POST" class="d-inline-flex align-items-center me-2">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
             <option value="pending" <?php if($task['status'] == 'pending') echo 'selected'; ?>>Đang chờ</option>
             <option value="in_progress" <?php if($task['status'] == 'in_progress') echo 'selected'; ?>>Đang làm</option>
             <option value="completed" <?php if($task['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
        </select>
    </form>

    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm me-2">Sửa</a>

    <form action="index.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa công việc này?');">
        <input type="hidden" name="delete_task" value="1">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
    </form>
</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
    
    <footer class="text-center p-4 mt-5 bg-white">
        Bài tập PHP - Ứng dụng To-Do List
    </footer>
</body>
</html>