<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$incompleteTasks = [];
$completedTasks = [];
$tasksPerPage = 5; // Set the maximum number of tasks per page to 5

// Get the current page number from the query string, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $tasksPerPage;

try {
    $userId = $_SESSION['user_id'];

    // Get incomplete tasks with pagination
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 0 AND user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $tasksPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $incompleteTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get completed tasks with pagination
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 1 AND user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $tasksPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $completedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of incomplete tasks
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE is_completed = 0 AND user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $totalIncompleteTasks = $stmt->fetchColumn();

    // Get total number of completed tasks
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE is_completed = 1 AND user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $totalCompletedTasks = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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
            <div class="sidebar-item">New Task</div>
            <div class="sidebar-item"><a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?!');">Logout</a></div>
        </div>

        <div class="content">
            <div class="welcome-section">
                <p>Welcome,  <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>!</p>
            </div>

            <div class="section">
                <h3>New Task</h3>
                <form action="add_task.php" method="POST" class="add-task-form">
                    <input type="text" name="task_name" placeholder="Task Name" required>
                    <button type="submit" class="add-task-btn">Add Task</button>
                </form>
            </div>

            <div class="section">
                <h3>Task Lists</h3>
                <div class="task-list">
                    <?php if (!empty($incompleteTasks)): ?>
                        <?php foreach ($incompleteTasks as $task): ?>
                            <div class="task-item">
                                <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                                <div class="task-actions">
                                    <a href="complete_task.php?id=<?php echo $task['id']; ?>" class="btn-complete">Complete</a>
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn-delete">Delete</a>
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn-edit">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-tasks">No active tasks</div>
                    <?php endif; ?>
                </div>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ceil($totalIncompleteTasks / $tasksPerPage); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="section">
                <h3>Completed Tasks</h3>
                <div class="task-list completed-list">
                    <?php if (!empty($completedTasks)): ?>
                        <button class="clear-btn" onclick="clearCompletedTasks()">Clear</button>
                        <?php foreach ($completedTasks as $task): ?>
                            <div class="task-item completed">
                                <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                                <span class="task-date-completed">
                                    <span class="date-label">Date Completed:</span>
                                    <?php echo date('m/d/Y h:i A', strtotime($task['date_completed'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-tasks">No completed tasks</div>
                    <?php endif; ?>
                </div>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ceil($totalCompletedTasks / $tasksPerPage); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
    function clearCompletedTasks() {
        if (confirm('Are you sure you want to clear all completed tasks?')) {
            window.location.href = 'clear_completed_tasks.php';
        }
    }
    </script>
</body>
</html>