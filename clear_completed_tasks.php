<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

try {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE is_completed = 1 AND user_id = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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