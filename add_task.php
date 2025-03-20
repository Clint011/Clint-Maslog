<?php
session_start();

// checl user log in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// check form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the input
    if (empty($_POST['task_name'])) {
        // Redirect back with error
        header('Location: index.php?error=Task name cannot be empty');
        exit;
    }
    
    require_once 'db.php';
    
    try {
        
        $stmt = $conn->prepare("SHOW COLUMNS FROM tasks");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        
        $taskName = trim($_POST['task_name']);
        $currentTime = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'];
        
        
        $sql = "INSERT INTO tasks (task_name, is_completed, created_at, user_id) VALUES (:task_name, 0, :created_at, :user_id)";
        $params = [
            ':task_name' => $taskName,
            ':created_at' => $currentTime,
            ':user_id' => $userId
        ];
        
        // Execute 
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        // Redirect back to index page
        header('Location: index.php?success=Task added successfully');
        exit;
    } catch (PDOException $e) {
        // Redirect with error message
        header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
} else {
    // not a POST request, redirect to index
    header('Location: index.php');
    exit;
}
?>
<div class="section">
    <h3>Completed Tasks</h3>
    <div class="task-list-header">
        <?php if (!empty($completedTasks)): ?>
            <button class="clear-btn" onclick="clearCompletedTasks()">Clear</button>
        <?php endif; ?>
    </div>
    <div class="task-list completed-list">
        <?php if (!empty($completedTasks)): ?>
            <?php foreach ($completedTasks as $task): ?>
                <div class="task-item completed">
                    <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                    <span class="task-date-completed">
                        <span class="date-label">Date Completed:</span>
                        <?php echo htmlspecialchars($task['date_completed']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-tasks">No completed tasks</div>
        <?php endif; ?>
    </div>
</div>
<script>
function clearCompletedTasks() {
    if (confirm('Are you sure you want to clear all completed tasks?')) {
        window.location.href = 'clear_completed_tasks.php';
    }
}
</script>
<?php
?>