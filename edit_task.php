<?php

require_once 'db.php';


if (isset($_GET['id']) && !empty($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $task_id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            die("Task not found");
        }
    } catch (PDOException $e) {
        die("Error retrieving task: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $task_id = (int)$_POST['task_id'];
    $task_name = trim($_POST['task_name']);
    $userId = $_SESSION['user_id'];
    
    if (empty($task_name)) {
        $error = "Task name cannot be empty";
    } else {

        try {
            $stmt = $conn->prepare("UPDATE tasks SET task_name = :task_name WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':task_name', $task_name);
            $stmt->bindParam(':id', $task_id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            die("Error updating task: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-header">Todo List</div>
            <div class="sidebar-item"><a href="index.php" class="newtask-link">New Task</a></div>
            <div class="sidebar-item"><a href="logout.php" class="logout-link">Logout</a></div>
        </div>

        <div class="content">
            
            <div class="welcome-section">
                <p>Hello  <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>!</p>
            </div>
            
            <div class="editor-container">

                 <div class="edit-container">
                     <div class="edit-task-section">
                     <h2>Edit Task</h2>
                         <?php if (isset($error)): ?>
                     <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="edit_task.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
                    <button type="submit" name="update_task" class= "update-btn" >Update Task</button>
                    <a href="index.php" class="cancel-btn">Cancel</a>
                 </form>
                </div>
            </div>
        </div>
            
            
        </div>
    </div>
</body>
</html>